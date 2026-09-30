<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20230313_modification_champ_menus_extranet_contact implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('nom_sql', 'menus_extranet')
            ->where('type_element', 'contact')
            ->where(function($r){
                $r->where('type', '!=', 20)->orWhere('liste_choix', '!=', 510);
            })
            ->get();

        $nouvelles_informations = array(
            'type' => 20,
            'liste_choix' => 510,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, true);

        return true;
    }
}

