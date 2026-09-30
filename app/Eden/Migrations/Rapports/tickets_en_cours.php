<?php
			return ["id_rapport" => "tickets_en_cours",
	"categorie" => "gestion_commerciale",
	"icone" => "table",
	"titre" => "Tickets en cours",
	"description" => "Liste des tickets en cours",
	"ordre" => "1014",
	"type" => "kanban",
	"kanban" => "statut",
	"kanban_colonnes" => "[0,10,20,30,50]",
	"kanban_entete_calcul_nombre" => "1",
	"kanban_afficher_utilisateur_1" => "utilisateur_id",
	"type_rapport" => "liste_libre",
	"type_element" => "ticket_client",
	"liste_libre" => [
		"type_element" => "ticket_client",
		"id_rapport" => "tickets_en_cours",
		'colonnes' => [
					array("liste_libre_id" => "57", "nom" => "#", "valeur" => "id", "ordre" => "1", "type" => "standard", ),
						array("liste_libre_id" => "57", "nom" => "Créé le", "valeur" => "cree_le", "ordre" => "2", "type" => "standard", ),
						array("liste_libre_id" => "57", "nom" => "Titre", "valeur" => "titre", "ordre" => "3", "type" => "standard", ),
						array("liste_libre_id" => "57", "nom" => "Statut", "valeur" => "statut", "ordre" => "4", "type" => "standard", ),
						array("liste_libre_id" => "57", "nom" => "Priorité", "valeur" => "priorite", "ordre" => "5", "type" => "standard", ),
						array("liste_libre_id" => "57", "nom" => "Affectation", "ordre" => "6", "methode" => "liste_affectation", "tri_desactive" => "1", "type" => "methode", ),
						],
				'calculs' => [
					],
				'filtres' => [
					array("liste_libre_id" => "57", "nom_sql" => "utilisateur_id", ),
						array("liste_libre_id" => "57", "nom_sql" => "statut", "ordre" => "1", ),
						array("liste_libre_id" => "57", "nom_sql" => "priorite", "ordre" => "2", ),
						],
				]];