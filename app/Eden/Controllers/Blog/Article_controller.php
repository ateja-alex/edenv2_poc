<?php

namespace App\Eden\Controllers\Blog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Article_controller extends Controller {

    /**
     *
     * On affiche une page article de blog
     *
     * @return Response
     */
    public function affiche($article) {

		$article_management = management('blog_article', $article->id);

		// on va chercher les données dans le management qui peut être surchargé si nécessaire
		$donnees = $article_management->charge_donnees_pour_blog();

		// On affiche la page de l'articles
        return view('eden::ecommerce.blog.article', $donnees);
    }


}
