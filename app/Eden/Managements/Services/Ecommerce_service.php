<?php

namespace App\Eden\Managements\Services;

/**
 * 
 * Ce service permet de gérer des cas spécifiques pour le ecommerce
 * 
 */
class Ecommerce_service {

	/**
	 *
	 * Pour certains projets on a une destination qui peut être retournée en spécifique via cette méthode
	 *
	 */
	public function recupere_destination_sur_mesure($url) {

		return false;
	}	

	/**
	 *
	 * Retourne un tableau exploded par / pour l'url courrante
	 *
	 */
	public function recupere_tableau_url_pour_recherche_de_famille() {

		return explode('/', url()->current());
	}	

	
}
