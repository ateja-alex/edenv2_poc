<?php

namespace App\Eden\Managements\Elements;

class Bon_retour_achat_management extends Document_management {

    /**
	*
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	*
	*/
	public function modele_lignes() {

		return modele('bon_retour_achat_lignes');
	}
}