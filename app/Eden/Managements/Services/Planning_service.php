<?php

namespace App\Eden\Managements\Services;

use App\Eden\Champs\Champ_liste_preenregistree;
use App\Eden\Managements\Cache_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Storage;

class Planning_service extends Planification_service {

    public $equipes_par_utilisateur = [];

	/**
	 *
	 * Recupère les données de la semaine voulue
	 *
	 */
	public function recuperer_donnees($parametres = array()) {

        $semaine_voulue = null;
        $nombre_de_semaines = 1;

        if(isset($parametres['semaine_voulue']))
            $semaine_voulue = $parametres['semaine_voulue'];

        if(isset($parametres['nombre_de_semaines']))
            $nombre_de_semaines = $parametres['nombre_de_semaines'];

        $parametres['type_taches_affichees'] = $parametres['type_taches_affichees'] ?? 'les_deux';

        if(!empty($parametres['initialisation'])) {

            $parametres_enregistres = parametre_utilisateur('parametres_planning', false, moi()->id);

            if(!empty($parametres_enregistres)) {

                try {

                    $parametres_enregistres = json_decode(base64_decode($parametres_enregistres), true);

                }
                catch(\Exception $e){

                    parametre_utilisateur('parametres_planning', null, moi()->id);

                }

                if (!empty($parametres_enregistres['filtres']))
                    $parametres['filtres'] = $parametres_enregistres['filtres'];

                if (!empty($parametres_enregistres['nombre_de_semaines']))
                    $nombre_de_semaines = $parametres_enregistres['nombre_de_semaines'];

                if (!empty($parametres_enregistres['type_taches_affichees']))
                    $parametres['type_taches_affichees'] = $parametres_enregistres['type_taches_affichees'];

            }
        }
        else{

            $parametres_a_enregistrer['nombre_de_semaines'] = $nombre_de_semaines;
            if(!empty($parametres['filtres']))
                $parametres_a_enregistrer['filtres'] = $parametres['filtres'];

            $parametres_a_enregistrer['type_taches_affichees'] = $parametres['type_taches_affichees'];

            parametre_utilisateur('parametres_planning', base64_encode(json_encode($parametres_a_enregistrer)), moi()->id);
        }

        $utilisateurs_id = $this->utilisateurs($parametres)->pluck('id')->toArray();

        $this->equipes_par_utilisateur = modele('equipe')
            ->select('equipe.*','utilisateur.id as utilisateur_id')
            ->join('utilisateur','utilisateur.equipe','equipe.id')
            ->whereIn('utilisateur.id',$utilisateurs_id)
            ->get()
            ->keyBy('utilisateur_id');

        $dates = $this->recuperer_dates($nombre_de_semaines,$semaine_voulue);

        $taches_par_utilisateur = $this->recuperer_taches_par_utilisateur($utilisateurs_id, $dates,$parametres);

        $filtres_planning = $this->filtres();

		return [
			'dates' => collect($dates),
			'filtre_utilisateurs' => $utilisateurs_id,
			'valeurs_filtres' => $parametres['filtres'] ?? [],
			'taches_par_utilisateur' => collect($taches_par_utilisateur),
			'filtres_planning' => collect($filtres_planning),
			'nombre_de_semaines' => $nombre_de_semaines,
			'type_taches_affichees' => $parametres['type_taches_affichees'],
			'utilisateurs' => modele('utilisateur')->whereIn('id',$utilisateurs_id)->get()->keyBy('id'),
		];
	}

