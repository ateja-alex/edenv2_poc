<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Commande_achat_ligne_fournisseur_controller extends Controller {

	/**
	 * 
	 * Permet de faire une récéption fractionnee
	 * 
	 */
    public function reception_totale(Request $formulaire, $id_element) {
		
		$formulaire = $formulaire->all();

		$commande_achat_ligne_founisseur = management('commande_achat_ligne_fournisseur',$id_element);
		
		$retour = $commande_achat_ligne_founisseur->enregistre_reception_totale();
		
		return response()->json(array('retour' => $retour));
	}

	/**
	 * 
	 * Permet de faire une récéption fractionnee
	 * 
	 */
    public function reception_fractionnee(Request $formulaire, $id_element) {

		$formulaire = $formulaire->all();

		$retour = true;

		$commande_achat_ligne = modele('commande_achat_lignes',$id_element);

		$quantite_receptionnee = 0 ;

		if(isset($formulaire['quantite'])){

            $quantite_receptionnee = intval($formulaire['quantite']);

        }

		if($commande_achat_ligne->recu == true ){

		    $retour = traduction('messages.php.commande.entierement_recu');
        }

		elseif($quantite_receptionnee == 0 || $quantite_receptionnee > $commande_achat_ligne->quantite){

            $retour = traduction('messages.php.commande.quantite_incorrecte');
        }

		else{

		    $management = management('commande_achat_lignes',$commande_achat_ligne->id);

		    if($quantite_receptionnee == $commande_achat_ligne->quantite){

                $management->enregistre(['recu' => 1]);
            }

		    else{

                $management->duplique_avec_modifications([
                    'quantite' => $quantite_receptionnee,
                    'recu' => 1,
                ]);

                $management->enregistre([
                    'quantite' => $commande_achat_ligne->quantite - $quantite_receptionnee,
                ]);

            }
        }
		
		return response()->json(array('retour' => $retour));
    }
	
}
