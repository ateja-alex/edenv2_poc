<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Projets_v2_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {
        $types_modele = [];
        $valeurs = [];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

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

                    'nom' => "Gestion de projet",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "gestion_de_projet",
                ),

                array(

                    'nom' => "Lier les questionnaires de satisfaction aux projets",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "lier_questionnaires_satisfactions_projet",
                ),

                array(

                    'nom' => "Participants sur les projets",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "projet_participants",
                ),

                array(

                    'nom' => "Projets sur les fiches clients",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "projets",
                ),

                array(

                    'nom' => "Le bloc projet reprend uniquement les projets du client",
                    'type' => "toggle",
                    'description' => "Activez cette fonctionnalité si vous utilisez les projets et que les projets sont liés aux clients. Sur la saisie des documents, si cette option est activée, l'ERP vous proposera les projets liés au client + le champ de recherche de projet. Si l'option est désactivée, vous aurez alors uniquement le champ de recherche de projet.",
                    'fonctionnalite' => "gescom_document_onglet_projet_uniquement_projet_client",
                    'valide' => true,
                ),
				
				/*
                array(

                    'nom' => "Nombre de semaine sur le planning",
                    'type' => "select",
                    'valeurs_select' => array(

                        '1' => '1 Semaine',
                        '2' => '2 Semaine',
                    ),
                    'description' => "",
                    'fonctionnalite' => "planning_nombre_semaines",
                ),
				*/
            ),
            'Calcul de la marge par projet' => array(

                array(

                    'nom' => "A utiliser pour le calcul des marges",
                    'type' => "select",
                    'valeurs_select' => array(

                        'devis_vente' => 'Devis',
                        'facture_vente' => 'Facture',
                        'commande_vente' => 'Commande',
                    ),
                    'description' => "",
                    'fonctionnalite' => "calcul_marges_type_element",
                ),

                array(

                    'nom' => "Utiliser les factures d'achat",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "calcul_marges_facture_achat",
                ),

                array(

                    'nom' => "Utiliser les BL vente pour la consommation",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "calcul_marges_achats_via_bl_vente",
                ),

                array(

                    'nom' => "Utiliser les achats saisis sur les documents",
                    'type' => "toggle",
                    'description' => "",
                    'fonctionnalite' => "calcul_marges_achats_saisis_sur_documents",
                ),
            ),
        );

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Projets';
    }

    public function obtenir_nom_module(){

        return 'Module de projet';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/project.png';
    }
}