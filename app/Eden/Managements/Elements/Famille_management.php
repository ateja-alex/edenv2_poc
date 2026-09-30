<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Familles_management;
use App\Eden\Models\Element_image;
use App\Eden\Models\Liste_libre;

use DB;


class Famille_management extends Element_management {

	/**
	 * 
	 * On retourne la liste des familles à afficher sur le site ecommerce
	 * 
	 * Il s'agit des familles de niveau 0 (sans famille parent)
	 * 
	 */
	public function familles_a_afficher() {
		
		$familles = modele('famille')->where(function($query) {
					
					$query->where('parent_id', 0);
					$query->orWhereNull('parent_id');
				})->orderBy('nom')->get();

		return $familles ;
	}
	
	/**
	 * 
	 * On retourne la liste des familles à afficher sur le site ecommerce
	 * 
	 * Il s'agit des familles de niveau autre que 0 (avec famille parent)
	 * 
	 */
	public function sous_familles_a_afficher() {
		
		$familles = modele('famille')->whereNotNull('parent_id')->where('parent_id', '!=', 0)->orderBy('nom')->get();
		
		return $familles ;
	}

	

    /*
     * Génération du fil d'ariane
     * 
     * Retourne un tableau de catégories
     */
    public function fil_ariane($categories_parent, $sous_categories) {
		
		$ariane = array();
		
		$article = modele('article')
								->where('url', request()->route()->parameters['url'])
								->get()
								->first();
		// Catégorie actuelle
		foreach ($sous_categories as $categorie) {
			if (
					( $article != null && $categorie->id == $article->famille_id )
					||
					request()->route()->parameters['url'] == $categorie->url
				) {
					$ariane[] = $categorie ;
				}
		}

		// Sous Catégorie
		if ( count($ariane)>0 ) {
			foreach ($sous_categories as $categorie) {
				if ( $categorie->id == end($ariane)->parent_id ) {
					$ariane[] = $categorie ;
				}
			}
		}

		// Catégorie Racine
		// Catégorie parent de la derniere entrée du tableau $ariane
		// Ou sinon, on prends la catégorie selon l'url actuelle
		foreach ($categories_parent as $categorie) {
			if (
				(
					count($ariane)>0
					?
						$categorie->id == end($ariane)->parent_id
					:
						request()->route()->parameters['url'] == $categorie->url
				)
			) {
				$ariane[] = $categorie ;
			}
		}
		return array_reverse ($ariane) ;
    }

	/**
	 * 
	 * @cf description sur Element_management
	 * 
	 * on vide le cache à l'enregistrement d'une famille
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		// on vide le cache
		Cache_management::vider();

        // On regénére la valeur des champs listes
        Cache_management::genere_valeurs_liste_formatees(2);
		
		// on vide le fichier de cache des familles
		Familles_management::supprime_cache();

        // on regénére les listes des familles
        $liste_libres = Liste_libre::where('type_element', 'famille')->get();

        foreach ($liste_libres as $liste_libre) {

            Cache_management::generation_liste_libre($liste_libre->id);
        }
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}
	
	/**
	 * 
	 * @cf description sur Element_management
	 * 
	 */
	protected function methodes_post_suppression($modele) {
		
		// on vide le cache
		Cache_management::vider();

        // On regénére la valeur des champs listes
        Cache_management::genere_valeurs_liste_formatees(2);
		
		// on vide le fichier de cache des familles
		Familles_management::supprime_cache();
		
		parent::methodes_post_suppression($modele);
	}
	
