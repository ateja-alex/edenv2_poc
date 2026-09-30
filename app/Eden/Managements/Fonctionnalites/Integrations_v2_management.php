<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;
use Illuminate\Support\Facades\Log;

class Integrations_v2_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {

        $valeurs['tache'] = management('tache')->champ('type_tache_rdv')->valeurs_possibles;

        try {

            $valeurs['microsoft_sharepoint_site'] = service('microsoft_sharepoint')->lister_tous_les_sites();
        } catch(\Exception $erreur){

            $valeurs['microsoft_sharepoint_site'] = array();
            Log::error('Erreur lors de la récupération des sites Sharepoint. Message : ' . $erreur->getMessage());
        }

        try {

            $valeurs['microsoft_sharepoint_dossier'] = service('microsoft_sharepoint')->lister_tous_les_dossiers();
        } catch(\Exception $erreur){

            $valeurs['microsoft_sharepoint_dossier'] = array();
            Log::error('Erreur lors de la récupération des dossiers du site Sharepoint. Message : ' . $erreur->getMessage());
        }

        $types_modele = ['mode_paiement', 'compte_bancaire'];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        return $this->fonctionnalites($valeurs);
    }

    /*
     *
     * Retourne les fonctionnalités du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(

            'Paramètres généraux' => array(

                array(

                    'nom' => "Projet id (ticket)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "projet_id_ticket_eden",
                ),
            ),
            'Microsoft Office 365' => array(

                array(

                    'nom' => "Utiliser la connexion Microsoft",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "microsoft_utiliser_connexion",
                ),

                array(

                    'nom' => "Microsoft : APP ID",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "microsoft_app_id",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(

                    'nom' => "Microsoft : APP SECRET",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "microsoft_app_secret",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(

                    'nom' => "Microsoft : APP Redirect URI",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "microsoft_redirect_uri",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(

                    'nom' => "Microsoft : ID Active Directory",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "microsoft_id_active_directory",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(
                    'nom' => "Microsoft : Nombre de mois synchro",
                    'type' => "input",
                    'description' => "Permet de choisir le nombre de mois avant et après aujourd'hui pour la synchro des événements",
                    'fonctionnalite' => "microsoft_nombre_mois",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(
                    'nom' => "Microsoft : Rafraîchir delta token x jours avant date de fin",
                    'type' => "input",
                    'description' => "Permet de déterminer combien de jours avant l'expiration on rafraîchit le delta token.",
                    'fonctionnalite' => "microsoft_rafraichissement_delta_token_avant_fin",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(
                    'nom' => "Microsoft : Type de RDV par défaut",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['tache']) ? $valeurs['tache'] : array(),
                    'description' => "Permet de choisir le type que doit prendre par défaut un RDV lors de la synchronisation Office vers Eden",
                    'fonctionnalite' => "microsoft_type_rdv_defaut",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(

                    'nom' => 'Utiliser la synchronisation Sharepoint (Liste à configurer: <a href="'.url('/eden/liste/parametrage_mappage_sharepoint').'">Paramétrage Mappage Sharepoint</a>)',
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "sharepoint_utiliser_synchronisation",
                    'fonctionnalite_mere' => "microsoft_utiliser_connexion",
                ),
                array(

                    'nom' => "Microsoft : Site racine d'Eden dans Sharepoint",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['microsoft_sharepoint_site']) ? $valeurs['microsoft_sharepoint_site'] : array(),
                    'description' => "",
                    'fonctionnalite' => "site_racine_eden_sharepoint",
                    'fonctionnalite_mere' => "sharepoint_utiliser_synchronisation",
                ),
                array(

                    'nom' => "Microsoft : Dossier racine d'Eden dans Sharepoint",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['microsoft_sharepoint_dossier']) ? $valeurs['microsoft_sharepoint_dossier'] : array(),
                    'description' => "",
                    'fonctionnalite' => "dossier_racine_eden_sharepoint",
                    'fonctionnalite_mere' => "sharepoint_utiliser_synchronisation",
                ),
            ),
            'M-Files' => array(
                array(

                    'nom' => 'Utiliser la synchronisation M-Files (Liste à configurer: <a href="'.url('/eden/liste/parametrage_mappage_mfiles').'">Paramétrage Mappage M-Files</a>)',
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "mfiles_utiliser_synchronisation",
                ),
                array(

                    'nom' => "M-Files : URL",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "mfiles_url",
                    'fonctionnalite_mere' => "mfiles_utiliser_synchronisation",
                ),
                array(

                    'nom' => "M-Files : Jeton d'authentification",
                    'type' => "connexion",
                    'description' => "",
                    'fonctionnalite' => "mfiles_jeton_authentification",
                    'fonctionnalite_mere' => "mfiles_utiliser_synchronisation",
                ),
                array(

                    'nom' => "M-Files : ID de l'attribut eden_id",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "mfiles_attribut_eden_id",
                    'fonctionnalite_mere' => "mfiles_utiliser_synchronisation",
                ),
            ),
            'Google' => array(

                array(

                    'nom' => "Clé api Google",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "cle_api_google",
                ),

                array(

                    'nom' => "Utiliser la connexion Google",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "google_utiliser_connexion",
                ),

                array(

                    'nom' => "Identifiants OAuth: ID client",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_app_id",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "Identifiants OAuth: Code secret",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_app_secret",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "PROJET ID",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_projet_id",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "APP Redirect URI",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_redirect_uri",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "Compte de service: ID client",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_compte_service_id_client",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "Compte de service: email",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_compte_service_email",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "Compte de service: ID clé privée",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "google_id_cle_privee",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(

                    'nom' => "Compte de service: clé privée",
                    'type' => "textarea",
                    'description' => "",
                    'fonctionnalite' => "google_cle_privee",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(
                    'nom' => "Google : Type de RDV par défaut",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['tache']) ? $valeurs['tache'] : array(),
                    'description' => "Permet de choisir le type que doit prendre par défaut un RDV lors de la synchronisation Google vers Eden",
                    'fonctionnalite' => "google_type_rdv_defaut",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
                array(
                    'nom' => "Google : Nombre de mois synchro",
                    'type' => "input",
                    'description' => "Permet de choisir le nombre de mois avant aujourd'hui pour la synchro des événements",
                    'fonctionnalite' => "google_nombre_mois",
                    'fonctionnalite_mere' => "google_utiliser_connexion",
                ),
            ),

            'Budget Insight' => array(
                array(
                    'nom' => "Utiliser Budget Insight",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "budget_insight_utiliser",
                ),
                array(
                    'nom' => "Budget Insight: ID client",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "budget_insight_client_id",
                    'fonctionnalite_mere' => "budget_insight_utiliser",
                ),
                array(

                    'nom' => "Budget Insight: SECRET client",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "budget_insight_client_secret",
                    'fonctionnalite_mere' => "budget_insight_utiliser",
                ),
                array(

                    'nom' => "Budget insight: filtrer sur l'entité pour le traitement des transactions",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "budget_insight_filtre_entite_pour_traitement_transactions",
                    'fonctionnalite_mere' => "budget_insight_utiliser",
                ),
            ),
            'DocuSign' => array(
                array(
                    'nom' => "Activation DocuSign",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "docusign_activation",
                ),
                array(
                    'nom' => "DocuSign: ID client",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "docusign_client_id",
                    'fonctionnalite_mere' => "docusign_activation",
                ),
                array(
                    'nom' => "DocuSign: ID utilisateur",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "docusign_utilisateur_id",
                    'fonctionnalite_mere' => "docusign_activation",
                ),
                array(
                    'nom' => "DocuSign: Public key",
                    'type' => "textarea",
                    'description' => "",
                    'fonctionnalite' => "docusign_rsa_public_key",
                    'fonctionnalite_mere' => "docusign_activation",
                ),
                array(
                    'nom' => "DocuSign: Private Key",
                    'type' => "textarea",
                    'description' => "",
                    'fonctionnalite' => "docusign_rsa_private_key",
                    'fonctionnalite_mere' => "docusign_activation",
                ),
            ),
            'Stripe' => array(
                array(
                    'nom' => "Utiliser Stripe",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "stripe_activation",
                ),
                array(
                    'nom' => "Clé publique (PROD)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "stripe_public_key",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),
                array(
                    'nom' => "Clé privée (PROD)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "stripe_private_key",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),
                array(
                    'nom' => "Clé publique (TEST)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "stripe_public_key_test",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),
                array(
                    'nom' => "Clé privée (TEST)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "stripe_private_key_test",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),
                array(
                    'nom' => "Mode production",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "stripe_live",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),
                array(

                    'nom' => "ID mode de paiement CB Stripe",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['mode_paiement']) ? $valeurs['mode_paiement'] : array(),
                    'description' => "",
                    'fonctionnalite' => "id_mode_de_paiement_cb_stripe",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),

                array(

                    'nom' => "Compte bancaire CB Stripe",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_bancaire']) ? $valeurs['compte_bancaire'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compte_bancaire_cb_stripe",
                    'valide' => true,
                    'fonctionnalite_mere' => "stripe_activation",
                ),
            ),
            'Payline' => array(
                array(
                    'nom' => "Utiliser Payline",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "payline_activation",
                ),
                array(

                    'nom' => "ID mode de paiement CB Payline",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['mode_paiement']) ? $valeurs['mode_paiement'] : array(),
                    'description' => "",
                    'fonctionnalite' => "id_mode_de_paiement_cb_payline",
                    'fonctionnalite_mere' => "payline_activation",
                ),

                array(

                    'nom' => "Compte bancaire CB Payline",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_bancaire']) ? $valeurs['compte_bancaire'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compte_bancaire_cb_payline",
                    'fonctionnalite_mere' => "payline_activation",
                ),
            ),
            'Payzen' => array(
                array(
                    'nom' => "Utiliser Payzen",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "payzen_activation",
                ),
                array(

                    'nom' => "ID mode de paiement CB PayZen",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['mode_paiement']) ? $valeurs['mode_paiement'] : array(),
                    'description' => "",
                    'fonctionnalite' => "id_mode_de_paiement_cb_payzen",
                    'fonctionnalite_mere' => "payzen_activation",
                ),

                array(

                    'nom' => "Compte bancaire CB PayZen",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_bancaire']) ? $valeurs['compte_bancaire'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compte_bancaire_cb_payzen",
                    'fonctionnalite_mere' => "payzen_activation",
                ),

                array(

                    'nom' => "ID mode de paiement prélèvement PayZen",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['mode_paiement']) ? $valeurs['mode_paiement'] : array(),
                    'description' => "",
                    'fonctionnalite' => "id_mode_de_paiement_prelevement_payzen",
                    'fonctionnalite_mere' => "payzen_activation",
                ),

                array(

                    'nom' => "Compte bancaire prélèvement PayZen",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['compte_bancaire']) ? $valeurs['compte_bancaire'] : array(),
                    'description' => "",
                    'fonctionnalite' => "compte_bancaire_prelevement_payzen",
                    'fonctionnalite_mere' => "payzen_activation",
                ),
            ),
            'Mindee' => array(
                array(
                    'nom' => "Mindee Clé",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "mindee_api_key",
                ),
            ),
            'Scribens' => array(
                array(
                    'nom' => "Utiliser Scribens",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "scribens_activation",
                ),
                array(
                    'nom' => "Scribens Clé",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "scribens_api_key",
                    'fonctionnalite_mere' => "scribens_activation",
                ),
            ),
            'ReCaptcha v3' => array(
                array(
                    'nom' => "Clé public",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "recaptcha_cle_public",
                ),
                array(
                    'nom' => "Clé privé",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "recaptcha_cle_prive",
                ),
            ),
            'Open AI' => array(
                array(
                    'nom' => "Utiliser Open AI",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "open_ai_activation",
                ),
                array(
                    'nom' => "Clé Open AI",
                    'type' => "textarea",
                    'fonctionnalite' => "open_ai_cle",
                    'fonctionnalite_mere' => "open_ai_activation",
                ),
                array(
                    'nom' => "Project ID Open AI",
                    'type' => "input",
                    'fonctionnalite' => "open_ai_project_id",
                    'fonctionnalite_mere' => "open_ai_activation",
                ),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Intégration';
    }

    public function obtenir_nom_module(){

        return 'Module d\' intégration';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/api.png';
    }
}
