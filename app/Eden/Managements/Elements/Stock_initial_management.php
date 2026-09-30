<?php

namespace App\Eden\Managements\Elements;

class Stock_initial_management extends Element_management {

    /**
     * @param $modifications
     * @param $modele
     * @return string|true
     *
     * On gére le stock initial si on renseigne que la quantité de conditionnement
     *
     */
    public function enregistre($modifications = array(), $modele = false){

        if(isset($modifications['quantite_conditionnement']) && !isset($modifications['stock_initial'])){

            $conditionnement = $modifications['conditionnement_id'] ?? $this->modele->conditionnement_id ?? null;

            if($conditionnement == null)
                $quantite = 1;
            else{
                $conditionnement_modele =  modele('conditionnement')->where('id',$conditionnement)->first();
                $quantite = $conditionnement_modele->quantite ?? 1;
            }

            $modifications['stock_initial'] = ($modifications['quantite_conditionnement'] * $quantite) ?? 0;
        }

        return parent::enregistre($modifications, $modele);
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

}