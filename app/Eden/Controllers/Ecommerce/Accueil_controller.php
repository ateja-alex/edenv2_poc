<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Accueil_controller extends Controller {

    /**
     *
     * On affiche la page d'accueil
     *
     * @return Response
     */
    public function afficher_accueil() {
		
		$familles = management('famille')->familles_a_afficher() ;

			// On affiche la page d'accueil
		 return view('eden::ecommerce.famille', [
		 
			'familles' => $familles,
		]);
	}	


}
