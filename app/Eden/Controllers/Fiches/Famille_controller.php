<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Models\Element_image;
use App\Eden\Controllers\Fiche_controller;

class Famille_controller extends Fiche_controller {

	/**
	 * 
	 * Retourne la liste des articles de la famille
	 *
	 */
	public function articles_famille($type_element, $id_element) {

		$modele = modele('famille', $id_element);
		$management = management('famille', $id_element, $modele);

		$articles = $management->contenu_famille($modele)['articles'];

		$articles = collect($articles)->sortBy('ordre')->toArray();

		return response()->json($articles);
	}

}