<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;
use Illuminate\Support\Facades\DB;

class S20251024_suppression_doublons_colonnes_liste_compte_email implements Script {

    public function execute(){

        $liste_compte_email = Liste_libre::where('type_element', 'compte_email')
            ->where(function($requete) {
                
                $requete->where('id_rapport', '');
                $requete->orWhereNull('id_rapport');
            })
            ->first();

        if(empty($liste_compte_email))
            return true;

        $nombre_doublons = Colonne::where('index_traduction', "liste.compte_email.colonne.mail_public")
            ->where('liste_libre_id', $liste_compte_email->id)
            ->count();

        if($nombre_doublons > 1)
            DB::delete('DELETE FROM listes_libres_colonnes where index_traduction = "liste.compte_email.colonne.mail_public" ORDER BY type LIMIT ' . ($nombre_doublons - 1));

        if(file_exists(app_path('Migrations/Listes_libres/compte_email.php')))
            Liste_libre_management::generer_fichier_migration_liste_libre($liste_compte_email->id, true);

        return true;
    }
}