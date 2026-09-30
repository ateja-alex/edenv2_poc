<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20230616_vider_champs_dropzones implements Script {

    public function execute() {

        //On recherche les types d'élément contenant des champs de type dropzone
        $types_element = Champ_libre::where('type', 15)->groupBy('type_element')->get()->pluck('nom_sql', 'type_element');

        foreach($types_element as $type_element => $nom_sql_champ){

            $nombre_a_faire = 100000;

            $nombre_elements = modele($type_element)
                ->avec_inactifs()->where($nom_sql_champ,"{}")->count();

            $nombre_de_boucle = ceil($nombre_elements / $nombre_a_faire);

            $management_element = management($type_element);

            for($i = 0;$i < $nombre_de_boucle;$i++){

                $nombre_a_eviter = $i * $nombre_a_faire;

                $elements = modele($type_element)->avec_inactifs()->where($nom_sql_champ,"{}")->skip($nombre_a_eviter)->take($nombre_a_faire);
                $elements = modele($type_element)->hydrate(select($elements));

                foreach($elements as $element) {

                    $management_element->modele = $element;
                    $management_element->enregistre_modele([$nom_sql_champ => null]);
                }
            }
        }

        return true;
    }
}

