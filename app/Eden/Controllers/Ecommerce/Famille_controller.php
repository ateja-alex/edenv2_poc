<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Famille_controller extends Controller {

    /**
     *
     * On affiche une page de famille d'articles
     *
     * @return Response
     */
    public function affiche($famille, $formulaire) {

		$famille_management = management('famille', $famille->id);

		// on va chercher les données dans le management qui peut être surchargé si nécessaire
		$donnees = $famille_management->charge_donnees_pour_commerce($formulaire);
		$donnees['parametres'] = $formulaire;
		$donnees['categories'] = management('famille')->familles_a_afficher();
		$donnees['sous_categories'] = management('famille')->sous_familles_a_afficher();
		$donnees['formulaire'] = $formulaire->all();

		// On génère le fil d'ariane
		$donnees['ariane'] = management('famille')->fil_ariane ($donnees['categories'], $donnees['sous_categories']) ;
		// On affiche la page famille d'articles
        return view('eden::ecommerce.famille', $donnees);
    }

}
