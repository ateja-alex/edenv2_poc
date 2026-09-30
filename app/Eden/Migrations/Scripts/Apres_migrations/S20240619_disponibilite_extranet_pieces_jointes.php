<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20240619_disponibilite_extranet_pieces_jointes implements Script {

    public function execute(){

        if(Schema::hasTable('element_piece_jointe') && Schema::hasColumn('element_piece_jointe', 'disponible_extranet') && Schema::hasColumn('element_piece_jointe', 'type_element_createur'))
            DB::update('UPDATE element_piece_jointe SET disponible_extranet = 1 where type_element_createur = "' . fonctionnalite('connexion_a_extranet_type_element') . '"');

        return true;
    }
}