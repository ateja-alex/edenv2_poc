<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Controllers\Maintenance_controller;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20240507_changement_champ_utilisateur implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type', 20)
            ->where('liste_choix',1)
            ->get();

        $nouvelles_informations = array(
            'liste_choix' => null,
            'type_element_ajax' => 'utilisateur',
            'type' => '42',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        $champs_libres = Champ_libre::where('type', 11)
            ->where('liste_choix',1)
            ->get();

        $nouvelles_informations = array(
            'liste_choix' => null,
            'type_element_ajax' => 'utilisateur',
            'type' => '10',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        $controller = new Maintenance_controller();
        $controller->suppresion_filtres_utilisateurs_rapports();

        return true;
    }
}
