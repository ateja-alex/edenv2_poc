<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20240617_refonte_bloc_echeances implements Script{

    public function execute(){

        $fichier = storage_path('app/eden_fiche_facture_vente.php');

        if(!is_file($fichier))
            return true;

        $contenu_tmp = file_get_contents($fichier);

        $contenu_tmp = str_replace('echeances', 'fiche_facture_vente_echeance', $contenu_tmp);

        file_put_contents($fichier, $contenu_tmp);

        return true;
    }
}
