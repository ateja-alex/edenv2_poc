<?php
namespace App\Eden;

/**
 *
 * Gère les variables globales pour Eden
 *
 */
abstract class Variables {

    public static $types_logs = [

		'creation' => 1,
		'modification' => 2,
		'suppression' => 3,
		'validation' => 4,
		'reglement' => 5,
		'email' => 6,
		'acceptation' => 7,
		'facturation' => 8,
		'expedition' => 9,
		'comptabilisation' => 10,
		'annulation_par_avoir' => 11,
		'refus' => 12,
		'annulation_reglement' => 13,
		'envoi_par_mail' => 14,
		'validation_reglement' => 15,
		'demande_de_relecture' => 16,
		'commande_fournisseur_realisee' => 17,
		'commande_fournisseur_recue' => 18,
		'demande_approbation' => 19,
		'approbation_emise' => 20,
		'approbation_refusee' => 21,
		'saisie_paiement' => 22,
		'commentaire_sur_mesure' => 23,
		'annulation_devis' => 24,
		'mise_en_attente' => 25,
		'demande_signature' => 26,
		'signature' => 27,
		'conversion' => 28,

    ];

    public static $historique_intitule = [

    	// Les intitulés sont les références vers les traductions.
		1 => ['fa-pencil-alt', 		'composant.historique.action_creation'],
		2 => ['fa-pencil-alt', 		'composant.historique.action_modification'],
		3 => ['fa-trash', 			'composant.historique.action_supression'],
		4 => ['fa-check', 			'composant.historique.action_validation'],
		5 => ['fa-credit-card', 	'composant.historique.action_reglement'],
		6 => ['fa-envelope', 		'composant.historique.action_email'],
		7 => ['fa-check', 			'composant.historique.action_acceptation'],
		8 => ['fa-check', 			'composant.historique.action_facturation'],
		9 => ['fa-check', 			'composant.historique.action_expedition'],
		10 => ['fa-check', 			'composant.historique.action_comptabilisation'],
		11 => ['fa-trash', 			'composant.historique.action_annulation_par_avoir'],
		12 => ['fa-times', 			'composant.historique.action_refus'],
		13 => ['fa-times', 			'composant.historique.action_annulation_reglement'],
		14 => ['fa-envelope', 		'composant.historique.action_envoi_par_mail'],
		15 => ['fa-check', 			'composant.historique.action_validation_reglement'],
		16 => ['fa-check', 			'composant.historique.action_demande_de_relecture'],
		17 => ['fa-check', 			'composant.historique.action_articles_commandes_ou_en_stock'],
		18 => ['fa-check', 			'composant.historique.action_tous_articles_prets_pour_expedition'],
		19 => ['fa-hourglass-half','composant.historique.action_demande_approbation_emise'],
		20 => ['fa-check', 			'composant.historique.action_approbation_acceptee'],
		21 => ['fa-times', 			'composant.historique.action_approbation_refusee'],
		22 => ['fa-credit-card', 	'composant.historique.action_saisie_paiement'],
		23 => ['fa-check', 			'composant.historique.action_commentaire'],
		24 => ['fa-times', 			'composant.historique.action_annulation_devis'],
		25 => ['fa-pause', 			'composant.historique.action_mise_attente'],
		26 => ['fa-pen-nib', 		'composant.historique.action_demande_signature'],
		27 => ['fa-signature', 		'composant.historique.action_signature_effectuee'],
		28 => ['fa-plus-circle', 	'composant.historique.conversion'],

    ];

    public static $documents_gescom = [

		// ventes
		'devis_vente',
		'commande_vente',
		'bon_preparation_vente',
		'bl_vente',
		'bon_retour_vente',
		'acompte_vente',
		'facture_vente',
		'avoir_vente',

		// achats
		'devis_achat',
		'commande_achat',
		'bl_achat',
		'acompte_achat',
		'facture_achat',
		'avoir_achat',
		'bon_retour_achat',
    ];

    public static function documents_gescom_disponibles(){
        return array_filter(self::$documents_gescom, fn($type_document) => fonctionnalite('gescom_' . $type_document));
    }

    public static function documents_vente_gescom_disponibles(){
        return array_filter(self::$documents_vente_gescom, fn($type_document) => fonctionnalite('gescom_' . $type_document));
    }

    public static function documents_achat_gescom_disponibles(){
        return array_filter(self::$documents_achat_gescom, fn($type_document) => fonctionnalite('gescom_' . $type_document));
    }

    public static function documents_gescom_lignes_disponibles(){
        return array_map(fn($d) => $d . '_lignes', self::documents_gescom_disponibles(self::$documents_gescom));
    }

    public static $documents_gescom_lignes = [

		// ventes
		'devis_vente_lignes',
		'commande_vente_lignes',
		'bon_preparation_vente_lignes',
		'bl_vente_lignes',
		'bon_retour_vente_lignes',
		'acompte_vente_lignes',
		'facture_vente_lignes',
		'avoir_vente_lignes',

		// achats
		'devis_achat_lignes',
		'commande_achat_lignes',
		'bl_achat_lignes',
		'acompte_achat_lignes',
		'facture_achat_lignes',
		'avoir_achat_lignes',
		'bon_retour_achat_lignes',
    ];

    public static $documents_vente_gescom = [

		'devis_vente',
		'commande_vente',
		'bon_preparation_vente',
		'bl_vente',
		'bon_retour_vente',
		'acompte_vente',
		'facture_vente',
		'avoir_vente',
    ];

    public static $documents_avoir_et_retour_vente = [

		'avoir_vente',
		'bon_retour_vente',
    ];

    public static $documents_vente_gescom_lignes = [

		'devis_vente_lignes',
		'commande_vente_lignes',
		'bon_preparation_vente_lignes',
		'bl_vente_lignes',
		'bon_retour_vente_lignes',
		'acompte_vente_lignes',
		'facture_vente_lignes',
		'avoir_vente_lignes',
    ];

