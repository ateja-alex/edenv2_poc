<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Migrations\Scripts\Script;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class S20240703_rattrapage_champ_pourcentage implements Script
{

    public function execute()
    {
        $liste_column_change_type = [];
        $champ_lignes = DB::table("eden_tableslibres")->join('eden_champslibres', 'eden_champslibres.type_element', '=', 'eden_tableslibres.type_element')
            ->where(function (Builder $query) {
                $query->whereNull("eden_tableslibres.vue_sql")->orWhere("eden_tableslibres.vue_sql", "0");
            })
            ->where('eden_champslibres.type', 17)->get();

        if($champ_lignes->count() == 0)
          return true;

        foreach ($champ_lignes as $champ_libre) {
            $liste_column_change_type[$champ_libre->type][$champ_libre->type_element][] = (array)$champ_libre;
        }

        try {
            Champ_libre_management::maj_champs_libres($liste_column_change_type[17], 'decimal(14,8)');
        } catch (\Exception $e) {
            return new \Exception("Une erreur c'est produite lors de la mise a jour de la convertion de double => decimal" + $e->getMessage());
        }
        return true;
    }
}
