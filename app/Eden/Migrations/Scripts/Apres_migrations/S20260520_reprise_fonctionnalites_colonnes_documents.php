<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20260520_reprise_fonctionnalites_colonnes_documents implements Script{
    public function execute(){

        if(!file_exists(storage_path('app/eden_fonctionnalites.php')))
            return true;

        $fonctionnalites_storage = include(storage_path('app/eden_fonctionnalites.php'));

        $fonctionnalites_storage['documents_colonnes_a_afficher_vente'] = $fonctionnalites_storage['documents_colonnes_a_afficher'];
        $fonctionnalites_storage['documents_colonnes_a_afficher_achat'] = $fonctionnalites_storage['documents_colonnes_a_afficher'];

        if(isset($fonctionnalites_storage['fonctionnalites_par_profil']['documents_colonnes_a_afficher'])){
            $fonctionnalites_storage['fonctionnalites_par_profil']['documents_colonnes_a_afficher_vente'] = $fonctionnalites_storage['fonctionnalites_par_profil']['documents_colonnes_a_afficher'];
            $fonctionnalites_storage['fonctionnalites_par_profil']['documents_colonnes_a_afficher_achat'] = $fonctionnalites_storage['fonctionnalites_par_profil']['documents_colonnes_a_afficher'];
            unset($fonctionnalites_storage['fonctionnalites_par_profil']['documents_colonnes_a_afficher']);
        }

        unset($fonctionnalites_storage['documents_colonnes_a_afficher']);
            
        Script_management::generer_fichier_fonctionnalite($fonctionnalites_storage);

        return true;
    }
}