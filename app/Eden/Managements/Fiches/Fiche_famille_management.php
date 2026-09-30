<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;


use App\Eden\Models\Elements\Article;
use App\Eden\Models\Elements\Famille;

use App\Eden\Variables;

/**
 * Gestion des fiches d'article
 */
class Fiche_famille_management extends Fiche_management {

	/**
	 * 
	 * Prépare les données pour la fiche
	 * 
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

		// On récupère les sous familles
		$donnees['sous_familles'] = Famille::where('parent_id', $this->id_element)->get();

		//on définit le nombre d'éléments par page pour la pagination
		$nb_pages = 10;
		
		// On va chercher les articles de la famille
		$tous_les_articles = Article::where('famille_id', $this->id_element)->with('famille')->orderBy('ordre')->get();
		// $donnees['articles'] = $this->prepare_pagination($tous_les_articles, 1, $nb_pages);
		$donnees['articles'] = $tous_les_articles;

		// On définit le nombre de pages pour le tableau des articles
		$donnees['total_articles'] = ceil(count($tous_les_articles) / $nb_pages);

		return $donnees;
	}
}
