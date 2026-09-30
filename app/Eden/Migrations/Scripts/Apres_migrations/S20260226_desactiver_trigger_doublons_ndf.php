<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Table_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20260226_desactiver_trigger_doublons_ndf implements Script {

    public function execute(){

        $trigger_doublons_ndf = modele('trigger_eden')->where('nom', 'Gestion doublon note de frais')->first();

        $trigger_management = management('trigger_eden', $trigger_doublons_ndf->id, $trigger_doublons_ndf);

        $trigger_management->supprime($trigger_doublons_ndf);

        $champ_doublon = Champ_libre::where('type_element','note_de_frais')->where('nom_sql','doublon_potentiel')->first();
        $champ_doublon->modification_post_validation = 1;
        $champ_doublon->save();

        if(file_exists(app_path('Migrations/note_de_frais.php'))){
            Table_libre_management::generer_fichier_migration('note_de_frais',true);
        }

        return true;
    }
}