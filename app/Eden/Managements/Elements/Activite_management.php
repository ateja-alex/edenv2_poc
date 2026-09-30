<?php

namespace App\Eden\Managements\Elements;

class Activite_management extends Element_management {

    /**
     *
     * Supprime un élément
     *
     * @param $modele le modèle que l'on veut supprimer (si non fourni, on se base sur le modèle lié au management)
     *
     * @return true si tout va bien, une erreur (string) si il y a une erreur (impossible de supprimer)
     *
     */
    public function supprime($modele = false) {

        if(!empty($modele) && !empty($modele->realise) && $modele->realise > 0){

            return traduction('messages.php.activite_projet.suppresion_impossible');

        }

        return parent::supprime($modele);
    }

}