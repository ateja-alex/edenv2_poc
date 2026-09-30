<?php

namespace App\Eden\Managements\Elements;

class Condition_commerciale_management extends Element_management {

    /*
     *
     * Ajoute l'option historique aux listes de conditions commerciales
     *
     */
    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'historique';

        return $liste_options;
    }


}