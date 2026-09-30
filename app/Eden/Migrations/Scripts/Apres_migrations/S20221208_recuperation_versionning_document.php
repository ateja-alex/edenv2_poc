<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Managements\Script_management;

class S20221208_recuperation_versionning_document implements Script {

    public function execute() {

        $documents = \App\Eden\Variables::$documents_gescom;

        $nouvelles_valeurs = array();

        foreach($documents as $type_element) {

            if(fonctionnalite('versionning_document') == false)
                $nouvelles_valeurs[$type_element] = false;
            else
                $nouvelles_valeurs[$type_element] = true;
        }

        Script_management::modifier_fonctionnalites(array(

            'versionning_document' => $nouvelles_valeurs,
        ));

        return true;
    }
}

