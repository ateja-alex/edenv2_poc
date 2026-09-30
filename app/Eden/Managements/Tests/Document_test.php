<?php

namespace App\Eden\Managements\Tests;

class Document_test extends Element_test {

	protected function test_creation() {

		$retour = parent::test_creation();

		if($retour === true)
			$retour = $this->management->valide();

		return $retour;
	}

	protected function donnees_test_enregistrement($type_element) {
		
		$donnees = parent::donnees_test_enregistrement($type_element);

		$donnees['articles'] = [];

		$article = modele('article')->first();
		
		if(!empty($article))
			$donnees['articles'][] = 	[
											'article_id' => $article->id,
											'quantite' => 1,
											'designation' => 'test '.date('d/m/Y H:i:s'),
											'tarif' => 100,
										];

		$donnees_test = test('adresse')->donnees_test_enregistrement('adresse');
		$adresse_management = management('adresse');
		$adresse_management->enregistre($donnees_test);

		$donnees['adresse_de_livraison'] = $adresse_management->modele->id;
		$donnees['adresse_de_facturation'] = $adresse_management->modele->id;

		return $donnees;
	}


	protected function test_suppression($management) {
		
		management('adresse', $management->modele->adresse_de_livraison)->supprime();

		$retour = parent::test_suppression($management);

		return $retour;
	}

}