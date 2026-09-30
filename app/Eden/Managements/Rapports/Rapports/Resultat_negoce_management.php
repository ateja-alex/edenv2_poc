<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;
use App\Eden\Models\Facture_vente_ligne;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Resultat_negoce_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * Génére le rapport indiquant combien de produits composés il est encore possible de faire selon le nombre d'ingrédients restants
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$this->rapport->titre = traduction('rapport.resultat_negoce.titre_affichage');
		
		$familles = modele('famille')->get();
		
		// les titres de la liste
		$nom_dates = array(traduction('rapport.resultat_negoce.colonnes.nom_famille_articles'));
		
		foreach($dates['dates'] as $date){
			
			$nom_dates[] = $date['nom'];
		}
		
		$nom_dates[] = traduction('rapport.resultat_negoce.colonnes.total_par_famille');
		$this->rapport->titres($nom_dates);
		
		$benefices_famille = array();
		$total_par_mois = array(traduction('rapport.resultat_negoce.colonnes.total_par_mois'));
		$total_periode_toutes_familles = 0;
		
		foreach($dates['dates'] as $date){
			
			$total_par_mois[$date['nom']] = 0;
			
			foreach($familles as $famille){				
					
				$articles_familles = DB::table('facture_vente_lignes')
					->join('facture_vente', 'facture_vente.id', '=', 'facture_vente_lignes.document_id')
					->join('article', 'article.id', '=', 'facture_vente_lignes.article_id')
					->where('facture_vente.date', '>', $date['periode'].'-01')
					->where('facture_vente.date', '<', $date['periode'].'-31')
					->where('article.famille_id', $famille->id)
					->get();
				
				$positif = 0;
				$negatif = 0;
				
				//On calcule les bénéfices et les coùts de production de chaque article de la famille
				foreach($articles_familles as $article){
					
					$positif += $article->quantite * $article->tarif * (100 - $article->remise)/100;
					$negatif += $article->prix_d_achat * $article->quantite;
				}
				
				$total_par_mois[$date['nom']] = $total_par_mois[$date['nom']] + strval($positif - $negatif);
				$benefices = strval($positif - $negatif);
				$benefices_famille[$famille->nom][] = $benefices;	
			}
			
			$total_par_mois[$date['nom']] .= ' ' . maquette('devise_application_symbole');
			
		}
		
		foreach($benefices_famille as $key => $famille){
			
			$total_famille = 0;
			
			foreach($famille as $ligne_famille){
				
				$total_famille += $ligne_famille;
				$ligne_famille .= ' ' . maquette('devise_application_symbole');
			}
			
			array_unshift($famille, $key);
			$famille[] = $total_famille;
			$total_periode_toutes_familles += $total_famille;
			$this->rapport->ligne($famille);
		}
		
		$total_par_mois[] .= $total_periode_toutes_familles;
		
		// On crée la ligne avec le total par mois
		$this->rapport->sous_titre($total_par_mois);
		
		//On génère le rapport
		return $this->rapport->genere($ajax);
    }
}