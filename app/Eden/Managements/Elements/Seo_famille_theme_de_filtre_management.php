<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Element_image;


class Seo_famille_theme_de_filtre_management extends Element_management {
	
	/**
	 * 
	 * Retourne les données necessaires pour afficher le contenu d'une page "famille" ecommerce
	 * 
	 */
	public function charge_donnees_pour_commerce($formulaire) {

		$donnees = array();

		if(!empty($this->modele->famille))
			$famille = management('famille', $this->modele->famille);

		// les informations sur la famille
		$donnees['famille'] = $this->modele;

		$formulaire_tableau = $formulaire->all();
		
		// on va chercher les articles et les sous articles
		$donnees['contenu_famille'] = $famille->contenu_famille($famille->modele, false, $formulaire);
		$donnees['valeur_du_filtre'] = $this->modele->valeur_du_filtre;
		$donnees['url_famille'] = $this->modele->famille_url;

		$themes_de_filtres = array();

		// on ajoute les images pour les articles et les thèmes de filtre
		foreach($donnees['contenu_famille']['articles'] as $id => $article) {
			

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

		$articles_temp = $donnees['contenu_famille']['articles'];
		$donnees['contenu_famille']['articles'] = [];

		// On filtre les articles selon les paramètres du thème de filtre choisi.
		foreach($articles_temp as $article) {
			
			$themes = management('article', $article->id)->filtres_disponibles_pour_themes_de_filtres();
			
			// Pour chaque thème
			foreach ($themes as $key => $theme) {

				// Si on ne filtre pas sur ce thème, on passe
				//if($theme['theme_de_filtres']->id != $this->modele->theme_de_filtre)
				//	continue;
					
				$filtres_dispo = $theme['filtres_dispo']->toArray();
				$filtres_choisis = $theme['filtres_choisis']->toArray();

				// On regarde si la valeur de thème filtrée fait parti des thèmes disponibles
				$id_valeur_filtree = false;
				foreach ($filtres_dispo as $id_valeur_filtre => $filtre) {

					if($filtre == $this->modele->valeur_du_filtre)
						$id_valeur_filtree = $id_valeur_filtre;
				}

				if(!$id_valeur_filtree)
					continue;

				// Si la valeur existe dans les filtres choisis, on ajoute l'article à la liste
				if(isset($filtres_choisis[$id_valeur_filtree]))
					$donnees['contenu_famille']['articles'][] = $article;


			}
		}

		// on ajoute les images pour les articles et on enlève les articles qui n'ont pas été filtrés
		foreach($donnees['contenu_famille']['articles'] as $id => $article) {
			

			$article->images = Element_image::where('type_element', 'article')->where('element_id', $article->id)->get();// on va chercher les thèmes de filtres
			$themes = management('article', $article->id)->filtres_disponibles_pour_themes_de_filtres();
			
			$filtres_choisis = array();
			$filtres_choisis_texte = array();
			
			foreach($themes as $theme) {
				
				$id_theme_de_filtre = $theme['theme_de_filtres']->id;

				$filtres_choisis[$id_theme_de_filtre] = array();
				
				foreach($theme['filtres_choisis'] as $id_filtre) {
					
					$filtres_choisis[$id_theme_de_filtre][] = $id_filtre;
					
					// on vérifie si l'id_filtre correspond bien au thème en cours
					// donc, on récupère le filtre
					$filtre = modele('filtre_theme_de_filtres', $id_filtre);
					
					if(empty($filtre) || $filtre->theme_de_filtres_id != $id_theme_de_filtre)
						continue;
					
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

		return $donnees;
	}

	

    /*
     * Génération du fil d'ariane
     * 
     * Retourne un tableau de catégories
     */
		/*
    public function fil_ariane() {
		
		$ariane = array();

		if(empty($this->modele))
			return $ariane;

		$famille = modele('famille', $this->modele->famille_parente);
		
		if(!empty($famille) && isset($famille->id))
			$ariane[] = $famille ;

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
		*/


}