<?php

namespace App\Eden\Champs;

class Champ_montant_entier extends Champ_montant {

    public function cree(){

        $this->attr('champ_entier', true, 1);
        return parent::cree();
    }
}
