<?php

namespace App\Eden\Managements\Services;


class Modele_par_defaut_service {

    /**
     *
     * Permet de récupérer le modèle par défaut d'un élémént
     *
     */
    public function recupere($type_element){

        if($type_element == 'tache')
            return management($type_element)->modele_par_defaut();
        else
            return modele_par_defaut($type_element);
    }
}