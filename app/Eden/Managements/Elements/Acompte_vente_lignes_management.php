<?php

namespace App\Eden\Managements\Elements;

class Acompte_vente_lignes_management extends Document_lignes_management {

    public function enregistre($modifications = array(), $modele = false) {

        $champs_a_vider = [
            'tarif_eco_contribution',
            'application_eco_contribution',
            'categorie_eco_contribution_id',
            'quantite_unite_eco_contribution'
        ];

        foreach ($champs_a_vider as $champ) {
            if(!empty($modifications[$champ]))
                unset($modifications[$champ]);

            if($this->existe() && !empty($this->modele->$champ))
                $this->modele->$champ = null;
        }

        return parent::enregistre($modifications, $modele);

    }
}