	/**
	 *
	 * Recupère les dates pour une semaine voulue
	 *
	 */
	public function recuperer_dates($nombre_de_semaines,$semaine_voulue = null,$nombre_jours_par_semaine= null) {

		if($semaine_voulue == null)
			$semaine_voulue = Carbon::now()->startOfWeek();
		else
			$semaine_voulue = Carbon::createFromFormat('Y-m-d', lundi($semaine_voulue));

		$lundi              = $semaine_voulue->format('d/m');
		$debut_plage_de_date = $semaine_voulue->format('Y-m-d');

        if(empty($nombre_jours_par_semaine))
		    $nombre_jours_par_semaine = fonctionnalite('planning_nombre_jours');
		
		$semaines_affichees = $nombre_de_semaines-1;
		
		$derniers_jours = array();
		$derniers_jours_matin = array();
		$derniers_jours_aprem = array();

        $vendredi = $semaine_voulue->copy()->addWeeks($semaines_affichees)->addDays($nombre_jours_par_semaine - 1)->format('d/m');
		$fin_plage_de_date = $semaine_voulue->copy()->addWeeks($semaines_affichees)->addDays($nombre_jours_par_semaine - 1)->format('Y-m-d');
		
		$semaine_precedente = Carbon::createFromFormat('Y-m-d', $semaine_voulue->format('Y-m-d'))->subWeek()->format('Y-m-d');
		$semaine_actuelle = date('Y-m-d');
		$semaine_suivante   = Carbon::createFromFormat('Y-m-d', $semaine_voulue->format('Y-m-d'))->addWeek()->format('Y-m-d');

		//affichage de la semaine du xxxx au xxxx
		$debut_de_semaine = $semaine_voulue->format('Y-m-d');
		$fin_de_semaine   = $semaine_voulue->copy()->addDays($nombre_jours_par_semaine - 1)->format('Y-m-d');

        $numero_semaine_voulue = $semaine_voulue->format("W");

        $semaines = [];

        $affichage_demi_journee = fonctionnalite('planning_affichage_demi_journee');

        $semaine = [
            'numero_semaine' => $numero_semaine_voulue
        ];

		//utiliser pour le tableau dans la création d'une tâche dans la modale
		for($i = 0; $i < $nombre_jours_par_semaine; $i++) {
			
			$jour = Variables::jours($semaine_voulue->copy()->addDays($i)->format('N'));

			$semaine['dates'][]      = $jour.' '.$semaine_voulue->copy()->addDays($i)->format('d/m');
			$semaine['dates_format'][]      = $semaine_voulue->copy()->addDays($i)->format('Y-m-d');

            if($affichage_demi_journee) {
                $semaine['matins'][] = $semaine_voulue->copy()->addDays($i)->format('Y-m-d') . ' am';
                $semaine['apres_midi'][] = $semaine_voulue->copy()->addDays($i)->format('Y-m-d') . ' pm';
            }
			
			if($i + 1 == $nombre_jours_par_semaine) {
				
				$derniers_jours[] = $semaine_voulue->copy()->addDays($i)->format('d/m');

                if($affichage_demi_journee) {
                    $derniers_jours_matin[] = $semaine_voulue->copy()->addDays($i)->format('Y-m-d');
                    $derniers_jours_aprem[] = $semaine_voulue->copy()->addDays($i)->format('Y-m-d');
                }
			}
		}

        $semaines[] = $semaine;
		
		// on ajoute les semaines suivantes si nécessaire
		for($i_semaine=1; $i_semaine<=$semaines_affichees; $i_semaine++) {

            $numero_semaine = $semaine_voulue->copy()->addWeeks($i_semaine)->format("W");

            $semaine = [
                'numero_semaine' => $numero_semaine
            ];
			
            for ($i = 0; $i < $nombre_jours_par_semaine; $i++) {
				
				$jour = Variables::jours($semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('N'));

                $semaine['dates'][] = $jour.' '.$semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('d/m');
                $semaine['dates_format'][] = $semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('Y-m-d');

                if($affichage_demi_journee) {
                    $semaine['matins'][] = $semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('Y-m-d') . ' am';
                    $semaine['apres_midi'][] = $semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('Y-m-d') . ' pm';
                }
				
				if($i + 1 == $nombre_jours_par_semaine) {
					
					$derniers_jours[] = $semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('d/m');

                    if($affichage_demi_journee) {
                        $derniers_jours_matin[] = $semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('Y-m-d');
                        $derniers_jours_aprem[] = $semaine_voulue->copy()->addWeeks($i_semaine)->addDays($i)->format('Y-m-d');
                    }
				}
            }

            $semaines[] = $semaine;
        }
		
		$semaines_affichees++;
		
		return [
            'numero_semaine_voulue' => $numero_semaine_voulue,
			'aujourdhui' => Variables::jours(Carbon::now()->format('N')).' '.date('d/m'), //permet de mettre en surbrillance la date du jour
			'derniers_jours' => $derniers_jours, //permet de mettre une border sur le dernier jours de la semaine
			'derniers_jours_matin' => $derniers_jours_matin, //permet de mettre une border sur le dernier jours de la semaine
			'derniers_jours_aprem' => $derniers_jours_aprem, //permet de mettre une border sur le dernier jours de la semaine
			'semaines_affichees' => $semaines_affichees,
			'nombre_jours_par_semaine' => $nombre_jours_par_semaine,
			'entete' => [
				'lundi'              => $lundi,
				'vendredi'           => $vendredi,
				'semaine_precedente' => $semaine_precedente,
				'semaine_actuelle' => $semaine_actuelle,
				'semaine_suivante'   => $semaine_suivante,
			],
			'planning' => [
				'semaine' => $semaines,
			],
			'plage_de_dates' => [
				'debut_de_semaine' => $debut_de_semaine,
				'fin_de_semaine'   => $fin_de_semaine,
			],
			'plage_de_dates_recuperation_taches' => [
				'debut' => $debut_plage_de_date,
				'fin'   => $fin_plage_de_date,
			],
            'indisponibilites' => service('jour_indisponibilite')->indisponibilites_planning($semaines,$debut_plage_de_date,$fin_plage_de_date)
		];
	}

