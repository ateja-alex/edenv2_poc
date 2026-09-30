<?php

namespace App\Eden\Managements\Elements;

class Bon_preparation_vente_management extends Document_management {

    /**
	 *
	 * Retourne une instance du modèle pour gérer les lignes des documents
	 * Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	 * (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	 *
	 */
	public function modele_lignes() {

		return modele('bon_preparation_vente_lignes');
	}

	/**
	 *
	 * Statuts disponibles:
	 *
	 * 0 => 'Pro forma',
     * 10 => 'À préparer',
     * 20 => 'Partiellement préparé',
	 * 30 => 'Expédié',
	 *
	 */
	public function gere_statut_automatique() {

		// pro forma
		if(empty($this->modele->valide)) {

			$this->enregistre_modele(array('statut' => 0));
			return;
		}

		// on va voir le statut par défaut, pour un bone de préparation validé
		$statut = 10; // À préparer


		$articles_du_document = modele('bon_preparation_vente_lignes')->where('document_id', $this->modele->id)->get();

		// il y a au moins des lignes traitées, on passe en facturée / expédiée / préparée
		if($articles_du_document->whereIn('transforme', array(1, 2))->count() > 0) {

			$statut = 30;

			// est ce qu'il y a des lignes non facturées ? => on passe en partiellement facturée / expédiée / préparée
			if($articles_du_document->whereIn('transforme', array(0, 1))->count() > 0)
				$statut = 20;
		}

		$this->enregistre_modele(array('statut' => $statut));

	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['bl_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bl_vente']);
        
        return parent::transformations_possibles($transformations_possibles);
    }

}