	public static $documents_vente_gescom_lignes_classique = [

		'devis_vente_lignes',
		'commande_vente_lignes',
		'bl_vente_lignes',
		'acompte_vente_lignes',
		'facture_vente_lignes',
		'avoir_vente_lignes',
    ];

    public static $documents_achat_gescom = [

		'devis_achat',
		'commande_achat',
		'bl_achat',
		'acompte_achat',
		'facture_achat',
		'avoir_achat',
		'bon_retour_achat',
    ];

    public static $documents_achat_gescom_lignes = [

		'devis_achat_lignes',
		'commande_achat_lignes',
		'bl_achat_lignes',
		'acompte_achat_lignes',
		'facture_achat_lignes',
		'avoir_achat_lignes',
		'bon_retour_achat_lignes',
    ];

    public static $documents_comptabilisable = [

        'acompte_achat',
        'acompte_vente',
        'avoir_achat',
        'avoir_vente',
        'facture_achat',
        'facture_vente',
        'note_de_frais'
    ];

    public static $bloc_documents_lies_vente = [
		'devis_vente',
		'commande_vente',
		'commande_achat',
		'bon_preparation_vente',
		'bl_vente',
		'acompte_vente',
		'facture_vente',
		'avoir_vente',
		'bon_retour_vente'
    ];

    public static $bloc_documents_lies_achat = [

		'devis_achat',
		'commande_achat',
		'commande_vente',
		'bl_achat',
		'acompte_achat',
		'facture_achat',
		'avoir_achat',
		'bon_retour_achat'
    ];

    public static $repartition_bloc_documents_lies_vente = [
		['devis_vente'],
        ['commande_vente'],
        ['commande_achat'],
        [
            'bon_preparation_vente',
            'bl_vente'
        ],
        [
            'acompte_vente',
            'facture_vente'
        ],
        [
            'avoir_vente',
            'bon_retour_vente'
        ],
    ];

    public static $repartition_bloc_documents_lies_achat = [
		['devis_achat'],
        ['commande_achat'],
        ['commande_vente'],
        ['bl_achat'],
        [
            'acompte_achat',
            'facture_achat'
        ],
        [
            'avoir_achat',
            'bon_retour_achat'
        ],
    ];