	public function recuperer_taches_par_utilisateur($utilisateurs_ids, $dates,&$parametres) {

		$taches_par_utilisateur = [];

        $debut = $dates['plage_de_dates_recuperation_taches']['debut'];
        $fin = $dates['plage_de_dates_recuperation_taches']['fin'];

		// les taches
		$taches = modele('tache')->with(['client', 'projet'])
                    ->where(function($r) use($debut,$fin) {
                        $r->where(function($r) use($debut,$fin) {
                            $r->where('date_de_debut','>=', $debut.' 00:00:00')
                                ->where('date_de_debut','<=',$fin.' 23:59:59');
                        });
                        $r->orWhere(function($r) use($debut,$fin) {
                            $r->where('date_de_fin','>=', $debut.' 00:00:00')
                                ->where('date_de_fin','<=',$fin.' 23:59:59');
                        });
                        $r->orWhere(function($r) use($debut,$fin) {
                            $r->where('date_de_debut','<=', $debut.' 00:00:00')
                                ->where('date_de_fin','>=',$fin.' 23:59:59');
                        });
                    })
                    ->whereIn('affectation',$utilisateurs_ids)
                    ->orderBy('date_de_debut')
                    ->orderBy('date_de_fin');

        if(!empty($parametres['filtres_tache'])){

            foreach($parametres['filtres_tache'] as $cle => $valeur){

                try{
                    $valeur_decode = json_decode($valeur,true);

                    $valeur = $valeur_decode;
                }
                catch (\Exception|\Throwable $e){}

                if(is_array($valeur) && !in_array(null, $valeur))
                    $taches->whereIn($cle,$valeur);
                else if(is_array($valeur))
                    $taches->where(function ($r) use ($cle, $valeur){
                        $r->whereIn($cle, $valeur)->orWhereNull($cle);
                    });
                else
                    $taches->where($cle,$valeur);
            }
        }

		// les congés
		$conges = modele('employe_demande_conge')
					->join('utilisateur', 'utilisateur.id', 'employe_demande_conge.employe_id')
                    ->whereIn('utilisateur.id',$utilisateurs_ids)
					->select('employe_demande_conge.*', 'utilisateur.id')
                    ->where(function($r) use($debut,$fin) {
                        $r->where(function($r) use($debut,$fin) {
                            $r->where('date_de_debut','>=', $debut.' 00:00:00')
                                ->where('date_de_debut','<=',$fin.' 23:59:59');
                        });
                        $r->orWhere(function($r) use($debut,$fin) {
                            $r->where('date_de_fin','>=', $debut.' 00:00:00')
                                ->where('date_de_fin','<=',$fin.' 23:59:59');
                        });
                        $r->orWhere(function($r) use($debut,$fin) {
                            $r->where('date_de_debut','<=', $debut.' 00:00:00')
                                ->where('date_de_fin','>=',$fin.' 23:59:59');
                        });
                    })
                    ->where(function($where){
                        $where->whereNull('statut')
                            ->orWhere('statut', '<', 2);
                    });

        $this->gestion_des_filtres($parametres['filtres'] ?? [],$taches,$conges);

        $affichage_demi_journee = fonctionnalite('planning_affichage_demi_journee');
        
        if($parametres['type_taches_affichees'] != 'conges'){

            $utilisateurs_autres_affectations_par_tache = modele('tache')
                ->select('utilisateur.*','tache.id as tache_id')
                ->join('tache as tache_affectation_groupe', function($join) {
                    $join->on('tache_affectation_groupe.groupe_affectations', 'tache.groupe_affectations')
                        ->on('tache_affectation_groupe.affectation', '!=', 'tache.affectation')
                        ->whereNotNull('tache_affectation_groupe.affectation');
                })
                ->join('utilisateur', 'utilisateur.id', 'tache_affectation_groupe.affectation')
                ->whereNotNull('tache.groupe_affectations')
                ->whereIn('tache.id',(clone $taches)->select('tache.id'))
                ->get()->groupBy('tache_id');

            $taches = $taches->get();

            foreach ($taches as $tache){

                if(empty($utilisateurs_autres_affectations_par_tache[$tache->id]))
                    $tache->autres_affectations_groupe = [];
                else{
                    $tableau_chaines_utilisateurs = [];

                    foreach($utilisateurs_autres_affectations_par_tache[$tache->id] as $utilisateur){
                        $tableau_chaines_utilisateurs[$utilisateur->id] = $utilisateur->prenom.' '.$utilisateur->nom;
                    }

                    $tache->autres_affectations_groupe = $tableau_chaines_utilisateurs;
                }
            }

            $taches = $taches->groupBy('affectation');
        }
        else 
            $taches = collect();

        $conges = $parametres['type_taches_affichees'] === 'tache' ? collect() : $conges->get()->groupBy('employe_id');

		foreach($utilisateurs_ids as $utilisateur_id) {

            foreach($dates['planning']['semaine'] as $semaine) {

                foreach ($semaine['dates_format'] as $jour) {

                    if($affichage_demi_journee) {

                        foreach (['am' => 'date_de_debut', 'pm' => 'date_de_fin'] as $temps => $champ) {

                            if ($temps == 'am') {
                                $debut = date('Y-m-d 00:00:00', strtotime($jour));
                                $fin = date('Y-m-d 12:59:59', strtotime($jour));
                            } else {
                                $debut = date('Y-m-d 13:00:00', strtotime($jour));
                                $fin = date('Y-m-d 23:59:59', strtotime($jour));
                            }

                            $jour_index = $jour . ' ' . $temps;

                            $taches_par_utilisateur[$utilisateur_id][$jour_index] = [];

                            if (isset($taches[$utilisateur_id])) {

                                $taches_a_ajouter = $taches[$utilisateur_id]->filter(function ($item) use ($debut, $fin) {

                                    $timestamp_debut = strtotime($item->date_de_debut);
                                    $timestamp_fin = strtotime($item->date_de_fin);

                                    return $timestamp_debut >= strtotime($debut) && $timestamp_debut <= strtotime($fin)
                                        || $timestamp_fin >= strtotime($debut) && $timestamp_fin <= strtotime($fin)
                                        || $timestamp_debut <= strtotime($debut) && $timestamp_fin >= strtotime($fin);
                                });

                                // on gère les taches
                                foreach ($taches_a_ajouter as $tache) {

                                    $tache_date_debut = new \DateTime(date('Y-m-d', strtotime($tache->date_de_debut)));
                                    $tache_date_fin = new \DateTime(date('Y-m-d', strtotime($tache->date_de_fin)));

                                    $nombre_de_jours_total = $tache_date_debut->diff($tache_date_fin)->days +1;

                                    if($nombre_de_jours_total > 1){
                                        $date_jour = new \DateTime($jour);
                                        $tache->numero_de_jours = ($tache_date_debut->diff($date_jour)->days + 1).'/'.$nombre_de_jours_total;
                                    }

                                    $taches_par_utilisateur[$utilisateur_id][$jour_index][] = $this->ajoute_tache_au_planning($tache, $utilisateur_id);
                                }
                            }

                            // on gère les congés
                            if (isset($conges[$utilisateur_id])) {

                                $conges_a_ajouter = $conges[$utilisateur_id]->filter(function ($item) use ($debut, $fin) {

                                    $timestamp_debut = strtotime($item->date_de_debut);
                                    $timestamp_fin = strtotime($item->date_de_fin . ($item->periode_de_fin == 1 ? ' 23:59:59' : ''));

                                    return $timestamp_debut >= strtotime($debut) && $timestamp_debut <= strtotime($fin)
                                        || $timestamp_fin >= strtotime($debut) && $timestamp_fin <= strtotime($fin)
                                        || $timestamp_debut <= strtotime($debut) && $timestamp_fin >= strtotime($fin);
                                });

                                foreach ($conges_a_ajouter as $conge) {

                                    $conge_date_debut = new \DateTime($conge->date_de_debut);
                                    $conge_date_fin = new \DateTime($conge->date_de_fin);

                                    $nombre_de_jours_total = $conge_date_debut->diff($conge_date_fin)->days +1;

                                    if($nombre_de_jours_total > 1){
                                        $date_jour = new \DateTime($jour);
                                        $conge->numero_de_jours = ($conge_date_debut->diff($date_jour)->days + 1).'/'.$nombre_de_jours_total;
                                    }

                                    $taches_par_utilisateur[$utilisateur_id][$jour_index][] = $this->ajoute_conge_au_planning($conge, $utilisateur_id);
                                }
                            }
                        }
                    }
                    else{

                        $taches_par_utilisateur[$utilisateur_id][$jour] = [];
                        $debut = date('Y-m-d 00:00:00', strtotime($jour));
                        $fin = date('Y-m-d 23:59:59', strtotime($jour));

                        if (isset($taches[$utilisateur_id])) {

                            $taches_a_ajouter = array_filter($taches[$utilisateur_id]->toArray(),function ($item) use ($debut, $fin) {

                                $date_de_debut = strtotime($item['date_de_debut']);
                                $date_de_fin = strtotime($item['date_de_fin']);
                                $debut = strtotime($debut);
                                $fin = strtotime($fin);

                               return $date_de_debut >= $debut && $date_de_debut <= $fin
                                   || $date_de_fin >= $debut && $date_de_fin <= $fin
                                   || $date_de_debut <= $debut && $date_de_fin >= $fin;
                            });

                            // on gère les taches
                            foreach (modele('tache')->hydrate($taches_a_ajouter) as $tache) {

                                $tache_date_debut = new \DateTime(date('Y-m-d', strtotime($tache->date_de_debut)));
                                $tache_date_fin = new \DateTime(date('Y-m-d', strtotime($tache->date_de_fin)));

                                $nombre_de_jours_total = $tache_date_debut->diff($tache_date_fin)->days +1;

                                if($nombre_de_jours_total > 1){
                                    $date_jour = new \DateTime($jour);
                                    $tache->numero_de_jours = ($tache_date_debut->diff($date_jour)->days + 1).'/'.$nombre_de_jours_total;
                                }

                                $taches_par_utilisateur[$utilisateur_id][$jour][] = $this->ajoute_tache_au_planning($tache, $utilisateur_id);
                            }
                        }

                        if (isset($conges[$utilisateur_id])) {

                            $jour_date = formate_date('Y-m-d', $jour);

                            $conges_a_ajouter = $conges[$utilisateur_id]->filter(function ($item) use ($debut,$fin) {

                                $date_de_debut = strtotime($item->date_de_debut);
                                $date_de_fin = strtotime($item->date_de_fin . ($item->periode_de_fin == 1 ? ' 23:59:59' : ''));
                                $debut = strtotime($debut);
                                $fin = strtotime($fin);

                                return $date_de_debut >= $debut && $date_de_debut <= $fin
                                    || $date_de_fin >= $debut && $date_de_fin <= $fin
                                    || $date_de_debut <= $debut && $date_de_fin >= $fin;
                            });

                            foreach ($conges_a_ajouter as $conge) {

                                $conge_date_debut = new \DateTime($conge->date_de_debut);
                                $conge_date_fin = new \DateTime($conge->date_de_fin);

                                $nombre_de_jours_total = $conge_date_debut->diff($conge_date_fin)->days +1;

                                if($nombre_de_jours_total > 1){
                                    $date_jour = new \DateTime($jour);
                                    $conge->numero_de_jours = ($conge_date_debut->diff($date_jour)->days + 1).'/'.$nombre_de_jours_total;
                                }

                                $taches_par_utilisateur[$utilisateur_id][$jour][] = $this->ajoute_conge_au_planning($conge, $utilisateur_id);
                            }
                        }
                    }
                }
            }
		}

		return $taches_par_utilisateur;
	}
	
