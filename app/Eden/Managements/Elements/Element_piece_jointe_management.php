<?php

namespace App\Eden\Managements\Elements;

class Element_piece_jointe_management extends Element_management {

    /**
     *
     * On rend la pièce jointe disponible pour l'extranet si elle a été créée via celui-ci
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(!empty(moi_extranet()))
            $modifications['disponible_extranet'] = 1;

        parent::enregistre($modifications, $modele);
    }

    public function donnee_lien_champ(){
        return 'storage/'.$this->modele->chemin;
    }

    public function affiche(){
        return $this->modele->nom;
    }
}