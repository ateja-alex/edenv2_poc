<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class S20250808_reprise_nom_vers_nom_sql_vue_sql implements Script
{
    public function execute(){

        if(Schema::hasColumns('vue_sql', ['nom', 'nom_sql']))
            DB::update('UPDATE vue_sql SET nom_sql = nom');

        return true;
    }
}