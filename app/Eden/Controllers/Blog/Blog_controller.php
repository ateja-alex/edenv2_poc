<?php

namespace App\Eden\Controllers\Blog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Controllers\Blog\Categorie_controller;
use App\Eden\Controllers\Blog\Article_controller;

class Blog_controller extends Controller {

    /**
     * 
     * On cherche la destination en fonction de l'url, soit une page article soit une page catégorie
     * 
     * @return Response
     */
    public function trouve_destination(Request $formulaire, Categorie_controller $categorie_controleur, Article_controller $article_controleur, $url) {
		
		$categorie = modele('blog_categorie')->where('url', $url)->first();
		
		if($categorie !== null) {
			
			// ok on a trouvé une famille qui correspond, on renvoit le bon controleur
			return $categorie_controleur->affiche($categorie, $formulaire);
		}
		
		$article = modele('blog_article')->where('url', $url)->first();
		
		if($article !== null) {
			
			// ok on a trouvé une famille qui correspond, on renvoit le bon controleur
			return $article_controleur->affiche($article, $formulaire);
		}
		
        // On retourne la vue
        dd("La page n'a pas été trouvée");
    }
	
	/**
	 * 
	 * Page d'accueil du blog
	 * 
	 */
	public function index() {
		
		$donnees = array();
		
		// on va chercher les catégories d'articles avec le nombre d'articles par catégorie
		$donnees['articles'] = modele('blog_article')->paginate(maquette('blog_nombre_articles_par_page'));
		
		// on va chercher les catégories d'articles avec le nombre d'articles par catégorie
		$donnees['categories'] = modele('blog_categorie')->where('id', '>', 0)->with('articles')->get();
		
		// On affiche la page categorie d'articles
        return view('eden::ecommerce.blog.index', $donnees);
	}

   
}
