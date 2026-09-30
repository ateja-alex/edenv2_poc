<?php

namespace App\Eden\Managements;

use App\Eden\Models\Formulaires_champs;
use App\Eden\Variables;

class Formulaire_management{

    public $formulaire = array();
    public $type_element = null;
    public $champs_formulaires = array();

    public function __construct($formulaire,$champs_formulaires = null,$type_element = null){

        $this->formulaire = $formulaire;

        $champs_formulaires = [];

        if($formulaire !== null) {
            if(!empty($type_element))
                $this->type_element = $type_element;
            else if (!empty($formulaire->type_element))
                $this->type_element = $formulaire->type_element;
            else
                $this->type_element = $formulaire->nom_formulaire;

            if ($champs_formulaires == null)
                $champs_formulaires = Formulaires_champs::where('nom_formulaire', $formulaire->nom_formulaire)->orderBy('ordre')->get();
        }

        $this->champs_formulaires = $champs_formulaires;
    }

    /**
     *
     * Permet de récupérer les champs obligatoires et notamment les conditions d'obligation des champs
     *
     */
    public function champs_obligatoires(){

        $champs_obligatoires = [];

        $champs = champs_libres($this->type_element)->keyBy('nom_sql');

        $champs_libres_obligatoires = champs_libres_obligatoires($this->type_element)->pluck('nom_sql')->toArray();

        $liste_formatees_zero_possible = Variables::liste_formatees_zero_possible();

        foreach($this->champs_formulaires as $champ_formulaire){

            if($champ_formulaire->type_champ == 0) {

                if(!isset($champs[$champ_formulaire->nom_sql]))
                    continue;

                $champ_libre = $champs[$champ_formulaire->nom_sql];

                $zero_possible = $champ_libre->type == 20 && in_array($champ_libre->liste_choix, $liste_formatees_zero_possible);

                if (in_array($champ_formulaire->nom_sql,$champs_libres_obligatoires)) {
                    $champs_obligatoires[] = [
                        'nom_sql' => $champ_formulaire->nom_sql,
                        'type' => $champ_libre->type,
                        'nom' => $champ_libre->nom,
                        'zero_possible' => $zero_possible,
                    ];
                    unset($champs_libres_obligatoires[array_search($champ_formulaire->nom_sql,$champs_libres_obligatoires)]);
                }
                elseif(!empty($champ_formulaire->condition_obligatoire))
                    $champs_obligatoires[] = [
                        'nom_sql' => $champ_formulaire->nom_sql,
                        'condition' => $champ_formulaire->condition_obligatoire,
                        'type' => $champ_libre->type,
                        'nom' => $champ_libre->nom,
                        'zero_possible' => $zero_possible,
                    ];
            }
        }

        foreach($champs_libres_obligatoires as $champ_libre_obligatoire){

            $champ_libre = $champs[$champ_libre_obligatoire];

            $zero_possible = $champ_libre->type == 20 && in_array($champ_libre->liste_choix, $liste_formatees_zero_possible);

            $champs_obligatoires[] = [
                'nom_sql' => $champ_libre_obligatoire,
                'type' => $champ_libre->type,
                'nom' => $champ_libre->nom,
                'zero_possible' => $zero_possible,
            ];
        }

        return $champs_obligatoires;
    }
}