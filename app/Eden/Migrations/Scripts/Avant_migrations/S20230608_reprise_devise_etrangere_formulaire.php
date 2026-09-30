<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20230608_reprise_devise_etrangere_formulaire implements Script {

    public function execute() {

        if(is_dir(app_path('Migrations/Formulaires_libres'))) {

            $repertoire = scandir(app_path('Migrations/Formulaires_libres'));

            foreach ($repertoire as $fichier) {

                if ($fichier == '.' || $fichier == '..')
                    continue;

                $contenu_tmp = file_get_contents(app_path('Migrations/Formulaires_libres/' . $fichier));

                $contenu_tmp = str_replace('utilisation_devise_etrangere','$root.utilisation_devise_etrangere',$contenu_tmp);

                $chemin_avec_nom_document = app_path('Migrations/Formulaires_libres/' . $fichier);

                if (file_exists($chemin_avec_nom_document) == true)
	    	        unlink($chemin_avec_nom_document);

                $fichier = fopen($chemin_avec_nom_document, "x+");
                fputs($fichier, $contenu_tmp );
                fclose($fichier);
            }
        }

        return true;
    }
}
