<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Controllers\Maintenance_controller;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Parametre;

class S20230222_reprise_parametres_cron implements Script
{

    public function execute() {

        $parametres = [
            'optimisation_tables' => 'execute_requete_sql_cron',
            'date_de_derniere_execution_mise_a_jour_index_recherche_element' => 'mise_a_jour_index_recherche_element',
            'date_initialisation_delta_office' => 'synchroniser_rdv_microsoft',
            'delta_token_office' => 'synchroniser_rdv_microsoft',
        ];

        foreach($parametres as $nom => $methode){

            $parametres_existants = Parametre::where('nom', $nom)->get();
            $cron = modele('cron')->where('nom', $methode)->first();

            if(empty($parametres_existants) || empty($cron))
                continue;

            foreach($parametres_existants as $parametre_existant){

                if(isset($parametre_existant->id_utilisateur, $parametre_existant->id_entite))
                    parametre_cron($nom, $cron->id, $parametre_existant->valeur, $parametre_existant->id_utilisateur, $parametre_existant->id_entite);
                elseif (isset($parametre_existant->id_utilisateur))
                    parametre_cron($nom, $cron->id, $parametre_existant->valeur, $parametre_existant->id_utilisateur);
                else
                    parametre_cron($nom, $cron->id, $parametre_existant->valeur);
            }
        }

        return true;
    }
}
