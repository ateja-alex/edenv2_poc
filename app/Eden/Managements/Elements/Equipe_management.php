<?php

namespace App\Eden\Managements\Elements;

class Equipe_management extends Element_management {

    public function affiche()
    {
        
        $badge_equipe = '<span class="badge badge-default" style="background:' . $this->modele->couleur_fond . '; color:' . $this->modele->couleur_police . ';">';
        $badge_equipe .= $this->champ('nom')->affiche() . "</span>";
        
        return $badge_equipe;
        
    }
}
