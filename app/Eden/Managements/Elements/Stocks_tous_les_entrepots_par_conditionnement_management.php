<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Stocks_par_conditionnement_management;

use Illuminate\Http\Request;

class Stocks_tous_les_entrepots_par_conditionnement_management extends Stocks_par_conditionnement_management {

    /**
     *
     * Gére l'affichage des auantités
     *
     */
    public function affichage_quantite($element,$colonne){

        if(!isset($element->$colonne))
            return 0;

        $champ_libre = champ_libre('stocks_tous_les_entrepots_par_conditionnement',$colonne)->champ;

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

}