<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_stocks_par_conditionnement_management extends Listes_management {

    public function modifie_liste_colonnes($colonnes){

        if(empty($this->id_liste))
            return $colonnes;

        foreach($colonnes as &$colonne){

            if($colonne->methode == 'stock_inventaire_reel') {
                $colonne->nom = '<div style="display:flex;align-items:center;gap:10px;">
                    <span>'.$colonne->nom.'</span>
                    <div class="ml-auto bouton_stock_inventaire_reel" onclick="event.stopPropagation();vue_instance.$refs.liste_libre_'.$this->id_liste.'.enregistrer_stock_inventaire_reel(event)">
                        <i class="fas fa-save"></i>
                    </div>
                </div>';
                $colonne->index_traduction = null;
            }
        }

        return $colonnes;
    }

    public function parametres_creation_element_colonne_champ_vide($parametres_champ,$element){

        $parametres = array(
            'article_id' => $element->article_id,
            'conditionnement_id' => $element->conditionnement_id,
            'quantite_conditionnement' => 0,
        );

        if($parametres_champ['type_element'] == 'stock_initial'){
            $parametres['entrepot_id'] = $element->entrepot_id;
        }

        return $parametres;
    }
}