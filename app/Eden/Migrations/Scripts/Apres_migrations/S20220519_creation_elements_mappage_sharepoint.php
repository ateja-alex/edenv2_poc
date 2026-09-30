<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220519_creation_elements_mappage_sharepoint implements Script {

    public function execute() {

        $types_elements_a_creer = ['client', 'fournisseur'];

        foreach($types_elements_a_creer as $type_element){

            $verification_existence_element = modele('parametrage_mappage_sharepoint')->where('type_element',  $type_element)->count();

            if($verification_existence_element > 0)
                continue;
            
            $nom_element = table_libre($type_element)->nom_table;

            $element_a_creer = [
                'type_element' => $type_element,
                'nom_dossier' => $nom_element,
            ];

            management('parametrage_mappage_sharepoint')->enregistre($element_a_creer);
        }
        return true;
    }
}
