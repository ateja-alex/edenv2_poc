<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20250813_date_modification_mot_de_passe_utilisateur implements Script{

    public function execute(){
        // Mise à jour de la colonne pour les utilisateurs existants
        DB::select('UPDATE utilisateur SET date_derniere_modification_mot_de_passe = NOW() WHERE mot_de_passe IS NOT NULL;');
    
        return true;
    }
}