<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;

class S20250903_utilisateur_extranet implements Script {

    public function execute() {

        $contacts = modele('contact')->where('extranet_compte_actif', 1)->get();

        foreach($contacts as $contact) {
            
            $utilisateur_extranet = management('utilisateur_extranet');

            $retour = $utilisateur_extranet->enregistre([
                'email' => $contact->extranet_login,
                'old_mot_de_passe' => $contact->extranet_mot_de_passe,
                'nom' => $contact->nom,
                'prenom' => $contact->prenom,
                'profil' => $contact->profil_extranet,
                'langue' => $contact->langue,
                'autorise_a_se_connecter' => $contact->extranet_compte_actif,
                'admin' => $contact->extranet_admin,
                'derniere_connexion' => $contact->extranet_derniere_connexion
            ]);

            if($retour !== true && empty($utilisateur_extranet->modele->id))
                continue;

            management('contact',$contact->id,$contact)->enregistre_modele([
                'extranet_mot_de_passe' => null,
                'utilisateur_extranet_id' => $utilisateur_extranet->modele->id
            ]);
        }

        return true;
    }

}