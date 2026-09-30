<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Filtres_et_options\Liste_valeurs;

/**
 *
 * Filtres pour choisir une ou plusieurs entités
 *
 */
class Projets {

    /**
     *
     *
     *
     */
    public static function applique($rapport) {

        return Liste_valeurs::applique($rapport, 'projets');
    }



}