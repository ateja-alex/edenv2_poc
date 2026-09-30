<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Saisie_des_temps_controller extends Controller{

    /**
	 *
	 * Affichage de la page de saisie_des_temps
	 *
	 */
    public function saisie_des_temps() {
		return view('eden::saisie_des_temps');
    }

    /**
	 *
	 * Chargement des dates
	 *
	 */
    public function charger_dates(Request $request) {

        $informations = service('saisie_des_temps')->charger_dates(array(
            'date' => $request->parametres['date'],
            'mode_affichage' => $request->mode_affichage,
        ));

        service('saisie_des_temps')->statut_saisie($informations,$request->parametres);

        return response()->json($informations);
    }

    /**
	 *
	 * Chargement des données d'un élément
	 *
	 */
    public function chargement_donnees(Request $request,$type_saisie = 'element') {

        $parametres = $request->parametres;

        $options = array(
            "utilisateur_id" => $parametres['utilisateur_id'],
            "type_element" => $parametres['type_element'],
            "mode_affichage" => $request->mode_affichage,
            "debut" => $request->date_debut,
            "fin" => $request->date_fin,
            "element_id" => $request->element_id ?? null,
            "elements_ids" => $parametres['elements_ids'] ?? [],
            "type_saisie" => $type_saisie
        );

        $service_saisie_des_temps = service('saisie_des_temps');

        $donnees = $service_saisie_des_temps->chargement_donnees($options);

        $service_saisie_des_temps->chargement_donnees_supplementaires($donnees,$options,$type_saisie);

        return response()->json($donnees);
    }

    /**
     *
     * Permet de désactiver un élément dans la saisie
     *
     */
    public function desactiver_element(Request $request){

        $parametres = $request->parametres;

        $options = array(
            "utilisateur_id" => $parametres['utilisateur_id'],
            "type_element" => $parametres['type_element'],
            "elements_ids" => $request->elements_ids,
            "debut" => $request->date_debut,
            "fin" => $request->date_fin,
            "element_id" => $request->element_id,
            "mode_affichage" => $request->mode_affichage,
            "type_saisie" => $request->type_saisie,
            "categorie_id" => $request->categorie_id ?? null,
        );

        $date_elements = service('saisie_des_temps')->desactiver_element($options);

        return response()->json(array('parametrage_date' => $date_elements));
    }

    /**
     *
     * Chargement du récapitulatif
     *
     */
    public function chargement_recapitulatif(Request $request){

        $recapitulatif = service('saisie_des_temps')->recapitulatif($request->all());

        return response()->json($recapitulatif);
    }

    public function changer_statut_saisie(Request $request,$type){

        $parametres = $request->parametres;

        $options = array(
            "utilisateur_id" => $parametres['utilisateur_id'],
            "type_element" => $parametres['type_element'],
            "debut" => $request->date_debut,
            "fin" => $request->date_fin,
            "nouveau_statut" => $request->nouveau_statut,
        );

        $retour = service('saisie_des_temps')->changer_statut_saisie($options,$type);

        return response()->json($retour);
    }
}