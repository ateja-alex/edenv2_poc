<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20230220_changement_type_colonne_service_utilisateur implements Script
{

    public function execute()
    {

        $champ_service = Champ_libre::where('type_element', 'utilisateur')->where('nom_sql', 'service')->first();

        $champs_libres_maj_par_type_element['utilisateur'] = [$champ_service];

        Champ_libre_management::maj_champs_libres($champs_libres_maj_par_type_element, 'INT');

        return true;
    }
}
