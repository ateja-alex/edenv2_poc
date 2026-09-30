<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

use App\Eden\Managements\Script_management;

class S20230130_suppression_flux_notification implements Script {

    public function execute() {

        if(!table_libre_existe('notification_flux_utilisateur')){
            return true;
        }

        $notification_flux_utilisateur = modele('notification_flux_utilisateur')->get()->pluck('id');

        foreach($notification_flux_utilisateur as $notification_id){

            management('notification_flux_utilisateur',$notification_id)->supprime();
        }

        return true;
    }
}

