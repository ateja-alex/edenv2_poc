<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Models\Elements\Article;
use App\Eden\Models\Elements\Famille;
use App\Eden\Models\Element_image;

use App\Eden\Controllers\Ecommerce\Famille_controller;
use App\Eden\Controllers\Ecommerce\Article_controller;

class Ecommerce_controller extends Controller {

    /**
     * 
     * On cherche la destination en fonction de l'url, soit une page article soit une page famille
     * 
     * @return Response
     */
    public function trouve_destination(Request $formulaire, Famille_controller $famille_controleur, Article_controller $article_controleur, $url) {
		
		$destination_specifique = service('ecommerce')->recupere_destination_sur_mesure($url);
		
		if($destination_specifique !== false)
			return $destination_specifique;
		
		/* seo_famille_theme_de_filtre */

		$seo_famille_theme_de_filtre = modele('seo_famille_theme_de_filtre')->where('url', $url)->first();

		if($seo_famille_theme_de_filtre !== null) {
			

			$seo_famille_theme_de_filtre = management('seo_famille_theme_de_filtre', $seo_famille_theme_de_filtre->id, $seo_famille_theme_de_filtre);

			// on va chercher les données dans le management qui peut être surchargé si nécessaire
			$donnees = $seo_famille_theme_de_filtre->charge_donnees_pour_commerce($formulaire);


			$donnees['parametres'] = $formulaire;
			//$donnees['categories'] = management('famille')->familles_a_afficher();
			//$donnees['sous_categories'] = management('famille')->sous_familles_a_afficher();
			$donnees['formulaire'] = $formulaire->all();
			
			// On génère le fil d'ariane
			//$donnees['ariane'] = $seo_famille_theme_de_filtre->fil_ariane() ;
			// On affiche la page famille d'articles
			return view('eden::ecommerce.famille', $donnees);
		}
		

		$url2 = service('ecommerce')->recupere_tableau_url_pour_recherche_de_famille();
		$end = end($url2);

		$familles = modele('famille')->where('url', $end);

		// On a trouvé plusieurs familles
		if($familles->count() > 1) {

			$famille_racine = modele('famille')->where('url', $url2[0])->first();
			$parent = $famille_racine;

			if(isset($url2[1]) && $url2[1] != $end) {
				
				$famille_parent = modele('famille')->where('url', $url2[1])->where('parent_id', $parent->id)->first();

				if(!empty($famille_parent)) {
					$parent = $famille_parent;
				}
			}

			if(isset($url2[2]) && $url2[2] != $end) {
				
				$famille_parent = modele('famille')->where('url', $url2[2])->where('parent_id', $parent->id)->first();

				if(!empty($famille_parent)) {
					$parent = $famille_parent;
				}
			}

			if(isset($url2[3]) && $url2[3] != $end) {
				
				$famille_parent = modele('famille')->where('url', $url2[3])->where('parent_id', $parent->id)->first();

				if(!empty($famille_parent)) {
					$parent = $famille_parent;
				}
			}

			foreach ($familles->get() as $f_test) {
				
				if($f_test->parent_id == $parent->id) {
					$famille = $f_test;
					continue;
				}
			}


			if(isset($famille) && $famille !== null) {
				
				// ok on a trouvé une famille qui correspond, on renvoit le bon controleur
				return $famille_controleur->affiche($famille, $formulaire);
			}

		} elseif($familles->count() == 1) {
			$famille = $familles->first();
		}




		$famille = modele('famille')->where('url', $url)->first();
		
		if($famille !== null) {
			
			
			
			// ok on a trouvé une famille qui correspond, on renvoit le bon controleur
			return $famille_controleur->affiche($famille, $formulaire);
		}
		
		// Traite si une URL correspond à une sous-famille
		if(strpos($url, '/') !== false) {

			$url2 = explode('/', $url);
			$url = end($url2);

			
			$familles = modele('famille')->where('url', $url)->get();
			
			// On prends la première famille
			$famille = $familles->first();

			// Mais si il y a plus d'une sous-famille avec la même URL, on va essayer de chercher celle qu'on recherche.
			if($familles->count() > 1) {
			
				$url_parent = prev($url2);
				$parent 	= modele('famille')->where('url', $url_parent)->first();

				// On a trouvé un
				if(!empty($parent)) {

					// On cherche parmis les familles trouvées pour sélectionner celle associée au parent
					foreach($familles as $f) {
						if($f->parent_id == $parent->id) {
							$famille = $f;
						}
					}

				} else {

					// On n'a pas trouvé de famille parent. Pour éviter l'erreur, on retourne quand même la première famille trouvée
					dd_eden("Famille parent introuvable. Erreur uniquement affichée pour les super-admin", $url2, $url_parent);
				}

			}


			if($famille !== null) {
				
				// ok on a trouvé une famille qui correspond, on renvoit le bon controleur
				return $famille_controleur->affiche($famille, $formulaire);
			}
		}
		
		$article = modele('article')->where('url', $url)->first();
		
		if($article !== null) {
			
			// ok on a trouvé une famille qui correspond, on renvoit le bon controleur
			return $article_controleur->affiche($article, $formulaire);
		}
		
        // On retourne la vue
        abort(404);
        dd("La page n'a pas été trouvée");
    }