	/**
	 * 
	 * Permet d'aller chercher le contenu d'une famille avec des sous familles et les articles
	 * 
	 * @return array
	 * 
	 */
	public function contenu_famille($modele_famille, $articles_par_famille = false, $parametres = false) {
		
		if($articles_par_famille === false) {
			
			if(is_array($parametres) && isset($parametres['avec_inactifs']) && $parametres['avec_inactifs'] === true) {
				
				$articles = modele('article')->avec_inactifs()->select('*');
			}
			else {
				
				$articles = modele('article')->select('*');
			}
			
			$articles->getQuery()->orders = null;
			
			if(!isset($parametres->tri)) {
				
				$articles = $articles->orderBy(DB::raw('tarif * (100 - promo) / 100'))->get();
			}
			elseif($parametres->tri == 'tarif_croissant') {
				
				$articles = $articles->orderBy(DB::raw('tarif * (100 - promo) / 100'))->get();
			}
			elseif($parametres->tri == 'tarif_decroissant') {
				
				$articles = $articles->orderBy(DB::raw('tarif * (100 - promo) / 100'), 'DESC')->get();
			}
			
			$articles_par_famille = array();

			foreach($articles as $article) {

				if(!isset($articles_par_famille[$article->famille_id]))
					$articles_par_famille[$article->famille_id] = array();

				if(is_array($parametres) && isset($parametres['articles_id_a_garder']) && is_array($parametres['articles_id_a_garder'])){

				    if(in_array($article->id,$parametres['articles_id_a_garder'])){

                        $articles_par_famille[$article->famille_id][] = $article;

                    }

                }

				else{

                    $articles_par_famille[$article->famille_id][] = $article;

                }
			}

			// On charge les articles par famille (les articles associés à d'autres familles que leur famille principale)
			$articles_famille = modele('article_famille')->join('article', 'article.id', '=', 'article_id')->select('article.*', 'article_famille.famille_id')->get();

			foreach($articles_famille as $article) {

				if(!isset($articles_par_famille[$article->famille_id]))
					$articles_par_famille[$article->famille_id] = array();

				$articles_par_famille[$article->famille_id][] = $article;
			}
        }

		$contenu_famille = array(
			
			'modele_famille' => $modele_famille,
			'sous_familles' => array(),
			'articles' => array(),
		);
		
		if(isset($articles_par_famille[$modele_famille->id])) {
			
			$contenu_famille['articles'] = $articles_par_famille[$modele_famille->id];
		}

		$sous_familles = modele('famille')->where('parent_id', $modele_famille->id)->get();
		
		foreach($sous_familles as $sous_famille) {
			
			$contenu_famille['sous_familles'][$sous_famille->id] = $this->contenu_famille($sous_famille, $articles_par_famille, $parametres);
		}
		
		return $contenu_famille;
	}
	
	/**
	 * 
	 * Retourne un tableau avec la liste des articles par famille
	 * 
	 */
	public function articles_par_famille($avec_inactifs = false) {
		
		$articles_par_famille = array();
			
		if($avec_inactifs === true)
			$articles = modele('article')->avec_inactifs()->get();
		else
			$articles = modele('article')->get();
		
		foreach($articles as $article) {

			if(!isset($articles_par_famille[$article->famille_id]))
				$articles_par_famille[$article->famille_id] = array();

			$articles_par_famille[$article->famille_id][] = $article;
		}
		
		return $articles_par_famille;
	}



	public function nombre_articles_famille($famille) {
		return modele('article')->where('famille_id', $famille->id)->count();
	}

	
	/**
	 * 
	 * Retourne la liste des familles parent pour une famille donnée
	 * 
	 */
	public function liste_familles_parent($famille_id, $liste = array()) {
		
		$famille_parent = modele('famille', $famille_id);
		
		$liste[] = $famille_parent->id;
		
		if(!empty($famille_parent->parent_id)) {
			
			$liste = $this->liste_familles_parent($famille_parent->parent_id, $liste);
		}
		
		return $liste;		
	}
	
	/**
	 * 
	 * Retourne les données necessaires pour afficher le contenu d'une page "famille" ecommerce
	 * 
	 */
	public function charge_donnees_pour_commerce($formulaire) {
		
		$donnees = array();

		// les informations sur la famille
		$donnees['famille'] = $this->modele;

		$formulaire_tableau = $formulaire->all();
		
		// on va chercher les articles et les sous articles
		$tous_articles = $this->contenu_famille($this->modele, false, $formulaire);
		
		$formulaire->avec_url = true;

		$donnees['contenu_famille'] = $this->contenu_famille($this->modele, false, $formulaire);

		$themes_de_filtres = array();

		//dump_eden($themes_de_filtres);

		// on ajoute les images pour les articles
		foreach($donnees['contenu_famille']['articles'] as $id => $article) {
			
			if(empty($article->url)) {
				
				unset($donnees['contenu_famille']['articles'][$id]);
				continue;
			}
			

			$article->images = Element_image::where('type_element', 'article')->where('element_id', $article->id)->get();

			// on va chercher les thèmes de filtres
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
			
			$donnees['contenu_famille']['articles'][$id]->filtres_choisis = $filtres_choisis_texte;
			
			// on retire l'article de la liste si c'est pas nécessaire
			if(!empty($formulaire_tableau['filtres'])) {
				
				foreach($formulaire_tableau['filtres'] as $id_theme_de_filtre_filtre => $filtres) {
					
					if(!isset($filtres_choisis[$id_theme_de_filtre_filtre])) {
						
						unset($donnees['contenu_famille']['articles'][$id]);
						continue;
					}
					
					$filtre_trouve = false;
					
					foreach($filtres_choisis[$id_theme_de_filtre_filtre] as $id_filtre) {
						
						if(in_array($id_filtre, $filtres))
							$filtre_trouve = true;
					}
					
					if($filtre_trouve === false)
						unset($donnees['contenu_famille']['articles'][$id]);
				}
			}
		}

		$donnees['themes_de_filtres'] = $themes_de_filtres;
		
		// on va chercher les urls des thèmes de filtres
		$urls_themes_de_filtres = array();
		
		foreach($themes_de_filtres as $id_theme_de_filtre => $filtres) {
			$urls_themes_de_filtres[$id_theme_de_filtre] = array();

			foreach($filtres as $id_filtre => $osef) {
				
				$nom_filtre = modele('filtre_theme_de_filtres', $id_filtre)->nom;
				
				$urls_themes_de_filtres[$id_theme_de_filtre][$id_filtre] = false;
				
				$test = modele('seo_famille_theme_de_filtre')
							->where('famille', $this->modele->id)
							->where('theme_de_filtre', $id_theme_de_filtre)
							->where('valeur_du_filtre', $nom_filtre)
							->first();
							
				
							
				if($test !== null) {
					
					$urls_themes_de_filtres[$id_theme_de_filtre][$id_filtre] = $test->url;
				}
			}
		}
		
		$donnees['urls_themes_de_filtres'] = $urls_themes_de_filtres;
		return $donnees;
	}


