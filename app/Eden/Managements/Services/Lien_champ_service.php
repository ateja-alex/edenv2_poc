<?php

namespace App\Eden\Managements\Services;

use App\Eden\Variables;

class Lien_champ_service{

    public function valeurs($lien_champ, $parametres = [], $valeur_brute = false, $filtres_valeur_final = []){

        $type_element = $parametres['type_element'] ?? null;
        $filtrages = $parametres['filtrages'] ?? [];

        list($requete, $nom_jointure_type_element,$table_jointure,$modele_champ_libre) = $this->requete($lien_champ, $type_element, $filtrages, true);

        if(!empty($type_element) && !empty($parametres['elements_ids']))
            $requete = $requete->whereIn($type_element.'.id', $parametres['elements_ids']);

        $liens_champ = explode('/', $lien_champ);

        $element_valeurs = $requete->get();

        $dernier_champ = end($liens_champ);
        $valeurs = [];

        if(strpos($dernier_champ, 'table_libre|') === 0){

            foreach($element_valeurs as $element_valeur){

                foreach(json_decode($element_valeur->_valeurs, true) as $valeur){

                    $type_valeur = explode('.',str_replace('table_libre|','',$dernier_champ))[0];

                    $element = modele($type_valeur)->forceFill($valeur);

                    $management_element = management($type_valeur, $element->id, $element);

                    if($valeur_brute)
                        $valeurs[] = $element;
                    else{
                        $valeurs[] = [
                            'valeur' => $management_element->donnee_lien_champ(),
                            'element' => $element_valeur->_type_element,
                            'type' => 'table_libre',
                            'management_element' => $management_element
                        ];
                    }
                }
            }

        }
        else if($valeur_brute)
            $valeurs = $element_valeurs;
        else if(strpos($dernier_champ, 'element_table_libre_final|') === 0){

            $element_table_libre_final = explode('.',str_replace('element_table_libre_final|','',$dernier_champ));

            $type_table = $element_table_libre_final[0];

            $id_element_table = $element_table_libre_final[1] ?? null;

            $filtre = array_filter($filtres_valeur_final['element_table_libre_final'], 
                fn($element) => $element['type_element'] == $type_table)[0] ?? null;

            if(empty($filtre))
                return [];

            $type_element_precedent = $nom_jointure_type_element[$table_jointure];

            if($id_element_table != null)
                $ids_element_table_libre = [$id_element_table];
            else
                $ids_element_table_libre = modele($filtre['type_element'])
                    ->where($filtre['champ_lien_type_element'],$type_element_precedent)
                    ->get()->pluck('id');

            foreach($element_valeurs as $element_valeur){

                $management_element = management($type_element_precedent, $element_valeur->id, $element_valeur);

                foreach($ids_element_table_libre as $id_element){

                    $valeur = $management_element->{$filtre['fonction']}($id_element);

                    $valeurs[] = [
                        'valeur' => $valeur,
                        'element' => $type_table,
                        'type' => 'element_table_libre_final',
                        'type_table' => $type_table,
                        'management_element' => sizeof($liens_champ) == 1 ? null : $management_element,
                    ];

                }

            }

        }
        else{

            foreach($element_valeurs as $element_valeur){

                $management_element = management($modele_champ_libre->type_element, $element_valeur->id, $element_valeur);
                
                if($modele_champ_libre->nom_sql == 'pdf' && in_array($modele_champ_libre->type_element,Variables::$documents_gescom))
                    $element_valeur->_valeur = 'eden/element/'.$modele_champ_libre->type_element.'/'.$element_valeur->id.'/afficher_pdf';
                elseif($modele_champ_libre->type == 7 && !str_contains($element_valeur->_valeur, 'http://') && !str_contains($element_valeur->_valeur, 'https://'))
                    $element_valeur->_valeur = 'storage/'.$element_valeur->_valeur;

                $valeurs[] = [
                    'valeur' => $element_valeur->_valeur,
                    'element' => $modele_champ_libre->type_element,
                    'type' => 'champ_libre',
                    'modele_champ_libre' => $modele_champ_libre,
                    'management_element' => sizeof($liens_champ) == 1 ? null : $management_element,
                ];

            }

        }

        return $valeurs;
    }

