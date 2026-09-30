<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use DB;

class S20251006_rattrapage_chaine_affichage_kanban implements Script {

    public function execute(){

        $types_kanban = Rapport_libre::select('type_element')
            ->whereNotNull('kanban')
            ->where(DB::raw('COALESCE(inactif,0)'), 0)
            ->get()
            ->pluck('type_element')
            ->toArray();

        $tables_libres = Table_libre::whereIn('type_element', $types_kanban)
            ->get()
            ->keyBy('type_element');
        foreach($types_kanban as $type_kanban) {

            $table_libre = $tables_libres[$type_kanban];

            if(!empty($table_libre->affichage_dans_kanban))
                continue;

            $table_libre->affichage_dans_kanban = $table_libre->affichage_dans_liste;
            $table_libre->save();

            Table_libre_management::generer_fichier_migration($type_kanban, true);
        }

        return true;
    }
}