	/**
	 * 
	 * Ajoute une tache au planning
	 * 
	 */
	protected function ajoute_tache_au_planning($tache, $utilisateur_id) {

        $tache_id = $tache->id;
		$autres_affectations_groupe = $tache->autres_affectations_groupe;
        unset($tache->autres_affectations_groupe);

		$tache_management = management('tache', $tache_id, $tache);

        $numero_de_jours = $tache->numero_de_jours ?? null;

        if(isset($tache->numero_de_jours))
            unset($tache->numero_de_jours);

		$tache = [
			'id' => $tache_id, 
			'label' => $tache->prive == 1 && $tache->affectation != moi()->id ? traduction('interface.calendrier.rdv_prive') : $tache_management->affiche_sur_planning(),
			'style' => 'background: '.maquette('background_tache').'; color: '.maquette('couleur_police_tache').';border: 1px solid' . maquette('background_tache'),
			'type' => 'tache',
			'numero_de_jours' => $numero_de_jours,
            'prive' => $tache->prive,
            'affectation' => $tache->affectation,
            'date_de_debut' => $tache->date_de_debut,
            'date_de_fin' => $tache->date_de_fin,
            'cree_par' => $tache->cree_par,
            'date_de_debut' => $tache->date_de_debut,
            'date_de_fin' => $tache->date_de_fin,
            'participant' => $tache->participant,
            'statut_participant' => $tache->statut_participant,
            'parent_id' => $tache->parent_id,
		];

        if(!empty($tache_management->modele->commentaire) && ($tache_management->modele->prive == 0 || ($tache_management->modele->prive == 1 && $tache_management->modele->affectation == moi()->id)))
            $tache['commentaire'] = html_entity_decode($tache_management->modele->commentaire);
        else
            $tache['commentaire'] = false;

        // on gère la couleur
        if(fonctionnalite('choix_couleur_tache_planning') == 'equipe') {

            $equipe = isset($this->equipes_par_utilisateur[$utilisateur_id]) ? $this->equipes_par_utilisateur[$utilisateur_id] : null;

            if (!empty($equipe) && !empty($equipe->couleur_fond))
                $tache['style'] = 'background: ' . $equipe->couleur_fond . '; color: ' . $equipe->couleur_police . ';border: 1px solid' . maquette('background_menus');
        }

        // on gère la couleur
        if(fonctionnalite('choix_couleur_tache_planning') == 'utilisateur') {

            $utilisateur = modele('utilisateur')->find($tache_management->modele->affectation);

            if (isset($utilisateur))
                $tache['style'] = (!empty($utilisateur->couleur_fond_tache) ? 'background: ' . $utilisateur->couleur_fond_tache . ';' : '') . (!empty($utilisateur->couleur_police_tache) ? 'color: ' . $utilisateur->couleur_police_tache . ';' : '') . ';border: 1px solid' . maquette('background_menus');
        }

        $type_tache_rdv = $tache_management->modele->type_tache_rdv;

        if(fonctionnalite('choix_couleur_tache_planning') == 'type_tache' && !empty($type_tache_rdv)) {

            if(empty($this->liste_504))
                $this->liste_504 = Cache_management::valeurs_liste_formatee(504);

            $valeurs_liste_formatees = $this->liste_504;

            if(!empty($valeurs_liste_formatees) && isset($valeurs_liste_formatees[$type_tache_rdv])){

                $style['background']='background: '.maquette('background_menus');
                $style['border']='border: 1px solid '.maquette('background_menus');
                $style['police']='color: #fff';

                if(isset($valeurs_liste_formatees[$type_tache_rdv]['couleur_fond']) && !empty($valeurs_liste_formatees[$type_tache_rdv]['couleur_fond'])){

                    $style['background'] = 'background: '.$valeurs_liste_formatees[$type_tache_rdv]['couleur_fond'];
                    $style['border'] = 'border: 1px solid '.$valeurs_liste_formatees[$type_tache_rdv]['couleur_fond'];
                }

                if(isset($valeurs_liste_formatees[$type_tache_rdv]['couleur_police']) && !empty($valeurs_liste_formatees[$type_tache_rdv]['couleur_police'])){

                    $style['police'] = 'color: '.$valeurs_liste_formatees[$type_tache_rdv]['couleur_police'];
                }

                $tache['style'] = implode(';',$style);
            }
        }
		
		return $tache;
	}
	
