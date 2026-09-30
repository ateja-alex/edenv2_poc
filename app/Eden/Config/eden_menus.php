<?php

/* Généré le 03/08/2022 10:24 */

return [
		array(

			'route_parametres' => 'client',
			'id' => "liste_client",
			'type_2' => "liste",
			'nom' => "Clients",
			'route' => array('base_eden.liste.index', ['client']),
			'icone' => 'fa-user',
			'inactif' => '',
			'index_traduction' => 'menus.lien.liste_client',
			'ordre' => '1',
		),
		array(

			'id' => "menu_commerce",
			'nom' => "Commerce",
			'icone' => 'fa-calculator',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_commerce',
			'ordre' => '2',
			'sous_menus' => array(

				array(

					'route_parametres_sous_menu' => 'coupon_reduction',
					'id' => "liste_coupon_reduction",
					'type_2' => "liste",
					'nom' => "Coupons réduction",
					'route' => array('base_eden.liste.index', ['coupon_reduction']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.liste_coupon_reduction',
				),
				array(

					'route_parametres_sous_menu' => 'credit',
					'id' => "liste_credit",
					'type_2' => "liste",
					'nom' => "Crédits",
					'route' => array('base_eden.liste.index', ['credit']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.liste_credit',
				),
				array(

					'route_parametres_sous_menu' => 'famille',
					'id' => "liste_famille",
					'type_2' => "liste",
					'nom' => "Catégories d'articles",
					'route' => array('base_eden.liste.index', ['famille']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '3',
					'index_traduction' => 'menus.sous_menus.lien.liste_famille',
				),
				array(

					'id' => "liste_article",
					'type_2' => "autre",
					'nom' => "Articles",
					'route' => array('article.liste', []),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '4',
					'index_traduction' => 'menus.sous_menus.lien.liste_article',
				),
			),
		),
		array(

			'id' => "menu_vente",
			'nom' => "Ventes",
			'icone' => 'fa-calculator',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_vente',
			'ordre' => '3',
			'sous_menus' => array(

				array(

					'route_parametres_sous_menu' => 'devis_vente',
					'id' => "liste_devis",
					'type_2' => "liste",
					'nom' => "Devis",
					'route' => array('base_eden.liste.index', ['devis_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.liste_devis',
				),
				array(

					'route_parametres_sous_menu' => 'commande_vente',
					'id' => "liste_commande",
					'type_2' => "liste",
					'nom' => "Commandes",
					'route' => array('base_eden.liste.index', ['commande_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.liste_commande',
				),
				array(

					'route_parametres_sous_menu' => 'bl_vente',
					'id' => "liste_bl",
					'type_2' => "liste",
					'nom' => "BL",
					'route' => array('base_eden.liste.index', ['bl_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '3',
					'index_traduction' => 'menus.sous_menus.lien.liste_bl',
				),
				array(

					'route_parametres_sous_menu' => 'acompte_vente',
					'id' => "liste_acomptes",
					'type_2' => "liste",
					'nom' => "Acomptes",
					'route' => array('base_eden.liste.index', ['acompte_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '4',
					'index_traduction' => 'menus.sous_menus.lien.liste_acomptes',
				),
				array(

					'route_parametres_sous_menu' => 'facture_vente',
					'id' => "liste_facture",
					'type_2' => "liste",
					'nom' => "Factures",
					'route' => array('base_eden.liste.index', ['facture_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '5',
					'index_traduction' => 'menus.sous_menus.lien.liste_facture',
				),
				array(

					'route_parametres_sous_menu' => 'avoir_vente',
					'id' => "liste_avoir",
					'type_2' => "liste",
					'nom' => "Avoirs",
					'route' => array('base_eden.liste.index', ['avoir_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '6',
					'index_traduction' => 'menus.sous_menus.lien.liste_avoir',
				),
				array(

					'route_parametres_sous_menu' => 'bon_retour_vente',
					'id' => "liste_bon_retour",
					'type_2' => "liste",
					'nom' => "Bons de retour",
					'route' => array('base_eden.liste.index', ['bon_retour_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '7',
					'index_traduction' => 'menus.sous_menus.lien.liste_bon_retour',
				),
				array(

					'route_parametres_sous_menu' => 'bon_preparation_vente',
					'id' => "liste_bon_preparation_vente",
					'type_2' => "liste",
					'nom' => "Bons de préparation",
					'route' => array('base_eden.liste.index', ['bon_preparation_vente']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '8',
					'index_traduction' => 'menus.sous_menus.lien.liste_bon_preparation_vente',
				),
			),
		),
		array(

			'id' => "menu_achats",
			'nom' => "Achats",
			'icone' => 'fa-calculator',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_achats',
			'ordre' => '4',
			'sous_menus' => array(

				array(

					'route_parametres_sous_menu' => 'fournisseur',
					'id' => "liste_fournisseur",
					'type_2' => "liste",
					'nom' => "Fournisseurs",
					'route' => array('base_eden.liste.index', ['fournisseur']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.liste_fournisseur',
				),
				array(

					'route_parametres_sous_menu' => 'devis_achat',
					'id' => "liste_devis_achat",
					'type_2' => "liste",
					'nom' => "Devis",
					'route' => array('base_eden.liste.index', ['devis_achat']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.liste_devis_achat',
				),
				array(

					'route_parametres_sous_menu' => 'commande_achat',
					'id' => "liste_commande_achat",
					'type_2' => "liste",
					'nom' => "Commandes",
					'route' => array('base_eden.liste.index', ['commande_achat']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '3',
					'index_traduction' => 'menus.sous_menus.lien.liste_commande_achat',
				),
				array(

					'route_parametres_sous_menu' => 'bl_achat',
					'id' => "liste_bl_achat",
					'type_2' => "liste",
					'nom' => "BR",
					'route' => array('base_eden.liste.index', ['bl_achat']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '4',
					'index_traduction' => 'menus.sous_menus.lien.liste_bl_achat',
				),
				array(

					'route_parametres_sous_menu' => 'acompte_achat',
					'id' => "liste_acompte_achat",
					'type_2' => "liste",
					'nom' => "Acomptes",
					'route' => array('base_eden.liste.index', ['acompte_achat']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '5',
					'index_traduction' => 'menus.sous_menus.lien.liste_acompte_achat',
				),
				array(

					'route_parametres_sous_menu' => 'facture_achat',
					'id' => "liste_facture_achat",
					'type_2' => "liste",
					'nom' => "Factures",
					'route' => array('base_eden.liste.index', ['facture_achat']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '6',
					'index_traduction' => 'menus.sous_menus.lien.liste_facture_achat',
				),
				array(

					'route_parametres_sous_menu' => 'avoir_achat',
					'id' => "liste_avoir_achat",
					'type_2' => "liste",
					'nom' => "Avoirs",
					'route' => array('base_eden.liste.index', ['avoir_achat']),
					'icone' => 'fa-calculator',
					'inactif' => '',
					'ordre_sous_menu' => '7',
					'index_traduction' => 'menus.sous_menus.lien.liste_avoir_achat',
				),
                array(

                    'route_parametres_sous_menu' => 'bon_retour_achat',
                    'id' => "liste_bon_retour_achat",
                    'type_2' => "liste",
                    'nom' => "Bons de retour",
                    'route' => array('base_eden.liste.index', ['bon_retour_achat']),
                    'icone' => 'fa-calculator',
                    'inactif' => '',
                    'ordre_sous_menu' => '8',
                    'index_traduction' => 'menus.sous_menus.lien.liste_bon_retour_achat',
                ),
			),
		),
		array(

			'id' => "menu_projets",
			'nom' => "Projets",
			'icone' => 'fa-cogs',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_projets',
			'ordre' => '5',
			'sous_menus' => array(

				array(

					'id' => "saisie_temps",
					'type_2' => "autre",
					'nom' => "Saisie des temps",
					'route' => array('saisie_des_temps.index', []),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.saisie_temps',
				),
				array(

					'route_parametres_sous_menu' => 'projet',
					'id' => "liste_projet",
					'type_2' => "liste",
					'nom' => "Projets",
					'route' => array('base_eden.liste.index', ['projet']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.liste_projet',
				),
			),
		),
		array(

			'id' => "menu_stocks",
			'nom' => "Stocks",
			'icone' => 'fa-cubes',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_stocks',
			'ordre' => '6',
			'sous_menus' => array(

				array(

					'route_parametres_sous_menu' => 'transformation_stocks',
					'id' => "transformation_des_stocks",
					'type_2' => "liste",
					'nom' => "Transformation des Stocks",
					'route' => array('base_eden.liste.index', ['transformation_stocks']),
					'icone' => 'fas fa-exchange-alt',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.transformation_des_stocks',
				),
				array(

					'route_parametres_sous_menu' => 'inventaires_tournants',
					'id' => "inventaire_tournant",
					'type_2' => "rapport",
					'nom' => "Inventaire tournant",
					'route' => array('base_eden.rapport.index', ['inventaires_tournants']),
					'icone' => 'fas fa-recycle',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.inventaire_tournant',
				),
				array(

					'route_parametres_sous_menu' => 'commande_achat_ligne_fournisseur',
					'id' => "reception_commande",
					'type_2' => "liste",
					'nom' => "Réception commande",
					'route' => array('base_eden.liste.index', ['commande_achat_ligne_fournisseur']),
					'icone' => 'fas fa-file-invoice',
					'inactif' => '',
					'ordre_sous_menu' => '3',
					'index_traduction' => 'menus.sous_menus.lien.reception_commande',
				),
				array(

					'route_parametres_sous_menu' => 'mouvement_de_stock',
					'id' => "liste_mouvement_stocks",
					'type_2' => "liste",
					'nom' => "Mvt stocks",
					'route' => array('base_eden.liste.index', ['mouvement_de_stock']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '4',
					'index_traduction' => 'menus.sous_menus.lien.liste_mouvement_stocks',
				),
				array(

					'route_parametres_sous_menu' => 'production_nomenclature',
					'id' => "liste_production_nomenclature",
					'type_2' => "liste",
					'nom' => "Production",
					'route' => array('base_eden.liste.index', ['production_nomenclature']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '5',
					'index_traduction' => 'menus.sous_menus.lien.liste_production_nomenclature',
				),
				array(

					'route_parametres_sous_menu' => 'gestion_des_stocks',
					'id' => "inventaire",
					'type_2' => "rapport",
					'nom' => "Etat des stocks",
					'route' => array('base_eden.rapport.index', ['gestion_des_stocks']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '6',
					'index_traduction' => 'menus.sous_menus.lien.inventaire',
				),
			),
		),
		array(

			'id' => "menu_treso",
			'nom' => "Tréso",
			'icone' => 'fa-dollar-sign',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_treso',
			'ordre' => '7',
			'sous_menus' => array(

				array(

					'route_parametres_sous_menu' => 'charge_recurrente',
					'id' => "saisie_treso",
					'type_2' => "autre",
					'nom' => "Suivi tréso",
					'route' => array('tresorerie.index', ['charge_recurrente']),
					'icone' => 'fa-dollar-sign',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.saisie_treso',
				),
				array(

					'id' => "budget_insight",
					'type_2' => "autre",
					'nom' => "Rapprochement",
					'route' => array('budget_insight.index', []),
					'icone' => 'fa-dollar-sign',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.budget_insight',
				),
			),
		),
		array(

			'id' => "ged",
			'type_2' => "autre",
			'nom' => "Bibliothèque",
			'route' => array('bibliotheque.index', []),
			'icone' => 'fa-file-word',
			'inactif' => '',
			'index_traduction' => 'menus.lien.ged',
			'ordre' => '8',
		),
		array(

			'id' => "menu_blog",
			'nom' => "Blog",
			'icone' => 'fa-cogs',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_blog',
			'ordre' => '9',
			'sous_menus' => array(

				array(

					'route_parametres_sous_menu' => 'blog_categorie',
					'id' => "liste_blog_categorie",
					'type_2' => "liste",
					'nom' => "Catégories",
					'route' => array('base_eden.liste.index', ['blog_categorie']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.liste_blog_categorie',
				),
				array(

					'route_parametres_sous_menu' => 'blog_article',
					'id' => "liste_blog_article",
					'type_2' => "liste",
					'nom' => "Articles",
					'route' => array('base_eden.liste.index', ['blog_article']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '2',
					'index_traduction' => 'menus.sous_menus.lien.liste_blog_article',
				),
			),
		),
		array(

			'id' => "menu_configuration",
			'nom' => "Configuration",
			'icone' => 'fa-cogs',
			'inactif' => '',
			'index_traduction' => 'menus.categorie.menu_configuration',
			'ordre' => '10',
			'sous_menus' => array(

				array(

					'id' => "champs_libres",
					'type_2' => "autre",
					'nom' => "Champs libres",
					'route' => array('parametrage.table_libre.liste', []),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '1',
					'index_traduction' => 'menus.sous_menus.lien.champs_libres',
				),
				array(

					'id' => "liste_utilisateur",
					'type_2' => "autre",
					'nom' => "Utilisateurs",
					'route' => array('parametrage.utilisateur.liste', []),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '3',
					'index_traduction' => 'menus.sous_menus.lien.liste_utilisateur',
				),
				array(

					'id' => "liste_profils",
					'type_2' => "autre",
					'nom' => "Profils",
					'route' => array('base_eden.liste.index', ['profil']),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '4',
					'index_traduction' => 'menus.sous_menus.lien.liste_profils',
				),
				array(

					'id' => "autres_parametres",
					'type_2' => "autre",
					'nom' => "Autres parametres",
					'route' => array('parametrage.elements', []),
					'icone' => 'fa-cogs',
					'inactif' => '',
					'ordre_sous_menu' => '6',
					'index_traduction' => 'menus.sous_menus.lien.autres_parametres',
				),
			),
		),
];