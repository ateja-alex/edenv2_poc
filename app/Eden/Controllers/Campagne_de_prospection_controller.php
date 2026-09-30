<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Listes_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Champ_libre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Campagne_de_prospection_controller extends Controller {

	/**
	 * 
	 * Permet d'ajouter des clients à une campagne de prospection
	 * 
	 */
    public function ajouter(Request $formulaire) {
		
		if(empty($formulaire->campagne_de_prospection_id))
			return response()->json(array('retour' => false, 'message' => traduction('messages.php.campagne_de_prospection.choix_campagne')));
        
        $retour = management('campagne_de_prospection')->ajouter_un_des_clients($formulaire);
        
        if($retour !== true)
            return response()->json(array('retour' => false,'erreur' => $retour));
		
		return response()->json(array('retour' => true));
    }
	
	/**
	 * 
	 * Permet de "lancer" une campagne de prospection
	 * 
	 */
    public function lancer($campagne_de_prospection_id,$type_element,$element_id) {

        $formulaire = request()->all();

        // Si le lancement provient d'une liste, on enregistre l'ordre de traitement de la campagne
        if(isset($formulaire['id_liste'])) {

            $source_lancement_prospection = $_SERVER['HTTP_REFERER'];

            $champ_a_recuperer = !empty($formulaire['champ_a_recuperer']) ? $formulaire['champ_a_recuperer'] : 'id';

            $id_liste = $formulaire['id_liste'];

            // on enregistre les paramètres
            $parametres_liste = Rapports_management::recupere_parametres('liste_' . $id_liste);

            $parametres_liste['recupere_ids'] = true;
            $parametres_liste['champ_id_a_recuperer'] = $champ_a_recuperer;

            $parametres = array(
                'parametres_liste' => $parametres_liste,
                'id_liste' => $id_liste,
                'source_lancement_prospection' => $source_lancement_prospection
            );

            parametre_utilisateur('parametres_lancement_campagne_de_prospection_'.$campagne_de_prospection_id, json_encode($parametres));
        }
        else
            parametre_utilisateur('parametres_lancement_campagne_de_prospection_'.$campagne_de_prospection_id, null);

        return redirect()->route('campagne_de_prospection.fiche_element', [$type_element, $element_id, $campagne_de_prospection_id,true]);
    }


    /**
	 * 
	 * On récupère le récapitulatif des campagnes de prospections par utilisateur
	 * 
	 */
    public function recapitulatif($campagne_de_prospection_id) {

        $recapitulatif = management('campagne_de_prospection',$campagne_de_prospection_id)->recapitulatif();

    	return response()->json($recapitulatif);
    }
}
