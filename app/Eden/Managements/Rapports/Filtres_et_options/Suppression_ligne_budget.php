<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Variables;

/**
 *
 * Filtres pour les dates mensuelles (choisir une période)
 *
 */
class Suppression_ligne_budget {
    public static function applique($rapport) {

        $rapport->option('suppression_ligne_budget');

        if(!empty(request()->all())){

            self::suppression($rapport);

        }

    }

    public static function suppression($rapport) {

        if(request()->budget_suppression_de_postes == 1){

            foreach(request()->all() as $request=>$value){

                if(preg_match('/poste_\d+$/',$request)){

                    if($value !== null){

                        $value = explode('.',$value)[1];
                        $retour = management('budget_poste',$value)->supprime();

                    }

                }
                else if(preg_match('/rubrique_\d+$/',$request)){

                    if($value !== null){

                        $value = explode('.',$value)[1];
                        $postes = modele('budget_poste')->select('id')->where('rubrique_id',$value)->get()->toArray();

                        foreach($postes as $poste){

                            management('budget_poste',$poste['id'])->supprime();

                        }

                        $retour = management('budget_rubrique',$value)->supprime();

                    }
                }

                $familles = modele('famille')->get()->toArray();
                $articles = modele('article')->get()->toArray();
                $rubriques = modele('budget_rubrique')->get()->toArray();
                
                $rapport->option('ajouter_ligne_budget',['rubriques' => $rubriques , 'familles' => $familles , 'articles' => $articles]);
            }
        }
    }
}