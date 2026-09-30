<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Variables;

/**
 *
 * Filtres pour les dates mensuelles (choisir une période)
 *
 */
class Edition_ligne_budget {
    public static function applique($rapport) {
        $rubriques = modele('budget_rubrique')->get()->toArray();
        $rapport->option('edition_ligne_budget',['rubriques' => $rubriques , 'cats' => []]);
        if(!empty(request()->all())){
            self::edition($rapport);
        }
    }

    public static function edition($rapport) {
        foreach(request()->all() as $request=>$value){
            if(preg_match('/poste_\d+$/',$request) || preg_match('/rubrique_\d+$/',$request)){
                if($value !== null){
                    $value = explode('.',$value)[1];
                    $cats = $value;
                }
            }
        }
        $rubriques = modele('budget_rubrique')->get()->toArray();
        $rapport->option('edition_ligne_budget',['rubriques' => $rubriques , 'cats' => $cats]);
    }
}