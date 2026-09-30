<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Parametrage_lien_champ_management;
use App\Eden\Variables;

class Parametrage_piece_jointe_email_management extends Parametrage_lien_champ_management{

    public $champ_valeur_dur = 'piece_jointe'; 

    public function __construct($type_element, $id_element = false, $modele = false){

        $this->filtres_valeur_final = [
            'champs' => [
                [
                    'type' => 7
                ],
                [
                    'nom_sql' => 'pdf',
                    'type_element' => Variables::$documents_gescom
                ]
            ],
            'tables_libres_final' => [
                'element_piece_jointe',
            ],
            'element_table_libre_final' => [
                [
                    'type_element' => 'modele_de_document',
                    'champ_lien_type_element' => 'type_element_autres',
                    'fonction' => 'creation_document_pdf',
                ],
            ]
        ];

        return parent::__construct($type_element, $id_element, $modele);
    }

    public function valeurs_globales($parametrages, $parametres){

        $valeurs_globales = [];

        foreach($parametrages as $parametrage){

            $management_parametrage = management($this->_type_element, $parametrage->id, $parametrage);

            $valeurs_globales[] = [
                'valeur' => $management_parametrage->valeur_liste(),
                'element' => $this->_type_element,
                'groupe_id' => $parametrage->id,
                'obligatoire' => $parametrage->niveau == 3
            ];
        }

        return $valeurs_globales;
    }

    public function valeurs_par_groupe($parametrages, $groupes_ids, $parametres){
        
        $type_lien = $this->_type_element;

        $filtrages = modele('recherche_avancee')
                ->whereIn('type', array_map(function($parametrage) use ($type_lien) {
                    return $type_lien.'_'.$parametrage['id'];
                }, $parametrages->toArray()))
                ->get()->groupBy('type')->map(function($group) {
                    return $group->keyBy('id_cible');
                });

        $valeurs_par_groupe = [];

        foreach($groupes_ids as $index => $elements_ids){

            $valeurs = [];

            foreach($parametrages as $parametrage){

                $management_parametrage = management($type_lien, $parametrage->id, $parametrage);

                $valeurs = array_merge(
                    $valeurs ?? [],
                    $management_parametrage->valeurs($elements_ids, $filtrages[$type_lien.'_'.$parametrage->id] ?? collect([]))
                );
            }
            
            $valeurs_ajouter = [];
            $valeurs_unique = [];
            
            foreach($valeurs as $valeur) {
                if (!in_array($valeur['valeur'], $valeurs_ajouter)) {
                    $valeurs_ajouter[] = $valeur['valeur'];
                    $valeurs_unique[] = $valeur;
                }
            }

            $valeurs_par_groupe[$index] = $valeurs_unique;
        }

        return $valeurs_par_groupe;
    }

    public function affichage_valeur($valeur, $management_element = null){

        if(empty($this->modele->lien_champ))
            return '<span>'.$valeur.'</span>';

        $liens_champs = explode('/',$this->modele->lien_champ);

        $champ_final = end($liens_champs);

        if(strpos($champ_final, 'table_libre|') == 0 && $management_element != null)
            return '<span title="'.$this->valeur_title().'">'.$management_element->affiche().'</span>';
        else if(strpos($champ_final, 'element_table_libre_final|') !== 0){

            $champ_final = explode('.',$champ_final);
            $modele_champ_libre = champ_libre_modele($champ_final[0], $champ_final[1]);

            $valeur = traduction($modele_champ_libre->index_traduction.'.nom');
        }

        if($management_element != null)
            return '<span title="'.$this->valeur_title().'">'.$valeur.'</span> - <a target="_blank" href="'.$management_element->lien_vers_element().'">'.$management_element->affiche().'</a>';

        return '<span title="'.$this->valeur_title().'">'.$valeur.'</span>';
    }
}