	/**
	 * 
	 * Ajoute une tache au planning
	 * 
	 */
	protected function ajoute_conge_au_planning($conge, $utilisateur_id) {
		
		$conge_management = management('employe_demande_conge', $conge->id,$conge);

        $numero_de_jours = $conge->numero_de_jours ?? null;

        if(isset($conge->numero_de_jours))
            unset($conge->numero_de_jours);
		
		$tache = [
			'id' => $conge->id,
			'label' => $conge_management->affiche_sur_planning(),
			'style' => 'background: '.maquette('background_tache').'; color: '.maquette('couleur_police_tache').';border: 1px solid' . maquette('background_tache'),
			'type' => 'conge',
            'statut' => $conge->statut,
            'valide_n1' => $conge->valide_n1,
            'numero_de_jours' => $numero_de_jours,
		];

        if(fonctionnalite('choix_couleur_tache_planning') == 'equipe') {

            $equipe = isset($this->equipes_par_utilisateur[$utilisateur_id]) ? $this->equipes_par_utilisateur[$utilisateur_id] : null;

            if (!empty($equipe) && !empty($equipe->couleur_fond))
                $tache['style'] = 'background: ' . $equipe->couleur_fond . '; color: ' . $equipe->couleur_police . ';border: 1px solid' . maquette('background_menus');
        }
		
		return $tache;
	}

