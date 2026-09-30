<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Element_image;

use DB;


class Pays_management extends Element_management {


	/**
	 * 
	 * Ajoute un pays à la volée, ou alors retourne l'id du pays si il existe
	 * 
	 */
	public function ajout_pays_volee($nom) {

		$pays = modele('pays')->where('nom', $nom)->first();

		if($pays != null) {
			return $pays->id;
		}

		$this->enregistre(['nom' => $nom]);

		return $this->modele->id;
	}
}