<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20250716_creation_dossier_exports implements Script
{
    public function execute(){

        $chemin_dossier = storage_path('app/public/exports');
        $chemin_public = storage_path('app/public');

        if(!is_dir(storage_path('app/public/exports')))
            mkdir($chemin_dossier, 0775);

        $contenu_public = scandir($chemin_public);
        
        foreach($contenu_public as $fichier){

            $chemin_fichier = $chemin_public . '/' . $fichier;

            if(is_dir($chemin_fichier))
                continue;

            if(strpos($fichier, 'export_') !== false)
                rename($chemin_fichier, $chemin_dossier . '/' . $fichier);
        }

        return true;
    }
}