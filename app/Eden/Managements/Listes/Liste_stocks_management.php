<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_stocks_management extends Listes_management {

    public function modifie_liste_colonnes($colonnes){

        $colonnes = parent::modifie_liste_colonnes($colonnes);

        if(empty($this->id_liste))
            return $colonnes;

        foreach($colonnes as &$colonne){

            if($colonne->methode == 'stock_inventaire_reel') {
                $colonne->nom = '<div style="display:flex;align-items:center;gap:10px;">
                    <span>'.$colonne->nom.'</span>
                    <div class="ml-auto bouton_stock_inventaire_reel" onclick="event.stopPropagation();vue_instance.$refs.liste_libre_'.$this->id_liste.'.enregistrer_stock_inventaire_reel(event,true)">
                        <i class="fas fa-save"></i>
                    </div>
                </div>';
                $colonne->index_traduction = null;
            }
        }

        return $colonnes;
    }
}