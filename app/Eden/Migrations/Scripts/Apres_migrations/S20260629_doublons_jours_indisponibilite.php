<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use DB;

class S20260629_doublons_jours_indisponibilite implements Script{

    public function execute(){

        $jours = DB::table('jour_indisponibilite')->whereNotNull('cle_externe')->get();

        $vus = [];
        $ids_a_supprimer = [];

        foreach ($jours as $jour) {
            if (isset($vus[$jour->cle_externe])) {
                $ids_a_supprimer[] = $jour->id;
            } else {
                $vus[$jour->cle_externe] = $jour->id;
            }
        }

        if (!empty($ids_a_supprimer)) {
            DB::table('jour_indisponibilite')->whereIn('id', $ids_a_supprimer)->delete();
        }

        DB::statement('ALTER TABLE jour_indisponibilite ADD UNIQUE KEY uk_cle_externe (cle_externe)');

        return true;
    }
}