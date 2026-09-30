<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Parametrage_lien_champ_management;

class Parametrage_destinataire_erp_management extends Parametrage_lien_champ_management{

    public $champ_valeur_dur = 'utilisateur_id'; 
    public $filtres_valeur_final = [
        'champs' => [
            ['type_element_ajax' => 'utilisateur']
        ],
        'tables_libres_final' => [
            'utilisateur',
        ],
    ];

    public function affichage_valeur($valeur, $management_element = null){

        if($management_element != null)
            return '<span title="'.$this->valeur_title().'">'.$valeur.'</span> - <a target="_blank" href="'.$management_element->lien_vers_element().'">'.$management_element->affiche().'</a>';

        return '<span title="'.$this->valeur_title().'">'.$valeur.'</span>';
    }

    public function valeurs_globales($parametrages, $parametres){

        $valeurs_globales = [];

        foreach($parametrages as $parametrage){

            $management_parametrage = management($this->_type_element, $parametrage->id, $parametrage);

            $valeurs_globales[] = [
                'valeur' => $management_parametrage->valeur_liste(),
                'element' => $this->_type_element,
                'groupe_id' => $parametrage->id,
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
}