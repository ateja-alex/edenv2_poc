<?php

namespace App\Eden\Managements\Elements;

class Bon_retour_vente_management extends Document_management {

    /**
	*
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	*
	*/
	public function modele_lignes() {

		return modele('bon_retour_vente_lignes');
	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat';

        if(fonctionnalite('regroupement_articles_documents'))
            $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat_prefiltre';

        return parent::transformations_possibles($transformations_possibles);
    }
}