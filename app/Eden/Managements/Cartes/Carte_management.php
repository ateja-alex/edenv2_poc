<?php

namespace App\Eden\Managements\Cartes;

use App\Eden\Managements\Listes_management;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Rapport_parametre;
use App\Eden\Models\Rapport_libre;

class Carte_management extends Listes_management {

    /**
     *
     * Initialisation des filtres 
     *
     */
    public function initialisation_filtres($id_rapport){

        $affichage_filtres = array();
        $affichage_filtres['filtres_affichages'] = $this->filtres_a_afficher($id_rapport);

        return $affichage_filtres;
    }

    /**
     *
     * liste des filtres à afficher
     *
     */
    public function filtres_a_afficher($id_rapport){
        
        $rapport = Rapport_libre::where('id_rapport', $id_rapport)->get()->pluck('id');
        $filtres = Liste_libre_filtre::where('rapport_id', $rapport)->orderBy('ordre')->get();

        foreach ($filtres as $filtre) {
            $filtre->type_element = !empty($filtre->type_element) ? $filtre->type_element : "adresse";
            $filtre->modele = champ_libre_modele('adresse',$filtre->nom_sql);
            $filtre->type_filtre = management('adresse')->champ($filtre->nom_sql)->type_filtre;
            $filtre->index_traduction = $filtre->modele->index_traduction;
        }
        
        return $filtres;
    }
}