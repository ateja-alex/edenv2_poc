<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Emails_v2_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {
        $valeurs = [];
        $types_modele = ['modele_email'];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        $documents_gescom_tmp = \App\Eden\Variables::$documents_gescom;
        $documents_gescom = [];

        foreach ($documents_gescom_tmp as $type_element){

            $documents_gescom[$type_element] = ucfirst(str_replace('_', ' ', $type_element));

        }

        $valeurs['documents_gescom'] = $documents_gescom;
        $valeurs['emails_intranet'] = ['note_de_frais' => 'Note de frais', 'demande_conge' => 'Demande de congés'];

        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(
            'Paramètres généraux' => array(

                array(

                    'nom' => "Utiliser les identifiants des comptes emails",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "email_utiliser_identifiants_comptes_emails",
                ),

                array(

                    'nom' => "Mettre en copie l'expediteur lors de l'envoi de mail",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "expediteur_en_copie_du_mail",
                ),

                array(

                    'nom' => "Sélection des pièces jointes des articles disponibles pour le type de document",
                    'type' => "badge_multiselection",
                    'description' => "",
                    'fonctionnalite' => "selection_pj_article_envoie_mail_documents_gescom",
                    'valeurs_badges' => isset($valeurs['documents_gescom']) ? $valeurs['documents_gescom'] : [],
                ),

                array(

                    'nom' => "Adresse email globale de l'application",
                    'type' => "input",
                    'description' => "L'email est celui de l'expéditeur en cas de mot de passe oublié par exemple",
                    'fonctionnalite' => "adresse_mail_fonctionnement_application",
                ),

                array(

                    'nom' => "Nom de l'expéditeur globale de l'application",
                    'type' => "input",
                    'description' => "Le nom est celui de l'expéditeur en cas de mot de passe oublié par exemple",
                    'fonctionnalite' => "nom_mail_fonctionnement_application",
                ),

                array(

                    'nom' => "Adresse email de support",
                    'type' => "input",
                    'description' => "Adresse email à contacter en cas d'échec des crons pendant une trop longue période par exemple",
                    'fonctionnalite' => "adresse_mail_support",
                ),
            ),
            'Synchronisation email' => array(
                array(
					'nom' => "Nombre de jours de délai de récupération des mails",
					'type' => "input",
					'description' => "",
					'fonctionnalite' => "releve_mail_jour_delai",
					'valide' => true,
				),
                array(
					'nom' => "Nombre d'éléments microsoft à récupérer par synchronisation",
					'type' => "input",
					'description' => "Les éléments sont récupérés dans l'ordre décroissant d'arrivée dans la boîte mail",
					'fonctionnalite' => "releve_mail_microsoft_nombre_a_recuperer",
					'valide' => true,
				),
            ),
            'Autres' => array(
                array(

                    'nom' => "Ne pas traiter les mails qui ne contiennent pas de documents PDF",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "boite_reception_mails_fournisseurs_eden_pas_traiter_mails_sans_pdf",
                ),
                array(

                    'nom' => "ID mail relance moyen de paiement",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
                    'description' => "",
                    'fonctionnalite' => "id_mail_relance_moyen_de_paiement",
                ),

                array(

                    'nom' => "ID mail encaissement cb",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
                    'description' => "",
                    'fonctionnalite' => "id_mail_encaissement_cb",
                ),
                array(

                    'nom' => "Nom de domaine interne (emails reçus)",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "email_recus_ndd_interne",
                ),
                array(

                    'nom' => "Emails intranet actifs",
                    'type' => "badge_multiselection",
                    'description' => "",
                    'valeurs_badges' => isset($valeurs['emails_intranet']) ? $valeurs['emails_intranet'] : [],
                    'fonctionnalite' => "emails_actifs_intranet",
                )
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Emails';
    }

    public function obtenir_nom_module(){

        return 'Module d\'emails';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/email.png';
    }
}
