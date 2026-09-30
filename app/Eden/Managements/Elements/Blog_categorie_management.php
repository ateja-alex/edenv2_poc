<?php

namespace App\Eden\Managements\Elements;


class Blog_categorie_management extends Element_management {

	/**
	 * 
	 * Retourne les données necessaires pour afficher le contenu d'une page "catégorie" pour le blog
	 * 
	 */
	public function charge_donnees_pour_blog() {
		
		$donnees = array();
		
		// les informations sur la famille
		$donnees['categorie'] = $this->modele;
		
		// on va chercher les catégories d'articles avec le nombre d'articles par catégorie
		$donnees['articles'] = $this->modele->articles()->paginate(maquette('blog_nombre_articles_par_page'));
		
		// on va chercher les catégories d'articles avec le nombre d'articles par catégorie
		$donnees['categories'] = modele('blog_categorie')->where('id', '>', 0)->with('articles')->get();
		
		return $donnees;
	}

}