<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;


/**
 * Gestion des fiches lots
 */
class Fiche_note_de_frais_management extends Fiche_management {

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        if(empty($donnees['note_de_frais']['accepte'])) {

            $options_fil_ariane[] = [
                'id' => 'valider_note_de_frais',
                'ordre' => -1,
                'evenements' => [
                    'validation_note_de_frais' => 'Validation de la note de frais' 
                ]
            ];

            $options_fil_ariane[] = [
                'id' => 'refuser_note_de_frais',
                'ordre' => 0,
                'evenements' => [
                    'refus_note_de_frais' => 'Refus de la note de frais' 
                ]
            ];
        }

        if(profil($this->type_element, $this->management->modele->entite_id ?? null, 'comptabilisation'))
            $options_fil_ariane[] = [
                'id' => 'comptabiliser',
                'ordre' => 1,
                'v-if' => 'note_de_frais.id && note_de_frais.accepte && !note_de_frais.comptabilisee'
            ];

        return $options_fil_ariane;
    }
}
