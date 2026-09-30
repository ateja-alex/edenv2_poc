<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Crm_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {
        $types_modele = ['modele_email'];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        // On récupère les champs des tâches qui pourront être repris lors de la création de récurrence
        $valeurs['champs_tache'] = champs_libres('tache')
            ->whereNotIn('nom_sql', ['id', 'inactif', 'modifie_le', 'modifie_par', 'cree_le', 'cree_par', 'cle_externe', 'date_de_debut',
                'date_de_fin', 'id_microsoft', 'id_google'])
            ->pluck('nom', 'nom_sql')
            ->toArray();

        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $documents_gescom_tmp = \App\Eden\Variables::$documents_gescom;
        $documents_gescom = [];
        $documents_gescom_achat = [];
        $documents_gescom_vente = [];

        foreach ($documents_gescom_tmp as $type_element){

            $type_element_modifie = ucfirst(str_replace('_', ' ', $type_element));
            $documents_gescom[$type_element] = $type_element_modifie;

            if(strpos($type_element, '_achat') !== false)
                $documents_gescom_achat[$type_element] = $type_element_modifie;
            else
                $documents_gescom_vente[$type_element] = $type_element_modifie;
        }

        $fonctionnalites = array(

            'Intranet' => array(
                array(

                    'nom' => "Activer l'intranet",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "intranet",
                ),
            ),
            'Alimentation de la timeline' => array(

                array(

                    'nom' => "Information traitement de la timeline sur les fiches clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "timeline_info_traitement",
                ),
                array(

                    'nom' => "Création d'un projet",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "alimentation_timeline_creation_projet",
                ),

                array(

                    'nom' => "Création d'un ticket client",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "alimentation_timeline_creation_ticket_client",
                ),
                array(
                    
                    'nom' => "Création d'une tâche",
                    'type' => "select",
                    'valeurs_select' => array(
                        'tache_todo' => 'Tache todo',
                        'tache_rdv' => "Tache rdv",
                        'les_2_taches' => "Les 2",
                        'desactive' => "Désactivé",
                    ),
                    'fonctionnalite' => "alimentation_timeline_creation_tache",
                ),
                array(

                    'nom' => "Création d'un document de vente pour un client",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $documents_gescom_vente,
                    'fonctionnalite' => "alimentation_timeline_document_vente",
                ),
                array(

                    'nom' => "Création d'un document d'achat pour un fournisseur",
                    'type' => "badge_multiselection",
                    'valeurs_badges' => $documents_gescom_achat,
                    'fonctionnalite' => "alimentation_timeline_document_achat",
                ),
                array(

                    'nom' => "Enregistrer systématiquement un échange pour chaque mail",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "enregistrer_systematique_email_en_echange",
                ),
            ),
            'Prospection' => array(

                array(

                    'nom' => "Campagne de prospection via interface",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "utiliser_campagne_prospection",
                ),

                array(

                    'nom' => "Prospection sur les fiches clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "fiche_client_prospection",
                ),
            ),
            'Utilisateur' => array(

                array(

                    'nom' => "Renouvellement du mot de passe pour les utilisateurs",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "renouvellement_mot_de_passe_utilisateur",
                ),

                array(

                    'nom' => "Durée de validité en jours du mot de passe",
                    'type' => "input",
                    'description' => "",
                    'fonctionnalite' => "duree_validite_mot_de_passe_utilisateur",
                    'fonctionnalite_mere' => "renouvellement_mot_de_passe_utilisateur"
                ),
            )
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'CRM';
    }

    public function obtenir_nom_module(){

        return 'Module CRM';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/icone_crm.png';
    }
}
