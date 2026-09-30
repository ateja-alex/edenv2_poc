<?php

namespace App\Eden\Managements\Elements;

class Mouvement_de_stock_management extends Element_management {

	/**
	 * 
	 * Retourne des tags pour les listes
	 * 
	 */
	public function liste_tags($modele) {
		
		if($modele->reserve == 1)
			return '<span class="badge badge-warning">Stock réservé</span>';
			
		return '<span class="badge badge-success">Stock réel</span>';
	}
	
	/**
     *
     * On ne peut pas supprimer un mouvement de stock lié à un BL achat ou à une commande achat
     *
     */
    public function liste_colonnes_options() {

        $liste_options = parent::liste_colonnes_options();
		
		if(in_array($this->_type_element, array('bl_achat', 'commande_achat'))) {
			
			if(in_array('supprimer',$liste_options))
				unset($liste_options[array_search('supprimer',$liste_options)]);

            if(in_array('dupliquer',$liste_options))
                unset($liste_options[array_search('dupliquer',$liste_options)]);
		}
		
		return $liste_options;
    }


	/**
	 *
	 * On met à jour le stock de l'article
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

        if(fonctionnalite('calcul_stock_actuel_article_post_mouvement'))
		    management('article', $this->modele->article_id)->calcule_stock_actuel();

	}
	
	/**
	 *
	 * On met à jour le stock de l'article
	 *
	 */
	protected function methodes_post_suppression($modele) {
		
		parent::methodes_post_suppression($modele);

        if(fonctionnalite('calcul_stock_actuel_article_post_mouvement'))
		    management('article', $this->modele->article_id)->calcule_stock_actuel();
	}

	/**
	 * Retourne la chaîne d'affichage ou le lien de l'élément lié au mouvement de stock
	 * @param mixed $modele
	 * @param mixed $type_affichage
	 * @return string
	 */
	public function affiche_element_lie($modele, $type_affichage = '') {

		if(empty($modele->type_document) || empty($modele->document_id))
			return '';
		
		$management = management($modele->type_document, $modele->document_id);

		try {
			return $management->affiche_lien();
		} catch (\Exception $e) {
			return '';
		}
	}
}