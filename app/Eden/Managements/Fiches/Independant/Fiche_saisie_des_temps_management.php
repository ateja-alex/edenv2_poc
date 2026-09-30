<?php

namespace App\Eden\Managements\Fiches\Independant;

use App\Eden\Managements\Cache_management;

class Fiche_saisie_des_temps_management extends Fiche_independant_management{

    public function genere_fichier_fiche($structure, $extranet){
        parent::genere_fichier_fiche($structure, $extranet);

        Cache_management::generation_module('saisie_des_temps');
    }
}
