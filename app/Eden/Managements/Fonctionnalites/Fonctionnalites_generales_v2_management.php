<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Fonctionnalites_generales_v2_management extends Fonctionnalite_management{

    public $depuis_fonctionnalite_avec_valeur = false;

    public function fonctionnalites_avec_valeurs()
    {
        $types_modele = [];
        $valeurs = [];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        $this->depuis_fonctionnalite_avec_valeur = true;
        
        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $documents_gescom_vente_tmp = Variables::$documents_vente_gescom;
        $documents_gescom_vente = [];

        foreach ($documents_gescom_vente_tmp as $type_element) {

            if($this->depuis_fonctionnalite_avec_valeur === true)
                $type_element_modifie = ucfirst(traduction('tables_libres.' . $type_element . '.nom_table'));
            else
                $type_element_modifie = ucfirst(str_replace('_', ' ', $type_element));

            $documents_gescom_vente[$type_element] = $type_element_modifie;
        }

        $fonctionnalites = array(

            'Paramètres généraux' => array(

                array(

                    'nom' => "Rechercher par défaut...",
                    'type' => "select",
                    'valeurs_select' => array(

                        '' => 'Tout',
                        'projet' => 'Projet',
                        'client' => 'Client',
                    ),
                    'description' => "",
                    'fonctionnalite' => "recherche_par_defaut",
                ),

                array(

                    'nom' => "Utiliser l'historique dans la navbar",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_historique_navbar",
                ),

                array(

                    'nom' => "Nombre de lignes dans l'historique",
                    'type' => "input",
                    'description' => "Permet de régler le nombre de ligne affichées dans l'historique",
                    'fonctionnalite' => "nombre_de_lignes_historique_bouton",
                    'fonctionnalite_mere' => "utiliser_historique_navbar",
                ),

                array(

                    'nom' => "Notifications",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "notifications",
                ),

                array(

                    'nom' => "Afficher les options de la liste à gauche",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "listes_activer_menu_options_a_gauche",
                ),

                array(

                    'nom' => "Limite avant export différé",
                    'type' => "input",
                    'description' => "Indique la limite d'élément à dépasser pour passer en export différé.",
                    'fonctionnalite' => "limite_export_differe",
                    'valide' => 1,
                ),

                array(

                    'nom' => "Nombre de lignes dans les listes par défaut",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "nombre_de_lignes_dans_listes",
                ),

                array(

                    'nom' => "Rechercher sur les adresses pour les clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "tags_recherche_adresse_pour_client",
                    'valide' => true,
                ),

                array(

                    'nom' => "Rechercher sur les contacts pour les clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "tags_recherche_contact_pour_client",
                    'valide' => true,
                ),

                array(

                    'nom' => "Utiliser connexion classique pour l'ERP",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_connexion_classique",
                ),

                array(

                    'nom' => "Supprimer les zeros superflux dans l'affichage des montants",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "supprimer_les_zeros_superflux",
                    'valide' => true,
                ),

                array(

                    'nom' => "Dashboard : transmission des filtres d'un tableau de bord à l'autre",
                    'type' => "toggle",
                    'description' => "Si la fonctionnalité est activée, les valeurs des filtres sélectionnés dans le header seront transmis lorsque l'on passe d'un tableau de bord à l'autre.",
                    'fonctionnalite' => "dashboard_transmission_de_filtres",
                    'valide' => true,
                ),
            ),

            'Fiche article : contenu' => array(

                array(

                    'nom' => "Thèmes de filtres",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "theme_de_filtres",
                ),

                array(

                    'nom' => "Déclinaisons",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_article_declinaisons",
                ),

                array(

                    'nom' => "Tarifs fournisseurs",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_article_fournisseurs",
                ),

                array(

                    'nom' => "Historique des prix d'achat de l'article",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_article_historique_prix_d_achat_de_l_article",
                ),

                array(

                    'nom' => "Calcul du prix de vente en fonction du prix d'achat et d'une marge",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "calcul_tarif_sur_fiche_article_avec_marge",
                ),

            ),

            'Fiche fournisseur : contenu' => array(

                array(

                    'nom' => "Thèmes de filtres",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "theme_de_filtres_fournisseur",
                ),

                array(

                    'nom' => "Emails reçus",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_fournisseur_emails_recus",
                ),

            ),

            'Fiche famille : contenu' => array(

                array(

                    'nom' => "Liste des articles avec photo",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_famille_articles_avec_photo",
                ),

                array(

                    'nom' => "Filtres sur liste",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "filtres_sur_liste",
                ),

            ),

            'Fiche client : contenu' => array(

                array(

                    'nom' => "Commerce : Onglet par défaut fiche client",
                    'type' => "select",
                    'valeurs_select' => array(

                        'factures' => 'Factures',
                        'devis' => 'Devis',
                        'commandes' => 'Commandes',
                        'avoirs' => 'Avoirs'
                    ),
                    'description' => "",
                    'fonctionnalite' => "fiche_client_onglet_par_defaut",
                ),

            ),

            'Adresse' => array(

                array(

                    'nom' => "Mode multi adresses",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "mode_multi_adresses",
                ),

            ),

            'Gestion des pièces jointes' => array(

                array(

                    'nom' => "Gestion des pièces jointes",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_pieces_jointes",
                ),

                array(

                    'nom' => "Titre",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_pieces_jointes_titre",
                ),

                array(

                    'nom' => "Nom",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_pieces_jointes_nom",
                ),

                array(

                    'nom' => "Poids",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_pieces_jointes_poids",
                ),

                array(

                    'nom' => "Extension",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_pieces_jointes_extension",
                ),

                array(

                    'nom' => "Garder le nom originel du fichier",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "pieces_jointes_garder_nom_originel",
                ),
                array(

                    'nom' => "Documents commerciaux disponibles dans le module pièce jointe",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $documents_gescom_vente,
                    'description' => "Permet de choisir les documents de vente qui seront disponibles dans le module Pièces Jointes pour les clients",
                    'fonctionnalite' => "pieces_jointes_documents_commerciaux_disponibles",
                    'valide' => true,
                ),
                array(

                    'nom' => "Taille maximale des images en Mo (compression automatique)",
                    'type' => "input",
                    'description' => "Au-delà de cette taille, les images du stockage sont compressées par le cron. Par défaut : 5 Mo.",
                    'fonctionnalite' => "compression_images_taille_maximale_en_mo",
                    'valide' => true,
                ),
            ),

            'Entités' => array(

                array(

                    'nom' => "Mode multi entités",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "mode_multi_entites",
                ),

                array(

                    'nom' => "Fiche client unique en multi entité",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_client_unique_multi_entite",
                    'fonctionnalite_mere' => "mode_multi_entites",
                ),

                array(

                    'nom' => "Gestion des droits des entités sur les répertoires",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_droit_entites_repertoire",
                    'fonctionnalite_mere' => "mode_multi_entites",
                ),

            ),

            'Blocage à la connexion' => array(

                array(

                    'nom' => "Nombre de tentatives maximum de connexion avant blocage",
                    'type' => "input",
                    'description' => "Permet de régler le nombre maximum de tentatives de connexion avant blocage",
                    'fonctionnalite' => "nombre_de_tentatives_connexion_maximum_avant_blocage",
                ),

                array(

                    'nom' => "Nombre de minutes de blocage à la connexion si blocage",
                    'type' => "input",
                    'description' => "Permet de régler le nombre de minutes du blocage ( proportionnel aux nombres de connexions échoué par rapport à la fonctionnalité au dessus",
                    'fonctionnalite' => "nombre_de_minutes_blocage",
                ),

                array(

                    'nom' => "Adresse mail à prevenir si robot détecté",
                    'type' => "input",
                    'description' => "Permet de paramétrer l'adresse email recevant une alerte en cas de détection de
                    25 ou plus de connexion échoué par une même IP, toutes adresses emails confondues",
                    'fonctionnalite' => "adresse_mail_a_prevenir_robot",
                ),

            ),

            'Intervention' => array(

                array(

                    'nom' => "Générer les interventions...",
                    'type' => "select",
                    'valeurs_select' => array(

                        '15 days' => '15 jours avant la date anniversaire',
                        '1 month' => '1 mois avant la date anniversaire',
                        '2 months' => '2 mois avant la date anniversaire',
                    ),
                    'description' => "",
                    'fonctionnalite' => "maintenance_delai_creation_intervention",
                ),
            ),

            'Feuille de temps' => array(

                array(

                    'nom' => "Création d'une feuille de temps lorsqu'on crée tache (modification, suppression)",
                    'type' => "toggle",
                    'description' => "Ne fonctionne pas à cause du fait qu'il y a un champ obligatoire projet_id sur les feuilles de temps",
                    'fonctionnalite' => "creation_feuille_de_temps_pour_tache",
                ),
                array(

                    'nom' => "Permettre le chronométrage de temps",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_chronometre",
                ),
            ),

            'Note de frais' => array(

                array(

                    'nom' => "Nombre de mois inférieur à aujourd'hui qui bloque la création de note de frais",
                    'type' => "input",
                    'fonctionnalite' => "mois_bloquant_saisie_note_de_frais",
                    'gestion_profil' => true
                ),
            ),

            'Accessibilité' => array(

                array(

                    'nom' => "Activer les options d'accessibilité sur Eden",
                    'type' => "toggle",
                    'fonctionnalite' => "accessibilite",
                ),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Fonctionnalités générales';
    }

    public function obtenir_nom_module(){

        return 'Module de fonctionnalités générales';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/functionnalities.png';
    }
}
