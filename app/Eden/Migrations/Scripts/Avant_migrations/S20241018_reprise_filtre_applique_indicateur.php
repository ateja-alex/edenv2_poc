<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20241018_reprise_filtre_applique_indicateur implements Script {

    public function execute() {

        $rapports_libre = Rapport_libre::whereNotNull('parametrage_rapport_libre')->get();

        foreach ($rapports_libre as $rapport) {
            $rapport_modifie = false;
            $parametrage_rapport_libre = [];

            if(!empty($rapport->parametrage_rapport_libre) && $rapport->parametrage_rapport_libre != "null")
                $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre);

            if(empty($parametrage_rapport_libre))
                continue;

            foreach ($parametrage_rapport_libre as $colonne => $valeur) {

                if(strpos($colonne, "filtre_applique_") === false)
                    continue;

                if(strpos($colonne, "filtre_applique_" . $rapport->type_element . "_") !== false)
                    continue;

                $nom_sql = str_replace(["filtre_applique_", $rapport->type_element. '_'], "", $colonne);
                $parametrage_rapport_libre->{"filtre_applique_" . $rapport->type_element. '_' . $nom_sql} = $valeur;
                unset($parametrage_rapport_libre->{$colonne});

                $rapport_modifie = true;
            }

            $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);

            if($rapport_modifie)
                $rapport->save();

        }

        return true;
    }
}

