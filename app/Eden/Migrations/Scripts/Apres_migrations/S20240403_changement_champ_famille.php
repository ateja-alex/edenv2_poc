<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20240403_changement_champ_famille implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type_element', 'article')->where('nom_sql', 'famille_id')->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'famille',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}

