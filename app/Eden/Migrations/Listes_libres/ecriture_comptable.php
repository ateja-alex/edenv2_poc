<?php

return [
	'type_element' => 'ecriture_comptable',
	'desactiver_options' => '1',
	'desactiver_creation' => '1',
	'desactiver_actions_individuelle' => '[]',
	'creation_taches_en_masse' => '1',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Numéro écriture", "valeur" => "ecriture_id", "ordre" => "1", ),
		array("nom" => "Date", "valeur" => "date", "ordre" => "3", ),
		array("nom" => "Journal", "valeur" => "journal_id.code_journal", "ordre" => "2", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Compte comptable", "valeur" => "compte_comptable_id", "ordre" => "3", ),
		array("nom" => "Débit", "valeur" => "debit", "ordre" => "7", "retour_a_la_ligne_impossible" => "1", ),
		array("nom" => "Crédit", "valeur" => "credit", "ordre" => "8", "retour_a_la_ligne_impossible" => "1", ),
		array("nom" => "Sens", "valeur" => "sens", "ordre" => "6", "type" => "standard", ),
		array("nom" => "Libellé", "valeur" => "libelle", "ordre" => "4", "type" => "standard", ),
		array("nom" => "Référence", "valeur" => "reference", "ordre" => "4", "type" => "standard", ),
		array("nom" => "Auxiliaire", "valeur" => "auxiliaire", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Intégration", "valeur" => "integration", "ordre" => "12", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "debit", "type_calcul" => "SUM", "nom" => "Débit", "unite" => "€", ),
		array("nom_sql" => "credit", "type_calcul" => "SUM", "nom" => "Crédit", "unite" => "€", "ordre" => "1", ),
	],
	'filtres' => [
		array("nom_sql" => "date", ),
		array("nom_sql" => "entite_id", "ordre" => "1", ),
		array("nom_sql" => "journal_id", "ordre" => "2", ),
		array("nom_sql" => "reference", "ordre" => "4", ),
		array("nom_sql" => "cree_le", "ordre" => "5", ),
		array("nom_sql" => "integration", "ordre" => "6", ),
	],
];