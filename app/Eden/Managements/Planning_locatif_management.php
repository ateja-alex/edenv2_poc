<?php

namespace App\Eden\Managements;

use DB;

/**
 * Gestion des calculs en gestion commerciale
 */
class Planning_locatif_management {
	
	public function __construct() {
				
	}
	
	/**
	 * 
	 * Retourne les articles disponibles pour une période
	 * 
	 */
	public function quels_articles_pour_periode($date_debut, $date_fin, $parametres = []) {
		
		if(strlen($date_debut) == 10) {
			
			$date_debut .= ' 00:00:00';
		}
		if(strlen($date_fin) == 10) {
			
			$date_fin .= ' 23:59:59';
		}
		
		// on va chercher tous les articles qui ont une facture sur la période
		$articles_deja_pris = modele('facture_vente')
					->select(DB::raw('DISTINCT article_id'))
					->join('facture_vente_lignes', 'facture_vente.id', 'facture_vente_lignes.document_id')
					->whereNotNull('article_id')
					->where(function($requete) use ($date_debut, $date_fin) {
						
						// soit il arrive pendant la période
						$requete->where(function($requete) use ($date_debut, $date_fin) {
							
							$requete->where('date_arrivee', '>=', formate_date('Y-m-d H:i:s', $date_debut));
							$requete->where('date_arrivee', '<=', formate_date('Y-m-d H:i:s', $date_fin));
						});
						
						// soit il part pendant la période
						$requete->orWhere(function($requete) use ($date_debut, $date_fin) {
							
							$requete->where('date_depart', '>=', formate_date('Y-m-d H:i:s', $date_debut));
							$requete->where('date_depart', '<=', formate_date('Y-m-d H:i:s', $date_fin));
						});
						
						// soit la période est incluse entre l'arrivée et le départ
						$requete->orWhere(function($requete) use ($date_debut, $date_fin) {
							
							$requete->where('date_arrivee', '<', formate_date('Y-m-d H:i:s', $date_debut));
							$requete->where('date_depart', '>', formate_date('Y-m-d H:i:s', $date_fin));
						});
						
					});
					
					
		// on regarde si on doit ajouter des filtres
		if(isset($parametres['factures'])) {
			
			if(isset($parametres['factures']['where'])) {
				
				foreach($parametres['factures']['where'] as $where) {
					
					if(isset($where[2])) {
						
						$articles_deja_pris->where($where[0], $where[1], $where[2]);
					}
					else {
						
						$articles_deja_pris->where($where[0], $where[1]);
					}
				}
			}
			
			if(isset($parametres['factures']['whereIn'])) {
				
				foreach($parametres['factures']['whereIn'] as $where) {
					
					$articles_deja_pris->whereIn($where[0], $where[1]);
				}
			}
			
			if(isset($parametres['factures']['where_ou_null'])) {
				
				foreach($parametres['factures']['where_ou_null'] as $where) {
					
					$articles_deja_pris->where(function($query) use ($where) {
						
						$query->where($where[0], $where[1]);
						$query->orWhereNull($where[0]);
					});
				}
			}
		}
					
		// dd_eden(vsprintf(str_replace(['?'], ['\'%s\''], $articles_deja_pris->toSql()), $articles_deja_pris->getBindings()));
		
		$articles_deja_pris = $articles_deja_pris->pluck('article_id');
		
		
		// on retourne tous les autres articles
		$articles = modele('article')->whereNotIn('id', $articles_deja_pris->toArray());
		
		if(isset($parametres['articles'])) {
			
			if(isset($parametres['articles']['where'])) {
				
				foreach($parametres['articles']['where'] as $where) {
					
					if(isset($where[2])) {
						
						$articles->where($where[0], $where[1], $where[2]);
					}
					else {
						
						$articles->where($where[0], $where[1]);
					}
				}
			}
			
			if(isset($parametres['articles']['whereIn'])) {
				
				foreach($parametres['articles']['whereIn'] as $where) {
					
					$articles->whereIn($where[0], $where[1]);
				}
			}
			
			if(isset($parametres['articles']['where_ou_null'])) {
				
				foreach($parametres['articles']['where_ou_null'] as $where) {
					
					$articles->where(function($query) use ($where) {
						
						$query->where($where[0], $where[1]);
						$query->orWhereNull($where[0]);
					});
				}
			}
		}
		
		return $articles->get()->keyBy('id');
	}
	
	/**
	 * 
	 * Retourne un tableau d'articles disponibles pour un ensemble de periodes
	 * 
	 * Si $tableau_par_article => false l'index du tableau est la période
	 * Si $tableau_par_article => true l'index du tableau est l'article id
	 * 
	 */
	public function quels_articles_pour_periodes($periodes, $parametres = [], $tableau_par_article = false) {
		
		$articles_dispo_par_periode = array();
		
		foreach($periodes as $periode_id => $info) {
			
			$articles_dispo_par_periode[$periode_id] = $this->quels_articles_pour_periode($info['debut'], $info['fin'], $parametres);
		}
		
		if($tableau_par_article === true) {
			
			$tableau_par_article = array();
			
			foreach($articles_dispo_par_periode as $periode_id => $articles) {
				
				foreach($articles as $article) {
					
					if(!isset($tableau_par_article[$article->id]))
						$tableau_par_article[$article->id] = array();
					
					$tableau_par_article[$article->id][] = $periode_id;
				}
			}
			
			return $tableau_par_article;
		}
		
		return $articles_dispo_par_periode;
	}
	

    
}
