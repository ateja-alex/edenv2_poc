<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

class Accueil_controller extends Controller {

	/**
	 * 
	 * Page d'accueil d'Eden
	 * 
	 */
    public function accueil($page = NULL) {

        if($_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet') && $page != 'extranet')
            return redirect()->route('extranet.accueil');
		
		temps_execution('debut controller');

    	if ( $page == NULL && empty(moi_extranet())) {
    		
    		$utilisateur = session('utilisateur_eden');
    		$page = $utilisateur->accueil;
    	}

		// on regarde si on a une page d'accueil standard
		$accueil_standard = maquette('page_accueil');
		
		if(empty($page) && !empty($accueil_standard)) {
			
			$page = $accueil_standard;
		}
		
    	if ( $page == NULL || strpos($page, 'eden/accueil') !== false) {
    		
    		$page = 'standard' ;
    	}


		if(strpos($page, 'eden/') !== false ) {
			
			return redirect()->to($page);
		}


    	$management = $this->recupere_management($page);
    	$donnees = $management->prepare_donnees_pour_accueil();

		temps_execution('fin controller');

		return view('eden::accueil.'.$page, $donnees);
    }


	/*
	*
	* Fonction pour aller chercher le management d'une page accueil
	*
	* @param $type_element string
	*
	*/
	function recupere_management($page) {

		$classes = array(

			"\\App\\Managements\\Accueil\\Accueil_" . $page . "_management",
			"\\App\\Eden\\Managements\\Accueil\\Accueil_" . $page . "_management",
			"\\App\\Eden\\Managements\\Accueil_management",
		);

		foreach($classes as $classe) {
			
			if(class_exists($classe)) {
				
				$management = new $classe();
				$management->page = $page;
				
				return $management;
			}
		}
	}


	function action($page, $action) {

    	$management = $this->recupere_management($page);

    	return $management->$action(); 
	}
}
