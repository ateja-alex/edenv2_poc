<?php

namespace App\Eden\Managements\Elements;

class Parametrage_chronometre_management extends Element_management
{

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'formulaire';

        return $liste_options;
    }
}