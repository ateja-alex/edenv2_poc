<?php

return [

	"categorie" => "crm",
	"icone" => "table",
	"titre" => "Composants d'un article",
	"description" => "Liste les composants d'un article",
	'ordre' => 1,
	"type" => "requete_sql",
	"type_rapport" => "liste_libre",
	"type_element" => "composition_article",
    "type_element_fiche" => "article",
	"cle_primaire" => "id",
	"cle_etrangere" => "article_id",
	"requete_sql" => "SELECT composition_article.*,COALESCE(composition_article.prix_achat,c.prix_achat,a.prix_d_achat * COALESCE(c.quantite,1)) as prix_achat_std,COALESCE(composition_article.tarif,c.tarif,COALESCE(a.tarif_force,a.tarif) * COALESCE(c.quantite,1)) as tarif_std, COALESCE(composition_article.prix_achat,c.prix_achat,a.prix_d_achat * COALESCE(c.quantite,1)) * composition_article.quantite as pa_quantite, COALESCE(composition_article.tarif,c.tarif,COALESCE(a.tarif_force,a.tarif) * COALESCE(c.quantite,1)) * composition_article.quantite as tarif_quantite
	FROM composition_article
	JOIN article a ON a.id = composition_article.article_enfant_id
	LEFT JOIN (SELECT id,quantite,tarif,prix_achat FROM conditionnement c) c ON c.id = composition_article.conditionnement
	#eden_join#
	WHERE #eden_profils_et_inactif# AND #eden_filtres_pour_fiche# AND #eden_recherche# AND #eden_filtres#
	#eden_order_by#",
	"liste_libre" => [
		"type_element" => "composition_article",
		"id_rapport" => "article_composants",
		"desactiver_actions" => "0",
		"desactiver_options" => "0",
		"desactiver_creation" => "0",
		'colonnes' => [
			array("nom" => "Article", "valeur" => "article_enfant_id", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => "article_enfant_id","champ" => "article_enfant_id"),
			array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "2","champ" => "quantite"),
			array("nom" => "Conditionnement", "valeur" => "conditionnement", "ordre" => "3","champ" => "conditionnement"),
			array("nom" => "Prix d'achat", "valeur" => "prix_achat_std", "ordre" => "4","champ" => "prix_achat"),
			array("nom" => "Tarif", "valeur" => "tarif_std", "ordre" => "5", "champ" => "tarif"),
		],
		'calculs' => [
			array("nom_sql" => "pa_quantite", "type_calcul" => "SUM", "nom" => "Total PA", "unite" => "€", "ordre" => "1", "champ_reference" => "prix_achat"),
			array("nom_sql" => "tarif_quantite", "type_calcul" => "SUM", "nom" => "Total tarif", "unite" => "€", "ordre" => "2", "champ_reference" => "tarif"),
		],
		'filtres' => [],
		'couleurs' => [],
	]
];