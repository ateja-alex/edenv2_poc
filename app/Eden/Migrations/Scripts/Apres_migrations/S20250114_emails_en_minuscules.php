<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20250114_emails_en_minuscules implements Script{

    public function execute(){

        $champs_email = Champ_libre::where('type', 0)->where('format_champ', 'email')->get();

        foreach($champs_email as $champ){

            if(Schema::hasTable($champ->type_element) && Schema::hasColumn($champ->type_element, $champ->nom_sql))
                DB::update('UPDATE ' . $champ->type_element . ' SET ' . $champ->nom_sql . ' = LOWER(' . $champ->nom_sql . ')');
        }

        return true;
    }

}