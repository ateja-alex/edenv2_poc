<?php

namespace App\Eden\Managements\Rapports\Rapports\Exemples;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_html_management;

use Illuminate\Http\Request;

class Rapport_html_exemple_management extends Rapports_management {
	
	/**
	 * 
	 * Commentez ici le rapport
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on instancie un nouveau rapport de type liste (vous n'avez rien à modifier)
		$rapport = new Rapport_html_management();
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$rapport->titre = "Nouveau titre";
		
		$rapport->html('<h1>Voici le html du rapport</h1>');
		$rapport->html('<br/>');
		$rapport->html('<br/>');
		$rapport->html('Et un peu de texte normal...');
		
		return $rapport->genere($ajax);
    }
	
	
	
	
	
}