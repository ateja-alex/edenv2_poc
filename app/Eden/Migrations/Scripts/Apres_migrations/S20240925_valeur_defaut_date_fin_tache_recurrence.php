<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class S20240925_valeur_defaut_date_fin_tache_recurrence implements Script
{

    public function execute()
    {

        $champ = Champ_libre::where('type_element', 'tache_recurrence')->where('nom_sql', 'date_de_fin')->get();
        $nouvelles_informations = ['valeur_defaut' => null];

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champ);

        return true;
    }
}