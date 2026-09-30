<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Schema;

class S20240424_suppression_index_recherche implements Script{

    public function execute()
    {
        Schema::dropIfExists("index_recherche");
        
        return true;
    }
}
