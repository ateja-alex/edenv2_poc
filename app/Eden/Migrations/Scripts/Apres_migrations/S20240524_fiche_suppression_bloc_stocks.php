<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20240524_fiche_suppression_bloc_stocks implements Script{

    public function execute(){

        $fichier = storage_path('app/eden_fiche_article.php');

        if(!is_file($fichier))
            return true;

        $contenu_tmp = file_get_contents($fichier);

        $contenu_tmp = str_replace('gestion_des_stocks','fiche_article_stocks',$contenu_tmp);

        file_put_contents($fichier,$contenu_tmp);

        return true;
    }
}
