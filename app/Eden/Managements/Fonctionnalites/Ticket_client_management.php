<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Ticket_client_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs(){

        $types_modele = ['modele_email'];
        $valeurs = [];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        $email = fonctionnalite('releve_mail_ticket_client_microsoft_email');

        $valeurs['dossiers_emails_microsoft'] = !empty($email) ? service('microsoft_email')->dossiers($email) : array();

        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(

            'Configuration relève de mails' => array(

                array(
					'nom' => "Synchronisation via Microsoft",
					'type' => "toggle",
					'description' => "Sur Microsoft Azure veuillez rajouter les autorisations applications sur Mail.Read et Mail.Send lors de l'activation de cette fonctionnalité. 
					    Si cette fonctionnalité n'est pas activé, on récupére la configuration email avec le type 'Ticket client'",
					'fonctionnalite' => "releve_mail_ticket_client_microsoft",
					'valide' => true,
				),
                array(
					'nom' => "Boîte mail microsoft à synchroniser",
					'type' => "input",
					'description' => "",
					'fonctionnalite' => "releve_mail_ticket_client_microsoft_email",
					'valide' => true,
                    'methode_changement' => 'recuperation_dossiers_boite_mail_microsoft',
                    'fonctionnalite_mere' => 'releve_mail_ticket_client_microsoft'
				),
                array(
					'nom' => "Dossier de la boîte mail microsoft à synchroniser",
					'type' => "select",
					'description' => "",
					'fonctionnalite' => "releve_mail_ticket_client_microsoft_dossier",
					'valide' => true,
                    'include' => 'dossiers_boite_mail_microsoft',
                    "valeurs_select" => isset($valeurs['dossiers_emails_microsoft']) ? $valeurs['dossiers_emails_microsoft'] : array(),
                    'fonctionnalite_mere' => 'releve_mail_ticket_client_microsoft_email'
				),
                array(
					'nom' => "Dossier de la boîte mail microsoft dans lequel les mails seront déplacés une fois traités",
					'type' => "select",
					'description' => "",
					'fonctionnalite' => "releve_mail_ticket_client_microsoft_dossier_deplacement_apres_traitement",
					'valide' => true,
                    "valeurs_select" => isset($valeurs['dossiers_emails_microsoft']) ? array_merge(['0' => 'Sans valeur'],$valeurs['dossiers_emails_microsoft']) : array(),
                    'fonctionnalite_mere' => 'releve_mail_ticket_client_microsoft_email'
				),
                array(
					'nom' => "Reconnaitre le client via son nom de domaine",
					'type' => "toggle",
					'description' => "",
					'fonctionnalite' => "releve_mail_ticket_client_reconnaitre_client_via_domaine",
					'valide' => true,
                ),
                array(
					'nom' => "Extensions des piéces jointes non autorisées",
					'type' => "input",
					'description' => "Les extensions doivent être séparés par des ';' (exemple : exe;png)",
					'fonctionnalite' => "releve_mail_ticket_client_extension_piece_jointe_bloque",
					'valide' => true,
                ),
                array(
					'nom' => "Taille maximale des piéces jointes en Mo",
					'type' => "input",
					'description' => "",
					'fonctionnalite' => "releve_mail_ticket_client_taille_maximale_piece_jointe_en_mo",
					'valide' => true,
                ),
			),

            "Gestion des mails d'alerte" => array(

                array(
					'nom' => "Modèle d'email de prise en charge",
					'type' => "select",
					'description' => "",
					'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
					'fonctionnalite' => "releve_mail_ticket_client_modele_email_creation_ticket",
					'valide' => true,
					'valeur_vide' => true,
				),
                array(
					'nom' => "Modèle d'email de réponse",
					'type' => "select",
					'description' => "",
					'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
					'fonctionnalite' => "releve_mail_ticket_client_modele_email_reponse_ticket",
					'valide' => true,
					'valeur_vide' => true,
				),
                array(
					'nom' => "Modèle d'email de prise en charge de réponse par mail d'un client",
					'type' => "select",
					'description' => "",
					'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
					'fonctionnalite' => "releve_mail_ticket_client_modele_email_reponse_client_mail_ticket",
					'valide' => true,
					'valeur_vide' => true,
				),
                array(
					'nom' => "Modèle d'email de clotûre",
					'type' => "select",
					'description' => "",
					'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
					'fonctionnalite' => "releve_mail_ticket_client_modele_email_cloture_ticket",
					'valide' => true,
					'valeur_vide' => true,
				),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Ticket client';
    }

    public function obtenir_nom_module(){

        return 'Module Ticket client';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/ticket_support.png';
    }
}