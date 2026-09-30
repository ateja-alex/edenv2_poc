<?php

namespace App\Eden\Managements\Services;

class Profil_service {

    /**
     *
     * Informations par type element
     *
     */
    public function types_elements_gestion_droits(){

        return array(
            'tableau_de_bord',
            'menus_liens',
            'menus_categories',
        );
    }
}