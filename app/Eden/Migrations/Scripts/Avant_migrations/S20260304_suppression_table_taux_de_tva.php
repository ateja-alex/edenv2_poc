<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

class S20260304_suppression_table_taux_de_tva implements Script {

    public function execute() {
        
        Script_management::supprimer_type_element('taux_de_tva');
        Script_management::supprimer_type_element('commentaire_fiche');
        
        return true;
    }
}
