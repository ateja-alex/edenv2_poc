<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Saisie_des_temps_interne_controller extends Controller{

    /**
	 *
	 * Saisie des temps interne
	 *
	 */
	public function index() {

		return view('eden::saisie_des_temps_interne');
	}

	/**
	 *
	 * On récupère le tableau récap des temps de la semaine par projet
	 *
	 */
	public function informations($date,$initialisation) {

        $retour = array();

        if($initialisation === true || $initialisation === 'true') {

            $projets = modele('projet')->whereIn('statut', [220,250,320])->get();

            foreach ($projets as $projet) {

                $projet->affichage_select = management('client', $projet->client_id)->affiche() . ", " . $projet->nom;
            }

            $projets = $projets->sortBy('affichage_select');

            $retour = array(
                'projets' => $projets,
            );

        }

        $feuilles_de_temps = modele('feuille_de_temps')
            ->where('date', '=', $date)
            ->where('utilisateur_id', moi()->id)
            ->where('type_element', 'projet')
            ->orderBy('element_id')
            ->get();

		$dates = array();
		$dates[] = array('date' => date("Y-m-d", strtotime('monday this week', strtotime($date))));
		$dates[] = array('date' => date("Y-m-d", strtotime('tuesday this week', strtotime($date))));
		$dates[] = array('date' => date("Y-m-d", strtotime('wednesday this week', strtotime($date))));
		$dates[] = array('date' => date("Y-m-d", strtotime('thursday this week', strtotime($date))));
		$dates[] = array('date' => date("Y-m-d", strtotime('friday this week', strtotime($date))));

		$total_semaine = 0;
		$projets_presents = array();
		$totaux_semaine = array();

		foreach ($dates as $index => &$date_semaine) {

			$totaux_projet = DB::table('feuille_de_temps')
	            ->select(DB::raw('sum(duree) as duree, element_id'))
	            ->where('date', '=', $date_semaine['date'])
				->where('utilisateur_id', moi()->id)
				->where('inactif', null)
                ->where('type_element', 'projet')
                ->groupBy('element_id')
				->get();

			$total_journee = 0;
			$date_semaine['heures'] = array();

			if($totaux_projet->isNotEmpty()){

				foreach ($totaux_projet as $total_projet) {

					if (!in_array($total_projet->element_id, $projets_presents))
						$projets_presents[] = $total_projet->element_id;

					$total_journee += $total_projet->duree;

					$date_semaine['heures'][$total_projet->element_id] = $total_projet->duree;

					if(isset($totaux_semaine['totaux_semaine_projet'][$total_projet->element_id]))
						$totaux_semaine['totaux_semaine_projet'][$total_projet->element_id] += $total_projet->duree;
					else
						$totaux_semaine['totaux_semaine_projet'][$total_projet->element_id] = $total_projet->duree;
				}
			}

			$date_semaine['heures_journee'] = $total_journee;
			$date_semaine['nom_jour'] = Variables::jours($index+1);

			$total_semaine += $total_journee;
		}

		$dates['total_semaine'] = $total_semaine;

        $retour = array_merge($retour,array(
            'recap' => $dates,
            'projets_presents' => $projets_presents,
            'totaux_projets' => $totaux_semaine,
            'feuilles_de_temps' => $feuilles_de_temps,
            'retour' => true,
        ));

		return response()->json($retour);
	}
}