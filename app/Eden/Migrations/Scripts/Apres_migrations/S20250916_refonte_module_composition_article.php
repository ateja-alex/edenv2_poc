<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20250916_refonte_module_composition_article implements Script{

    public function execute(){

        $fichier = storage_path('app/eden_fiche_article.php');

        if(!is_file($fichier))
            return true;

        $contenu_tmp = file_get_contents($fichier);

        $contenu_tmp = str_replace('composition_des_articles','article_composants',$contenu_tmp);

        file_put_contents($fichier,$contenu_tmp);

        return true;
    }
}