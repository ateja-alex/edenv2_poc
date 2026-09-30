<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

use App\Eden\Models\Champ_libre;

class S20230327_changement_liste_choix_public_email_recus implements Script
{

    public function execute(){

        $champs_libres = Champ_libre::where('nom_sql', 'public')->where('type_element','email_recus')->get();

        $nouvelles_informations = array(
            'liste_choix' => 14,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs_libres);

        return true;
    }
}
