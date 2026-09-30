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
class Temps_saisi_par_utilisateur_par_semaine_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
        $this->rapport->parametrage_rapport_libre['filtres_rapport'] = [
            'feuille_de_temps.utilisateur_id',
            'feuille_de_temps.date',
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

        $management_element = management('feuille_de_temps');
        $this->rapport->applique_filtres($management_element);
        $this->rapport->recupere_valeurs_filtres($management_element);

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
        $feuilles_de_temps = modele('feuille_de_temps')
        ->leftJoin('utilisateur', 'utilisateur.id', '=', 'feuille_de_temps.utilisateur_id')
        ->select(\DB::raw("SUM(duree) as temps_total, date,DATE_FORMAT(date,'%u') as semaine, utilisateur_id"))
        ->having('temps_total','>',0);

        $serie_management = new Serie_management([],$this->rapport);

        $feuilles_de_temps = $serie_management->applique_filtres_du_rapport($feuilles_de_temps, $management_element);

        $feuilles_de_temps = $feuilles_de_temps->groupBy(\DB::raw("DATE_FORMAT(date,'%u')"), 'utilisateur_id')->get();

        $dates = $feuilles_de_temps->pluck('date')->toArray();
        $dates = array_unique($dates);
        $periodes = !empty($dates) ? $serie_management->periodes('hebdomadaire', array_values($dates)[0], end($dates)) : [];

		// on va chercher les utilisateurs et les temps saisis
        $utilisateurs = modele('utilisateur')
            ->whereIn('id', $feuilles_de_temps->pluck('utilisateur_id')->toArray())
            ->orderBy('prenom')
            ->get();

		// On charge les semaines validées
        $feuilles_de_temps_periode_validee = modele('feuille_de_temps_periode_validee')
            ->get()
            ->groupBy('utilisateur_id');

        $feuilles_de_temps_periode_terminee = modele('feuille_de_temps_periode_terminee')
            ->get()
            ->groupBy('utilisateur_id');

		$semaines_terminees = [];

		$semaines = [];

		foreach($periodes as $periode) {

            $semaines[] = $periode['numero_semaine'];
            $titres[] = traduction('rapport.temps_saisi_par_utilisateur_par_semaine.colonnes.semaine')." ".$periode['numero_semaine'];
        }

		$this->rapport->titres($titres);

		$temps_par_utilisateurs = [];
		
		foreach($utilisateurs as $utilisateur) {

			$identite = $utilisateur->prenom.' '.$utilisateur->nom;

			if(!isset($semaines_terminees[$identite]))
				$semaines_terminees[$identite] = [];

            $feuilles_de_temps_utilisateur = $feuilles_de_temps->where('utilisateur_id', $utilisateur->id);

            if($feuilles_de_temps_utilisateur->isNotEmpty()) {

                foreach ($periodes as $periode) {

                    $temps = $feuilles_de_temps_utilisateur->where('semaine',$periode['numero_semaine'])->first();

                    $temps_par_utilisateurs[$identite][$periode['numero_semaine']] = floatval($temps['temps_total'] ?? 0);

                    if(isset($feuilles_de_temps_periode_validee[$utilisateur->id])) 
                        $validee = $feuilles_de_temps_periode_validee[$utilisateur->id]
                            ->where('date_debut', '<=', $periode['date_debut'])
                            ->where('date_fin', '>=', $periode['date_fin'])->first();

                    if(isset($feuilles_de_temps_periode_terminee[$utilisateur->id]))
                        $terminee = $feuilles_de_temps_periode_terminee[$utilisateur->id]
                            ->where('date_debut', '<=', $periode['date_debut'])
                            ->where('date_fin', '>=', $periode['date_fin'])->first();
                    
                    if(!empty($validee))
                        $semaines_terminees[$identite][$periode['numero_semaine']] = 2;
                    else if(!empty($terminee))
                        $semaines_terminees[$identite][$periode['numero_semaine']] = 1;
                }
            }
		}

		foreach($temps_par_utilisateurs as $nom => $temps) {

			$ligne = [];
			$ligne[] = $nom;

			foreach($semaines as $semaine) {

				if(isset($semaines_terminees[$nom][$semaine]))
					$classe = $semaines_terminees[$nom][$semaine] === 1 ? 'semaine_terminee' : 'semaine_validee';
				else
					$classe = 'semaine_non_validee';

				if(isset($temps[$semaine]))
					$ligne[] = [$temps[$semaine], $classe];
				else
					$ligne[] = '';
			}

			$this->rapport->ligne($ligne);
		}

        $this->rapport->forcer_generation = true;
		return $this->rapport->genere($ajax);
    }


	/**
	 *
	 *
	 * Retourne les numeros de semaine pour un mois
	 *
	 */

	public function retourne_numero_de_semaine_pour_le_mois($mois, $annee) {

		$nombre_de_jours_par_mois = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);

		$semaine_par_mois = [];

		for($i = 1; $i <= $nombre_de_jours_par_mois; $i++) {

			$semaine = date('W', strtotime("$annee-$mois-$i"));

			if(!in_array($semaine, $semaine_par_mois))
				$semaine_par_mois[] = $semaine;
		}

		return $semaine_par_mois;
	}
}