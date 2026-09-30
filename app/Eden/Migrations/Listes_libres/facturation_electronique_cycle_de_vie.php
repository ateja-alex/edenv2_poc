<?php

return [
	'type_element' => 'facturation_electronique_cycle_de_vie',
	'colonnes' => [
		array("nom" => "Facturation électronique achat", "valeur" => "facturation_electronique_achat_id", "ordre" => "1", "lien_vers_element" => "0", "lien_vers_autre_element" => "facturation_electronique_achat", "type" => "standard", ),
		array("nom" => "Facture de vente", "valeur" => "facture_vente_id", "ordre" => "2", "lien_vers_element" => "0", "lien_vers_autre_element" => "facture_vente", "type" => "standard", ),
		array("nom" => "Avoir de vente", "valeur" => "avoir_vente_id", "ordre" => "3", "lien_vers_element" => "0", "lien_vers_autre_element" => "avoir_vente", "type" => "standard", ),
		array("nom" => "Statut (achat)", "valeur" => "statut_achat", "ordre" => "4", "type" => "standard", ),
		array("nom" => "Statut (vente)", "valeur" => "statut_vente", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Motif", "valeur" => "motif", "ordre" => "6", "type" => "standard", ),
		array("nom" => "Fichier CDAR", "valeur" => "fichier_cdar", "ordre" => "7", "type" => "standard", ),
		array("nom" => "Statut d'envoi", "valeur" => "statut_envoi", "ordre" => "8", "type" => "standard", ),
		array("nom" => "Motif erreur d'envoi", "valeur" => "motif_erreur_envoi", "ordre" => "9", "type" => "standard", ),
		array("nom" => "Date de l'évènement", "valeur" => "date_evenement", "ordre" => "10", "type" => "standard", ),
		array("nom" => "Créé le", "valeur" => "cree_le", "ordre" => "11", "type" => "standard", ),
	],
	'calculs' => [],
	'filtres' => [
		array("nom_sql" => "facturation_electronique_achat_id", "ordre" => "1", ),
		array("nom_sql" => "facture_vente_id", "ordre" => "2", ),
		array("nom_sql" => "avoir_vente_id", "ordre" => "3", ),
	],
];
