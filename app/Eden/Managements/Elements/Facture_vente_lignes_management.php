<?php

namespace App\Eden\Managements\Elements;

class Facture_vente_lignes_management extends Document_lignes_management {

    /**
     *
     * Permet de vérifier si les lignes sources d'un document sont en reliquat, si oui on passe le document en transforme_en_facture =
     *
     */
	public function mise_a_jour_documents_lies_reliquat($modele){

        if(empty($modele))
            return false;

        $management_entete_origine = $this->management_entete_origine();

        if($management_entete_origine == null)
            return false;

        $management_entete_origine->management_principal = $this->management_principal ?? $this->management_entete();

        $attributs = $management_entete_origine->modele->getAttributes();

        if(!array_key_exists('transforme_en_facture',$attributs))
            return false;

        $articles_documents_origine = $management_entete_origine->articles();

        $transforme_en_facture = 1;

        foreach($articles_documents_origine as $article){

            if($transforme_en_facture && $article->transforme != 2)
                $transforme_en_facture = 0;

        }
		
		if($management_entete_origine->modele->transforme_en_facture != $transforme_en_facture) {
			
			$management_entete_origine->enregistre_modele(array(
				'transforme_en_facture' => $transforme_en_facture,
			));
		}

        return true;
    }

	/**
	 *
	 * On lie les acomptes et les factures pour ne pas lier 2 fois un acompte à une facture
	 *
	 */
	public function mise_a_jour_acomptes_lies_aux_factures() {

		if(empty($this->modele->type_element_source) || $this->modele->type_element_source != 'acompte_vente')
			return;

		if(empty($this->modele->id_element_source))
			return;

		if(empty(fonctionnalite('compta_article_id_pour_acompte')))
			return;

		// il y a déjà un lien
		if(modele('lien_acompte_facture')->where('acompte_vente_id', $this->modele->id_element_source)->first() !== null)
			return;

		// pas de lien, donc on doit l'enregistrer
		$lien_acompte_facture = management('lien_acompte_facture');

		$info = array(

			'acompte_vente_id' => $this->modele->id_element_source,
			'facture_vente_id' => $this->modele->document_id,
		);

		$lien_acompte_facture->enregistre($info);

		return true;
	}

	/**
	 *
	 * Quand on supprime une facture, alors on supprime le lien entre acompte et facture
	 *
	 */
	protected function supprime_lien_acompte_facture($modele) {

		if(empty(fonctionnalite('compta_article_id_pour_acompte')))
			return;

		if(empty($modele->type_element_source) || $modele->type_element_source != 'acompte_vente')
			return;

		if(empty($modele->id_element_source))
			return;

		// il y a déjà un lien
		$lien = modele('lien_acompte_facture')->where('acompte_vente_id', $modele->id_element_source)->first();

		// il a déjà été supprimé
		if($lien === null)
			return;

		// on le supprime
		management('lien_acompte_facture', $lien->id)->supprime();

		return true;

	}

    /**
	 *
	 * On met à jour le statut des documents liés
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        $this->mise_a_jour_documents_lies_reliquat($this->modele);

		// met à jour les acomptes utilisés sur les factures
        $this->mise_a_jour_acomptes_lies_aux_factures();
		
		// on met à jour les stocks
        if($this->modele->type_element_source != 'bl_vente')
            $this->mise_a_jour_stocks();

    }

    /**
	 *
	 * On met à jour le statut des documents liés
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);

        if($this->modele->type_element_source == 'commande_vente' ||$this->modele->type_element_source == 'bl_vente') {
            $management_entete_origine = $this->management_entete_origine();

            $management_entete_origine->management_principal = $this->management_principal ?? $this->management_entete();

            $management_entete_origine->mise_a_jour_statut_facture();
        }

		$this->mise_a_jour_documents_lies_reliquat($modele);

		$this->supprime_lien_acompte_facture($modele);

        // on met à jour les stocks
        if($this->modele->type_element_source != 'bl_vente')
            $this->mise_a_jour_stocks(false);

	}
}
