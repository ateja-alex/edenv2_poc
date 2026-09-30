<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_trigger_eden_management extends Listes_management {

    /**
     * @param $liste_libre
     * @return mixed
     *
     * Lorsque que l'on est sur le zoom on peut supprimer le filtre de trigger
     *
     */
    public function filtres_a_afficher($liste_libre,$rapport)
    {
        $filtres_a_afficher = parent::filtres_a_afficher($liste_libre,$rapport);

        $zoom = strpos($_SERVER['HTTP_REFERER'],'tables_libres/zoom') !== false;

        if(!$zoom)
            return $filtres_a_afficher;

        foreach($filtres_a_afficher as $index => $filtre){

            if($filtre['nom_sql'] == 'type_element_id')
                unset($filtres_a_afficher[$index]);
        }

        return $filtres_a_afficher;
    }
}