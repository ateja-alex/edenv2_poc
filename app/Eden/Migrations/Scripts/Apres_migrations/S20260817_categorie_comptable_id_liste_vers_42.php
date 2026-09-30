<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;
use App\Eden\Models\Champ_libre;

class S20260817_categorie_comptable_id_liste_vers_42 implements Script {

    public function execute(){

        $champs_libres = Champ_libre::where('liste_choix', 114)->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'categorie_comptable',
            'liste_choix' => 0,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}