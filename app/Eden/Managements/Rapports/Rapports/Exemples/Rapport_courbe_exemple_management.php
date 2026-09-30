<?php

namespace App\Eden\Managements\Rapports\Rapports\Exemples;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_courbe_management;

use Illuminate\Http\Request;

class Rapport_courbe_exemple_management extends Rapports_management {
	
	/**
	 * 
	 * Commentez ici le rapport
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on instancie un nouveau rapport de type liste (vous n'avez rien à modifier)
		$rapport = new Rapport_courbe_management();
		
		// on récupère les différents filtres (pour voir la liste des filtres dispo, allez dans App/Eden/Rapports/Filtres_et_options)
		$dates = $this->dates_mensuelles($rapport);
		$entites = $this->entites($rapport);
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$rapport->titre = "Nouveau titre";
		
		// la légende
		$rapport->legende('1ere valeur');
		$rapport->legende('2eme valeur');
		$rapport->legende('3eme valeur');
		
		// les séries & valeurs
		$rapport->serie('1ere série');
		$rapport->valeur('1ere série', 10);
		$rapport->valeur('1ere série', 20);
		$rapport->valeur('1ere série', 30);
		
		$rapport->serie('2eme série');
		$rapport->valeur('2eme série', 30);
		$rapport->valeur('2eme série', 20);
		$rapport->valeur('2eme série', 10);
		
		return $rapport->genere($ajax);
    }
	
	
	
	
	
}