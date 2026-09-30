<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Gestion_commerciale_v2_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {
        $types_modele = ['entrepot', 'mode_paiement'];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        $documents_gescom_tmp = Variables::$documents_gescom;

        foreach ($documents_gescom_tmp as $type_element) {

            $type_element_modifie = ucfirst(traduction('tables_libres.' . $type_element . '.nom_table'));

            if (isset(fonctionnalite('modification_document_valide')[$type_element]) &&
                fonctionnalite('modification_document_valide')[$type_element] == true &&
                (strpos($type_element, 'facture') === false && 
                strpos($type_element, 'acompte') === false && 
                strpos($type_element, 'avoir') === false)
            )
                $valeurs['documents_gescom_modifiable_post_validation'][$type_element] = $type_element_modifie;

            $valeurs['documents_gescom'][$type_element] = $type_element_modifie;

            if (strpos($type_element, '_vente') !== false)
                $valeurs['documents_gescom_vente'][$type_element] = $type_element_modifie;
        }
        $valeurs['compte_comptable'] = modele('compte_comptable')->get()->pluck('libelle', 'id')->toArray();

        $documents_ecart_gestion_ttc = [];

        foreach (\App\Eden\Variables::$documents_gescom as $document) {
            $documents_ecart_gestion_ttc[$document] = ucfirst(traduction('tables_libres.' . $document . '.nom_table'));
        }

        $documents_ecart_gestion_ttc['note_de_frais'] = ucfirst(traduction('tables_libres.note_de_frais.nom_table'));

        $valeurs['documents_ecart_gestion_ttc'] = $documents_ecart_gestion_ttc;

        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(

            // saisie des documents
            'Type de documents' => array(
                array(

                    'nom' => "Activer les documents",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
                    'fonctionnalite' => "gescom_document",
                ),
            ),
            // saisie des documents
            'Saisie des documents' => array(

                array(

                    'nom' => "Afficher les documents liés avec le détail des statuts",
                    'type' => "toggle",
                    'description' => "Attention, il faut que l'option &laquo; Afficher les documents liés &raquo; soit activée",
                    'fonctionnalite' => "gescom_afficher_recap_documents_lies_avec_details",
                    'valide' => true,
                ),
                array(

                    'nom' => "Afficher l'état des reliquats sur les lignes des documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_afficher_etat_reliquats_lignes_documents",
                ),
                array(

                    'nom' => "Pour les documents liés, on affiche les montants ...",
                    'type' => "select",
                    'valeurs_select' => array(

                        'HT' => 'En HT',
                        'TTC' => 'En TTC',
                    ),
                    'description' => "",
                    'fonctionnalite' => "gescom_documents_lies_montant_en_ht_ou_ttc",
                    'valide' => true,
                ),

                array(

                    'nom' => "Préselection des articles",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_preselection_articles_sur_document",
                    'valide' => true,
                    'a_ameliorer' => true,
                ),
                array(

                    'nom' => "Mode de préselection des articles",
                    'type' => "select",
                    'valeurs_select' => array(
                        'classique' => 'Classique',
                        'affichage_total' => 'Affichage total',
                    ),
                    'description' => "",
                    'fonctionnalite' => "gescom_mode_preselection_articles_sur_document",
                    'valide' => false,
                    'a_ameliorer' => true,
                ),
                array(

                    'nom' => "Sélection des articles via fournisseur",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_mode_selection_articles_via_fournisseur",
                    'valide' => true,
                ),
                array(

                    'nom' => "Cacher la recherche d'article générale",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_cacher_saisie_article_generale",
                    'fonctionnalite_mere' => "gescom_mode_selection_articles_via_fournisseur",
                    'valide' => true,
                ),
                array(

                    'nom' => "Mettre à jour le prix d'achat via article fournisseur prioritaire",
                    'type' => "toggle",
                    'description' => "Si le tarif de l'article fournisseur prioritaire évolue ou si l'on créé un article fournisseur prioritaire sans conditionnement alors on met à jour le prix d'achat de l'article.",
                    'fonctionnalite' => "copier_prix_achat_pas_conditionnement_article",
                ),
                array(

                    'nom' => "Ne pas replier par défaut les blocs quand le document existe",
                    'type' => "toggle",
                    'description' => "Si cette option est activée, et si on est en mode sans onglet, alors tous les blocs seront dépliés par défaut sur la page de saisie de document",
                    'fonctionnalite' => "gescom_ne_pas_plier_par_defaut_les_blocs_sur_saisie_document",
                    'valide' => true,
                ),
                array(

                    'nom' => "Modification Prix Unitaire impossible sur document de type vente",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "modification_prix_unitaire_vente",
                ),
                array(

                    'nom' => "Activer le versionning document",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
                    'fonctionnalite' => "versionning_document",
                    'valide' => true,
                ),
                array(

                    'nom' => "Afficher l'encours à la saisie d'un document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "afficher_encours_saisie_documents",
                    'valide' => true,
                ),
                array(

                    'nom' => "Nombre de chiffres pour la numérotation des documents",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "nombre_chiffres_numerotation",
                ),
                array(

                    'nom' => "Affichage du pays sur les adresses",
                    'type' => "toggle",
                    'description' => "Pas pris en compte sur le modèle de doc standard",
                    'fonctionnalite' => "documents_adresses_pays",
                    'valide' => "",
                ),
                array(

                    'nom' => "Afficher la modale pour transformer un document vente en commande fournisseur",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "transformer_fournisseurs_par_article_commande_achat_modale",
                    'valide' => false,
                ),
                array(

                    'nom' => "Lien des contacts sur les documents (pouvoir lier les contacts du client au document)",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "lien_contacts_sur_documents",
                    'valide' => true,
                ),
                array(

                    'nom' => "Afficher les paiements non rattache à un document lors de la séléction d'un client",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "afficher_paiements_non_rattache_sur_documents",
                ),
                array(

                    'nom' => "Remplacement article",
                    'type' => "badge_multiselection",
                    'description' => "Possibilité de remplacer un article sur un document",
                    'fonctionnalite' => "gescom_remplacement_article",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
					'valide' => true,
                ),


                array(
                    'nom' => "Afficher les prix sur les documents",
                    'type' => "badge_multiselection",
                    'description' => "Permet d'afficher les colonnes qui concernent la tarification (prix de vente, achat, tva, etc) sur certains documents",
                    'fonctionnalite' => "saisie_documents_afficher_prix",
                    'valeurs_badges' => array(
                        'bl_vente' => "Bl vente",
                        'bl_achat' => "Bl achat",
                        'bon_preparation_vente' => "Bon de préparation vente",
                        'bon_retour_vente' => "Bon de retour vente",
                    ),
                    'valide' => true,
                ),
                array(

                    'nom' => "Autoriser les montants négatifs",
                    'type' => "select",
                    'valeurs_select' => array(
                        'bloquant' => 'Bloquant',
                        'avertissement' => 'Avertissement',
                        'pas_de_blocage' => 'Pas de blocage',
                    ),
                    'description' => "Indiquer le comportement à l'enregistrement pour un document négatif",
                    'fonctionnalite' => "gescom_autoriser_document_negatif",
                    'valide' => true,
                ),
                array(

                    'nom' => "Vérification prix de vente inférieur au prix d'achat",
                    'type' => "badge_multiselection",
                    'description' => "Alerte à l'enregistrement si le prix de vente d'un article est inférieur à son prix d'achat.",
                    'fonctionnalite' => "gescom_verification_prix_vente_inferieur_prix_achat",
                    'valeurs_badges' => $valeurs['documents_gescom_vente'] ?? array(),
                ),
                array(

                    'nom' => "Prix net modifiable sur les documents",
                    'type' => "toggle",
                    'description' => "Donne la possibilité de modifier le prix net des articles sur les documents",
                    'fonctionnalite' => "gescom_prix_net_modifiable",
                    'valeurs_badges' => $valeurs['documents_gescom_vente'] ?? array(),
                ),

                array(

                    'nom' => "Afficher 'Ajouter un lot' sur les documents",
                    'type' => "badge_multiselection",
                    'description' => "Choisir les types de documents pour lesquels cette option s'affichera",
                    'fonctionnalite' => "gescom_afficher_option_ajout_lot",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
                ),

                array(

                    'nom' => "Alerter article avec quantité nulle",
                    'type' => "toggle",
                    'description' => "Alerter à l'enregistrement d'un document si un article possède une quantité nulle",
                    'fonctionnalite' => "gescom_validation_quantite_article_nulle",
                ),
                array(

                    'nom' => "Bloquer la validation de documents si le client présente un retard de paiement",
                    'type' => "badge_multiselection",
                    'fonctionnalite' => "bloquer_validation_document_si_client_retard_paiement",
                    'valeurs_badges' => array(
                        'devis_vente' => 'Devis vente',
                        'commande_vente' => 'Commande vente',
                        'bl_vente' => 'BL vente',
                        'acompte_vente' => 'Acompte vente',
                        'facture_vente' => 'Facture vente',
                        'avoir_vente' => 'Avoir vente',
                        'devis_achat' => 'Devis achat',
                        'commande_achats' => 'Commande Achat',
                    ),
                ),
            ),
            // tableau de saisie d'articles dans les documents commerciaux
            'Gestion commerciale : documents : saisie des articles' => array(
                array(

                    'nom' => "Garder la valeur de la recherche dans la saisie d'article sur document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_garder_valeur_saisie_des_articles",
                ),
                array(

                    'nom' => "Valeur de l'arrondi sur les documents",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "arrondi_sur_les_documents",
                ),
                array(

                    'nom' => "Alerte prix d'achat nul sur les documents",
                    'type' => "toggle",
                    'description' => "Lors de l'enregistrement d'un document, si le prix d'achat d'un article est nul 
                    et que la fonctionnalité est activée, on demande la confirmation à l'utilisateur qu'il veut bien 
                    continuer l'enregistrement.",
                    'fonctionnalite' => "alerte_document_prix_achat_nul",
                ),
                array(

                    'nom' => "Utilisation des unités sur les documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "ajout_colonne_unite_sur_documents",
                ),
                array(

                    'nom' => "Utilisation des disponibilités sur les documents",
                    'type' => "badge_multiselection",
                    'description' => "",
                    'fonctionnalite' => "ajout_colonne_disponibilite_sur_documents",
                    'valeurs_badges' => array(
                        'devis_vente' => 'Devis vente',
                        'commande_vente' => 'Commande vente',
                        'bl_vente' => 'BL vente',
                        'acompte_vente' => 'Acompte vente',
                        'facture_vente' => 'Facture vente',
                        'avoir_vente' => 'Avoir vente',
                        'devis_achat' => 'Devis achat',
                        'commande_achats' => 'Commande Achat',
                        'bon_preparation_vente' => 'Bon de préparation vente',
                        'bon_retour_vente' => 'Bon de retour vente',
                        'bl_achat' => 'Bl achat',
                        'acompte_achat' => 'Acompte Achat',
                        'facture_achat' => 'Facture Achat',
                        'avoir_achat' => 'Avoir Achat',
                        'bon_retour_achat' => 'Bon de retour achat',
                    ),
                ),
                array(

                    'nom' => "Modification total HT possible sur articles",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "modification_total_hors_taxe_article",
                ),
                array(

                    'nom' => "Choix du code article sur la saisie des documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "choix_code_article_sur_saisie_document",
                ),
                array(

                    'nom' => "Photo des articles sur les documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_photo_article_sur_document",
                ),
                array(

                    'nom' => "Masquer certaines lignes du document sur les devis",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_masquer_lignes_du_document",
                ),
                array(

                    'nom' => "Commentaires WYSIWYG entre deux articles sur documents commerciaux",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "commentaires_wysiwyg_documents",
                ),
                array(

                    'nom' => "Notes internes WYSIWYG entre deux articles sur documents commerciaux",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "notes_internes_wysiwyg_documents",
                ),
                array(

                    'nom' => "Descriptions WYSIWYG sur les articles sur documents commerciaux",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "description_wysiwyg_documents",
                ),
                array(

                    'nom' => "Numéros de série",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "numeros_de_serie",
                ),
                array(

                    'nom' => "Sélection des documents avec les numéros de séries",
                    'type' => "badge_multiselection",
                    'description' => "",
                    'fonctionnalite' => "type_document_numero_de_serie",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
                ),
                array(

                    'nom' => "Numéros de lots",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "numeros_de_lot",
                ),
                array(

                    'nom' => "Regroupement d'articles dans un document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "regroupement_articles_documents",
                ),
                array(

                    'nom' => "Couleur des regroupements",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "couleur_regroupement_articles_documents",
                ),
                array(

                    'nom' => "Couleur des sous-regroupements",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "couleur_sous_regroupement_articles_documents",
                ),

                array(

                    'nom' => "Position des actions sur le bloc saisie des articles",
                    'type' => "select",
                    'valeurs_select' => array(

                        'haut' => 'En haut',
                        'bas' => 'En bas',
                        'les_deux' => 'Les deux'
                    ),
                    'description' => "Permet de définir si les actions se situent en haut, en bas ou les deux sur le bloc de saisie des articles",
                    'fonctionnalite' => "position_actions_saisie_des_articles",
                ),

                array(

                    'nom' => "Utiliser les coefficients sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_les_coefficients",
                ),

                array(

                    'nom' => "Utiliser les calculateur sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "calculateur_sur_document",
                ),

                array(

                    'nom' => "Utiliser les titres sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des titres dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_titres",
                ),

                array(

                    'nom' => "Utiliser les sauts de ligne sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des sauts de ligne dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_sauts_de_ligne",
                ),

                array(

                    'nom' => "Utiliser les sauts de page sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des sauts de page dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_sauts_de_page",
                ),

                array(

                    'nom' => "Utiliser les sous totaux sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des sous totaux dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_sous_totaux",
                ),

                array(

                    'nom' => "Utiliser les commentaires sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des commentaires dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_commentaires",
                ),

                array(

                    'nom' => "Utiliser les notes_internes sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des notes internes dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_notes_internes",
                ),

                array(

                    'nom' => "Utiliser les remises sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des lignes de remises dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_remises",
                ),

                array(

                    'nom' => "Colonnes sur les documents de ventes",
                    'type' => "badge_multiselection",
                    'description' => "Choisir les colonnes que vous voulez afficher dans vos documents de ventes",
                    'fonctionnalite' => "documents_colonnes_a_afficher_vente",
                    'valeurs_badges' => [
                        'utilisateur_id' => 'Utilisateur',
                        'code_article' => 'Code Article',
                        'unite' => 'Unité',
                        'prix_achat' => 'Prix d\'achat',
                        'marge_appliquee' => 'Marge appliquée',
                        'tarif' => 'Tarif',
                        'remise' => 'Remise',
                        'tarif_net' => 'Tarif net',
                        'marge_brute_montant' => 'Marge brute (montant)',
                        'marge_brute_pourcentage' => 'Marge brute (%)',
                        'marge' => 'Marge nette (montant)',
                        'marge_pourcentage' => 'Marge nette (%)',
                        'total' => 'Total',
                        'eco_contribution' => 'Eco contribution',
                        'tva' => 'TVA',
                        'categorie_comptable_article_id' => 'Catégorie comptable',
                        'total_ttc' => 'Total TTC',
                    ],
                    'gestion_profil' => true
                ),
                array(

                    'nom' => "Colonnes sur les documents d'achats",
                    'type' => "badge_multiselection",
                    'description' => "Choisir les colonnes que vous voulez afficher dans vos documents d'achats",
                    'fonctionnalite' => "documents_colonnes_a_afficher_achat",
                    'valeurs_badges' => [
                        'utilisateur_id' => 'Utilisateur',
                        'code_article' => 'Code Article',
                        'unite' => 'Unité',
                        'prix_achat' => 'Prix d\'achat',
                        'marge_appliquee' => 'Marge appliquée',
                        'tarif' => 'Tarif',
                        'remise' => 'Remise',
                        'tarif_net' => 'Tarif net',
                        'marge_brute_montant' => 'Marge brute (montant)',
                        'marge_brute_pourcentage' => 'Marge brute (%)',
                        'marge' => 'Marge nette (montant)',
                        'marge_pourcentage' => 'Marge nette (%)',
                        'total' => 'Total',
                        'eco_contribution' => 'Eco contribution',
                        'tva' => 'TVA',
                        'categorie_comptable_article_id' => 'Catégorie comptable',
                        'total_ttc' => 'Total TTC',
                    ],
                    'gestion_profil' => true
                ),
                array(

                    'nom' => "Afficher date de dernière mise à jour du prix d'achat dans la recherche sur les documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_affichage_prix_achat_liste_articles",
                ),
                array(

                    'nom' => "Afficher les compteurs d'articles sur la saisie d'articles et le footer",
                    'type' => "toggle",
                    'description' => "Affiche un numéro sur chaque ligne d'article, ainsi que sur le footer sticky",
                    'fonctionnalite' => "gescom_affichage_compteurs_articles",
                ),
            ),
            
            'Gestion images' => array(

                array(

                    'nom' => "Utiliser les images sur la saisie de documents",
                    'type' => "toggle",
                    'description' => "Permet d'insérer des images dans le corps du document",
                    'fonctionnalite' => "saisie_documents_utiliser_les_images",
                ),

                array(

                    'nom' => "Afficher les images dans la recherche d'articles pour la saisie d'un document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "recherche_article_document_gescom_avec_image",
                ),
            ),

            'Avancement' => array(
                array(

                    'nom' => "Activer les factures d'avancement",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_document_avancement",
                ),
                array(

                    'nom' => "Générer les factures d'avancement à l'échelle du projet",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_gerer_avancement_via_projet",
                    'fonctionnalite_mere' => "gescom_document_avancement",
                ),
                array(

                    'nom' => "Générer les factures d'avancement via les devis",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_gerer_avancement_via_devis",
                    'fonctionnalite_mere' => "gescom_document_avancement",
                ),
                array(

                    'nom' => "Afficher des titres de séparation dans la facturation à l'avancement depuis projet",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_afficher_titres_separations_facturation_avancement",
                    'fonctionnalite_mere' => "gescom_document_avancement",
                ),
                array(

                    'nom' => "Pouvoir modifier le pourcentage précédent sur un article",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_avancement_pouvoir_modifier_pourcentage_precedent",
                    'fonctionnalite_mere' => "gescom_document_avancement",
                ),
                array(

                    'nom' => "Pouvoir indiquer un pourcentage inférieur à la valeur précédente dans la valeur actuelle",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_avancement_accepter_reduction_avancement_actuel",
                    'fonctionnalite_mere' => "gescom_document_avancement",
                ),
            ),

            'Nomenclature' => array(
                array(

                    'nom' => "Activer les nomenclatures sur les documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_document_avec_nomenclature",
                ),
                array(

                    'nom' => "Affiche le code article des articles nomenclature",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "affichage_code_article_nomenclature",
                ),
                array(

                    'nom' => "Activer les tarifs forcés pour les nomenclatures",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_les_tarifs_forces_pour_les_nomenclatures",
                ),
                array(

                    'nom' => "Activer les tarifs forcés pour les nomenclatures enfant d'une autre nomenclature",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_les_tarifs_forces_pour_les_nomenclatures_enfant",
                    'fonctionnalite_mere' => "utiliser_les_tarifs_forces_pour_les_nomenclatures",
                ),
                array(

                    'nom' => "Bloquer le tarif des nomenclatures",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_bloquer_tarif_nomenclature",
                ),
                array(

                    'nom' => "Déplier/replier les nomenclatures",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_nomenclature_afficher_lignes",
                ),
            ),

            'PDF' => array(
                array(

                    'nom' => "Possibilité de cacher les totaux sur les documents PDF",
                    'type' => "toggle",
                    'description' => "OK pour l'enregistrement, mais pas pris en comtpe sur le PDF standard",
                    'fonctionnalite' => "gescom_document_cacher_totaux_sur_pdf",
                    'valide' => false,
                ),
                array(

                    'nom' => "Générer un pdf pour les documents",
                    'type' => "badge_multiselection",
                    'description' => "Choisir les types de documents pour lesquels on génerera un PDF",
                    'fonctionnalite' => "type_document_generer_pdf",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
                ),
                array(

                    'nom' => "Ne pas générer le PDF automatiquement",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_ne_pas_generer_pdf_automatiquement",
                ),
                array(

                    'nom' => "Bloquer le pdf une fois le document validé",
                    'type' => "badge_multiselection",
                    'description' => "Choisir les types de documents pour lesquels on bloquera le PDF à la validation",
                    'fonctionnalite' => "gescom_type_document_bloquer_pdf_post_validation",
                    'valeurs_badges' => $valeurs['documents_gescom_vente'] ?? array(),
                ),
                array(

                    'nom' => "Prévisualisation des pdfs avant la validation",
                    'type' => "toggle",
                    'description' => "Lors de la validation d'un document, permettre la prévisualisation des pdfs avant la validation",
                    'fonctionnalite' => "gescom_visionnage_pdf_pre_validation",
                ),
            ),

            'Remises' => array(
                array(

                    'nom' => "Utiliser les remises de pied de page sur les documents commerciaux",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_document_remises_pied_de_page",
                    'valide' => true,
                ),
                array(

                    'nom' => "Calcul des remises en montant",
                    'type' => "select",
                    'valeurs_select' => array(

                        'HT' => 'En HT',
                        'TTC' => 'En TTC',
                    ),
                    'description' => "",
                    'fonctionnalite' => "type_remise_globale_en_montant",
                    'valide' => true,
                ),
            ),

            'Devis' => array(
                array(

                    'nom' => "Utiliser les variantes de devis",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_variantes_devis",
                    'valide' => true,
                    'a_ameliorer' => true,
                ),
                array(

                    'nom' => "Transformer un devis en facture avec pourcentage",
                    'type' => "toggle",
                    'description' => "La modale n'est pas accessible",
                    'fonctionnalite' => "transformer_devis_vente_facture_pourcentage",
                    'valide' => false,
                ),
                array(

                    'nom' => "Génération automatique d'une commande après la validation du devis",
                    'type' => "toggle",
                    'description' => "Génère une commande à partir du devis après validation de celui ci",
                    'fonctionnalite' => "gescom_creation_commande_automatique_validation_devis",
                    'valide' => false,
                ),
                array(

                    'nom' => "Nombre de jours par défaut de la date d'expiration après la date sur les devis ventes",
                    'type' => "input",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "delai_expiration_devis",
                ),
            ),

            'BL' => array(
                array(

                    'nom' => "Regrouper les lignes articles lors de la transformation des commandes en BL",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "transformer_regrouper_lignes_articles_commandes_vente_bl",
                    'valide' => false,
                ),
            ),

            'Marge' => array(
                array(

                    'nom' => "Marge totale minimum sur le document",
                    'type' => "input",
                    'placeholder' => "Saisissez un %",
                    'description' => "Compare la marge minimum saisie ici et la marge sur le document, et affiche un message rouge si la marge est insuffisante",
                    'fonctionnalite' => "marge_mini_sur_documents_commerciaux",
                    'valide' => true,
                    'gestion_profil' => true,
                ),
                array(

                    'nom' => "Marge totale recommandée sur le document",
                    'type' => "input",
                    'placeholder' => "Saisissez un %",
                    'description' => "Compare la marge minimum saisie ici et la marge sur le document, et affiche un message orange si la marge est insuffisante",
                    'fonctionnalite' => "marge_recommandee_sur_documents_commerciaux",
                    'valide' => true,
                ),
                array(

                    'nom' => "Calcul du pourcentage de la marge par ",
                    'type' => "select",
                    'valeurs_select' => array(

                        'prix_de_vente' => 'Prix de vente',
                        'prix_d_achat' => "Prix d'achat",
                    ),
                    'description' => "",
                    'fonctionnalite' => "type_calcul_du_pourcentage_marge",
                ),
                array(

                    'nom' => "Utiliser réglage de marge par nature",
                    'type' => "toggle",
                    'description' => "Permet de régler les marges sur un document via la nature de l'article",
                    'fonctionnalite' => "utiliser_reglage_marge_par_nature",
                    'fonctionnalite_mere' => "type_calcul_du_pourcentage_marge",
                    'fonctionnalite_mere_valeur_cible' => "prix_de_vente",
                    'valide' => true,
                ),
            ),

            'TVA' => array(
                array(

                    'nom' => "Modification TVA impossible sur document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "modification_tva_impossible_sur_documents",
                ),
                array(

                    'nom' => "Alerter si le document contient des TVA à 0",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "alerte_tva_0_sur_document",
                ),
                array(

                    'nom' => "Afficher le Total HT du document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "recap_saisie_document_afficher_total_ht",
                    'valide' => true,
                ),
                array(

                    'nom' => "Afficher le Total TVA du document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "recap_saisie_document_afficher_total_tva",
                    'valide' => false,
                ),
                array(

                    'nom' => "Afficher le Total TTC du document",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "recap_saisie_document_afficher_total_ttc",
                    'valide' => true,
                ),
            ),

            'Gestion des stocks' => array(
                array(

                    'nom' => "Entrepot par défaut",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['entrepot']) ? $valeurs['entrepot'] : array(),
                    'description' => "",
                    'fonctionnalite' => "entrepot_id_par_defaut",
                ),
                array(

                    'nom' => "Activer la gestion des entrepôts sur chaque ligne d'articles",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "entrepot_sur_ligne",
                ),
                array(

                    'nom' => "Activer la gestion des stocks",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "activer_gestion_stock",
                ),
                array(

                    'nom' => "Adresse email pour la gestion des stocks",
                    'type' => "input",
                    'placeholder' => "Saisissez un email",
                    'description' => "",
                    'fonctionnalite' => "adresse_email_gestion_stocks",
                    'fonctionnalite_mere' => "activer_gestion_stock",
                ),
                array(

                    'nom' => "Afficher les stocks dispo sur les devis",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_stocks_dispo_sur_devis",
                    'fonctionnalite_mere' => "activer_gestion_stock",
                ),
                array(

                    'nom' => "Afficher les stocks dispo sur les commandes",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_stocks_dispo_sur_commande",
                    'fonctionnalite_mere' => "activer_gestion_stock",
                ),
                array(

                    'nom' => "Considérer les nomenclatures comme des produits finis",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_nomenclature_stocks_produits_finis",
                    'fonctionnalite_mere' => "activer_gestion_stock",
                ),
                array(

                    'nom' => "Bloquer la suppression d'un article s'il est présent dans des documents commerciaux ou s'il a du stock",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_bloquer_suppression_article_avec_stock",
                    'fonctionnalite_mere' => "activer_gestion_stock",
                ),
                array(

                    'nom' => "Eclatement automatique des articles conditionnés d'un BL achat en éléments unitaires",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_eclatement_automatique_conditionnement_bl_achat",
                ),
            ),

            'Fonctionnalités' => array(
                array(

                    'nom' => "Aide à la saisie des commandes achats depuis les documents de vente",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "aide_a_la_saisie_des_commandes_achats_depuis_les_documents_de_vente",
                    'valide' => true,
                ),
                array(

                    'nom' => "Utiliser conditionnement",
                    'type' => "toggle",
                    'description' => "Utiliser les conditionnements d'articles sur l'ERP",
                    'fonctionnalite' => "utiliser_conditionnement",
                    'valide' => true,
                ),
                array(
                    'nom' => "Gestion de l'intégrité de la séquence chronologique",
                    'type' => "toggle",
                    'description' => "Permet d'activer / désactiver la vérification des dates lors de la validation d'une facture, acompte et avoir",
                    'fonctionnalite' => "gestion_integrite_sequence_chronologique",
                ),
                array(
                    'nom' => "Gestion de l'intégrité de la séquence chronologique : recommencer à zero chaque année",
                    'type' => "toggle",
                    'description' => "Si ce paramètre est activé, la section chronologique est contrôlée année par année (donc on pourra toujours facturer au 31/12)",
                    'fonctionnalite' => "gestion_integrite_sequence_chronologique_par_annee",
                ),
                array(
                    'nom' => "Pouvoir saisir des documents en devise étrangère",
                    'type' => "toggle",
                    'description' => "Permet d'afficher la somme d'un document via une devise étrangére",
                    'fonctionnalite' => "saisie_documents_devise_etrangere",
                    'valide' => true,
                ),
                array(
                    'nom' => "Sélection du champ qui va permettre de faire la conversion des devises étrangères",
                    'type' => "select",
                    'fonctionnalite' => "documents_devise_etrangere_champ_conversion",
                    'valeurs_select' => array(
                        'tarif' => "Activer sur le prix unitaire",
                        'prix_achat' => "Activer sur le prix d'achat",
                    ),
                    'valide' => true,
                    'fonctionnalite_mere' => "saisie_documents_devise_etrangere",
                ),
                array(
                    'nom' => "Griser le prix converti",
                    'type' => "toggle",
                    'fonctionnalite' => "documents_devise_etrangere_griser_prix_converti",
                    'valide' => true,
                    'fonctionnalite_mere' => "saisie_documents_devise_etrangere",
                ),
                array(

                    'nom' => "Activer le style (css) sur les lignes (titres, commentaires) des documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_activer_style_sur_ligne_document",
                ),
                array(
                    'nom' => "Mise à jour des articles",
                    'type' => "toggle",
                    'description' => "Bouton permettant de mettre à jour les articles à partir de la table article sur devis et commandes ventes dont la composition des nomenclatures",
                    'fonctionnalite' => "option_mettre_a_jour_nomenclatures_et_tarifs",
                ),
                array(

                    'nom' => "Mise à jour uniquement de tarif",
                    'type' => "toggle",
                    'description' => "Bouton de mise à jour des prix des articles sur devis et commandes ventes ( uniquement les tarifs )",
                    'fonctionnalite' => "gescom_document_bouton_rafraichir_article_tarifs",
                ),
                array(

                    'nom' => "Mise à jour des articles lors de la duplication",
                    'type' => "select",
                    'description' => "",
                    'valeurs_select' => array(
                        0 => "Désactivé",
                        'article_tarif' => "Composition complète des nomenclatures/produits assemblés + tarifs",
                        'tarif' => "Tarifs uniquement",
                    ),
                    'fonctionnalite' => "mise_a_jour_tarif_duplication",
                ),
                array(

                    'nom' => "Comptabilisation",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "listes_factures_autres_action_comptabiliser",
                ),
                array(

                    'nom' => "Frais de livraison",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "frais_de_port_sur_documents_commerciaux",
                ),
                array(

                    'nom' => "Envoyer une relance depuis un devis",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "relance_devis_vente_utilisateur",
                ),
                array(
                    'nom' => "Gestion des crédits",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_credits",
                ),
                array(

                    'nom' => "Utiliser les coupons de réduction",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_document_coupon_reduction",
                ),
                array(

                    'nom' => "Afficher le bandeau du bas sur les GESCOM",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_afficher_bandeau_du_bas",
                ),
                array(

                    'nom' => "Informations à afficher sur le bandeau du bas",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => [
                        'nombre_articles' => 'Nombre d\'articles',
                        'total_quantite' => 'Total quantité',
                        'total_ht' => 'Total HT',
                        'total_ttc' => 'Total TTC',
                        'total_ttc_apres_remise' => 'Total TTC après remise'
                    ],
                    'description' => "",
                    'fonctionnalite' => "bandeau_bas_informations",
                    'fonctionnalite_mere' => "gescom_afficher_bandeau_du_bas",
                ),
                array(

                    'nom' => "Mode de vente",
                    'type' => "select",
                    'valeurs_select' => array(
                        'B2C' => 'B2C',
                        'B2B' => 'B2B',
                    ),
                    'description' => "",
                    'fonctionnalite' => "mode_de_vente",
                ),
                array(

                    'nom' => "Affacturage",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_affacturage",
                ),
                array(

                    'nom' => "Ligne de chèque dans mode de paiement",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['mode_paiement']) ? $valeurs['mode_paiement'] : array(),
                    'description' => "",
                    'fonctionnalite' => "ligne_de_cheque_dans_mode_paiement",
                ),
                array(

                    'nom' => "Date à utiliser pour considérer qu'une facture est due",
                    'type' => "input",
                    'placeholder' => "Saisissez un nom de champ type date sur les factures",
                    'description' => "Saisissez un nom de champ type date sur les factures",
                    'fonctionnalite' => "date_a_utiliser_pour_considerer_une_facture_comme_due",
                ),
                array(

                    'nom' => "Nombre de chiffres décimaux sur les tarifs",
                    'type' => "input",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "nombre_de_chiffres_decimaux_sur_les_tarif",
                ),
            ),

            'Règles de gestion' => array(
                array(

                    'nom' => "Faire l'arrondi par ligne pour calculer les totaux",
                    'type' => "toggle",
                    'description' => "Si cette fonctionnalité est activée, le total de la facture sera la sommes des arrondis de toutes les lignes. Si elle est désactivée, le total du document sera l'arrondi de la somme de toutes les lignes.",
                    'fonctionnalite' => "gescom_document_arrondi_par_ligne",
                ),
                array(

                    'nom' => "Gestion de l'arrondi sur une ligne",
                    'type' => "select",
                    'valeurs_select' => array(
                        'total' => 'Sur le total',
                        'prix_unitaire' => 'Sur le prix unitaire'
                    ),
                    'description' => "/!\ Il faut configurer les modèles de documents en conséquence pour reprendre la bonne méthode de calcul /!\ ",
                    'fonctionnalite' => "gescom_regle_arrondi_ligne",
                ),
                array(

                    'nom' => "Gérer la suppression d'une facture validée",
                    'type' => "select",
                    'valeurs_select' => array(

                        'creer_avoir' => 'Créer un avoir',
                        'empecher_suppression' => 'Empecher suppression',
                        'supprimer' => 'Supprimer',
                    ),
                    'description' => "",
                    'fonctionnalite' => "gescom_suppression_facture_valide",
                ),
                array(

                    'nom' => "Générer un avoir en guise de suppression d'acompte",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "annulation_acompte_par_avoir",
                ),
                array(
                    'nom' => "Commande vente annulable",
                    'description' => "",
                    'fonctionnalite' => "gescom_commande_vente_annulable_non_supprimable",
                    'type' => "select",
                    'valeurs_select' => array(
                        0 => "Désactivé",
                        'annulable_non_supprimable' => 'Annulable mais non supprimable',
                        'annulable_supprimable' => 'Annulable et supprimable',
                    )
                ),
                array(

                    'nom' => "Pouvoir modifier un document validé",
                    'description' => "",
                    'fonctionnalite' => "modification_document_valide",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $valeurs['documents_gescom'] ?? array(),
                ),
                array(

                    'nom' => "Valider le document à l'enregistrement",
                    'type' => "badge_multiselection",
                    'fonctionnalite' => "valider_document_enregistrement",
                    'valeurs_badges' => $valeurs['documents_gescom_modifiable_post_validation'] ?? array(),
                ),
                array(
                    'nom' => "Bloquer la modification / suppression des factures de plus de 30 jours",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "autorise_a_modifier_factures_de_plus_de_x_jours",
                ),
                array(

                    'nom' => "Cloturer la saisie des factures du mois précédent le",
                    'type' => "select",
                    'valeurs_select' => array(

                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '7' => '7',
                        '8' => '8',
                        '9' => '9',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12',
                        '13' => '13',
                        '14' => '14',
                        '15' => '15',
                        '16' => '16',
                        '17' => '17',
                        '18' => '18',
                        '19' => '19',
                        '20' => '20',
                        '21' => '21',
                        '22' => '22',
                        '23' => '23',
                        '24' => '24',
                        '25' => '25',
                    ),
                    'description' => "",
                    'fonctionnalite' => "cloture_comptable_mensuelle_le",
                ),
                array(

                    'nom' => "Paramétrage avancé des documents",
                    'type' => "toggle",
                    'description' => "Permet de définir des règles par client : nombre d'impressions, champs obligatoires, modèles de document...",
                    'fonctionnalite' => "parametrage_avance_des_documents",
                ),

                array(

                    'nom' => "Lors d'une fusion de document (vers une facture), ne pas facturer les BL à 0",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_ne_pas_facturer_bl_lors_fusion_vers_facture",
                ),
                array(

                    'nom' => "Lors d'une fusion de document (vers une facture), ne pas facturer les commandes à 0",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_ne_pas_facturer_commande_vente_lors_fusion_vers_facture",
                ),
                array(

                    'nom' => "Lors de la génération d'une facture depuis action en masse, génère des titres de séparation",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_separation_dans_facturer_documents",
                ),
                array(

                    'nom' => "Lors de la génération d'une facture depuis action en masse, génère des sous totaux de séparation",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_separation_sous_total_dans_facturer_documents",
                ),
                array(

                    'nom' => "Bloquer la suppression d'un client si on lui a déjà édité des documents commerciaux",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_bloquer_suppression_client_si_presence_documents_commerciaux",
                ),
                array(

                    'nom' => "Bloquer la suppression d'un fournisseur si on lui a déjà édité des documents commerciaux",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gescom_bloquer_suppression_fournisseur_si_presence_documents_commerciaux",
                ),
                array(

                    'nom' => "Bloquer la validation d'un BL si il y a des stocks négatifs",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "bloquer_validation_bl_si_stock_negatif",
                ),
                array(

                    'nom' => "Bloquer la modification des codes articles",
                    'type' => "toggle",
                    'description' => "Permet de bloquer la modification du code article une fois que l'article a été créé (et le code article renseigné)",
                    'fonctionnalite' => "bloquer_modification_code_article",
                ),
                array(

                    'nom' => "Bloquer les doublons de codes articles",
                    'type' => "toggle",
                    'description' => "Permet de bloquer la possibilité d'avoir un doublon dans les codes articles",
                    'fonctionnalite' => "bloquer_doublon_code_article",
                ),
                array(

                    'nom' => "Un seul BR par commande FRS / jour",
                    'type' => "toggle",
                    'description' => "Si le fournisseur a livré plusieurs commandes la même journée, nous génèrerons alors un BR par commande fournisseur, et non un BR pour l'ensemble des commandes",
                    'fonctionnalite' => "gescom_un_seul_br_par_commande_achat_par_jour",
                ),
                array(

                    'nom' => "Document figeant l'éco-contribution",
                    'type' => "select",
                    'valeurs_select' => $valeurs['documents_gescom_vente'] ?? array(),
                    'description' => "Indique le document à partir duquel le flux de document est figé pour la récupération des valeurs d'éco-contribution",
                    'fonctionnalite' => "eco_contribution_document_fixant_valeur",
                ),
                array(

                    'nom' => "Ecart de gestion TTC dans les documents",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $valeurs['documents_ecart_gestion_ttc'] ?? [],
                    'description' => "Permet de gérer des centimes d'écart de gestion en TTC sur les documents",
                    'fonctionnalite' => "ecart_gestion_ttc",
                ),
                array(

                    'nom' => "Compte de produit pour l'écart de gestion",
                    'type' => "select",
                    'valeurs_select' => $valeurs['compte_comptable'] ?? [],
                    'fonctionnalite' => "ecart_gestion_compte_produit",
                    'fonctionnalite_mere' => "ecart_gestion_ttc"
                ),
                array(

                    'nom' => "Compte de charge pour l'écart de gestion",
                    'type' => "select",
                    'valeurs_select' => $valeurs['compte_comptable'] ?? [],
                    'fonctionnalite' => "ecart_gestion_compte_charge",
                    'fonctionnalite_mere' => "ecart_gestion_ttc"
                ),
                array(

                    'nom' => "Seuil maximum de l'écart de gestion TTC",
                    'type' => "input",
                    'fonctionnalite' => "seuil_ecart_gestion_ttc",
                    'fonctionnalite_mere' => "ecart_gestion_ttc"
                ),
            ),

            'Gestion du catalogue articles' => array(
                array(

                    'nom' => "Composition des articles",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "composition_des_articles",
                ),
                array(

                    'nom' => "Bloquer les doublons de référence article par fournisseur",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "bloquer_doublon_reference_article_fournisseur",
                ),
                array(

                    'nom' => "On supprime le prix d'achat de l'article si l'article_fournisseur de référence est supprimé",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "supprimer_prix_achat_article_fournisseur_prioritaire",
                ),
                array(

                    'nom' => "Calcul du stock actuel des articles après l'ajout ou la suppression d'un mouvement de stock",
                    'type' => "toggle",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "calcul_stock_actuel_article_post_mouvement",
                ),
            ),

            'Relance par mail' => array(
                array(

                    'nom' => "Numéro de la ligne de relance par email",
                    'type' => "input",
                    'placeholder' => "ID du type de relance correspondand à email",
                    'description' => "ID du type de relance correspondand à email",
                    'fonctionnalite' => "numero_de_la_ligne_de_relance_par_email",
                ),

                array(

                    'nom' => "Modèle de relance 1",
                    'type' => "textarea",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "modele_relance_1",
                ),
                array(

                    'nom' => "Modèle de relance 2",
                    'type' => "textarea",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "modele_relance_2",
                ),
                array(

                    'nom' => "Modèle de relance 3",
                    'type' => "textarea",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "modele_relance_3",
                ),
                array(

                    'nom' => "Modèle de relance 4",
                    'type' => "textarea",
                    'placeholder' => "",
                    'description' => "",
                    'fonctionnalite' => "modele_relance_4",
                ),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Gestion commerciale';
    }

    public function obtenir_nom_module(){

        return 'Module de gestion commerciale';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/euro.png';
    }
}
