<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20240531_modification_id_ligne_pivot implements Script {

    public function execute(){

        $tables_pivots = Champ_libre::whereNotNull('table_pivot')
            ->where('table_pivot','!=','')
            ->get()->pluck('table_pivot')->toArray();

        foreach($tables_pivots as $table_pivot){

            if(!Schema::hasTable($table_pivot))
                continue;

            if(!Schema::hasColumn($table_pivot, 'id') && Schema::hasColumn($table_pivot, 'id_ligne_pivot'))
                DB::select('ALTER TABLE '.$table_pivot.' RENAME COLUMN id_ligne_pivot TO id;');
        }

        return true;
    }
}