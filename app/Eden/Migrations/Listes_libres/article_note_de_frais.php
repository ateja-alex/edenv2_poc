<?php

return [
	'type_element' => 'article_note_de_frais',
	'desactiver_actions_individuelle' => '[]',
	'formulaire_modale' => '1',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id"),
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "1", ),
		array("nom" => "Plafonné ?", "valeur" => "remboursement_plafonne", "ordre" => "2", ),
		array("nom" => "Plafond", "valeur" => "plafond", "ordre" => "3", ),
		array("nom" => "Compte comptable", "valeur" => "compte_comptable", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Famille OCR", "valeur" => "categorie_depense_mindee", "ordre" => "6", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
	],
];