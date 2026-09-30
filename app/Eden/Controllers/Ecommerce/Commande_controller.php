<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class Commande_controller extends Controller {

    /**
     * 
     * Affiche le panier
     * 
     * @return Response
     */
    public function panier() {
		
		
		$panier = management('panier')->recupere();
		
		$panier->informations_panier();
		
		return view('eden::ecommerce.commande.panier', ['panier' => $panier]);
    }

    /**
     * 
     * Affiche la page de paiement
     * 
     * @return Response
     */
    public function adresses() {
		
		return view('eden::ecommerce.commande.adresses');
    }

    /**
     * 
     * Affiche la validation de la commande
     * 
     * @return Response
     */
    public function valide() {
        
        $panier = '';
        return view('eden::ecommerce.commande.valide', ['commande' => $panier]);
    }

    
}
