<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20230510_favicon implements Script {

    public function execute() {

        // Transfert du fichier logo vers la favicon
            $maquettes = modele('maquette')->get();
            foreach($maquettes as $maquette) {
                $maquette->favicon = $maquette->logo_application;
                $maquette->save();
            }

        // Ajout de la favicon au formulaire libre
            $formulaire = [
                'nom_formulaire'=> 'maquette',
                'type_element' => 'maquette',
                'nom_sql' => 'favicon',
                'taille_avant' => 0,
                'taille_libelle'=> 2,
                'taille_champ' => 4,
                'taille_apres' => 0,
            ];
            Script_management::ajout_champ_formulaire_libre('maquette', $formulaire);

        return true;
    }
}