<?php


namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Variables;
use File;
use Illuminate\Support\Facades\DB;

class S20240228_suppresion_format_champ_15_min implements Script{

    public function execute(){

        $champs_libres = Champ_libre::where('format_champ', '15min')->get();

        $nouvelles_informations = array(
            'format_champ' => "",
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        return true;
    }
}