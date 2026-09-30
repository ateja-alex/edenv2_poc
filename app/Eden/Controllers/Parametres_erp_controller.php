<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Parametres_erp_controller extends Controller {


    /**
	 *
	 * Permet de récupérer la valeur d'un paramétre en ajax
	 *
	 */
    public function recuperer_parametre($nom,$type = null,$variable = null) {

        $parametre = null;

        if($type == null)
            $parametre = parametre($nom);

        if($type == 'entite')
            $parametre = parametre_entite($variable,$nom);

        if($type == 'utilisateur')
            $parametre = parametre_utilisateur($nom,false,$variable);

		// On retourne la vue
		return response()->json(array('parametre' => $parametre));
    }

	/**
	 *
	 * Permet d'enregistrer une liste de paramètres
	 *
	 */
    public function enregistrer(Request $request) {

        $type = 'entite';

        if(isset($request->type))
            $type = $request->type;

        // on sauvegarde les parametres
        foreach ($request->parametres as $id_variable => $parametres_par_variable) {

            foreach ($parametres_par_variable as $nom => $nouvelle_valeur) {

                if ($id_variable == 0)
                    parametre($nom, $nouvelle_valeur);
                elseif($type == 'utilisateur')
                    parametre_utilisateur($nom, $nouvelle_valeur,$id_variable);
                else
                    parametre_entite($id_variable, $nom, $nouvelle_valeur);
            }
        }

		// On retourne la vue
		return response()->json(array('retour' => true));
    }
}