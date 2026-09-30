<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20240201_reprise_donnees_fonctionnalite_suppression_facture implements Script {

    public function execute() {

        if(!fonctionnalite('annulation_facture_par_avoir'))
            Script_management::modifier_fonctionnalites(array('gescom_suppression_facture_valide' => 'empecher_suppression'));
        
        return true;
    }

}