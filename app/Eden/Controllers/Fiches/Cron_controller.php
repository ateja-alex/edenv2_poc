<?php

namespace App\Eden\Controllers\Fiches;

use App\Eden\Controllers\Fiche_controller;
use App\Eden\Controllers\Cron_controller as Cron_controller_eden;
use App\Http\Controllers\Cron_controller as Cron_controller_spe;
use DB;
use Illuminate\Support\Facades\Log;

class Cron_controller extends Fiche_controller {

    public function executer_cron($type_element, $id_element){

        $utilisateur_connecte = session()->get('utilisateur_eden');

        $cron = modele('cron')->select('nom', 'standard')->where('id', $id_element)->first();
        $nom_cron = $cron->nom;
        try{

            if($cron->standard)
                $controller = new Cron_controller_eden($nom_cron);
            else
                $controller = new Cron_controller_spe($nom_cron);

            $controller->$nom_cron();
        } catch(\Exception | \Throwable $e){

            unset($controller);

            session()->put('utilisateur_eden', $utilisateur_connecte);
            Log::error("[Exécution manuelle cron] L'exécution du cron " . $nom_cron . " a échouée : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());

            return response()->json([
                'succes' =>false,
                'message'=> traduction('messages.php.cron.erreur_execution', null, [
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ])
            ]);
        }

        unset($controller);

        session()->put('utilisateur_eden', $utilisateur_connecte);

        return response()->json(['succes' => true]);
    }

    /**
     * Supprime les paramètres liés au cron correspondant à l'id passé en paramètre
     * @param mixed $type_element
     * @param mixed $id_element
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function supprimer_parametres_lies($type_element, $id_element){

        $cron_parametres = modele('cron_parametres')->where('id_cron', $id_element)->get();

        foreach($cron_parametres as $cron_parametre){

            management('cron_parametres', $cron_parametre->id, $cron_parametre)->supprime();
        }

        return response()->json(['succes'=> true]);
    }
}
