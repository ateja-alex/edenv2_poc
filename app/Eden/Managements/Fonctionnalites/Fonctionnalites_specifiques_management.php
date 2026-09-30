<?php

namespace App\Eden\Managements\Fonctionnalites;

use App\Eden\Variables;
use App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

class Fonctionnalites_specifiques_management extends Fonctionnalite_management{

    public function fonctionnalites_avec_valeurs()
    {
        $types_modele = [];
        $valeurs = [];

        foreach ($types_modele as $type_modele) {

            $valeurs[$type_modele] = modele($type_modele)->get()->pluck('nom', 'id')->toArray();
        }

        return $this->fonctionnalites($valeurs);

    }

    /*
     *
     * Retour les fonctionnalitées du module
     *
     */
    public function fonctionnalites($valeurs = array()){

        $fonctionnalites = array(

           /* 'Paramètres généraux' => array(

                array(

                    'nom' => "Rechercher par défaut...",
                    'type' => "select",
                    'valeurs_select' => array(

                        '' => 'Tout',
                        'projet' => 'Projet',
                        'client' => 'Client',
                    ),
                    'description' => "",
                    'fonctionnalite' => "recherche_par_defaut",
                ),

            ),*/
        );

        return $fonctionnalites;
    }

    public function fonctionnalites_mise_en_page_par_defaut(){

        $fonctionnalites_spe = config('fonctionnalites_specifiques');

        $fonctionnalites = array();

        foreach ($fonctionnalites_spe as $nom => $valeur){

            $nom_mis_en_forme = ucfirst(str_replace('_',' ', $nom));

            $array_fonctionnalite = array();

            $array_fonctionnalite['nom'] = $nom_mis_en_forme;
            $array_fonctionnalite['fonctionnalite'] = $nom;
            $array_fonctionnalite['type'] = 'input';

            $fonctionnalites['Paramètres généraux'][] = $array_fonctionnalite;
        }

        return $fonctionnalites;
    }

    public function obtenir_type_module(){

        return 'Fonctionnalités spécifiques';
    }

    public function obtenir_nom_module(){

        return 'Module de fonctionnalités spécifiques';
    }

    public function obtenir_icone_module(){

        return 'eden/images/pictos/functionnalities.png';
    }
}
