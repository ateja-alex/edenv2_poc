<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Champ_libre_management;

use DB;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20221221_relance_script_cacher_acces_au_support_utilisateur implements Script
{

    public function execute()
    {


        $classe_a_instancier = "\\App\\Eden\\Migrations\\Scripts\\Apres_migrations\\S20220602_changement_type_cacher_acces_support_utilisateur";

        $classe_instanciee = new $classe_a_instancier();

        // Je relance un ancien script  qui fait déjà en partie ce que le ticket demande
        return $classe_instanciee->execute();
    }
}
