<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use DB;

class S20220503_changement_champ_valeur_parametres implements Script
{

    public function execute()
    {

        DB::select('ALTER TABLE `eden_parametres` MODIFY COLUMN `valeur` LONGTEXT NULL;');

        return true;
    }
}
