<?php

namespace App\Eden\Managements\Services;


use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class Saisie_des_temps_service {

    /**
     * @param $options
     * @return array
     *
     * Chargement des dates affichés lors de la saisie des temps
     *
     */
    public function charger_dates($options){

        $date = $options['date'];

        $mode_affichage = $options['mode_affichage'];

        if($mode_affichage == 3) {

			$debut = date('Y-m-01', strtotime($date));
			$fin = date('Y-m-t', strtotime($date));

            $affichage_date = Variables::mois_de_lannee_format_complet(date('m', strtotime($date))).' '.date('Y', strtotime($date));
		}
        elseif($mode_affichage == 2){
            $debut = date('Y-m-d', strtotime('monday this week', strtotime($date)));
		    $fin = date('Y-m-d', strtotime("sunday this week",strtotime($date)));

            $affichage_date = traduction('interface.saisie_des_temps.semaine_du_au',
                null,
                [date('W', strtotime($date)),date('d/m/Y', strtotime($debut)),date('d/m/Y', strtotime($fin))]
            );
		}
        else{
            $debut = $date;
		    $fin = $date;

            $affichage_date = Variables::jours(date('N', strtotime($date))).' '.date('d', strtotime($date)).' '. Variables::mois_de_lannee_format_complet(date('m', strtotime($date))).' '.date('Y', strtotime($date));
		}

        $dates_pour_saisie = $this->dates_pour_saisie($debut,$fin);

        $informations_dates = array(
            'dates_pour_saisie' => $dates_pour_saisie,
            'affichage_date' => $affichage_date,
            'debut' => $debut,
            'fin' => $fin,
        );

        return $informations_dates;
    }

    /**
     *
     * Permet de récupérer les dates de saisies
     *
     */
    public function dates_pour_saisie($debut,$fin){

        $dates = [];

        $date_jour = $debut;

        $jours_actifs = fiche('saisie_des_temps')->structure_fiche()['options']['jours'];

        $dernier_jour = max($jours_actifs);

        $jours = Variables::tableau_jours();

        $desactivation_jours_indisponibilites = fiche('saisie_des_temps')->structure_fiche()['options']['desactiver_jours_indisponibilites'] ?? 0;

        if($desactivation_jours_indisponibilites)
            $indisponibilites = service('jour_indisponibilite')->indisponibilites($debut,$fin);

        while(strtotime($debut) <= strtotime($date_jour) && strtotime($date_jour) <= strtotime($fin)){

            if(in_array(date('N',strtotime($date_jour)) - 1,$jours_actifs)) {

                if($desactivation_jours_indisponibilites)
                    $jour_indisponibilite = $indisponibilites
                        ->where('date_debut', '<=' ,$date_jour)
                        ->where('date_fin', '>=' ,$date_jour)->values();

                $dates[] = array(
                    'date' => $date_jour,
                    'affichage' => $jours[date('N', strtotime($date_jour))].' '.date('d/m', strtotime($date_jour)),
                    'delimiteur' => $dernier_jour == date('N',strtotime($date_jour)) - 1,
                    'jour_indisponibilite' => $desactivation_jours_indisponibilites && $jour_indisponibilite->isNotEmpty() ? $jour_indisponibilite : false,
                );
            }

            $date_jour = date('Y-m-d',strtotime($date_jour . ' +1 day'));
        }

        return $dates;
    }

    /**
     * @param $options
     * @return array
     *
     * Charge les données nécessaires au fonctionnement de la saisie des temps
     *
     */
    public function chargement_donnees($options){

        //Récupération du modéle de requête des feuiles de temps
        $requete_feuille_de_temps = $this->requete_feuille_de_temps($options);

        $debut = $options['debut'];
        $fin = $options['fin'];
        $utilisateur_id = $options['utilisateur_id'];
        $type_element = $options['type_element'];
        $mode_affichage = $options['mode_affichage'];
        $element_id = $options['element_id'] ?? null;

        $informations = array(
            'debut' => $options['debut'],
            'fin' => $options['fin']
        );

        $desactiver_enregistrer_elements_selectionnes = !empty(fiche('saisie_des_temps')->structure_fiche()['options']['desactiver_enregistrer_elements_selectionnes']);

        $this->statut_saisie($informations, $options);

        $saisie_terminee = $informations['statut_saisie_terminee'] == 2;

        if(!$saisie_terminee && !$desactiver_enregistrer_elements_selectionnes) {

            // Récupération des elements séléctionnés par l'utilisateur précédemment
            $dates_elements = modele('sdt_date_elements_selectionnes')
                ->whereBetween('date_debut',[$debut,$fin])
                ->where('utilisateur_id',$utilisateur_id)
                ->where('type_element',$type_element)
                ->orderBy('date_fin')
                ->get();

            $date_elements = $dates_elements->keyBy('mode_affichage')[$mode_affichage] ?? null;
        }
        else
            $date_elements = null;

        // On merge sur les ids présent dans la feuille de temps pour avoir les données au complet
        $elements_ids = array_unique(
            array_merge($options['elements_ids'],$requete_feuille_de_temps->get()->pluck('element_id')->toArray())
        );

        if(!$desactiver_enregistrer_elements_selectionnes) {
            // Cas de la séléction d'un nouvel élément
            if (!empty($element_id)) {

                $management = empty($date_elements) ? management('sdt_date_elements_selectionnes') : management('sdt_date_elements_selectionnes', $date_elements->id, $date_elements);

                if (!in_array($element_id, $elements_ids))
                    $elements_ids[] = $element_id;

                $management->enregistre(array(
                    'date_debut' => $debut,
                    'date_fin' => $fin,
                    'utilisateur_id' => $utilisateur_id,
                    'type_element' => $type_element,
                    'elements_ids' => json_encode($elements_ids),
                    'mode_affichage' => $mode_affichage,
                ));

                $date_elements = $management->modele;
            } else if (!$saisie_terminee) {

                // Cas de l'initialisation
                // Si pas de séléction sur les dates indiqués on regarde la configuration la plus proche
                if ($dates_elements->isEmpty()) {

                    $date_elements_precedent = modele('sdt_date_elements_selectionnes')
                        ->where('utilisateur_id', $utilisateur_id)
                        ->where('type_element', $type_element)
                        ->where(function ($condition) use ($debut, $fin, $mode_affichage) {
                            $condition->where('date_debut', '<=', $debut)
                                ->where('date_fin', '>=', $fin)
                                ->orWhere('date_debut', '<', $debut)
                                ->where('mode_affichage', $mode_affichage);
                        })
                        ->orderBy('mode_affichage')
                        ->orderBy('date_fin', 'desc')
                        ->first();

                    if (!empty($date_elements_precedent))
                        $elements_ids = array_merge($elements_ids, json_decode($date_elements_precedent->elements_ids, true));
                } else {

                    foreach ($dates_elements as $date_elements_boucle) {
                        $elements_ids = array_merge($elements_ids, json_decode($date_elements_boucle->elements_ids, true));
                    }
                }
            }
        }

        $elements_ids = array_unique($elements_ids);

        $feuilles_de_temps = clone $requete_feuille_de_temps;

        // Cas de la séléction d'un nouvel élément
        if(!empty($element_id)) {

            $feuilles_de_temps = $feuilles_de_temps->where('feuille_de_temps.element_id',$element_id)->get();

            $element = modele($type_element)->where('id', $element_id)->first();

            $management_element = management($type_element, $element->id, $element);

            $element->affichage = $management_element->affiche_fiche_type();

            $element->feuilles_de_temps = $feuilles_de_temps->keyBy('date');

            return array(
                'element' => $element,
                'parametrage_date' => $date_elements
            );
        }

        $elements = modele($type_element)->whereIn('id', $elements_ids)->get();

        // On récupére toutes les feuilles de temps associés en les triant en fonction du type d'affichage
        $feuilles_de_temps = $feuilles_de_temps
            ->whereIn('feuille_de_temps.element_id',$elements_ids)
            ->get()
            ->groupBy('element_id')
            ->map(function($feuille_de_temps_groupes) use($options){
                if (in_array($options['type_saisie'],['categorie','activite'])){
                    $champ = $options['type_saisie'] == 'categorie' ? 'categorie_id' : 'activite_id';

                    return $feuille_de_temps_groupes->groupBy($champ)
                        ->map(function ($feuille_de_temps_groupes_categories) use($options){
                            return $feuille_de_temps_groupes_categories->keyBy('date');
                        });
                }
                else
                    return $feuille_de_temps_groupes->keyBy('date');
            });

        foreach($elements as $element){

            $management_element = management($type_element, $element->id, $element);

            $element->affichage = $management_element->affiche_fiche_type();

            $element->feuilles_de_temps = isset($feuilles_de_temps[$element->id]) ? $feuilles_de_temps[$element->id] : [];
        }

        $commentaire = isset(fiche('saisie_des_temps')->structure_fiche()['options']['gestion_commentaires']) &&
            fiche('saisie_des_temps')->structure_fiche()['options']['gestion_commentaires'] == 1 ?? 0;

        $commentaires = $commentaire ? $this->requete_commentaire_feuille_de_temps($options)
            ->whereIn('feuille_de_temps_commentaire.element_id',$elements_ids)
            ->get()
            ->groupBy('element_id')
            ->map(function($feuille_de_temps_groupes) use($options){
                if (in_array($options['type_saisie'],['categorie','activite'])){
                    $champ = $options['type_saisie'] == 'categorie' ? 'categorie_id' : 'activite_id';

                    return $feuille_de_temps_groupes->groupBy($champ)
                        ->map(function ($feuille_de_temps_groupes_categories) use($options){
                            return $feuille_de_temps_groupes_categories->keyBy('date');
                        });
                }
                else
                    return $feuille_de_temps_groupes->keyBy('date');
            }) : [];

        return array('elements' => $elements,'parametrage_date' => $date_elements,'commentaires' => $commentaires);
    }

    /**
     * @param $donnees
     * @param $options
     * @param $type_saisie
     * @return void
     *
     * Gére le chargement des données nécessaires en fonction du type de saisie des temps
     *
     */
    public function chargement_donnees_supplementaires(&$donnees,$options,$type_saisie){

        if(empty($options['element_id']))
            $donnees['utilisateur_a_valider'] = \DB::table('utilisateur_validation_saisie_temps')
                ->where('valeur', moi()->id)
                ->get()->pluck('cle_locale')->toArray();

        if($type_saisie == 'element')
            return;

        // Saisie par catégorie
        if($type_saisie == 'categorie' && empty($options['element_id']))
            $donnees['categories'] = $this->categories($options);
        //Saisie par activité
        elseif($type_saisie == 'activite'){

            $elements = isset($donnees['element']) ? collect([$donnees['element']]) : $donnees['elements'];

            // Récupération des activités
            $activites = modele('activite')
                ->where('activite.type_element',$options['type_element'])
                ->whereIn('activite.element_id',$elements->pluck('id'))
                ->leftJoin('activite_utilisateurs_id as au','activite.id','au.cle_locale')
                ->leftJoin('feuille_de_temps',function($join) use ($options){
                    $join->on('feuille_de_temps.activite_id','activite.id')
                        ->where('feuille_de_temps.utilisateur_id',$options['utilisateur_id'])
                        ->whereBetween('feuille_de_temps.date',[$options['debut'],$options['fin']])
                        ->whereRaw('COALESCE(feuille_de_temps.inactif,0) = 0');
                })
                ->select('activite.*')
                ->where(function($condition) use ($options){
                    $condition->whereNull('au.valeur')
                        ->orWhere('au.valeur',$options['utilisateur_id'])
                        ->orWhereNotNull('feuille_de_temps.id');
                })
                ->groupBy('activite.id')
                ->orderBy('activite.prioritaire','desc')
                ->get();

            foreach($activites as $activite){
                $activite->affichage = management('activite',$activite->id,$activite)->affiche();
            }

            $activites_par_elements = $activites->groupBy(['element_id','categorie_activite_id']);

            //Récupération des catégories et tri des activités par catégories
            $categories = modele('categorie_activite')
                ->where('activite.type_element',$options['type_element'])
                ->whereIn('activite.element_id',$elements->pluck('id'))
                ->join('activite','activite.categorie_activite_id','categorie_activite.id')
                ->leftJoin('activite_utilisateurs_id as au','activite.id','au.cle_locale')
                ->leftJoin('feuille_de_temps',function($join) use ($options){
                    $join->on('feuille_de_temps.activite_id','activite.id')
                        ->where('feuille_de_temps.utilisateur_id',$options['utilisateur_id'])
                        ->whereBetween('feuille_de_temps.date',[$options['debut'],$options['fin']])
                        ->whereRaw('COALESCE(feuille_de_temps.inactif,0) = 0');
                })
                ->where('activite.id','>',0)
                ->select('categorie_activite.*')
                ->where(function($condition) use ($options){
                    $condition->whereNull('au.valeur')
                        ->orWhere('au.valeur',$options['utilisateur_id'])
                        ->orWhereNotNull('feuille_de_temps.id');
              })
                ->groupBy('activite.id')
                ->get()->keyBy('id');

            foreach($categories as $categorie){
                $categorie->affichage = management('categorie_activite',$categorie->id,$categorie)->affiche();
            }

            foreach($elements as $element){

                $activites_par_categorie = [];

                if(!isset($activites_par_elements[$element->id]))
                    $activites_par_elements[$element->id] = [];

                foreach($activites_par_elements[$element->id] as $categorie_id => $activites){

                    $categorie = clone $categories[$categorie_id];

                    $categorie->activites = $activites;

                    $activites_par_categorie[] = $categorie;
                }

                $element->activites_par_categorie = $activites_par_categorie;
            }
        }
    }

    /**
     * @param $options
     * @return mixed
     *
     * Retourne un modéle de requête sur les feuille des temps avec les conditions qui correspondent au contexte
     *
     */
    public function requete_feuille_de_temps($options){

        $requete = modele('feuille_de_temps')
            ->whereBetween('feuille_de_temps.date', [$options['debut'],$options['fin']])
            ->where('feuille_de_temps.utilisateur_id', $options['utilisateur_id']);

        if(!empty($options['type_element']))
            $requete->where('feuille_de_temps.type_element',$options['type_element']);

        if($options['type_saisie'] == 'categorie')
            $requete->where('feuille_de_temps.categorie_id','>',0);
        
        if($options['type_saisie'] == 'activite'){
            $requete->select('feuille_de_temps.*','activite.categorie_activite_id as categorie_id')
                ->join('activite','activite.id','feuille_de_temps.activite_id')
                ->where('activite_id','>',0);
        }

        return $requete;
    }

    /**
     * @param $options
     * @return mixed
     *
     * Retourne un modéle de requête sur les commentaires de feuille des temps avec les conditions qui correspondent au contexte
     *
     */
    public function requete_commentaire_feuille_de_temps($options){

        $requete = modele('feuille_de_temps_commentaire')
            ->where('feuille_de_temps_commentaire.type_element',$options['type_element'])
            ->whereBetween('feuille_de_temps_commentaire.date', [$options['debut'],$options['fin']])
            ->where('feuille_de_temps_commentaire.utilisateur_id', $options['utilisateur_id'])
            ->where('feuille_de_temps_commentaire.mode_affichage', $options['mode_affichage']);

        if($options['type_saisie'] == 'categorie')
            $requete->where('feuille_de_temps_commentaire.categorie_id','>',0);

        if($options['type_saisie'] == 'activite'){
            $requete->select('feuille_de_temps_commentaire.*','activite.categorie_activite_id as categorie_id')
                ->join('activite','activite.id','feuille_de_temps_commentaire.activite_id')
                ->where('activite_id','>',0);
        }

        return $requete;
    }

    /**
     * @param $options
     * @return mixed|null
     *
     * Permet de déactiver un élément et de supprimer toutes les feuilles de temps associés
     *
     */
    public function desactiver_element($options){

        $utilisateur_id = $options['utilisateur_id'];
        $type_element = $options['type_element'];
        $elements_ids = $options['elements_ids'];
        $debut = $options['debut'];
        $fin = $options['fin'];
        $element_id = $options['element_id'];
        $mode_affichage = $options['mode_affichage'];
        $categorie_id = $options['categorie_id'];

        $feuilles_de_temps = $this->requete_feuille_de_temps($options)->where('feuille_de_temps.element_id', $element_id);

        $commentaires = $this->requete_commentaire_feuille_de_temps($options)->where('feuille_de_temps_commentaire.element_id', $element_id);

        $desactiver_enregistrer_elements_selectionnes = !empty(fiche('saisie_des_temps')->structure_fiche()['options']['desactiver_enregistrer_elements_selectionnes']);

        // Si on désactive uniquement une catégorie, le suivi de l'élément est toujours d'actualité
        if(!empty($categorie_id)) {

            $feuilles_de_temps->where('feuille_de_temps.categorie_id', $categorie_id);
            $commentaires->where('feuille_de_temps_commentaire.categorie_id', $categorie_id);
        }
        elseif(!$desactiver_enregistrer_elements_selectionnes){

            $dates_elements = modele('sdt_date_elements_selectionnes')
                ->whereBetween('date_debut', [$debut, $fin])
                ->where('utilisateur_id', $utilisateur_id)
                ->where('type_element', $type_element)
                ->get();

            $date_elements = $dates_elements->keyBy('mode_affichage')[$mode_affichage] ?? null;

            if ($date_elements == null && !empty($elements_ids)) {

                $management_date_elements = management('sdt_date_elements_selectionnes');
                $management_date_elements->enregistre(array(
                    'date_debut' => $debut,
                    'date_fin' => $fin,
                    'utilisateur_id' => $utilisateur_id,
                    'type_element' => $type_element,
                    'elements_ids' => json_encode($elements_ids),
                    'mode_affichage' => $mode_affichage
                ));

                $date_elements = $management_date_elements->modele;
            }

            foreach ($dates_elements as $date_elements_boucle) {

                $management = management('sdt_date_elements_selectionnes', $date_elements_boucle->id, $date_elements_boucle);

                $elements_ids = json_decode($date_elements_boucle->elements_ids, true);

                if (!in_array($element_id, $elements_ids))
                    continue;

                unset($elements_ids[array_search($element_id, $elements_ids)]);

                if (empty($elements_ids)) {

                    if (!empty($date_elements) && $date_elements->id == $management->modele->id)
                        $date_elements = null;

                    $management->supprime();
                } else
                    $management->enregistre(array(
                        'elements_ids' => json_encode(array_values($elements_ids))
                    ));
            }

        }

        $feuilles_de_temps = $feuilles_de_temps->get();

        foreach ($feuilles_de_temps as $feuille_de_temps) {

            management('feuille_de_temps', $feuille_de_temps->id, $feuille_de_temps)->supprime();
        }

        $commentaires = $commentaires->get();

        foreach ($commentaires as $commentaire) {

            management('feuille_de_temps_commentaire', $commentaire->id, $commentaire)->supprime();
        }

        return $date_elements ?? null;
    }

    /**
     * @param $parametres
     * @return array
     *
     * Chargement du bloc de récapitulatif
     *
     */
    public function recapitulatif($parametres){

        $date_debut = $parametres['date'];

        $recapitulatif = array();

        $jours = fiche('saisie_des_temps')->structure_fiche()['options']['jours'];

        sort($jours);
        $recapitulatif['jours'] = $jours;

        $debut = date('Y-m-01', strtotime($date_debut));

        $numero_jour = date('N',strtotime($debut));

        if($numero_jour > 1)
           $debut = date('Y-m-d',strtotime($debut.' previous monday'));

        $fin = date('Y-m-t', strtotime($date_debut));

        $numero_jour = date('N',strtotime($fin));

        if($numero_jour < 7)
           $fin  = date('Y-m-d',strtotime($fin.' next sunday'));

        $date_jour = $debut;

        $mois = date('m', strtotime($date_debut));

        $parametres = array_merge($parametres,array(
            'debut' => $debut,
            'fin' => $fin,
        ));

        $utilisateur = modele('utilisateur',$parametres['utilisateur_id']);

        $unite = fiche('saisie_des_temps')->structure_fiche()['options']['unite'];

        if($utilisateur->type_contrat == 1)
            $unite = 'heure';
        else if($utilisateur->type_contrat == 2)
            $unite = 'jour';

        $champ_de_duree = $unite == 'jour' ? 'duree_jours' : 'duree';

        unset($parametres['type_element']);

        $temps_par_jour = $this->requete_feuille_de_temps($parametres)
            ->select('date',DB::raw('SUM('.$champ_de_duree.') as duree'))
            ->groupBy('date')
            ->get()
            ->pluck('duree','date');

        $feuilles_de_temps_periode_validee = modele('feuille_de_temps_periode_validee')
            ->where('utilisateur_id',$parametres['utilisateur_id'])
            ->get();

        $feuilles_de_temps_periode_terminee = modele('feuille_de_temps_periode_terminee')
            ->where('utilisateur_id',$parametres['utilisateur_id'])
            ->get();

        $desactivation_jours_indisponibilites = fiche('saisie_des_temps')->structure_fiche()['options']['desactiver_jours_indisponibilites'] ?? 0;

        if($desactivation_jours_indisponibilites)
            $indisponibilites = service('jour_indisponibilite')->indisponibilites($debut,$fin);

        while(strtotime($debut) <= strtotime($date_jour) && strtotime($date_jour) <= strtotime($fin)){

            $temps = strtotime($date_jour);
            $numero_semaine = date('W',$temps);
            $semaine = intval($numero_semaine);
            $numero_jour = date('N',$temps)-1;

            if(empty($recapitulatif['semaines'][$semaine])){
                $recapitulatif['semaines'][$semaine] = array(
                    'debut' => date('d/m',strtotime($date_jour)),
                    'fin' => date('d/m',strtotime($date_jour.' next sunday')),
                    'jours' => array(),
                    'numero' => $numero_semaine
                );
            }

            if(in_array($numero_jour,$jours)) {

                $statut = !empty($feuilles_de_temps_periode_validee->where('date_debut', '<=', $date_jour)
                    ->where('date_fin', '>=', $date_jour)->first()) ? 2 :
                    (!empty($feuilles_de_temps_periode_terminee->where('date_debut', '<=', $date_jour)
                        ->where('date_fin', '>=', $date_jour)->first())? 1 : 0 );

                $dans_mois = date('m', strtotime($date_jour)) == $mois;

                if($desactivation_jours_indisponibilites)
                    $jour_indisponibilite = $indisponibilites
                        ->where('date_debut', '<=' ,$date_jour)
                        ->where('date_fin', '>=' ,$date_jour)->values();

                $classe = !$dans_mois || ($desactivation_jours_indisponibilites && $jour_indisponibilite->isNotEmpty()) ? 'jour_desactive' : ($statut == 2 ? 'temps_validee' : ($statut == 1 ? 'temps_terminee' : ''));

                $recapitulatif['semaines'][$semaine]['jours'][$numero_jour] = array(
                    'valeur' => $date_jour,
                    'nombre_heures' => $dans_mois ? $temps_par_jour[$date_jour] ?? 0 : 0,
                    'affichage' => date('d/m', $temps),
                    'dans_mois' => $dans_mois,
                    'statut' => $statut,
                    'classe' => $classe,
                );
            }

            $date_jour = date('Y-m-d',strtotime($date_jour . ' +1 day'));
        }

        return $recapitulatif;
    }

    /**
     * @param $informations
     * @param $parametres
     * @param $type
     * @return void
     *
     * Récupére le statut de saisie des temps (terminé,validé)
     *
     */
    public function statut_saisie(&$informations,$parametres,$type = 'terminee'){

        $table = 'feuille_de_temps_periode_'.$type;

        $feuille_de_temps_periode = modele($table)
            ->where('utilisateur_id',$parametres['utilisateur_id'])
            ->where('date_debut','<=',$informations['debut'])
            ->where('date_fin','>=',$informations['fin'])
            ->get();

        if($feuille_de_temps_periode->isNotEmpty()){

            $informations[$table] = $feuille_de_temps_periode;
            $informations['statut_saisie_'.$type] = 2;
        }
        else {
            $feuille_de_temps_periode = modele($table)
                ->where('utilisateur_id', $parametres['utilisateur_id'])
                ->where(function ($condition) use ($informations) {
                    $condition->where(function ($where) use ($informations) {
                        $where->where('date_fin', '>', $informations['fin'])
                            ->whereBetween('date_debut', [$informations['debut'], $informations['fin']]);
                    })->orWhere(function ($where) use ($informations) {
                        $where->whereBetween('date_fin', [$informations['debut'], $informations['fin']])
                            ->where(function ($sous_where) use ($informations) {
                                $sous_where->where('date_debut', '<', $informations['debut'])
                                    ->orWhereBetween('date_debut', [$informations['debut'], $informations['fin']]);
                            });
                    });
                })
                ->get();

            if ($feuille_de_temps_periode->isNotEmpty()) {
                $informations[$table] = $feuille_de_temps_periode;
                $informations['statut_saisie_' . $type] = 1;
            } else {
                $informations[$table] = [];
                $informations['statut_saisie_' . $type] = 0;
            }
        }
        
        $validateurs = \DB::table('utilisateur_validation_saisie_temps')
            ->where('cle_locale', $parametres['utilisateur_id'])
            ->get()->pluck('valeur')->toArray();

        $informations['validateurs'] = $validateurs;

        if($informations['statut_saisie_'.$type] > 0 && $type == 'terminee')
            $this->statut_saisie($informations,$parametres,'validee');
    }

    /**
     * @param $options
     * @param $type
     * @return array|true[]
     *
     * Effectue le changement de statut de saisie des temps (terminé,validé)
     *
     */
    public function changer_statut_saisie($options,$type){

        $nouveau_statut = $options['nouveau_statut'];

        $debut = $options['debut'];
        $fin = $options['fin'];
        $utilisateur_id = $options['utilisateur_id'];

        $informations = array(
            'debut' => $options['debut'],
            'fin' => $options['fin']
        );

        $this->statut_saisie($informations,$options);

        $ancien_statut = $informations['statut_saisie_'.$type];

        if($nouveau_statut == $ancien_statut)
            return array('retour' => true);

        $table = 'feuille_de_temps_periode_'.$type;

        //Vérification de la possibilité de modifier le statut
        if($type == 'terminee' && ($nouveau_statut == 0 || $nouveau_statut == 2) && moi()->id != $options['utilisateur_id'] && !in_array(moi()->id,$informations['validateurs']))
            return array('retour' => false, 'message' => traduction('messages.php.saisie_des_temps.erreur.interdiction_terminer_autre_utilisateur'));

        if($type == 'validee' && ($nouveau_statut == 0 || $nouveau_statut == 2) && !in_array(moi()->id,$informations['validateurs']))
            return array('retour' => false, 'message' => traduction('messages.php.saisie_des_temps.erreur..interdiction_valider_autre_utilisateur'));

        if($type == 'validee' && $informations['statut_saisie_terminee'] < 2 || $type == 'terminee' && !empty($informations['statut_saisie_validee']))
            return array('retour' => false, 'message' => traduction('messages.php.saisie_des_temps.erreur.changement_statut_impossible'));

        $date_debut_pour_enregistrement = $debut;
        $date_fin_pour_enregistrement = $fin;

        // Gestion des périodes validée ou terminée en fonction du nouveau statut
        foreach($informations[$table] as $periode_terminee){

            $management_periode = management($table,$periode_terminee->id,$periode_terminee);

            if($nouveau_statut == 0){

                if($periode_terminee->date_debut < $debut){

                    management($table)->enregistre(array(
                        'date_debut' => $periode_terminee->date_debut,
                        'date_fin' => date('Y-m-d',strtotime($debut.' -1 day')),
                        'date_'.$type => $management_periode->modele->date_terminee,
                        'utilisateur_id' => $utilisateur_id,
                        'utilisateur_validateur' => moi()->id
                    ));
                }

                if($periode_terminee->date_fin > $fin){

                    management('feuille_de_temps_periode_terminee')->enregistre(array(
                        'date_debut' => date('Y-m-d',strtotime($fin.' +1 day')),
                        'date_fin' => $periode_terminee->date_fin,
                        'date_'.$type => $management_periode->modele->date_terminee,
                        'utilisateur_id' => $utilisateur_id,
                        'utilisateur_validateur' => moi()->id
                    ));
                }

                $management_periode->supprime();
            }
            else if($nouveau_statut == 2 && $ancien_statut < 2){

                if($management_periode->modele->date_debut < $date_debut_pour_enregistrement)
                    $date_debut_pour_enregistrement = $management_periode->modele->date_debut;

                if($management_periode->modele->date_fin > $date_fin_pour_enregistrement)
                    $date_fin_pour_enregistrement = $management_periode->modele->date_fin;

                $management_periode->supprime();
            }

        }

        //Gestion des périodes validée ou terminée au moment d'une confirmation d'action
        if($nouveau_statut == 2 && $ancien_statut < 2){

            //Vérification si jonction possible, c'est à dire si la période d'avant est au même temps on ne fait plus qu'une ligne
            $jours = fiche('saisie_des_temps')->structure_fiche()['options']['jours'];

            $jour_debut = date('N',strtotime($date_debut_pour_enregistrement))-1;

            $jour_fin = date('N',strtotime($date_fin_pour_enregistrement))-1;

            $jour_avant = null;

            for($jour = $jour_debut-1;$jour != $jour_debut;$jour--){

                if($jour == -1)
                    $jour = 7;
                else if($jour_avant === null && in_array($jour,$jours))
                    $jour_avant = $jour;
            }

            $jour_apres = null;

            for($jour = $jour_fin+1;$jour != $jour_fin;$jour++){

                if($jour == 7)
                    $jour = -1;
                else if($jour_apres === null && in_array($jour,$jours))
                    $jour_apres = $jour;
            }

            $date_fin_avant = date('Y-m-d',strtotime($date_debut_pour_enregistrement .' last '.jddayofweek($jour_avant,1)));

            $periode_terminee_avant = modele($table)
                ->where('utilisateur_id',$utilisateur_id)
                ->whereBetween('date_fin',[$date_fin_avant,$date_debut_pour_enregistrement])
                ->first();

            if(!empty($periode_terminee_avant)){

                $date_debut_pour_enregistrement = $periode_terminee_avant->date_debut;

                management($table,$periode_terminee_avant->id,$periode_terminee_avant)->supprime();
            }

            $date_debut_apres = date('Y-m-d',strtotime($date_fin_pour_enregistrement .' next '.jddayofweek($jour_apres,1)));

            $periode_terminee_apres = modele($table)
                ->where('utilisateur_id',$utilisateur_id)
                ->whereBetween('date_debut',[$date_fin_pour_enregistrement,$date_debut_apres])
                ->first();

            if(!empty($periode_terminee_apres)){

                $date_fin_pour_enregistrement = $periode_terminee_apres->date_fin;

                management($table,$periode_terminee_apres->id,$periode_terminee_apres)->supprime();
            }

            management($table)->enregistre(array(
                'date_debut' => $date_debut_pour_enregistrement,
                'date_fin' => $date_fin_pour_enregistrement,
                'date_'.$type => date('Y-m-d'),
                'utilisateur_id' => $utilisateur_id,
                'utilisateur_validateur' => moi()->id
            ));
        }

        $informations = array(
            'debut' => $options['debut'],
            'fin' => $options['fin']
        );

        $this->statut_saisie($informations,$options);

        return array('retour' => true,'informations' => $informations);
    }

    /**
     *
     * Permet de récupérer des catégories
     *
     */
    public function categories($options){

        $categories = modele('categorie_activite')
            ->leftJoin('categorie_activite_utilisateurs_id as cu','categorie_activite.id','cu.cle_locale')
            ->leftJoin('feuille_de_temps',function($join) use ($options){
                $join->on('feuille_de_temps.categorie_id','categorie_activite.id')
                    ->where('feuille_de_temps.utilisateur_id',$options['utilisateur_id'])
                    ->whereBetween('feuille_de_temps.date',[$options['debut'],$options['fin']])
                    ->whereRaw('COALESCE(feuille_de_temps.inactif,0) = 0');
            })
            ->select('categorie_activite.*')
            ->where(function($condition) use ($options){
                $condition->whereNull('cu.valeur')
                    ->orWhere('cu.valeur',$options['utilisateur_id'])
                    ->orWhereNotNull('feuille_de_temps.id');
            })
            ->groupBy('categorie_activite.id')
            ->orderBy('ordre')->get();

        foreach ($categories as $categorie) {
            $categorie->affichage = management('categorie_activite', $categorie->id, $categorie)->affiche();
        }

        return $categories;
    }
}
