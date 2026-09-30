<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220420_utilisateur_type_connexion implements Script {

    public function execute() {

        $utilisateurs_a_supprimer = array(
            'ayoub.elkajji@easy-developpement.fr',
            'clementine.mariage@easy-developpement.fr',
            'samir.guenouni@easy-developpement.fr',
            'steven.proag@easy-developpement.fr',
            'jonathan.lefrileux@easy-developpement.fr',
        );

        $utilisateurs = modele('utilisateur')
            ->where(function($r) use ($utilisateurs_a_supprimer){

                $r->whereNull('type_utilisateur')
                ->orWhereIn('email', $utilisateurs_a_supprimer);
            })
            ->get();

        foreach($utilisateurs as $utilisateur) {

            if(in_array($utilisateur->email, $utilisateurs_a_supprimer) && $utilisateur->inactif != 1){

                $utilisateur->inactif = 1;
                $utilisateur->save();
                continue;
            }

            if($utilisateur->type_utilisateur == null || $utilisateur->type_utilisateur == '') {

                $utilisateur->type_utilisateur = 0;
                $utilisateur->save();
            }
        }
        
        return true;
    }
}