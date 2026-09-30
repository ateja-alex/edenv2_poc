<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20240820_changement_filtre_serie implements Script {

    public function execute() {

        $rapports_libre = Rapport_libre::whereNotNull('parametrage_rapport_libre')->get();

        foreach ($rapports_libre as $rapport) {
            $rapport_modifie = false;
            $parametrage_rapport_libre = [];

            if(!empty($rapport->parametrage_rapport_libre) && $rapport->parametrage_rapport_libre != "null")
                $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre);

            if(empty($parametrage_rapport_libre))
                continue;

            if(!empty($parametrage_rapport_libre->series)) {
                foreach ($parametrage_rapport_libre->series as $index_serie => $serie) {
                    $rapport_modifie = true;
                    $nouvelle_serie = [];

                    if(!is_iterable($serie))
                        continue;

                    foreach ($serie as $index => $valeur) {
                        if (strpos($index, "filtre_applique_") !== false) {
                            $nom_sql = str_replace("filtre_applique_", "", $index);
                            $nouvelle_serie[$rapport->type_element. '_' . $nom_sql] = $valeur;
                        }
                        else
                            $nouvelle_serie[$index] = $valeur;
                    }

                    if(!is_array($parametrage_rapport_libre->series))
                        $parametrage_rapport_libre->series->$index_serie = $nouvelle_serie;
                    else
                        $parametrage_rapport_libre->series[$index_serie] = $nouvelle_serie;
                }
            }

            $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);

            if($rapport_modifie)
                $rapport->save();

        }

        return true;
    }
}