    public function requete($lien_champ, $type_element = null, $filtrages = [], $json_final = false){

        $liens_champ = explode('/', $lien_champ);

        $index_depart = 0;

        if(empty($type_element) && strpos($liens_champ[0], 'table_libre|') === 0){
            $type_element = explode('.', str_replace('table_libre|', '', $liens_champ[0]))[0];
            $index_depart = 1;
        }

        //Ajout des inactifs notamment pour gérer les liens champs vers des éléments supprimés, afin de ne pas perdre les valeurs de ces liens champs lors la suppression des éléments liés (Utilisé pour les synchronisations externes)
        if($json_final)
            $requete = modele($type_element)->avec_inactifs();
        else
            $requete = modele($type_element);

        if($index_depart == 1 && isset($filtrages[0])){
            $filtrage = $filtrages[0];
            $structure = management('recherche_avancee',$filtrage->id, $filtrage)->structure();
            management('recherche_avancee')->applique_filtrage($structure, $requete, $type_element);
        }

        $index_jointure = 0;
        $table_jointure = $type_element;
        $jointure_champ = $table_jointure.'.id';
        $nom_jointure_type_element = [$type_element => $type_element];

        foreach($liens_champ as $index_lien => $lien_champ){

            if($index_lien < $index_depart)
                continue;

            if(strpos($lien_champ, 'table_libre|') === 0){

                $table_libre_lien = explode('.',str_replace('table_libre|', '', $lien_champ));

                $requete_join_table = modele($table_libre_lien[0])->newQuery();

                if(isset($filtrages[$index_lien])){
                    $filtrage = $filtrages[$index_lien];
                    $structure = management('recherche_avancee',$filtrage->id, $filtrage)->structure();
                    management('recherche_avancee')->applique_filtrage(
                        $structure,
                        $requete_join_table,
                        $table_libre_lien[0]
                    );
                }

                if($index_lien == sizeof($liens_champ) - 1 && $json_final){

                    $nom_sql_liaison = $table_libre_lien[1];

                    $colonnes = \DB::getSchemaBuilder()->getColumnListing($table_libre_lien[0]);

                    $json_objet = implode(', ',array_map(fn($colonne) => ("\"$colonne\", ".$table_libre_lien[0].".$colonne"),$colonnes));

                    $requete_join_table->selectRaw($nom_sql_liaison.",CONCAT('[',GROUP_CONCAT(JSON_OBJECT($json_objet) SEPARATOR ','),']') as _valeurs")
                        ->groupBy($nom_sql_liaison);

                    if($table_libre_lien[0] == 'element_piece_jointe')
                        $requete_join_table->where($table_libre_lien[0].'.type_element',$nom_jointure_type_element[$table_jointure]);

                    $requete = $requete->selectRaw($table_jointure.'.*, _valeurs, "'.$nom_jointure_type_element[$table_jointure].'" as _type_element')
                        ->joinSub($requete_join_table, $table_libre_lien[0], $table_jointure.'.id',$table_libre_lien[0].'.'.$nom_sql_liaison)
                        ->groupBy($table_jointure.'.id');
                }
                else{

                    if($index_lien == sizeof($liens_champ) - 1)
                        $index_jointure = 'final';

                    $table_jointure = 't'.$index_jointure;
                    $nom_jointure_type_element[$table_jointure] = $table_libre_lien[0];

                    if(!empty($table_libre_lien[1]))
                        $requete = $requete->joinSub($requete_join_table->select($table_libre_lien[0].'.*'), $table_jointure, $jointure_champ, $table_jointure.'.'.$table_libre_lien[1]);
                    else
                        $requete = $requete->crossJoinSub($requete_join_table->select($table_libre_lien[0].'.*'), $table_jointure);

                    $jointure_champ = $table_jointure.'.id';
                    $index_jointure++;

                }
                    
            }
            else if(strpos($lien_champ, 'element_table_libre_final|') === 0){
                $requete = $requete->select($table_jointure.'.*')
                    ->groupBy($table_jointure.'.id');
            }
            else{

                $champ_libre_lien = explode('.',$lien_champ);

                if($champ_libre_lien[1] == 'id'){
                    $jointure_champ = $table_jointure.'.id';
                }
                else{

                    $modele_champ_libre = champ_libre_modele($champ_libre_lien[0], $champ_libre_lien[1]);

                    if($modele_champ_libre->type == 10){
                        $table_jointure = 't'.$index_jointure;
                        if(table_libre_existe($modele_champ_libre->table_pivot))
                            $requete = $requete->joinSub(modele($modele_champ_libre->table_pivot)->select($modele_champ_libre->table_pivot.'.*'), $table_jointure, $jointure_champ, $table_jointure.'.cle_locale');
                        else
                            $requete = $requete->join($modele_champ_libre->table_pivot.' as '.$table_jointure, $jointure_champ, $table_jointure.'.cle_locale');
                        
                        $jointure_champ = $table_jointure.'.valeur';
                        $index_jointure++;
                    }
                    else
                        $jointure_champ = $table_jointure.'.'.$champ_libre_lien[1];

                    if($index_lien == sizeof($liens_champ) - 1){
                        $requete = $requete->select($jointure_champ.' as _valeur',
                            ($modele_champ_libre->type == 10 ? (
                                $index_jointure > 1 ? 't'.($index_jointure-2) : $type_element
                            ) : $table_jointure).'.*');

                        if($modele_champ_libre->nom_sql == 'pdf' && in_array($modele_champ_libre->type_element,Variables::$documents_gescom))
                            $requete = $requete->groupBy($table_jointure.'.id');
                        else
                            $requete = $requete->where($jointure_champ, '!=', '')
                                ->whereNotNull($jointure_champ)
                                ->groupBy($jointure_champ);
                    }
                    else{
                        $alias_table_jointure = 't'.$index_jointure;
                        $nom_jointure_type_element[$alias_table_jointure] = $modele_champ_libre->type_element_ajax;
                        $requete = $requete->joinSub(modele($modele_champ_libre->type_element_ajax)->select($modele_champ_libre->type_element_ajax.'.*'), $alias_table_jointure, $jointure_champ, $alias_table_jointure.'.id');
                        $table_jointure = $alias_table_jointure;
                        $index_jointure++;
                    }
                }
            }
        }

        return [$requete,$nom_jointure_type_element,$table_jointure,$modele_champ_libre ?? null];
    }

    public function type_element_lien_champ($lien_champ){
        return explode('.',array_last(explode('|',$lien_champ)))[0] ?? '';
    }
}