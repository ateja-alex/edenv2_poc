<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Parametrage_lien_champ_management;

class Parametrage_destinataire_email_management extends Parametrage_lien_champ_management{

    public $champ_valeur_dur = 'adresse_mail'; 
    public $filtres_valeur_final = [
        'champs' => [
            ['format_champ' => 'email']
        ]
    ];

    public function valeurs_globales($parametrages, $parametres){

        $valeurs_globales = [
            0 => [],
            1 => [],
            2 => [],
            3 => []
        ];

        foreach($parametrages as $parametrage){

            $management_parametrage = management($this->_type_element, $parametrage->id, $parametrage);

            $type_parametrage = empty($parametres['niveau']) || $parametres['niveau'] == 1 ? 0 : $parametrage->type;

            $valeurs_globales[$type_parametrage][] = [
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

            $valeurs_par_type = [
                0 => [],
                1 => [],
                2 => [],
                3 => []
            ];

            foreach($parametrages as $parametrage){

                $management_parametrage = management($type_lien, $parametrage->id, $parametrage);

                $type_parametrage = empty($parametrage->notification_manuelle_id) && (empty($parametres['niveau']) || $parametres['niveau'] == 1) ? 0 : $parametrage->type;

                $valeurs_par_type[$type_parametrage] = array_merge(
                    $valeurs_par_type[$type_parametrage] ?? [],
                    $management_parametrage->valeurs($elements_ids, $filtrages[$type_lien.'_'.$parametrage->id] ?? collect([]))
                );
            }
            
            foreach($valeurs_par_type as &$valeurs){

                $valeurs_ajouter = [];
                $valeurs_unique = [];
                
                foreach($valeurs as $valeur) {
                    if (!in_array($valeur['valeur'], $valeurs_ajouter)) {
                        $valeurs_ajouter[] = $valeur['valeur'];
                        $valeurs_unique[] = $valeur;
                    }
                }

                $valeurs = $valeurs_unique;
            }

            $valeurs_par_groupe[$index] = $valeurs_par_type;
        }

        return $valeurs_par_groupe;
    }

    public function affichage_valeur($valeur, $management_element = null){

        if(empty($this->modele->lien_champ))
            return '<span>'.$valeur.'</span>';

        if($management_element != null)
            return '<span title="'.$this->valeur_title().'">'.$valeur.'</span> - <a target="_blank" href="'.$management_element->lien_vers_element().'">'.$management_element->affiche().'</a>';

        return '<span title="'.$this->valeur_title().'">'.$valeur.'</span>';
    }
}