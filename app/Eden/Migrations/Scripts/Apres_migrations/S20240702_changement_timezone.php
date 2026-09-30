<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20240702_changement_timezone implements Script{

    public function execute(){

        $fichier = config_path('app.php');

        if(!is_file($fichier))
            return true;

        $contenu_tmp = file_get_contents($fichier);

        $contenu_tmp = str_replace("'timezone' => 'UTC'", "'timezone' => 'Europe/Paris'", $contenu_tmp);

        file_put_contents($fichier, $contenu_tmp);

        return true;
    }
}