    /**
     *
     * Affiche les résultats de la recherche
     *
     */
    public function recherche(Request $formulaire) {

    	$donnees = [];
		$donnees['recherche'] = $formulaire->recherche;

    	$articles = modele('article')
    					->select('article.*')
    					->join('famille', 'famille_id', 'famille.id')
    					->where('article.url', '!=', '');

    	foreach (explode(' ', $formulaire->recherche) as $recherche) {
    		
            $articles->where(function ($query) use ($recherche) {
                $query
                	->where('article.designation', 'LIKE', '%'.$recherche.'%')
                     //->orWhere('article.description_longue', 'LIKE', '%'.$recherche.'%')
                     ->orWhere('famille.nom', 'LIKE', '%'.$recherche.'%')
                     //->orWhere('famille.texte_introduction', 'LIKE', '%'.$recherche.'%')
                	;
            });
    	}

    	$articles = $articles->get();


		$donnees['contenu'] = ["articles" => $articles];

		// on ajoute les images pour les articles
		foreach($donnees['contenu']['articles'] as $id => $article) {
			

			$article->images = Element_image::where('type_element', 'article')->where('element_id', $article->id)->get();// on va chercher les thèmes de filtres
			$themes = management('article', $article->id)->filtres_disponibles_pour_themes_de_filtres();
			
			$filtres_choisis = array();
			$filtres_choisis_texte = array();
			
			foreach($themes as $theme) {
				
				$id_theme_de_filtre = $theme['theme_de_filtres']->id;
				
				if(!isset($themes_de_filtres[$id_theme_de_filtre])) {
					
					$themes_de_filtres[$id_theme_de_filtre] = array();
				}
				
				$filtres_choisis[$id_theme_de_filtre] = array();
				
				foreach($theme['filtres_choisis'] as $id_filtre) {
					
					$filtres_choisis[$id_theme_de_filtre][] = $id_filtre;
					
					// on vérifie si l'id_filtre correspond bien au thème en cours
					// donc, on récupère le filtre
					$filtre = modele('filtre_theme_de_filtres', $id_filtre);
					
					if(empty($filtre) || $filtre->theme_de_filtres_id != $id_theme_de_filtre)
						continue;
					
					if(!isset($themes_de_filtres[$id_theme_de_filtre][$id_filtre])) {
						
						$themes_de_filtres[$id_theme_de_filtre][$id_filtre] = 0;
					}
					
					$themes_de_filtres[$id_theme_de_filtre][$id_filtre]++;
					
					$filtres_choisis_texte[] = $filtre->nom;
				}
			}
			
			$donnees['contenu']['articles'][$id]->filtres_choisis = $filtres_choisis_texte;
			
			// on retire l'article de la liste si c'est pas nécessaire
			if(!empty($formulaire_tableau['filtres'])) {
				
				foreach($formulaire_tableau['filtres'] as $id_theme_de_filtre_filtre => $filtres) {
					
					if(!isset($filtres_choisis[$id_theme_de_filtre_filtre])) {
						
						unset($donnees['contenu']['articles'][$id]);
						continue;
					}
					
					$filtre_trouve = false;
					
					foreach($filtres_choisis[$id_theme_de_filtre_filtre] as $id_filtre) {
						
						if(in_array($id_filtre, $filtres))
							$filtre_trouve = true;
					}
					
					if($filtre_trouve === false)
						unset($donnees['contenu']['articles'][$id]);
				}
			}
		}
		
		// On affiche la page famille d'articles
        return view('eden::ecommerce.recherche', $donnees);


    }
   
}