    /**
	 *
	 * Recupère la liste des utilisateurs avec l'équipe s'il y a
	 *
	 */
	public function recupere_equipes_d_utilisateurs($utilisateurs) {

		$retour = [];

		$equipes = modele('equipe')->orderBy('nom')->get();

		foreach($equipes as $equipe) {

			if(count($utilisateurs->where('equipe', $equipe->id))) {
				$retour[$equipe->id]['couleur_fond'] = $equipe->couleur_fond;
				$retour[$equipe->id]['utilisateurs'] = [];
			}
		}

		if(count($utilisateurs->where('equipe', null)))
			$retour['non_attribue']['couleur_fond'] = '#ddd';

		foreach($utilisateurs as $utilisateur) {

			if($utilisateur->equipe != null)
				$retour[$utilisateur->equipe]['utilisateurs'][] = $utilisateur;
			else
				$retour['non_attribue']['utilisateurs'][] = $utilisateur;
		}

		return $retour;
	}

    /**
     * @param $parametres
     * @return mixed
     *
     * Plannin utilisateurs
     *
     */
    public function utilisateurs($parametres){

        $utilisateurs = modele('utilisateur')
            ->select('utilisateur.*')
            ->leftJoin('utilisateur_restrictions_erp', function($query) {
                $query->on('utilisateur_restrictions_erp.cle_locale', '=', 'utilisateur.id')
                    ->where('utilisateur_restrictions_erp.valeur', 2);
            })
            ->whereNull('valeur');

        $filtre_affectation = null;

        if(!empty($parametres['filtres'])) {

            foreach ($parametres['filtres'] as $filtre) {

                if ($filtre['id'] == 1)
                    $filtre_affectation = $filtre;
            }
        }

        if(!empty($filtre_affectation)) {
            $champ_libre = new Champ_libre();
            $champ_libre->type_element = 'utilisateur';
            $champ_libre->nom_sql = 'id';
            $champ_libre->nom = 'Utilisateur';
            $champ_libre->type = 20;
            $champ_libre->liste_choix = 1;

            $champ = new Champ_liste_preenregistree($champ_libre);
            $utilisateurs = $champ->applique_filtre_sur_requete($filtre_affectation['valeurs'], $utilisateurs)->get();
        }
        else
            $utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles($utilisateurs);

        return  $utilisateurs->keyBy('id');
    }

