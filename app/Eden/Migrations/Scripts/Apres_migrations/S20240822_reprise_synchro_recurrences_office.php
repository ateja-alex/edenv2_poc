<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use Illuminate\Support\Facades\DB;
use App\Eden\Migrations\Scripts\Script;
class S20240822_reprise_synchro_recurrences_office implements Script {

    public function execute() {

        if(!fonctionnalite('microsoft_utiliser_connexion'))
            return true;

        $recurrences_microsoft = modele('tache')->where('tache_parent', '!=', 1)->whereNotNull('tache_parent')->get()->groupBy('tache_parent');

        $recurrences_eden = modele('tache')->where('tache_parent', 1)->get();
        $occurrences_eden = modele('tache')->whereNotNull('parent_id')->get()->groupBy('parent_id')->toArray();
        $recurrences = modele('tache_recurrence')->get()->keyBy('tache_parent_id')->toArray();

        $management_tache = management('tache');

        foreach($recurrences_microsoft as $id_parent => $taches) {

            foreach($taches as $tache) {

                $affectation = $tache->affectation;
                $management_tache->supprime($tache);
                DB::delete("DELETE FROM cron_parametres where id_utilisateur = ".$affectation." and nom in ('date_initialisation_delta_office', 'date_reinitialisation_delta_office', 'delta_token_office')");
            }
        }

        foreach($recurrences_eden as $tache){

            $occurrences = $occurrences_eden[$tache->id];
            $recurrence = $recurrences[$tache->id];

            foreach($occurrences as $occurrence)
                service('microsoft_calendrier')->supprimer_evenement($occurrence->id_microsoft, $occurrence->affectation);

            $modifications = $tache->toArray();
            $modifications['infos_recurrence'] = $recurrence->toArray();

            service('recurrence')->creer_recurrence_sur_calendrier_microsoft($modifications, $tache);
        }
    }
}