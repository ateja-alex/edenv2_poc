<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Models\Panier_detail;

class Panier_controller extends Controller {

    /**
     * 
     * Ajoute un produit au panier via ajax
     * 
     * @return Response
     */
    public function ajax_ajoute_produit_au_panier(Request $formulaire) {
		
		$panier = management('panier')->recupere();
		
		$panier->ajoute_produit($formulaire);
		
		$panier->informations_panier();
		
		return response()->json($panier);
    }

    /**
     * 
     * Ajoute un produit au panier et redirige vers la fiche article
     * 
     * @return Response
     */
    public function ajoute_produit_au_panier(Request $formulaire) {
		
		$panier = management('panier')->recupere();
		
		$panier->ajoute_produit($formulaire);
		
		$article = modele('article', $formulaire->article_id);
		
		$panier->informations_panier();
		
		return redirect()->to($article->url)->with('information', traduction('messages.php.ecommerce.panier.article_ajoute'));
    }
	
	
}
