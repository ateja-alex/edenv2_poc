<?php

namespace App\Eden\Controllers\Blog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Categorie_controller extends Controller {

    /**
     * 
     * On affiche une page de catégorie de blog
     * 
     * @return Response
     */
    public function affiche($categorie, $formulaire) {
		
		$categorie_management = management('blog_categorie', $categorie->id);
		
		// on va chercher les données dans le management qui peut être surchargé si nécessaire
		$donnees = $categorie_management->charge_donnees_pour_blog();
		
		$donnees['parametres'] = $formulaire;
		
		// On affiche la page categorie d'articles
        return view('eden::ecommerce.blog.categorie', $donnees);
    }

   
}
