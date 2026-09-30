<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Calcul_gescom_management;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;
use App\Eden\Managements\Rapports\Serie_management;

use Illuminate\Http\Request;
use Carbon\Carbon;

/**
* Gestion des rapports
*/
class Suivi_jours_travailles_par_utilisateur_par_semaine_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
        $this->rapport->parametrage_rapport_libre['filtres_rapport'] = [
            'jour_travaille.utilisateur_id',
            'jour_travaille.date',
            'utilisateur.equipe',
            'utilisateur.profil_id',
        ];
    }
    
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
        $this->rapport->html = '';

        $management_element = management('jour_travaille');
        $this->rapport->applique_filtres($management_element);
        $this->rapport->recupere_valeurs_filtres($management_element);

        // On rajoute un filtre par défaut pour éviter l'affichage de trop de données
        foreach ($this->rapport->options as $id_filtre => $info) {
            if($info['nom_sql'] !== 'date')
                continue;

            if(!in_array($id_filtre,collect($this->rapport->valeurs_filtre)->pluck('id')->toArray())) {
                $this->rapport->valeurs_filtre($id_filtre, [
                    'variable' => 'depuis_janvier'
                ]);
            }
        }

		$titres = array(ucfirst(table_libre('utilisateur')->element));

        // on va chercher les utilisateurs et les temps saisis
        $semaines_concernes = modele('jour_travaille')
            ->leftJoin('utilisateur', 'utilisateur.id', '=', 'jour_travaille.utilisateur_id')
            ->where('statut','>',0)
            ->select(\DB::raw("jour_travaille.id,DATE_FORMAT(date,'%x-%v') as semaine"))
            ->whereRaw("DATE_FORMAT(date,'%x') = DATE_FORMAT(date,'%Y')")->groupBy('semaine');

        $serie_management = new Serie_management([],$this->rapport);

        $semaines_concernes = $serie_management->applique_filtres_du_rapport($semaines_concernes, $management_element);

        $semaines_concernes = $semaines_concernes->get();

        $jours_travailles = modele('jour_travaille')
            ->leftJoin('utilisateur', 'utilisateur.id', '=', 'jour_travaille.utilisateur_id')
            ->where('statut','>',0)
            ->whereIn(\DB::raw("DATE_FORMAT(date,'%x-%v')"),$semaines_concernes->pluck('semaine')->toArray())
            ->select(\DB::raw("SUM(total_jours) as temps_total,MIN(COALESCE(statut,0)) as statut, date,DATE_FORMAT(date,'%x-%v') as semaine, utilisateur_id"));

        $rapport_sans_date = clone $this->rapport;

        // On supprime le filtre de date qui est en index 2 pour bien gérer les semaines
        foreach($rapport_sans_date->valeurs_filtre as $index_filtre => $valeur_filtre){
            if($valeur_filtre['id'] == 2)
                unset($rapport_sans_date->valeurs_filtre[$index_filtre]);
        }

        $serie_management = new Serie_management([],$rapport_sans_date);

        $jours_travailles = $serie_management->applique_filtres_du_rapport($jours_travailles, $management_element);

        $jours_travailles = $jours_travailles->groupBy(\DB::raw("DATE_FORMAT(date,'%v')"), 'utilisateur_id')->get();

        $dates = $jours_travailles->pluck('date')->toArray();
        $dates = array_unique($dates);
        $periodes = !empty($dates) ? $serie_management->periodes('hebdomadaire', array_values($dates)[0], end($dates)) : [];

		// on va chercher les utilisateurs et les temps saisis
        $utilisateurs = modele('utilisateur')
            ->whereIn('id', $jours_travailles->pluck('utilisateur_id')->toArray())
            ->orderBy('prenom')
            ->get();

		foreach($periodes as &$periode) {

            $periode['annee_semaine'] = date('Y',strtotime($periode['date_fin'])).'-'.$periode['numero_semaine'];
            $titres[] = traduction('rapport.temps_saisi_par_utilisateur_par_semaine.colonnes.semaine')." ".$periode['numero_semaine'];
        }

		$this->rapport->titres($titres);

		foreach($utilisateurs as $utilisateur) {

            $ligne = [];
			$ligne[] = $utilisateur->prenom.' '.$utilisateur->nom;
            $temps = $jours_travailles->where('utilisateur_id', $utilisateur->id)->keyBy('semaine')->toArray();

            foreach($periodes as $periode_tmp) {

                $valeur_semaine = $temps[$periode_tmp['annee_semaine']] ?? ['temps_total' => 'N/A','statut' => 0];

                if(is_numeric($valeur_semaine['temps_total']))
                    $valeur_semaine['temps_total'] = montant($valeur_semaine['temps_total']);

                $classe = 'semaine_statut_'.$valeur_semaine['statut'];

                $ligne[] = [$valeur_semaine['temps_total'], $classe];
			}

			$this->rapport->ligne($ligne);
		}

        $this->rapport->forcer_generation = true;
		return $this->rapport->genere($ajax);
    }
}