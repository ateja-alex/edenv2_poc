<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_fiche_note_de_frais_note_de_frais_lignes_management extends Listes_management {

    /**
     *
     * Fonction qui permet d'effectuer un traitement particulier sur les calculs
     *
     */
    public function obtenir_colonnes($liste_id, $table_libre, $type_export = 'basique') {

        $colonnes = parent::obtenir_colonnes($liste_id, $table_libre, $type_export);

        $parametres = request()->all();

        if(!empty($parametres['filtres_pour_fiche'])){

            if(!is_array($parametres['filtres_pour_fiche']))
                $parametres['filtres_pour_fiche'] = unserialize(base64_decode($parametres['filtres_pour_fiche']));

            if(isset($parametres['filtres_pour_fiche']['note_de_frais_id'])){

                $note_de_frais = modele('note_de_frais',$parametres['filtres_pour_fiche']['note_de_frais_id']);

                $devise = modele('devise',$note_de_frais->devise);

                if(empty($note_de_frais->devise) || $devise->code == maquette('devise_application_iso'))
                    $colonnes = $colonnes->reject(function($element) {
                        return $element->methode == 'montant_ht_devise';
                    });
            }
        }

        return $colonnes->values();
    }

}