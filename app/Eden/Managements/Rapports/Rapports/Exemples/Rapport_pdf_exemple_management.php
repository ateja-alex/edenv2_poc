<?php

namespace App\Eden\Managements\Rapports\Rapports\Exemples;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_pdf_management;

use Illuminate\Http\Request;

class Rapport_pdf_exemple_management extends Rapports_management {
	
	/**
	 * 
	 * Commentez ici le rapport
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on instancie un nouveau rapport de type liste (vous n'avez rien à modifier)
		$rapport = new Rapport_pdf_management();
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$rapport->titre = "Nouveau titre";
		
		$donnees_pour_pdf = array();
        $donnees_pour_pdf['variable1'] = 'Hello';
        $donnees_pour_pdf['variable2'] = 'World';

        $rapport->genere_pdf($donnees_pour_pdf);
		
		return $rapport->genere($ajax);
    }
	
	
	
	
	
}