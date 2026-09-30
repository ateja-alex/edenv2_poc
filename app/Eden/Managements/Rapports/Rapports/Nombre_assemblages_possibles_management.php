<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Nombre_assemblages_possibles_management extends Rapports_management {

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
	 * Génère le rapport indiquant combien de produits composés il est encore possible de faire selon le nombre d'ingrédients restants
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$this->rapport->titre = traduction('rapport.nombre_assemblages_possibles.colonnes.titre');
		
		// les titres de la liste
		$this->rapport->titres(
            array(
                traduction('rapport.nombre_assemblages_possibles.colonnes.nom_article'),
                traduction('rapport.nombre_assemblages_possibles.colonnes.ingredient_critique'),
                traduction('rapport.nombre_assemblages_possibles.colonnes.quantite_restante_a_produire')
            )
        );
		
		// On récupère les articles composés de plusieurs ingrédients
		$articles_composes = modele('article')->join('composition_article', 'article.id', '=', 'composition_article.article_id')
            ->where(function($where){
                $where->where('composition_article.inactif',0)
                    ->orWhereNull('composition_article.inactif');
            })
            ->select('article.*')->groupBy('composition_article.article_id')->get();
		
		// Pour chaque article composé
		foreach($articles_composes as $article_compose){
			
			// On récupère les ingrédients
			$composition_article = modele('article')->join('composition_article', 'article.id', '=', 'composition_article.article_enfant_id')
                ->where(function($where){
                    $where->where('composition_article.inactif',0)
                        ->orWhereNull('composition_article.inactif');
                })
                ->select('article.*', 'composition_article.quantite')->where('article_id', $article_compose->id)->get();
			
			// Pour chaque ingrédient, on calcule le stock actuel, puis en fonction de ce stock,
			// on calcule le nombre d'assemblages possibles pour le produit composé.
			foreach($composition_article as $ingredient){
				
				$ingredient->stock_article = management('article', $ingredient->id)->stock_actuel();

                if($ingredient->quantite > 0)
				    $ingredient->quantite_possible_article_compose = floor($ingredient->stock_article/$ingredient->quantite);
                else
                    $ingredient->quantite_possible_article_compose = 0;

				$quantite_possible_a_fabriquer = $ingredient->quantite_possible_article_compose;
			}
			
			//On vérifie quel ingrédient permet le moins d'assemblages et on récupère son nom et le nombre d'assemblages possible 
			foreach($composition_article as $ingredient){
				
				if($ingredient->quantite_possible_article_compose <= $quantite_possible_a_fabriquer){
					
					$quantite_possible_a_fabriquer = $ingredient->quantite_possible_article_compose;
					$ingredient_critique = $ingredient->id;
				}
			}
			
			// On crée la ligne du rapport
			$this->rapport->ligne(array(management('article', $article_compose->id)->affiche_lien(), management('article', $ingredient_critique)->affiche_lien(), $quantite_possible_a_fabriquer));
		}
		
		//On génère le rapport
		return $this->rapport->genere($ajax);
    }
}