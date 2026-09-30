<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20231221_changement_type_suivi_recette_easydev implements Script {

    public function execute() {

        $champs_libres = Champ_libre::where('type_element', 'suivi_recette_easydev')->where('liste_choix', 67)->get();
        
        $nouvelles_informations = array(
            'liste_choix' => 145,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);
        
        return true;
    }
}

