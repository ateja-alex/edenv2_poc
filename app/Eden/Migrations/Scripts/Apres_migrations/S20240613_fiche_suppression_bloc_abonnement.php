<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20240613_fiche_suppression_bloc_abonnement implements Script{

    public function execute(){

        $fiches = ['article','client'];

        foreach($fiches as $fiche) {

            $fichier = storage_path('app/eden_fiche_'.$fiche.'.php');

            if(!is_file($fichier))
                continue;

            $contenu_tmp = file_get_contents($fichier);

            $contenu_tmp = str_replace('abonnements', 'fiche_'.$fiche.'_article_recurrent', $contenu_tmp);

            file_put_contents($fichier, $contenu_tmp);
        };

        return true;
    }
}
