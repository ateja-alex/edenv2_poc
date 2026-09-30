<?php

namespace App\Eden\Controllers\Fiches;

use App\Eden\Controllers\Fiche_controller;

use PDF;


class Proposition_commerciale_controller extends Fiche_controller {

    /**
	 *
	 * Permet de générer le PDF de la poposition commerciale
	 *
	 */
    public function genere_pdf() {
		
		$fiche_management = fiche($this->type_element, $this->id_element);
		
		return $fiche_management->genere_pdf_pour_fiche();
	}

	

}