	public function get_famille_racine($iteration = 0) {
		
		if ( $iteration++ > 10 ) 
			return false ;

		if(empty($this->modele->parent_id)) {
			
			return $this->modele->id;
		} 
		else {
			return management('famille', $this->modele->parent_id)->get_famille_racine($iteration);
		}
	}
	
	/**
	 * 
	 * Retourne le premier ID famille trouvé parmis les $familles_id
	 * 
	 * 0 sinon
	 * 
	 */
	public function dernier_element_parent($familles_id) {
		
		if(in_array($this->modele->id, $familles_id))
			return $this->modele->id;
		
		if(in_array($this->modele->parent_id, $familles_id))
			return $this->modele->parent_id;
		
		if(empty($this->modele->parent_id))
			return 0;
		
		$nouvelle_famille = management('famille', $this->modele->parent_id);
		
		return $nouvelle_famille->dernier_element_parent($familles_id);
	}


	/**
	 * 
	 * 
	 * Surcharge de enregistre() de element_management
	 * 
	 * 
	 */
	public function enregistre($modifications = Array(), $modele = false) {

		$this->modele_avant_enregistrement = $this->modele;

		if(isset($modifications['parent_id']) && !empty($this->modele) && $modifications['parent_id'] == $this->modele->id)
			return traduction('messages.Vous ne pouvez pas définir une famille comme étant elle même sa famille mère', 'Vous ne pouvez pas définir une famille comme étant elle même sa famille mère');

		
		/*

		@todo : vérifier si on ne crée pas une boucle infinie

		// $retour = parent::enregistre($modifications, $modele);

		$i = 0;

		$modele_en_cours = $this->modele;

		while($i < 4){ 
			
			if ($modele_en_cours->parent_id != null) {

				$i++;
				$modele_en_cours = modele('famille', $modele_en_cours->parent_id);
			}
			else{
				$i = 5;
				$retour = "ok";
			}

			if ($i == 3) 
				$retour = "probleme";
			
		}

		*/

		return parent::enregistre($modifications, $modele);
	}
	
	/**
	 * 
	 * Ajoute une famille à la volée, ou alors retourne l'id de la famille si elle existe
	 * 
	 */
	public function ajout_famille_volee($nom_famille) {

		$famille = modele('famille')->where('nom', $nom_famille)->first();

		if($famille != null) {
			return $famille->id;
		}

		$this->enregistre(['nom' => $nom_famille]);

		return $this->modele->id;
	}

    /**
	 *
	 * Empeche la suppression si la famille est rattaché à un article
	 *
	 */
	public function supprime($modele = false) {

        $contenu_famille = $this->contenu_famille($this->modele,false);

        $retour = $this->verification_famille_non_lies($contenu_famille);

        if($retour !== true)
            return $retour;

		return parent::supprime($modele);
	}

    /**
     *
     * Permet de vérifier qu'une famille n'est lié pas aucun article avant la suppresion
     *
     */
    public function verification_famille_non_lies($contenu_famille){

        $retour = true;

        if(!empty($contenu_famille['articles']))
            return 'La famille : "'.$contenu_famille['modele_famille']->nom.'" est liée à des articles, la suppresion est donc impossible';

        foreach($contenu_famille['sous_familles'] as $famille){

            $retour = $this->verification_famille_non_lies($famille);

            if($retour !== true)
                return $retour;
        }

        return $retour;

    }


}