<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20250711_trigger_calcul_nb_jours_demande_conges implements Script{

    public function execute(){

        $trigger = modele('trigger_eden')->where('nom', 'Calcul du nombre de jours d\'une demande de congés')->first();

        $requete_avant = "UPDATE employe_demande_conge
                            SET nombre_jours = (SELECT COUNT(jours.id) - IF(periode_de_debut = periode_de_fin,0.5,IF(periode_de_debut > periode_de_fin,1,0))
                            FROM employe_demande_conge
                            JOIN (
                                WITH RECURSIVE jours AS (
                                SELECT employe_demande_conge.date_de_debut AS jour,employe_demande_conge.id,employe_demande_conge.date_de_fin
                                FROM employe_demande_conge
                                UNION ALL
                                SELECT jour + INTERVAL 1 DAY,id,date_de_fin
                                FROM jours 
                                WHERE jour + INTERVAL 1 DAY <= date_de_fin
                                )
                                    SELECT jour,jours.id
                                    FROM jours
                                    LEFT JOIN jour_indisponibilite ON jour BETWEEN jour_indisponibilite.date_debut AND jour_indisponibilite.date_fin
                                    WHERE WEEKDAY(jour) < 5 AND jour_indisponibilite.id IS NULL
                            ) as jours ON jours.id = employe_demande_conge.id
                            WHERE employe_demande_conge.id = #id_cible#
                            GROUP BY employe_demande_conge.id)
                            WHERE employe_demande_conge.id = #id_cible#;
            ";

        if($trigger !== null){

            if($trigger->requete != $requete_avant)
                return true;

            $management = management('trigger_eden',$trigger->id,$trigger);
        }
        else
            $management = management('trigger_eden');

        $table_demande_conge_id = Table_libre::where('type_element','employe_demande_conge')->value('id');

        $retour = $management->enregistre(array(
            'nom' => 'Calcul du nombre de jours d\'une demande de congés',
            'type_element_id' => $table_demande_conge_id,
            'type_element_concerne_id' => $table_demande_conge_id,
            'requete' => "UPDATE employe_demande_conge
                            SET nombre_jours = (SELECT COUNT(jours.id) - IF(periode_de_debut = periode_de_fin,0.5,IF(periode_de_debut > periode_de_fin,1,0))
                            FROM employe_demande_conge
                            JOIN (
                                WITH RECURSIVE jours AS (
                                SELECT employe_demande_conge.date_de_debut AS jour,employe_demande_conge.id,employe_demande_conge.date_de_fin
                                FROM employe_demande_conge
                                UNION ALL
                                SELECT jour + INTERVAL 1 DAY,id,date_de_fin
                                FROM jours 
                                WHERE jour + INTERVAL 1 DAY <= date_de_fin
                                )
                                    SELECT jour,jours.id
                                    FROM jours
                                    LEFT JOIN jour_indisponibilite ON jour BETWEEN jour_indisponibilite.date_debut AND jour_indisponibilite.date_fin AND coalesce(jour_indisponibilite.inactif,0) = 0
                                    WHERE WEEKDAY(jour) < 5 AND jour_indisponibilite.id IS NULL
                            ) as jours ON jours.id = employe_demande_conge.id
                            WHERE employe_demande_conge.id = #id_cible#
                            GROUP BY employe_demande_conge.id)
                            WHERE employe_demande_conge.id = #id_cible#;
            ",
            'requete_elements_concernes' => "SELECT * FROM employe_demande_conge WHERE id = #id_cible#",
        ));

        if($retour !== true)
            throw new \Exception($retour, 500);

        return true;
    }
}