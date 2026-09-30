<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20240329_type_entrepot_mouvement_de_stock implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type_element', 'mouvement_de_stock')
            ->where('nom_sql', 'entrepot_id')
            ->where('type',20)
            ->get();

        $nouvelles_informations = array(
            'liste_choix' => null,
            'type_element_ajax' => 'entrepot',
            'type' => '42',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}

