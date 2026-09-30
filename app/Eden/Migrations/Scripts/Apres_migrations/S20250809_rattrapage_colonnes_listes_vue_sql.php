<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Eden\Models\Colonne;

class S20250809_rattrapage_colonnes_listes_vue_sql implements Script {
    public function execute(){

        $colonnes_listes = Colonne::select('listes_libres_colonnes.*', 'eden_listeslibres.type_element', 'eden_listeslibres.id_rapport', 'traduction_index.index')
            ->leftJoin('traduction_index', DB::raw("CONCAT(listes_libres_colonnes.index_traduction, '.nom')"), 'traduction_index.index')
            ->join('eden_listeslibres', 'listes_libres_colonnes.liste_libre_id', 'eden_listeslibres.id')
            ->whereIn('listes_libres_colonnes.liste_libre_id', function($sous_requete){
                $sous_requete->select('eden_listeslibres.id')
                    ->from('eden_listeslibres')
                    ->join('vue_sql', 'eden_listeslibres.type_element', 'vue_sql.nom_sql');
            })
            ->whereNull('traduction_index.index')
            ->get();

        $champs_vue_sql_par_type = Champ_libre::select('eden_champslibres.nom_sql', 'eden_champslibres.nom', 'eden_champslibres.type_element')
            ->join('vue_sql', 'eden_champslibres.type_element', 'vue_sql.nom_sql')
            ->get()
            ->groupBy('type_element');

        foreach($champs_vue_sql_par_type as $type_element => $champs_vue_sql)
            $champs_vue_sql_par_type[$type_element] = $champs_vue_sql->keyBy('nom_sql');
        
        $service_traduction = service('traduction');
        
        foreach($colonnes_listes as $colonne) {

            if(!empty($colonne->index) || $colonne->nom === '#')
                continue;

            $nom_champ = !empty($colonne->methode) ? 
                $colonne->methode : (!empty($colonne->champ) ? 
                    $colonne->champ : str_replace('#', '', $colonne->valeur));
            
            $base_index_traduction = [
                !empty($colonne->id_rapport) ? 'rapport' : 'liste',
                !empty($colonne->id_rapport) ? $colonne->id_rapport : $colonne->type_element,
                'colonne',
                $nom_champ
            ];

            $colonne->nom = $champs_vue_sql[$colonne->type_element][$nom_champ]->nom ?? str_replace('_', ' ', ucfirst($nom_champ));

            $colonne->index_traduction = $service_traduction->calcul_index_traduction(5, $base_index_traduction, ['nom' => $colonne->nom]);

            $colonne->save();           
        }
        
        return true;
    }
}