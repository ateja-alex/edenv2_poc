<?php

return [

	'id_rapport' => 'fiche_client_facture_vente_lignes',
	'type_element' => 'facture_vente_lignes',
	'fiche' => 'client',
	'cle_etrangere' => 'facture_vente_lignes.client_id',

	'colonnes' => [
		array(
			'nom' => 'Date',
			'valeur' => 'document_id.date',
			'type' => 'standard',
			),
		array(
			"nom" => "Article",
			'valeur' => 'article_id',
			),
		array(
			"nom" => "Quantité",
			'valeur' => 'quantite',
			),
		array(
			"nom" => "Remise",
			'valeur' => '#remise# %',
			'type' => 'concatenation',
			),
		array(
			"nom" => "Tarif",
			'valeur' => 'tarif',
			),
	],
	'calculs' => [
	],

	'filtres' => [
		array(
			"type_element" => "facture_vente",
			"nom_sql" => "date",
		),
	],
];