<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;
use App\Eden\Variables;

use DB;

/**
 * Gestion des fiches projets
 */
class Fiche_bordereau_management extends Fiche_management {

    /**
     *
     * On surcharge pour gérer les filtres sur fiches
     *
     */
    public function listes_sur_fiche($structure, $campagne_de_prospection_en_cours = false){

        $retour = parent::listes_sur_fiche($structure, $campagne_de_prospection_en_cours);

        $modele = modele($this->type_element,$this->id_element);

        if(!empty($retour['listes_sur_fiche']['unitaire']['fiche_bordereau_paiement'])){

            if($modele->statut == 0 || $modele->statut == null) {
                $retour['listes_sur_fiche']['unitaire']['fiche_bordereau_paiement']['filtres_pour_fiche'] = [
                    'dynamique' => 0,
                    'cle' => 'bordereau_id',
                    'valeur' => array(0, $this->id_element),
                ];
            }
        }

        return $retour;
    }

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'impression',
            'ordre' => -1
        ];

        return $options_fil_ariane;
    }

}
