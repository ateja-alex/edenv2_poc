<?php

namespace App\Eden\Controllers\Fiches;
use App\Eden\Controllers\Fiche_controller;
use Illuminate\Http\Request;

class Fournisseur_controller extends Fiche_controller {
	
    public function contacts() {

        $contacts = management('contact')->recuperer_contact_element('fournisseur', $this->id_element);

        return response()->json($contacts);
	}
	
	/**
	 *
	 * Synchronise les filtres sélectionnés sur les fournisseurs
	 *
	 */
    public function enregistre_filtres(Request $formulaire) {

		$fournisseur = modele('fournisseur')->find($this->id_element);

		$fournisseur->filtres()->sync($formulaire->filtres);

		return response()->json(true);
	}
	
	
}