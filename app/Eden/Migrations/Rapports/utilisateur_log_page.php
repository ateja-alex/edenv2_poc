<?php
	return [

	"categorie" => "administration",
	"icone" => "table",
	"titre" => "Connexion des utilisateurs",
	"description" => "Liste le nombre de connexion des utilisateurs en fonction d'intervalles de temps",
	'ordre' => 1,
	"type" => "requete_sql",
	"type_rapport" => "liste_libre",
	"type_element" => "log_page",
	"index_traduction" => "rapport.utilisateur_log_page",
	"requete_sql" => "WITH log_page AS (SELECT DISTINCT utilisateur_id, DATE(DATE) AS DATE FROM log_page)
SELECT utilisateur.id as id,
CONCAT(utilisateur.prenom, ' ', utilisateur.nom) AS affichage_utilisateur,
SUM(case when date > date_add(now(), INTERVAL -7 day) then 1 else 0 end) as sept_derniers_jours,
SUM(case when date > date_add(now(), INTERVAL -30 day) then 1 else 0 end) as trente_derniers_jours,
SUM(case when date > date_add(now(), INTERVAL -90 day) then 1 else 0 end) as quatre_vingt_dix_derniers_jours,
SUM(case when date > date_add(now(), INTERVAL -365 day) then 1 else 0 end) as trois_cents_soixante_cinq_derniers_jours,
COUNT(log_page.utilisateur_id) as nb_total_jours_actifs
FROM utilisateur
LEFT JOIN log_page ON log_page.utilisateur_id = utilisateur.id
 #eden_join#
WHERE COALESCE(utilisateur.inactif,0) = 0 AND #eden_recherche# AND #eden_filtres#
GROUP BY utilisateur.id
#eden_order_by#",
	"liste_libre" => [
		"type_element" => "log_page",
		"id_rapport" => "utilisateur_log_page",
		"desactiver_actions" => "0",
		"desactiver_options" => "0",
		"desactiver_creation" => "0",
		'colonnes' => [

			array("nom" => "#", "valeur" => "id", "ordre" => "0"),
			array("nom" => "Utilisateur", "valeur" => "affichage_utilisateur", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => "utilisateur_id"),
			array("nom" => "7 derniers jours", "valeur" => "sept_derniers_jours", "ordre" => "2"),
			array("nom" => "30 derniers jours", "valeur" => "trente_derniers_jours", "ordre" => "3"),
			array("nom" => "90 derniers jours", "valeur" => "quatre_vingt_dix_derniers_jours", "ordre" => "5"),
			array("nom" => "365 derniers jours", "valeur" => "trois_cents_soixante_cinq_derniers_jours", "ordre" => "6")
		],
		'calculs' => [

		],
		'filtres' => [

			array("nom_sql" => "utilisateur_id", "ordre" => "0"),
		],
		'couleurs' => [

		],
	]
];