<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Intranet_management;
use App\Eden\Managements\Authentification_management;

use App\Eden\Models\Liste_libre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Cookie;
use Mail;

class Intranet_controller extends Controller{

	/**
	 * 
 	 * Affiche le menu principal
	 *	
	 */

  	public function afficher(){

        $structure_intranet = config('eden_intranet');
        $listes = array();

        if(!empty($structure_intranet['lignes'])){

            foreach($structure_intranet['lignes'] as $cle => $ligne){

                if(!empty($ligne['modules'])){

                    foreach($ligne['modules'] as $module){

                        if($module['type_module'] == 'liste' && !empty($module['liste']))
                            $listes[$module['type_element']] = $module['liste'];
                    }
                }else{
                    unset($structure_intranet['lignes'][$cle]);
                    continue;
                }

            }
        }

        return view('eden::intranet.accueil',
            [
                'structure_intranet' => $structure_intranet,
                'listes' => $listes
            ]
        );
	
	}

	/**
   	 * Affichage d'une erreur si la saisie est mal effectuée
   	 */

  	public function erreur_demande(){
			
		return view('eden::intranet.erreur_demande');

	}

    /**
     *
     * Récupére les employes annuaires
     *
     */
    public function employes_annuaires(){

        $employes_annuaires = modele('utilisateur')->liste_utilisateurs_visibles();

        return response()->json(
            array(
                'employes_annuaires' => $employes_annuaires,
            )
        );
    }

}