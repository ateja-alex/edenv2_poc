<?php

namespace App\Eden\Controllers\Fiches;
use App\Eden\Controllers\Fiche_controller;
use Illuminate\Support\Facades\DB;

class Requete_sql_cron_controller extends Fiche_controller
{

    /**
     *
     * Retourne la liste des questions
     *
     */
    public function lancer_requete($type_element,$id_element){
        $requete = modele($type_element,$id_element);

        try{
            //Exécute la requête
            $debut_execution = microtime(true);
            DB::select($requete->requete);
            
            management('requete_sql_cron', $requete->id)->enregistre_modele([
                'duree_derniere_execution' => microtime(true)-$debut_execution,
            ]);
        }
        catch(\Exception $e){
            return response()->json(array('retour' => $e->getMessage()));
        }

        return response()->json(array('retour' => true));
    }
}