    public static $correspondance_jour_microsoft = [

        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

    public static $correspondance_jour_google = [

        'MO' => 1,
        'TU' => 2,
        'WE' => 3,
        'TH' => 4,
        'FR' => 5,
        'SA' => 6,
        'SU' => 7,
    ];

    public static $correspondance_frequence = [

        'daily'=> 1,
        'weekly'=> 2,
        'absoluteMonthly'=> 3,
        'relativeMonthly'=> 3,
        'absoluteYearly'=> 4,
        'relativeYearly'=> 4,
    ];

    public static $correspondance_frequence_relative = [

        'first'=> 1,
        'second'=> 2,
        'third'=> 3,
        'fourth'=> 4,
        'last'=> 5,
    ];

    public static $correspondance_couleurs_maquette = [

        1 => 'couleur_police_nom_application',
        2 => 'background_navbar',
        3 => 'background_menus',
        4 => 'background_menus_extranet',
        5 => 'background_menus_hover',
        6 => 'background_sous_menus',
        7 => 'couleur_texte_menus',
        8 => 'couleur_liens',
        9 => 'background_tache',
        10 => 'police_tache',
        11 => 'highcharts-color-#numero_couleur#'
    ];

    public static $champs_compatibles = [

        0 => [ //'Texte',
            ['type' => 0,],
            ['type' => 6,],
        ],
        1 => [ //'Liste libre',
            ['type' => 1, 'champ_compatible' => 'liste_choix']
        ],
        2 => [ //'Nombre entier',
            ['type' => 2,],
            ['type' => 3,],
        ],
        3 => [ //'Nombre décimal',
            ['type' => 3,]
        ],
        4 => [ //'Date',
            ['type' => 4,]
        ],
        5 => [ //'Date et heure',
            ['type' => 5,]
        ],
        6 => [ //'Zone de texte',
            ['type' => 6,]
        ],
        7 => [ //'Import de fichier',
            ['type' => 7,]
        ],
        8 => [ // Heure
            ['type' => 8]
        ],
        9 => [ //'Sélection couleur',
            ['type' => 9,]
        ],
        10 => [ //'Sélection multiple (checkbox)'
            ['type' => 10,]
        ],
        13 => [ //'Note'
            ['type' => 13,]
        ],
        14 => [ //'Numérotation automatique'
            ['type' => 14,]
        ],
        15 => [ //'Dropzone (import multiple)'
            ['type' => 15,]
        ],
        16 => [ //'Tableau'
            ['type' => 16,]
        ],
        17 => [ //'Pourcentage'
            ['type' => 17,]
        ],
        18 => [ //'Timestamp'
            ['type' => 18,]
        ],
        20 => [ //'Liste formatée'
            ['type' => 20, 'champ_compatible' => 'liste_choix']
        ],
        21 => [ //'Type Element Dynamique'
            ['type' => 21,]
        ],
        22 => [ //'ID Element Dynamique'
            ['type' => 22,]
        ],
        42 => [ //'Sélection élément'
            ['type' => 42, 'champ_compatible' => 'type_element_ajax']
        ],
    ];

    public static $formats_champ_texte_verifiables = [

        'siren',
        'siret',
        'bic',
        'iban',
        'secu_sociale',
        'tva_intra'
    ];

    public static $champs_tache_synchronises = [

        'date_de_debut',
        'date_de_fin',
        'titre',
        'commentaire',
        'prive',
        'journee_entiere',
        'type_tache_rdv',
        'participants',
        'statut_participant',
        'visioconference'
    ];

    public static function mois_de_lannee_format_3($mois) {

		switch($mois) {

			case '01':
			case '1':
				return traduction('interface.mois_format_3.janvier');

			case '02':
			case '2':
				return traduction('interface.mois_format_3.fevrier');

			case '03':
			case '3':
				return traduction('interface.mois_format_3.mars');

			case '04':
			case '4':
				return traduction('interface.mois_format_3.avril');

			case '05':
			case '5':
				return traduction('interface.mois_format_3.mai');

			case '06':
			case '6':
				return traduction('interface.mois_format_3.juin');

			case '07':
			case '7':
				return traduction('interface.mois_format_3.juillet');

			case '08':
			case '8':
				return traduction('interface.mois_format_3.aout');

			case '09':
			case '9':
				return traduction('interface.mois_format_3.septembre');

			case '10':
				return traduction('interface.mois_format_3.octobre');

			case '11':
				return traduction('interface.mois_format_3.novembre');

			case '12':
				return traduction('interface.mois_format_3.decembre');
		}
    }

    public static function mois_de_lannee_format_complet($mois) {

		switch($mois) {

			case '01':
			case '1':
				return traduction('interface.mois_complet.janvier');

			case '02':
			case '2':
				return traduction('interface.mois_complet.fevrier');

			case '03':
			case '3':
				return traduction('interface.mois_complet.mars');

			case '04':
			case '4':
				return traduction('interface.mois_complet.avril');

			case '05':
			case '5':
				return traduction('interface.mois_complet.mai');

			case '06':
			case '6':
				return traduction('interface.mois_complet.juin');

			case '07':
			case '7':
				return traduction('interface.mois_complet.juillet');

			case '08':
			case '8':
				return traduction('interface.mois_complet.aout');

			case '09':
			case '9':
				return traduction('interface.mois_complet.septembre');

			case '10':
				return traduction('interface.mois_complet.octobre');

			case '11':
				return traduction('interface.mois_complet.novembre');

			case '12':
				return traduction('interface.mois_complet.decembre');
		}
    }

    public static function tableau_mois_de_lannee_format_complet() {

		$clefs = ['1', '01', '2', '02', '3', '03', '4', '04', '5', '05', '6', '06', '7', '07', '8', '08', '9', '09', '10', '11', '12'];

		$retour = [];
		foreach($clefs as $clef) {
			$retour[$clef] = Variables::mois_de_lannee_format_complet($clef);
		}

		return $retour;
    }

    public static function mois_de_lannee_format_complet_majuscule($mois) {

		switch($mois) {
			case '01':
			case '1':
				return traduction('interface.mois_complet_majuscule.janvier');

			case '02':
			case '2':
				return traduction('interface.mois_complet_majuscule.fevrier');

			case '03':
			case '3':
				return traduction('interface.mois_complet_majuscule.mars');

			case '04':
			case '4':
				return traduction('interface.mois_complet_majuscule.avril');

			case '05':
			case '5':
				return traduction('interface.mois_complet_majuscule.mai');

			case '06':
			case '6':
				return traduction('interface.mois_complet_majuscule.juin');

			case '07':
			case '7':
				return traduction('interface.mois_complet_majuscule.juillet');

			case '08':
			case '8':
				return traduction('interface.mois_complet_majuscule.aout');

			case '09':
			case '9':
				return traduction('interface.mois_complet_majuscule.septembre');

			case '10':
      		return traduction('interface.mois_complet_majuscule.octobre');

			case '11':
      		return traduction('interface.mois_complet_majuscule.novembre');

			case '12':
      		return traduction('interface.mois_complet_majuscule.decembre');
      }
    }

	public static function jours($jour) {

		switch($jour) {
			case 1:
				return traduction('interface.jours.lundi');

			case 2:
				return traduction('interface.jours.mardi');

			case 3:
				return traduction('interface.jours.mercredi');

			case 4:
				return traduction('interface.jours.jeudi');

			case 5:
				return traduction('interface.jours.vendredi');

			case 6:
				return traduction('interface.jours.samedi');

			case 7:
				return traduction('interface.jours.dimanche');

		}
	}

	public static function jours_francais($jour) {

		switch($jour) {
			case 1:
				return 'lundi';

			case 2:
				return 'mardi';

			case 3:
				return 'mercredi';

			case 4:
				return 'jeudi';

			case 5:
				return 'vendredi';

			case 6:
				return 'samedi';

			case 7:
				return 'dimanche';

		}
	}

	public static function jours_format_3($jour) {

		switch($jour) {
			case 1:
				return traduction('interface.jours_format_3.lundi');

			case 2:
				return traduction('interface.jours_format_3.mardi');

			case 3:
				return traduction('interface.jours_format_3.mercredi');

			case 4:
				return traduction('interface.jours_format_3.jeudi');

			case 5:
				return traduction('interface.jours_format_3.vendredi');

			case 6:
				return traduction('interface.jours_format_3.samedi');

			case 7:
				return traduction('interface.jours_format_3.dimanche');

		}
	}

	public static function tableau_jours() {

		$clefs = [1, 2, 3, 4, 5, 6, 7];

		$retour = [];
		foreach($clefs as $clef) {
			$retour[$clef] = Variables::jours($clef);
		}

		return $retour;
	}


    public static function types_champs_libres() {

		return array(

			'-5' => 'Sous formulaire',
			'-4' => 'Fin onglet',
			'-3' => 'Onglet',
			'-2' => 'HTML',
			'-1' => 'Titre',
			'0' => 'Texte',
			'1' => 'Liste libre',
			'2' => 'Nombre entier',
			'3' => 'Nombre décimal',
			'4' => 'Date',
			'5' => 'Date et heure',
			'6' => 'Zone de texte',
			'7' => 'Import de fichier',
            '8' => 'Heure',
            '9' => 'Sélection couleur',
			'10' => 'Sélection multiple',
			'13' => 'Note',
			'14' => 'Numérotation automatique',
			'15' => 'Dropzone (import multiple)',
			'16' => 'Tableau',
            '17' => 'Pourcentage',
			'18' => 'Timestamp',
            '20' => 'Liste formatée',
            '21' => 'Type Element Dynamique',
            '22' => 'ID Element Dynamique',
			'42' => 'Sélection élément',
		);
    }

    public static function types_champs_libres_bdd(){
        return array(
            '-2' => 'VARCHAR(1000)',
            '0' => 'VARCHAR(300)',
            '1' => 'INT',
            '2' => 'BIGINT',
            '3' => 'DECIMAL(27,6)',
            '4' => 'DATE',
            '5' => 'DATETIME',
            '6' => 'LONGTEXT',
            '7' => 'VARCHAR(255)',
            '8' => 'TIME',
            '9' => 'VARCHAR(10)',
            '10' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            '13' => 'INT',
            '14' => 'VARCHAR(255)',
            '15' => 'LONGTEXT',
            '16' => 'LONGTEXT',
            '17' => 'DECIMAL(14,8)',
            '18' => 'TIMESTAMP',
            '20' => 'INT',
            '22' => 'INT',
            '42' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
        );
    }

    public static function liste_formatees() {

		return array(
            '1' => 'Types de liens de menus',            
            '2' => 'Noms des couleurs dans la maquette',
            '3' => 'Oui / Non / Sans valeur',
            '4' => 'Compte bancaire',
            '6' => 'Conditions de paiement',
            '7' => 'Modes de paiement',
            '8' => 'Entrepôts',
            '9' => 'Couleurs d\'événements Google',
            '10' => 'Types de fréquences',
            '11' => 'Valeurs badges jours pour les récurrences',
            '12' => 'Jours',
            '13' => 'Nombres ordinaux',
            '14' => 'Oui / Non',
            '15' => 'Catégorie du blog',
            '16' => 'Type de coupon réduction',
            '17' => 'Employés',
            '18' => 'Catégories activités de projet',
            '19' => 'Types de comptes email',
            '20' => 'Restrictions ERP',
            '21' => 'Type de blocage blacklist email ticket_client',
            '25' => 'Devises',
            '26' => 'Langues',
            '27' => 'CGV',
            '28' => 'Pays',
            '29' => 'Comptes comptables',
            '30' => 'Journaux comptables',
            '31' => 'Types d\'adresse',
            '32' => 'Canaux de vente',
            '33' => 'Types d\'échanges',
            '34' => 'Statuts des paniers',
            '35' => 'Statuts des rappels',
            '36' => 'Catégories de modèles d\'email',
            '38' => 'Statut des clients',
            '39' => 'Civilités',
            '40' => 'Groupes de recouvrement',
            '41' => 'Origine des erreurs',
            '42' => 'Origine des crédits',
            '43' => 'Etape des leads',
            '44' => 'Statut des leads',
            '45' => 'Chaleur des leads / prospects / projets (prospection)',
            '46' => 'Type de question dans les questionnaires',
            '50' => 'Entrepots',
            '51' => "Statut des activités sur les projets",
            '52' => "Statut des projets",
            '53' => "Périodes de debut de demande de congés",
            '54' => "Raisons de demande de congés",
            '55' => "Types de coupons réduction",
            '56' => "Statut des transactions budget insight",
            '57' => "Type de relance",
            '58' => "Type de déclinaisons sur les articles",
            '59' => "Familles de déclinaisons",
            '60' => "Types de récurrence",
            '61' => "Modèle à utiliser pour la récurrence",
            '62' => "Types d'article",
            '63' => "Statut demande de prix",
            '64' => "Equipe",
            '65' => "Statuts des bugs pour les recette clients / easy dev",
            '66' => "Membres equipe Easy Dév",
            '67' => "Criticité des bugs",
            '68' => "Comptes bancaires budget insight",
            '69' => "Synchro compte mail",
            '70' => "Types d'action pour les workflow",
            '71' => "Liste des types elements",
            '72' => "Liste des triggers possibles pour les workflows",
            '73' => "Transports de notification",
            '75' => "Type de facture (avancement)",
            '76' => "Liste des fournisseurs",
            '78' => "Type de d'élément dans le tableau de bord",
            '80' => 'Statut des interventions de maintenance',
            '81' => "Unités de vente pour les articles",
            '82' => "Liste des documents de gestion commerciale (Vente et achat)",
            '83' => "Liste de tous les modèles pour tous les devis_vente",
            '84' => "Liste de tous les modèles pour tous les acompte_vente",
            '85' => "Liste de tous les modèles pour tous les avoir_vente",
            '86' => "Liste de tous les modèles pour tous les bl_vente",
            '87' => "Liste de tous les modèles pour tous les facture_vente",
            '88' => "Liste de tous les modèles pour tous les avoir_achat",
            '89' => "Liste de tous les modèles pour tous les bl_achat",
            '90' => "Liste de tous les modèles pour tous les commande_achat",
            '91' => "Liste de tous les modèles pour tous les devis_achat",
            '92' => "Liste de tous les modèles pour tous les facture_achat",
            '93' => "Liste de tous les modèles pour tous les commande_vente",
            '94' => "Thèmes de filtres",
            '95' => "Transporteurs",
            '96' => "Statuts des propositions commerciales (statut interne)",
            '97' => "Statuts des propositions commerciales (réponse)",
            '98' => "Type ligne rapport paramétrable",
            '99' => "Probabilité",
            '100' => "Statuts des devis vente",
            '101' => "Statuts des commandes vente",
            '102' => "Statuts des bl vente",
            '190' => "Liste de tous les modèles pour tous les acompte_achat",
            '191' => "Liste de tous les modèles pour tous les bon_preparation_vente",
            '192' => "Liste de tous les modèles pour tous les bon_retour_vente",
            '193' => "Liste de tous les modèles pour tous les bon_retour_achat",
            '103' => "Statuts des acomptes vente",
            '104' => "Statuts des factures vente",
            '107' => "Statuts des avoirs vente",
            '108' => "Statuts des commandes achat",
            '109' => "Type de mouvement de stock",
            '110' => "Statuts pour les lignes de BL VENTE",
            '111' => "Liste des adresses internes",
            '112' => "Type de TVA",
            '113' => "Sens de TVA",
            '114' => "Catégories comptables",
            '117' => "Liste du theme pour un indicateur dans un bloc",
            '118' => "Liste des documents de vente",
            '120' => "Liste des types de bugs",
            '121' => "Liste des types de destinataire pour les e-mails",
            '124' => "Statuts des devis achat",
            '125' => "Statuts des commandes achat",
            '126' => "Statuts des bl achat",
            '127' => "Statuts des acomptes achat",
            '128' => "Statuts des factures achat",
            '129' => "Statuts des avoirs achat",
            '130' => 'Années civiles',
            '131' => "Liste des types d'actions pour les approbations",
            '140' => "Disponibilité des articles pour leur saisie",
            '141' => "Statut bordereau",
            '143' => "Liste des types de numéros de série des articles",
            '144' => "Devis : accepté / refusé / annulé",
            '145' => "Criticité des bugs pour suivi_recette_easydev",
            '149' => "Status pour les approbations",
            '150' => "Status pour les tickets hotline (les clients des clients)",
            '160' => "Type d'utilisateur",
            '200' => "Budget : types de rubriques",
            '300' => "Type de vue pour les vues sql",
            '301' => "Statut pour les notes de frais",
            '302' => "Statut des lignes sur les documents",
            '303' => "Statut des lignes sur les documents (commande_vente => commande_achat) (le fait de commander chez le frs)",
            '304' => "Statut des lignes sur les documents (commande_vente => commande_achat) (le fait d'attendre une livraison)",
            '310' => "Natures des articles",
            '311' => "Modèles de nature des articles",
            '312' => "Types de tableaux de bord",
            '315' => "Types de statuts docusign pour les devis",
            '501' => 'Compte comptable',
            '502' => 'Mode de paiement insight',
            '503' => 'Type de tache todo',
            '504' => 'Type de tache rdv',
            '505' => 'Comptes email',
            '506' => 'Comptes email public',
            '507' => 'Questionnaires',
            '511' => 'Evenements de campagne emailing',
            '520' => 'Types de fichier export comptable',
            '521' => 'Formats de date export comptable',
            '523' => 'Alignement de la valeur',
            '530' => 'Types de paiements',
            '539' => 'Conditions des notifications manuelles',
            '540' => "Statuts des bons de préparation vente",
            '541' => 'Type de notification manuelle',
            '560' => 'Encryptage mail synchro',
            '561' => 'Type connexion',
            '570' => 'Statut import en cours',
            '580' => 'Type de message des échanges de ticket client',
            '581' => 'Statut contact',
            '590' => 'Catégorie des traductions',
            '591' => "Liste des stockages externes",
            '592' => "Types de configuration email",
            '600' => "Statuts d'annulation",
            '601' => "Protocoles email",
            '602' => "Types de compte à synchronisation",
            '605' => "Autorisation Intranet",
            '610' => "Status campagne de prospection",
            '615' => "Catégories dépenses MINDEE",
            '620' => "Type destinataire email",
            '621' => "Type d'élément pour les licences",
            '622' => "Niveau du destinataire email",
            '626' => "Type de jour d'insponibilité",
            '630' => "Type de document : Doc Achat/Vente / Autres documents",
            '631' => "Mode d'affichage de saisie des temps",
            '635' => "Type d'application d'éco-contribution",
            '640' => "Type de contrat utilisateur",
            '650' => "Opérateur recheche avancée",
            '700' => "Type de valeur pour les chronomètres",
            '701' => "Statut du suivi des jours travaillés",
            '710' => "Niveau de détail modele de facturation des temps",
            '715' => "Type de requête trigger eden",
            '716' => "Type de champs pour l'enrichissement des données",
            '720' => "Possibilité double facteur d'authentification",
            '725' => "Type de service pour la synchronisation",
            '726' => "Sens des données des champs pour la synchronisation",
            '727' => "Type de synchronisation service externe",
            '728' => "Evenements de synchronisation pour les logs",
            '729' => "Statut facturation électronique (facture_vente)",
            '730' => "Type de document facturation électronique (BT-3)",
            '731' => "Cadre de facturation facturation électronique (BT-23)",
            '732' => "Zone fiscale facturation électronique (catégorie comptable)",
            '736' => "Statut d'envoi CDAR (facturation_electronique_cycle_de_vie)"
		);
    }

	/**
	 *
	 * Liste des id de listes formatées éditables
	 *
	 */
	public static function liste_formatees_editable() {

		return array(

			16, // => 'Type de coupon réduction',
			26, // => 'Langues',
			31, // => 'Types d\'adresse',
			33, // => 'Types d\'échanges',
			34, // => 'Statuts des paniers',
			35, // => 'Statuts des rappels',
			36, // => 'Catégories de modèles d'emails',
			38, // => 'Statut des clients',
			39, // => 'Civilités',
			41, // => 'Origine des erreurs',
			42, // => 'Origine des crédits',
			43, // => 'etape sur les leads',
			44, // => 'statut sur les leads',
			45, // => 'Chaleur des leads / prospects / projets (prospection)',
			46, // => 'Type de question dans les questionnaires',
			51, // => "Statut des activités sur les projets",
			52, // => "Statut des projets",
			53, // => "Périodes de debut de demande de congés",
			54, // => "Raisons de demande de congés",
			55, // => "Types de coupons réduction",
			56, // => "Statut des transactions budget insight",
			58, // => "Type de déclinaisons sur les articles",
			60, // => "Types de récurrence",
			61, // => "Modèle à utiliser pour la récurrence",
			62, // => "Types d'article",
			63, // => "Type statut demande de prix",
			65, // => "statuts des bugs remontés lors des recettes Easy Dév / Clients",
			66, // => "équipe easy dév pour la gestion des bugs",
			67, // => "Criticité des bugs",
			70, // => "Types d'action pour les workflow",
			72, // => "Liste des triggers possibles pour les workflows",
			73, // => "Transports de notification",
			75, // => "Type de facture (avancement)",
			78, // => "Type de d'élément dans le tableau de bord",
			80, // => "Statut des interventions de maintenance",
			96, // => "Etapes des propositions commerciales",
			97, // => "Statuts des propositions commerciales",
			98, // => "Type ligne rapport paramétrable",
			99, // => "Probabilité",
			100, // => "Status des devis vente",
			101, // => "Status des commandes vente",
			102, // => "Status des bl vente",
			103, // => "Status des acomptes vente",
			104, // => "Status des factures vente",
			107, // => "Status des avoirs vente",
			108, // => "Status des commandes achat",
			109, // => "Type de mouvement de stock",
			110, // => "Préparation partielle",
			112, // => "Type de TVA",
			113, // => "Sens de TVA",
            117,  // => "Liste du theme pour un indicateur dans un bloc",
			120, // => "Liste des types de bugs",
			121, // => "Liste des types d'actions",
			124, // => "Statuts des devis achat",
			125, // => "Statuts des commande achat",
			126, // => "Statuts des BL achat",
			127, // => "Statuts des acompte achat",
			128, // => "Statuts des facture achat",
			129, // => "Statuts des avoirs achat",
			131, // => "Liste des types d'actions pour les approbations",
			140, // => "Disponible pour saisie articles",
			141, // => "statut bordereau",
			143, // => "Liste des types de numéros de série des articles",
			144, // => "Nouveaux statuts des devis, avec le "annulé"",
            145, // => "Criticité des bugs pour suivi_recette_easydev
			149, // => "Status pour les approbations",
			150, // => "Status pour les tickets hotline (les clients des client",
			160, // => "Type d'utilisateur",
			200, // => "Type de rubrique pour le budget",
			300, // => "Type de vue pour les vues sql",
			301, // => "Statut note de frais",
			302, // => "Statut des lignes sur les documents",
			303, // => "Statut des lignes sur les documents (commande_vente => commande_achat) => le fait de commander chez le fournisseur",
			304, // => "Statut des lignes sur les documents (commande_vente => commande_achat) => livraison",
			312, // => "Types de tableaux de bord",
			315, // => "Types de statuts docusign pour les devis",
			502, // => "Modes de paiement insight",
            503, // => 'Type de tache todo',
            504, // => 'Type de tache rdv',
            511, // => 'Statut d'évenement de campagne emailing',
            520, // => 'Types de fichiers pour les exports comptables',
            530, // => 'Types de paiements encaissements / décaissements',
            539, // => 'Conditions des notifications manuelles',
            540, // => "Status des bons de preparation vente",
            541, // => "Type de notification manuelle",
            560, // => "Encryptage mail synchro",
            561, // => "Type de connexion",
            570, // => "Statut import",
            580, // => "Type de message des échanges de ticket client",
            590, // => "Catégorie de traductions",
            600, // => "Statuts d'annulation",
            605, // => "Autorisation Intranet",
            610, // => "Statuts campagne de prospection",
            620, // => "Type destinataire email",
            622, // => "Niveau du destinataire email",
            626, // => "Type de jour d'insponibilité",
            650, // => "Opérateur recheche avancée",
            720, // => "Possibilité double facteur d'authentification",
            728, // => "Evenements de synchronisation pour les logs"
            729, // => "Statut facturation électronique (facture_vente)"
            730, // => "Type de document facturation électronique (BT-3)"
            731, // => "Cadre de facturation facturation électronique (BT-23)"
            732, // => "Zone fiscale facturation électronique (catégorie comptable)"
            736, // => "Statut d'envoi CDAR (facturation_electronique_cycle_de_vie)"
		);
	}

    /**
     *
     * Liste des id de listes formatées pour lesquelles la valeur 0 est possible même en obligatoire
     *
     */
    public static function liste_formatees_zero_possible() {
        return [
            14 , //Champ oui / non
            34 , //statut des paniers ecommerce
            35 , //statuts des rappels
            44 , //Statut des leads
            51 , //Statut des activités sur les projets
            53 , //Périodes de debut de demande de congés
            54 , //Raisons de demande de congés
            56 , //Statut des transactions budget insight
            58 , //Types de déclinaisons sur les articles
            60 , //Types de récurrence
            61 , //Modèle à utiliser pour la récurrence
            62 , //Type d'article
            63 , //Type statut demande de prix
            75 , //Type de facture (avancement)
            97 , //Statuts des propositions commerciales
            98 , //Type de contenu pour les lignes de rapports paramétrables
            99 , //Probabilité sur les projets
            109 , //Type de mouvement de stock
            110 , //Préparation partielle
            112 , //Type de TVA
            113 , //Sens de TVA
            121 , //Liste des types d'actions
            131 , //Liste des types d'actions pour les approbations
            149 , //Status pour les approbations
            150 , //Status pour les tickets hotline (les clients des clients)
            160 , //Type d'utilisateur
            200 , //Type de rubrique pour le budget
            140 , //Disponibilité des articles pour leur saisie
            143 , //Liste des types de numéros de série des articles
            144 , //Nouveaux statuts des devis, avec le "annulé"
            141 , //statut bordereau
            502 ,
            300 ,
            301 , //Statut note de frais
            302 , //Statut des lignes sur les documents
            303 , //Statut des lignes sur les documents (commande_vente => commande_achat) => le fait de commander chez le fournisseur
            304 , //Statut des lignes sur les documents (commande_vente => commande_achat) => livraison
            312 , //Types de tableaux de bord
            315 , //Types de statuts docusign pour les devis
            511 , //Statut d'évenement de campagne emailing
            530 , //Types de paiements encaissements / décaissements
            580 , //Type de message des échanges de ticket client
            581 , //Statut de contact
            600 , //Statuts d'annulation
            635 , //Type d'application d'éco-contribution
            726 , //Sens des données des champs pour la synchronisation
            727 , //Type de synchronisation service externe
        ];
    }

	/**
	 *
	 * Retourne la liste des listes libres
	 *
	 */
    public static function liste_libres() {

		$liste_champs = \App\Eden\Models\Champ_libre::whereIn('type', array(1,12))->where(function($r) { $r->where('liste_choix', 0)->orWhereNull('liste_choix'); })->orderBy('type_element')->get();

		$liste_des_listes_libres = array();

		foreach($liste_champs as $champ) {

			$table = table_libre($champ->type_element);
			$liste_des_listes_libres[$champ->id_cl] = $table->nom_table.' : '.$champ->nom;
		}

		return $liste_des_listes_libres;
    }

    public static function liste_tables() {

		return \App\Eden\Models\Table_libre::orderBy('nom_table')->get()->pluck('nom_table', 'type_element');
    }


	// Affiche la liste des type_element de tables qui ne sont pas incluses dans la liste_libre actuelle
    public static function liste_libre_choix_ajout() {

		$liste_tables = \App\Eden\Models\Table_libre::orderBy('type_element')->get();
		$listes_libres = \App\Eden\Models\Liste_libre::orderBy('type_element')->whereNull('id_rapport')->get();
		$listes_libres = [];

		$liste_des_tables = array();
        $liste_des_listes_libres = array();

		foreach($liste_tables as $table) {

			$liste_des_tables[$table->type_element] = $table->type_element;
		}
		foreach($listes_libres as $libre) {

			$liste_des_listes_libres[$libre->type_element] = $libre->type_element;
		}

		$choix_liste_libre = array_diff($liste_des_tables, $liste_des_listes_libres);

		return $choix_liste_libre;

    }

	// Affiche la liste des tables pour lesquelles la création rapide est active
	public static function element_creation_rapide(){

		$liste_tables = \App\Eden\Models\Table_libre::orderBy('type_element')->get();
		$liste_des_tables = array();
		$i = 0;
		foreach($liste_tables as $table) {

		    if($table->creation_rapide == 1){

			    $liste_des_tables[$i] = $table->type_element;
				$i++;
			}
		}

		return $liste_des_tables;

	}

    public static function namespace_modeles(){

        return 'App\\Eden\\Models\\';
	}

	/**
	 *
	 * Liste des tables libres sur lesquelles on peut faire des workflows,
	 * Le but est d'éviter de boucle sur toutes les tables libres possibles
	 *
	 */
    public static function tables_libres_pour_workflow(){

        return array(

			// 'devis_vente',
			// 'commande_vente',
			// 'bl_vente',
			// 'acompte_vente',
			// 'facture_vente',
			// 'avoir_vente',
			// 'devis_achat',
			// 'commande_achat',
			// 'bl_achat',
			// 'acompte_achat',
			// 'facture_achat',
			// 'avoir_achat',
			// 'article',
			// 'client',
			// 'fournisseur',
			// 'contact',
			// 'lead',
			// 'projet',
			// 'feuille_de_temps',
			'approbation',
			'ticket_client',
			'ticket_client_echange',
		);
	}

    /**
     *
     * Liste des etapes de l'inscriiption à une manifestation pour l'extranet
     *
     */
    public static function listes_etapes_inscription_manifestation_extranet() {

        return array(

            30 => array(
                'nom' => 'Inscription',
                'fonction' => 'dossier_inscription',
            ),
            31 => array(
                'nom' => 'Choix du stand',
                'fonction' => 'choix_stand',
            ),
            32 => array(
                'nom' => 'Choix des options du stand',
                'fonction' => 'choix_options',
            ),
            33 => array(
                'nom' => 'Communications',
                'fonction' => 'choix_communications',
            ),
            34 => array(
                'nom' => 'Autres',
                'fonction' => 'autre',
            ),
        );
    }

    /**
     *
     * Liste les catégories pour les listes formatées
     *
     * Pour l'instant c'est uniquement utilisé pour les filtres sur les listes
     *
     */
	public static function categories_pour_listes_formatees($type_element = null,$nom_sql = null) {

        $categories = array(
            'commande_vente' => array(
                'statut' => array(
                    3 => 'AR',
                    5 => 'AR',
                    10 => 'Disponibilité articles',
                    15 => 'Disponibilité articles',
                    20 => 'Production',
                    25 => 'Production',
                    30 => 'Production',
                    40 => 'BL',
                    43 => 'BL',
                    45 => 'BL',
                    50 => 'Facturation',
                )
            )
        );

        if($type_element != null){

            if(!isset($categories[$type_element]))
                return array();

            if($nom_sql != null){

                if(!isset($categories[$type_element][$nom_sql]))
                    return array();

                return $categories[$type_element][$nom_sql];

            }
            else
                return $categories[$type_element];
        }

        return $categories;
    }

    /**
     *
     * Fonction qui renvoie les variables disponibles lors du filtrage des dates
     *
     */
    public static function variables_champ_date(){

        $prochain_mois = strtotime('first day of +1 month');
        $precedent_mois = strtotime('first day of -1 month');
        $plus_deux_mois = strtotime('first day of +2 months');

        return [
            [
                'valeur' => 'cette_annee',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_1er_janvier_au_31_decembre', null, array(date('Y'), date('Y'))),
            ],
            [
                'valeur' => 'annee_derniere',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_1er_janvier_au_31_decembre', null,array(date('Y')-1, date('Y')-1)),
            ],
            [
                'valeur' => 'annee_prochaine',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_1er_janvier_au_31_decembre', null, array(date('Y')+1, date('Y')+1)),
            ],
            [
                'valeur' => 'depuis_janvier',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_1er_janvier_a_aujourdhui',null,array(date('Y'))),
            ],
            [
                'valeur' => 'ce_mois_ci',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_1er_du_mois_a_aujourdhui', null, array(Variables::mois_de_lannee_format_complet(date('m')), date('Y'))),
            ],
            [
                'valeur' => 'le_mois_dernier',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_premier_au',null,array(Variables::mois_de_lannee_format_complet(date('m', $precedent_mois)), date('Y', $precedent_mois), date('t', $precedent_mois), Variables::mois_de_lannee_format_complet(date('m', $precedent_mois)), date('Y', $precedent_mois))),
            ],
            [
                'valeur' => 'le_mois_prochain',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_premier_au',null,array(Variables::mois_de_lannee_format_complet(date('m', $prochain_mois)), date('Y', $prochain_mois), date('t', $prochain_mois), Variables::mois_de_lannee_format_complet(date('m', $prochain_mois)), date('Y', $prochain_mois))),
            ],
            [
                'valeur' => '3_prochains_mois',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_premier_au',null,array(Variables::mois_de_lannee_format_complet(date('m', strtotime('now'))), date('Y', strtotime('now')), date('t', strtotime('now')), Variables::mois_de_lannee_format_complet(date('m', $plus_deux_mois)), date('Y', $plus_deux_mois))),
            ],
            [
                'valeur' => 'aujourdhui',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.le',null,array(date('d/m/Y'))),
            ],
            [
                'valeur' => 'hier',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.le', null,array(date('d/m/Y', strtotime('-1 day')))),
            ],
            [
                'valeur' => 'demain',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.le', null,array(date('d/m/Y', strtotime('+1 day')))),
            ],
            [
                'valeur' => '30_derniers_jours',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_a_aujourdhui',null, array(date('d/m/Y', strtotime('-30 days')))),
            ],
            [
                'valeur' => '7_derniers_jours',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_a_aujourdhui', null,array(date('d/m/Y', strtotime('-7 days')))),
            ],
            [
                'valeur' => '7_prochains_jours',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.de_aujourdhui_au', null,array(date('d/m/Y', strtotime('+7 days')))),
            ],
            [
                'valeur' => '6_prochains_jours',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.de_aujourdhui_au',null,array(date('d/m/Y', strtotime('+6 days')))),
            ],
            [
                'valeur' => '30_prochains_jours',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.de_aujourdhui_au',null,array(date('d/m/Y', strtotime('+30 days')))),
            ],
            [
                'valeur' => 'cette_semaine',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.de_lundi_a_aujourdhui'),
            ],
            [
                'valeur' => 'semaine_prochaine',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.du_au',null,[date('d/m/Y', strtotime(lundi_prochain())),date('d/m/Y', strtotime(dimanche_prochain(lundi_prochain())))]),
            ],
            [
                'valeur' => 'jusqua_dimanche',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.daujourdhui_a_dimanche'),
            ],
            [
                'valeur' => 'jusqu_a_aujourdhui',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.jusqu_a_aujourdhui'),
            ],
            [
                'valeur' => 'passe',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.date_deja_passee'),
            ],
            [
                'valeur' => 'pas_passe',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.date_pas_encore_passee'),
            ],
            [
                'valeur' => 'pas_renseigne',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.non_renseigne'),
            ],
            [
                'valeur' => 'renseigne',
                'description' => traduction('filtres.cree_filtre_pour_liste.champ_date.renseigne'),
            ],
        ];
    }

    public static function type_filtre_par_type_champ(){

        return [
            0 => 'filtre-texte',
            1 => 'filtre-liste-libre',
            2 => 'filtre-montant',
            3 => 'filtre-montant',
            4 => 'filtre-date',
            5 => 'filtre-date',
            6 => 'filtre-texte',
            7 => 'filtre-piece-jointe',
            17 => 'filtre-montant',
            20 => 'filtre-liste-formatee',
            21 => 'filtre-type-element-dynamique',
            42 => 'filtre-recherche-element',
            '42|utilisateur' => 'filtre-utilisateur',
            '42|famille' => 'filtre-famille',
        ];
    }

    public static function extension_fichier_accepte(){

        return [
            'video/3gpp',
            'video/3gpp2',
            'application/x-7z-compressed',
            'video/3gpp2',
            'application/x-7z-compressed',
            'audio/aac',
            'video/x-msvideo',
            'image/bmp',
            'text/csv',
            'application/msword',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-fontobject',
            'application/epub+zip',
            'image/gif',
            'application/gzip',
            'image/vnd.microsoft.icon',
            'text/calendar',
            'image/jpeg',
            'application/json',
            'audio/midi',
            'audio/mpeg',
            'video/mp4',
            'video/mpeg',
            'audio/ogg',
            'video/ogg',
            'application/ogg',
            'application/onenote',
            'audio/opus',
            'font/otf',
            'application/pdf',
            'image/png',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-publisher',
            'application/x-rar-compressed',
            'application/vnd.ms-outlook',
            'application/rtf',
            'image/svg+xml',
            'application/x-tar',
            'image/tiff',
            'font/ttf',
            'text/plain',
            'application/vnd.visio',
            'audio/wav',
            'audio/webm',
            'video/webm',
            'image/webp',
            'audio/x-ms-wma',
            'video/x-ms-wmv',
            'font/woff',
            'font/woff2',
            'application/vnd.ms-excel',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/xml',
            'text/xml',
            'application/zip',
            'message/rfc822',
        ];
    }


}
