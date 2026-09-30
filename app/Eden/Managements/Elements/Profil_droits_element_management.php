<?php

namespace App\Eden\Managements\Elements;


class Profil_droits_element_management extends Element_management{

    public function affichage_champ_creation($modele){

        if(!empty($modele->nom_sql))
            return traduction('liste.profil_droits_element.creation.na');

        return $this->champ('creation')->affiche($modele->creation);
    }
}