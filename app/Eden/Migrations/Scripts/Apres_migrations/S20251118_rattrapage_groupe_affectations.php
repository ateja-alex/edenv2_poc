<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20251118_rattrapage_groupe_affectations implements Script {

    public function execute(){

        $doublons_groupe_affectations = modele('tache')->avec_inactifs()
            ->select('groupe_affectations')
            ->whereNotNull('groupe_affectations')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->groupBy('groupe_affectations')
            ->get();
            
        foreach($doublons_groupe_affectations as $doublon){
        
            $taches_groupe_par_parent = modele('tache')->avec_inactifs()
                ->where('groupe_affectations', $doublon->groupe_affectations)
                ->get()
                ->groupBy('parent_id');

            foreach($taches_groupe_par_parent as $parent_id => $taches){

                // S'il ne reste plus qu'un seul groupe de tâche et que c'est une récurrence, on peut garder le groupe d'affectations actuel, on fait donc un continue
                if(!empty($parent_id) && $taches_groupe_par_parent->count() === 1)
                    continue;
                
                foreach($taches as $index => $tache){

                    // S'il n'y a qu'un seul groupe de tâche, que ce ne sont pas des tâches récurrentes, et qu'il ne reste qu'une seule tâche dans le groupe, 
                    // on peut garder le groupe d'affectations actuel, on fait donc un continue
                    if(empty($parent_id) && $taches_groupe_par_parent->count() === 1 && $taches_groupe_par_parent[$parent_id]->count() === 1)
                        continue;

                    $nouveau_groupe = modele('tache')
                        ->avec_inactifs()
                        ->select(DB::raw('COALESCE(MAX(groupe_affectations), 0) + 1 AS id_groupe'))
                        ->first()
                        ->id_groupe;

                    if(!empty($parent_id)){

                        DB::update('UPDATE tache SET groupe_affectations = ' . $nouveau_groupe . ' WHERE parent_id = ' . $parent_id . ' OR id = ' . $parent_id);
                        unset($taches_groupe_par_parent[$parent_id]);
                        continue 2;
                    }

                    management('tache', $tache->id, $tache)->enregistre_modele(['groupe_affectations' => $nouveau_groupe]);
                    unset($taches_groupe_par_parent[$parent_id][$index]);
                }
            }
        }

        return true;
    }
}