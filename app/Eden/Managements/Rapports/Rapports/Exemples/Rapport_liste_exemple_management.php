<?php

namespace App\Eden\Managements\Rapports\Rapports\Exemples;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
 * 
 * Commentez ici le rapport
 * 
 * Listes : les rapports de type liste sont similaire à la liste des clients par exemple
 * 
 * Ils conviennent pour par exemple :
 * 
 * Des listes de factures à relancer
 * Des listes de projets en cours
 * Etc.
 * 
 */
class Rapport_liste_exemple_management extends Rapports_management {
	
	/**
	 * 
	 * Commentez ici le rapport
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on instancie un nouveau rapport de type liste (vous n'avez rien à modifier)
		$rapport = new Rapport_liste_management();
		
		// on récupère les différents filtres (pour voir la liste des filtres dispo, allez dans App/Eden/Rapports/Filtres_et_options)
		$dates = $this->dates_mensuelles($rapport);
		$entites = $this->entites($rapport);
		
		// éventuellement si le titre du rapport doit être forcé (facultatif)
		// => on parle bien du titre global du rapport, pas de la ligne de titre de la table
		$rapport->titre = "Nouveau titre";
		
		// les titres de la liste
		$rapport->titres(array('titre 1', 'titre 2', 'titre 3'));
		
		// ligne 1
		$rapport->ligne(array('ligne 1.1', 'ligne 1.2', 'ligne 1.3'));
		
		// ligne 1
		$rapport->ligne(array('ligne 2.1', 'ligne 2.2', 'ligne 2.3'));
		
		return $rapport->genere($ajax);
    }
	
	
	
	
	
}