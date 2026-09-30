<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Recherche_controller extends Controller {

    /**
     * 
     * On affiche le résultat de la recherche
     * 
     * @return Response
     */
    public function effectuer_recherche(Request $mot_cle) {

        $mot_cle = $mot_cle->search;
        // On défini le manager article
        $manager = management('article');
        
        // On défini les champs utilisés pour la recherche
        $champs = $manager->recherche_pour_ecommerce();

        $articles = $manager->requete_recherche_pour_ecommerce($champs, $mot_cle);

		// On affiche la page du résultat de la recherche
     return view('eden::ecommerce.recherche', [
        'articles' => $articles,
        'mot_cle' => $mot_cle
     ]);
 }

}
