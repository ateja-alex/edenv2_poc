<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20230727_ajout_colonne_disponibilite_sur_documents implements Script
{

    public function execute()
    {

        $fonctionnalite = fonctionnalite('ajout_colonne_disponibilite_sur_documents');

        // Si la fonctionnalité est un booléen, on va la transformer en tableau de booléen
        if(is_bool($fonctionnalite)) {

            $modifications = [];
            $documents_gescom = \App\Eden\Variables::$documents_gescom;
            foreach($documents_gescom as $document) {
                $modifications[$document] = $fonctionnalite;
            }

            Script_management::modifier_fonctionnalites(['ajout_colonne_disponibilite_sur_documents' => $modifications]);
        }
        
        return true;
    }
}
