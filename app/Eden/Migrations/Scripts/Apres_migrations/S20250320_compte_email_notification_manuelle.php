<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Controllers\Parametrage\Champs_libres_controller;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;
use Illuminate\Support\Facades\Log;

class S20250320_compte_email_notification_manuelle implements Script {

    public function execute() {

        $notifications_manuelles = modele('notification_manuelle')
            ->whereNull('compte_email_id')->get();

        $comptes_emails = modele('compte_email')->get()->pluck('id','adresse_email');

        foreach($notifications_manuelles as $notification_manuelle){

            if(isset($comptes_emails[$notification_manuelle->expediteur_email]))
                management('notification_manuelle',$notification_manuelle->id,$notification_manuelle)->enregistre_modele([
                    'compte_email_id' => $comptes_emails[$notification_manuelle->expediteur_email]
                ]);
        }

        $champs_libres = Champ_libre::where('type_element', 'notification_manuelle')
            ->whereIn('nom_sql', ['expediteur_email','expediteur_champ'])->get();

        foreach($champs_libres as $champ_libre){
            $champ_libre->delete();
        }

        // Si le fichier existe, on regénére le fichier de migrations
        if (file_exists(app_path() . '/Migrations/notification_manuelle.php'))
            Table_libre_management::generer_fichier_migration('notification_manuelle');

        $formulaire_champ = Formulaires_champs::where('type_element','notification_manuelle')->where('nom_vue','choix_expediteur')->first();

        if(!empty($formulaire_champ)) {
            $formulaire_champ->nom_vue = null;
            $formulaire_champ->type_vue = null;
            $formulaire_champ->condition_obligatoire = 'notification_manuelle.type_notification == 2';
            $formulaire_champ->type_champ = 0;
            $formulaire_champ->taille_libelle = 2;
            $formulaire_champ->taille_champ = 4;
            $formulaire_champ->nom_sql = 'compte_email_id';
            $formulaire_champ->save();
        }

        if (file_exists(app_path() . '/Migrations/Formulaires_libres/notification_manuelle.php'))
            Maintenance_management::generer_fichier_migration_formulaire('notification_manuelle');

        return true;
    }
}

