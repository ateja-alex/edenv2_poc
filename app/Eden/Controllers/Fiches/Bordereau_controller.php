<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Controllers\Fiche_controller;

use DB;

class Bordereau_controller extends Fiche_controller {

    /**
	 *
	 * Rattache les paiements pour ce bordereau
	 *
	 */
    public function rattacher_paiement(Request $formulaire,$type_element, $id_element) {

        $formulaire =$formulaire->all();

        foreach($formulaire['ids'] as $id){

            $management_paiement = management('paiement',$id);

            if($management_paiement->modele->bordereau_id == 0)
                $management_paiement->enregistre_modele(array('bordereau_id'=>$id_element));
        }

        $bordereau = management('bordereau',$id_element);

        $bordereau->calcul_somme_montant_bordereau();

        return response()->json(array('retour' => true,'bordereau' => $bordereau->modele));

	}

    /**
     *
     * Détacher les paiements pour ce bordereau
     *
     */
    public function detacher_paiement(Request $formulaire,$type_element, $id_element) {

        $formulaire =$formulaire->all();

        foreach($formulaire['ids'] as $id){

            $management_paiement = management('paiement',$id);

            $management_paiement->enregistre_modele(array('bordereau_id'=>0));

        }

        $bordereau = management('bordereau',$id_element);

        $bordereau->calcul_somme_montant_bordereau();

        return response()->json(array('retour' => true,'bordereau' => $bordereau->modele));

    }

	/**
	 *
	 * Imprime le bordereau au format pdf
	 *
	 */

	public function imprimer_pdf($id_bordereau){

	    dd_eden($id_bordereau);
    }

}
