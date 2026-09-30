<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Migrations\Scripts\Apres_migrations\S20221125_remplacement_vue_sous_formulaire;
class S20230306_remplacement_vue_sous_formulaire_2 implements Script{

    public function execute(){
        
        $script = new S20221125_remplacement_vue_sous_formulaire();
        return $script->execute();
        
    }
    
}