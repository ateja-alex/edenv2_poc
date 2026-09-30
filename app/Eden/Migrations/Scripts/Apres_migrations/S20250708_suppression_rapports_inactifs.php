<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;
use Schema;

class S20250708_suppression_rapports_inactifs implements Script
{

    public function execute(){

        $rapports_a_supprimer = [
            'articles_ecommerce',
            'commissions_des_commerciaux_pourcentage_ca',
            'objectif_ca_par_entite_saisie',
            'objectif_ca_par_famille_saisie',
            'suivi_recette_easydev_kanban'
        ];

        $nom_dossier_migrations = app_path('Migrations/Rapports');
        $contenu_dossier_migration = is_dir($nom_dossier_migrations) ? scandir($nom_dossier_migrations) : array();

        foreach($rapports_a_supprimer as $index => $rapport_a_supprimer){

            if(in_array($rapport_a_supprimer . '.php', $contenu_dossier_migration))
                unset($rapports_a_supprimer[$index]);
        }
        
        if(Schema::hasTable('eden_rapports'))
            DB::update("UPDATE eden_rapports SET inactif = 1 where id_rapport in ('" . implode("', '", $rapports_a_supprimer) . "')");

        return true;
    }
}