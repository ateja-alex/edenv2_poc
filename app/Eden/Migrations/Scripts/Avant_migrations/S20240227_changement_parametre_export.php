<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use Illuminate\Support\Facades\DB;

class S20240227_changement_parametre_export implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'parametres')->where('type_element', 'export')->get();

        $nouvelles_informations = array(
            'type' => 6,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, true);

        return true;
    }
}

