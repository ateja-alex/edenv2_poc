<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20230807_creation_modeles_recurrence implements Script {

    public function execute() {

        $management = management('tache_recurrence_modele');
        $modeles_a_creer = [
            [

                'nom' => 'Tous les jours',
                'frequence' => 1,
                'type_frequence' => 1,
            ],
            [

                'nom' => 'Tous les jours du Lundi au Vendredi',
                'frequence' => 1,
                'type_frequence' => 2,
                'jours_concernes' => ["1", "2", "3", "4", "5"]
            ],
            [

                'nom' => 'Toutes les semaines',
                'frequence' => 1,
                'type_frequence' => 2,
            ],
            [

                'nom' => 'Tous les mois',
                'frequence' => 1,
                'type_frequence' => 3,
            ],
            [

                'nom' => 'Tous les ans',
                'frequence' => 1,
                'type_frequence' => 4,
            ],
        ];

        foreach($modeles_a_creer as $modele){

            $management->modele = null;
            $management->enregistre($modele);
        }

        return true;
    }
}

