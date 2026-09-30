<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Extranet_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs(){

        $valeurs['maquette'] = modele('maquette')->select('id',\DB::raw("CONCAT('Maquette ',id,' : ',nom_application) AS nom_maquette"))->get()->pluck('nom_maquette','id')->toArray();
        $valeurs['modele_email'] = modele('modele_email')->get()->pluck('nom', 'id')->toArray();
        $valeurs['compte_email'] = modele('compte_email')->get()->pluck('adresse_email', 'id')->toArray();

        return $this->fonctionnalites($valeurs);
    }

    /*
     *
     * Retournes les fonctionnalités du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $maquettes_disponibles =

        $fonctionnalites = array(

            'Paramètres généraux' => array(

                array(

                    'nom' => "Utilisation de l'extranet",
                    'type' => "toggle",
                    'description' => "Active ou désactive l'utilisation de l'extranet",
                    'fonctionnalite' => "utiliser_extranet",
                    'valide' => true,
                ),
                array(

                    'nom' => "URL de l'extranet",
                    'type' => "input",
                    'description' => "Sous-domaine différent du sous-domaine principal, au format 'votre-sous-domaine-extranet.easydev.run'",
                    'placeholder' => "L'url de votre extranet",
                    'fonctionnalite' => "url_extranet",
                    'fonctionnalite_mere' => "utiliser_extranet",
                    'valide' => true,
                ),
                array(

                    'nom' => "Maquette de l'extranet",
                    'type' => "select",
                    'valeurs_select' => isset($valeurs['maquette']) ? $valeurs['maquette'] : array(),
                    'description' => "Maquette qui sera activé sur l'extranet",
                    'fonctionnalite' => "maquette_extranet",
                    'fonctionnalite_mere' => "utiliser_extranet",
                    'valide' => true,
                ),
            ),
            'Mail' => array(

                array(
                    'nom' => "Compte email extranet",
                    'type' => "select",
                    'description' => "",
                    'valeurs_select' => isset($valeurs['compte_email']) ? $valeurs['compte_email'] : array(),
                    'fonctionnalite' => "extranet_compte_email",
                    'valide' => true,
                    'valeur_vide' => true,
                ),

                array(
                    'nom' => "Modèle d'email d'inscription",
                    'type' => "select",
                    'description' => "",
                    'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
                    'fonctionnalite' => "extranet_modele_email_inscription",
                    'valide' => true,
                    'valeur_vide' => true,
                ),

                array(
                    'nom' => "Modèle d'email de mot de passe oublié",
                    'type' => "select",
                    'description' => "",
                    'valeurs_select' => isset($valeurs['modele_email']) ? $valeurs['modele_email'] : array(),
                    'fonctionnalite' => "extranet_modele_email_mdp_oublie",
                    'valide' => true,
                    'valeur_vide' => true,
                ),
            ),
            'Utilisateur' => array(

                array(

                    'nom' => "Renouvellement du mot de passe pour les utilisateurs",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "renouvellement_mot_de_passe_utilisateur_extranet",
                ),

                array(

                    'nom' => "Durée de validité en jours du mot de passe",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "duree_validite_mot_de_passe_utilisateur_extranet",
                    'fonctionnalite_mere' => "renouvellement_mot_de_passe_utilisateur_extranet"
                ),
            )
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Extranet';
    }

    public function obtenir_nom_module(){

        return 'Module extranet';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/functionnalities.png';
    }
}
