<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Suivi_jours_travailles_controller extends Controller{

    /**
	 *
	 * Affichage de la page de suivi_jours_travailles
	 *
	 */
    public function suivi_jours_travailles() {
		return view('eden::suivi_jours_travailles');
    }

    /**
     *
     * Initialisation des valeurs du suivi
     *
     */
    public function initialisation() {

        return response()->json(service('suivi_jours_travailles')->initialisation());
    }

    /**
     *
     * Actualisation des valeurs du suivi
     *
     */
    public function actualisation(Request $request) {

        $parametres = $request->all();

        return response()->json(service('suivi_jours_travailles')->actualisation($parametres));
    }

    /**
     *
     * Permet de statut pour les dates effectives
     *
     */
    public function changer_statut_saisie(Request $request){

        $parametres = $request->all();

        return response()->json(service('suivi_jours_travailles')->changer_statut_saisie($parametres['nouveau_statut'],$parametres['parametres']));
    }

}