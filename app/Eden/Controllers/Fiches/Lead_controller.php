<?php

namespace App\Eden\Controllers\Fiches;
use App\Eden\Controllers\Fiche_controller;

use Illuminate\Http\Request;


class Lead_controller extends Fiche_controller {

	/**
	 *
	 * Retourne la liste des intérets (entité)
	 *
	 */
	public function interets_entites($type_element, $id_element) {

		// on va chercher le management
		$management = fiche($this->type_element, $this->id_element);
		
		return $management->interets_entites();
	}

	/**
	 *
	 * Retourne la liste des intérets (article)
	 *
	 */
	public function interets_articles($type_element, $id_element) {

		// on va chercher le management
		$management = fiche($this->type_element, $this->id_element);
		
		return $management->interets_articles();
	}

	/**
	 *
	 * Crée une proposition commerciale pour le lead
	 *
	 */
	public function creer_proposition_commerciale($type_element, $id_element) {

		// on va chercher le management
		$management = management('proposition_commerciale');
		
		$infos = array(
			
			'lead_id' => $id_element,
			'date' => date('Y-m-d'),
			'statut' => 1,
			'reponse' => 0,
		);
		
		$management->enregistre($infos);
		
		return redirect()->route('base_eden.fiche.index', ['proposition_commerciale', $management->modele->id]);
	}
}
