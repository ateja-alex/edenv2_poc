<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use App\Eden\Variables;
use Illuminate\Support\Facades\DB;

class S20250331_changement_type_conditionnement_lignes implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'conditionnement')->whereIn('type_element', Variables::$documents_gescom_lignes)->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'conditionnement',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, true);

        return true;
    }
}

