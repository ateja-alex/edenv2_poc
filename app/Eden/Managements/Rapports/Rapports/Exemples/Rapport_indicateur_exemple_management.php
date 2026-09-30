<?php

namespace App\Eden\Managements\Rapports\Rapports\Exemples;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_indicateur_management;

use Illuminate\Http\Request;

class Rapport_indicateur_exemple_management extends Rapports_management {
	
	/**
	 * 
	 * Commentez ici le rapport
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on instancie un nouveau rapport de type liste (vous n'avez rien à modifier)
		$rapport = new Rapport_indicateur_management();
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$rapport->titre = "Nouveau titre";
		
		$rapport->valeur = 100;
		
		return $rapport->genere($ajax);
    }
	
	
	
	
	
}