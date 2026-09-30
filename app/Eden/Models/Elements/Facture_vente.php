<?php

namespace App\Eden\Models\Elements;

class Facture_vente extends Element {

    /**
	 * 
	 * Utilisé pour appliquer automatiquement la gestion des profils, et la suppression des éléments inactifs
	 * 
	 */
	public function newQuery($excludeDeleted = true) {
		
        $builder = parent::newQuery($excludeDeleted);
		
		return $builder;
    }
	
	/**
	 *
     * Récupère les contacts du document
	 *
     */
    public function contacts() {
		
        return $this->belongsToMany('App\Eden\Models\Elements\Contact', 'facture_vente_contacts_ids', 'cle_locale', 'valeur');
    }
	
	/**
	 *
     * Récupère tous les champs libres pour une table libre
	 *
     */
    public function client() {
		
        return $this->hasOne('App\Eden\Models\Client', 'id', 'client_id');
    }


    /**
	 *
     * Lien avec la table facture_vente_lignes
	 *
     */
	public function facture_vente_lignes() {
		
        return $this->hasMany('App\Eden\Models\Facture_vente_ligne', 'document_id', 'id');
    }


    /**
	 *
     * Lien avec la table facture_vente_lignes (join)
	 *
     */
	public function join_facture_vente_lignes() {
		
        return $this->join('facture_vente_lignes', 'facture_vente_lignes.document_id', 'facture_vente.id');
    }

    /**
	 *
     * Lien avec la table article (join)
	 *
     */
	public function join_article() {
		
        return $this->join('facture_vente_lignes', 'facture_vente_lignes.document_id', 'facture_vente.id')->join('article', 'facture_vente_lignes.article_id', 'article.id');
    }
	
}
