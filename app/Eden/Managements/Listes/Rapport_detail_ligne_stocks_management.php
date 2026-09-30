<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Rapport_detail_ligne_stocks_management extends Liste_stocks_par_conditionnement_management {

    public function modifie_liste_colonnes($colonnes){

        if(empty($this->id_liste))
            return $colonnes;

        foreach($colonnes as &$colonne){

            if($colonne->methode == 'stock_inventaire_reel') {
                $colonne->nom = '<div style="display:flex;align-items:center;gap:5px;">
                    <span>'.$colonne->nom.'</span>
                    <div class="ml-auto bouton_stock_inventaire_reel" onclick="event.stopPropagation();vue_instance.$emit(\'enregistrer_stock_inventaire_reel_dans_detail\',event)">
                        <i class="fas fa-save"></i>
                    </div>
                </div>';
                $colonne->index_traduction = null;
            }
        }

        return $colonnes;
    }
}