<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20240530_gestion_export implements Script{

    public function execute()
    {
        if(Schema::hasColumn("export", "utilisateur_id"))
            DB::select('UPDATE export SET type_element_createur = "utilisateur", element_id_createur = utilisateur_id');

        return true;
    }
}
