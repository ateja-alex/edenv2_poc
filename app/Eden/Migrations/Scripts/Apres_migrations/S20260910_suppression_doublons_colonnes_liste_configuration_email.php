<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;

class S20260910_suppression_doublons_colonnes_liste_configuration_email implements Script {

    public function execute(){

        $liste_configuration_email = Liste_libre::where('type_element', 'configuration_email')
            ->where(function($requete) {

                $requete->where('id_rapport', '');
                $requete->orWhereNull('id_rapport');
            })
            ->first();

        if(empty($liste_configuration_email))
            return true;

        $colonnes_en_collision = Colonne::where('index_traduction', "liste.configuration_email.colonne.adresse_email")
            ->where('liste_libre_id', $liste_configuration_email->id)
            ->orderBy('id')
            ->get()
            ->groupBy('valeur');

        foreach($colonnes_en_collision as $colonnes){

            if($colonnes->count() > 1)
                Colonne::whereIn('id', $colonnes->slice(1)->pluck('id'))->delete();
        }

        Colonne::where('liste_libre_id', $liste_configuration_email->id)
            ->where('valeur', 'login')
            ->update([
                'nom' => 'Login',
                'index_traduction' => 'liste.configuration_email.colonne.login',
            ]);

        if(file_exists(app_path('Migrations/Listes_libres/configuration_email.php')))
            Liste_libre_management::generer_fichier_migration_liste_libre($liste_configuration_email->id, true);

        return true;
    }
}
