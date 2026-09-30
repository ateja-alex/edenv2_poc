<?php

namespace App\Eden\Managements\Elements;


use Illuminate\Http\Request;

class Stocks_par_conditionnement_management extends Element_management {

    /**
     *
     * Gére l'affichage d'un champ de stock inventaire permettant de modifier le stock réel
     *
     */
    public function stock_inventaire_reel($modele) {

        return '<input onclick="event.stopPropagation()" class="input_stock_inventaire_reel" id_ligne="'.$modele->id.'">';
    }

    /**
     *
     * Gére l'affichage des auantités
     *
     */
    public function affichage_quantite($element,$colonne){

        if(!isset($element->$colonne))
            return 0;

        $champ_libre = champ_libre('stocks_par_conditionnement',$colonne)->champ;

        $valeur = $champ_libre->affiche($element->$colonne);

        if(empty($element->conditionnement_id))
            return $valeur;

        $valeur_conditionnement = 0;

        $colonne_conditionnement = $colonne.'_conditionnement';

        if(!empty($element->$colonne_conditionnement))
            $valeur_conditionnement = $champ_libre->affiche($element->$colonne_conditionnement);

        if(empty($valeur_conditionnement))
            return $valeur_conditionnement;

        return $valeur_conditionnement.' ( '.$valeur.' )';
    }

    /**
     *
     * Affichage une colonne avec l'alerte adéquat en fonction des seuils
     *
     */
    public function affichage_colonne_avec_alerte($element,$colonne){

        $valeur = $element->{$colonne.'_conditionnement'};

        $valeur_affichage = $this->affichage_quantite($element,$colonne);

        if(empty($element->seuil_mini) && empty($element->seuil_alerte))
            return $valeur_affichage;

        $couleur_par_score = array(
            2 => array(
                'icone' => "fa-check",
                'couleur' => "green"
            ),
            1 => array(
                'icone' => "fa-exclamation-triangle",
                'couleur' => "orange"
            ),
            0 => array(
                'icone' => "fa-exclamation-triangle",
                'couleur' => "red"
            ),
        );

        $score_globable = 2;

        if($valeur < $element->seuil_mini)
            $score_globable = 0;
        elseif($valeur < $element->seuil_alerte)
            $score_globable = 1;

        $alerte='<span class="ml-auto badge" style="height: fit-content;color:white;position: relative;background-color: '.$couleur_par_score[$score_globable]['couleur'].'">
            <i class="fas '.$couleur_par_score[$score_globable]['icone'].'"></i>
        </span>';

        return '<div style="display: flex">
                    <span>'.$valeur_affichage.'</span>
                    '.$alerte.'
                </div>';
    }
}