    /**
     *
     * Permet de gérer les filtres
     *
     */
    public function gestion_des_filtres($filtres,&$taches,&$demandes_cp){

        $filtres_a_afficher = $this->filtres();

        //Pour chaque filtre gérer on va appliquer le filtrage pour le champ de tache et le champ d'employe_demande_conge
        foreach($filtres as $filtre){

            $modele_filtre = null;

            foreach($filtres_a_afficher as $filtre_a_afficher){

                if($filtre_a_afficher['id'] == $filtre['id'])
                    $modele_filtre = $filtre_a_afficher;
            }

            if(empty($modele_filtre))
                continue;

            if(!empty($filtre['valeurs'])) {
                foreach ($modele_filtre['valeurs_dans_champs'] as $type_element => $nom_sql) {

                    $champ = champ_libre($type_element, $nom_sql);

                    if ($type_element == 'tache')
                        $taches = $champ->champ->applique_filtre_sur_requete($filtre['valeurs'], $taches);

                    if ($type_element == 'employe_demande_conge')
                        $demandes_cp = $champ->champ->applique_filtre_sur_requete($filtre['valeurs'], $demandes_cp);
                }
            }
        }

    }

    /**
     *
     * Permet de récupérer les filtres utilisés pour le planning
     *
     */
    public function filtres(){

        $filtres = array(
            array(
                'id' => 1,
                'nom_sql' => 'affectation',
                'type_element' => 'tache',
                'valeurs_dans_champs' => array(
                    'tache' => 'affectation',
                    'employe_demande_conge' => 'employe_id',
                ),
                'index_traduction' => 'champs_libres.tache.affectation.nom',
                'type_filtre' => management('tache')->champ('affectation')->type_filtre
            ),
            array(
                'id' => 2,
                'nom_sql' => 'type_tache_rdv',
                'type_element' => 'tache',
                'valeurs_dans_champs' => array(
                    'tache' => 'type_tache_rdv'
                ),
                'index_traduction' => 'champs_libres.tache.type_tache_rdv.nom',
                'type_filtre' => management('tache')->champ('type_tache_rdv')->type_filtre
            ),
        );

        foreach($filtres as &$filtre){

            $modele = clone management($filtre['type_element'])->champ($filtre['nom_sql'])->modele;

            $filtre['modele'] = $modele;
        }

        return $filtres;
    }
    
