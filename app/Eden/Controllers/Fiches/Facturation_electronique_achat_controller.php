<?php

namespace App\Eden\Controllers\Fiches;

use App\Eden\Controllers\Fiche_controller;

class Facturation_electronique_achat_controller extends Fiche_controller {

    /**
	 *
	 * Recherche des factures d'achat candidates pour le rapprochement avec cette facturation électronique achat
	 *
	 */
    public function candidats_facture_achat($type_element, $id_element) {

        return management($type_element, $id_element)->trouver_candidats_facture_achat();
    }
}
