<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Devis_achat_ligne;

class Devis_achat_management extends Devis_management {

	/**
	*
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	*
	*/
	public function modele_lignes() {

		return new Devis_achat_ligne;
	}

	/**
	 *
	 * Crée un mouvement de stock
	 *
	 */
	public function creer_mouvement_stock($parametre = "document") {

		return parent::creer_mouvement_stock($this->_type_element);
	}

	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);
	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['commande_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'commande_achat']);
        $transformations_possibles['bl_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bl_achat']);
        $transformations_possibles['acompte_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'acompte_achat']);
        $transformations_possibles['facture_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'facture_achat']);

        return parent::transformations_possibles($transformations_possibles);
    }

}