    public function impression($semaine_voulue, $parametres_initiaux){

        $chemins_pdf = [];

        $nombre_de_semaines = $parametres_initiaux['nombre_de_semaines'] ?? 1;

        $dates = $this->recuperer_dates($nombre_de_semaines,$semaine_voulue);

        $utilisateurs = $this->selection_utilisateurs_impression($semaine_voulue, $parametres_initiaux)->keyBy('id');

        $utilisateurs_id = $utilisateurs->pluck('id')->toArray();

        $taches = $this->recuperer_taches_par_utilisateur($utilisateurs_id, $dates,$parametres_initiaux);

        $equipes = modele('equipe')
            ->select('equipe.*')
            ->join('utilisateur','utilisateur.equipe','equipe.id')
            ->whereIn('utilisateur.id',$utilisateurs_id)
            ->groupBy('equipe.id')
            ->get();

        foreach ($parametres_initiaux['semaines_voulues'] as $semaine){

            $taches_de_la_semaine = $taches;
            $debut_semaine = $semaine['debut_semaine'];
            $fin_semaine = $semaine['fin_semaine'];

            foreach($taches_de_la_semaine as &$dates_semaine){

                $dates_semaine = array_filter($dates_semaine,function($date) use ($debut_semaine,$fin_semaine){
                    return $debut_semaine <= $date && $date <= $fin_semaine;
                },ARRAY_FILTER_USE_KEY);
            }

            $taches_par_equipe = [];

            foreach($equipes as $equipe){

                $taches_par_utilisateur = array_filter($taches_de_la_semaine,function($utilisateur_id) use ($utilisateurs,$equipe){
                    return $utilisateurs[$utilisateur_id]->equipe == $equipe->id;
                },ARRAY_FILTER_USE_KEY);

                if(!empty($taches_par_utilisateur))
                    $taches_par_equipe[] = [
                        'nom_html' => '<div style="background:' . $equipe->couleur_fond . ';color:' . $equipe->couleur_police . ';">' . $equipe->nom . '</span>',
                        'taches' => $taches_par_utilisateur
                    ];
            }

            $taches_par_utilisateur_sans_equipe = array_filter($taches_de_la_semaine,function($utilisateur_id) use ($utilisateurs){
                    return empty($utilisateurs[$utilisateur_id]->equipe);
                },ARRAY_FILTER_USE_KEY);

            if(!empty($taches_par_utilisateur_sans_equipe))
                $taches_par_equipe[] = [
                    'nom_html' => '<div>' . traduction('composant.planning.sans_equipe') . '</div>',
                    'taches' => $taches_par_utilisateur_sans_equipe
                ];

            $donnees_planning = [
                'logo_application' => $parametres_initiaux['logo_application'],
                'numero_semaine' => date_create_from_format('Y-m-d', $debut_semaine)->format('W'),
                'taches_par_equipe' => $taches_par_equipe,
                'utilisateurs' => $utilisateurs,
                'dates' => $this->recuperer_dates(1,$debut_semaine),
                'date_debut' => [
                    'format_fr' => date('d/m/Y', strtotime($debut_semaine)),
                    'format_us' => $debut_semaine,
                ],
                'date_fin' => [
                    'format_fr' => date('d/m/Y', strtotime($fin_semaine)),
                    'format_us' => $fin_semaine,
                ]
            ];

            $pdf = \PDF::loadView('eden::pdf.planning',$donnees_planning)->setPaper('a4', 'landscape');

            $options = $pdf->getDomPDF()->getOptions();
            $options->set('isRemoteEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->setOptions($options);

            $chemin_pdf = 'public/planning/planning_semaine_' . date('Ymd',strtotime($debut_semaine)) . '.pdf';

            Storage::put($chemin_pdf, $pdf->output());

            $chemins_pdf[] = storage_path('app/' .$chemin_pdf);
        }
        
        $nom_du_pdf = 'planning_' . $semaine_voulue . '.pdf';

        return [$chemins_pdf,$nom_du_pdf];
    }
}
