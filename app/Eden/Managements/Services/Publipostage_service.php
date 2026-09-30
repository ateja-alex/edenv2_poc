<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Facades\Schema;

class Publipostage_service {

    public function publipostage($valeurs_a_publiposter, $type_element, $element_ids, $valeur_brute = false){

        $selects = [];
        $joins = [];
        $index_alias = 0;
        $champs_libres = [];
        $autres_variables_a_publiposter = [];
        $valeurs_publipostes = [];
        $valeurs_multiples = [];

        foreach($valeurs_a_publiposter as $valeur_a_publiposter){

            if($valeur_a_publiposter == '#maintenant'){
                $valeurs_publipostes[$valeur_a_publiposter] = date('d/m H:i:s');
                continue;
            }

            $base_liaison = '';

            $liaisons = explode('[', $valeur_a_publiposter);

            $liaison_precedente = [
                'alias' => $type_element,
                'type_element' => $type_element
            ];
            
            foreach($liaisons as $index => $liaison){

                $liaison = str_replace(']', '', $liaison);

                $modele_champ_libre = champ_libre_modele($liaison_precedente['type_element'],$liaison);
                $champ_libre = !empty($modele_champ_libre) ? champ_libre($liaison_precedente['type_element'],$liaison) : null;

                if($index == sizeof($liaisons) - 1 && (empty($modele_champ_libre) || $modele_champ_libre->type != 10)){
                    if(strpos($liaison, '#') === 0){
                        $autres_variables_a_publiposter[] = [
                            'liaison' => $liaison,
                            'valeur_a_publiposter' => $valeur_a_publiposter,
                            'type_element' => $liaison_precedente['type_element']
                        ];
                        $selects[] = "GROUP_CONCAT( DISTINCT ".$liaison_precedente['alias'].".id SEPARATOR '|') as `".$valeur_a_publiposter."`";
                    }
                    else{
                        $champs_libres[$valeur_a_publiposter] = $champ_libre;
                        $selects[] = "GROUP_CONCAT( DISTINCT ".$liaison_precedente['alias'].".".$liaison." SEPARATOR '|') as `".$valeur_a_publiposter."`";
                    }
                }
                else {

                    if($base_liaison != '')
                        $base_liaison .= '|';

                    $base_liaison .= $liaison;

                    if(isset($joins[$base_liaison])){
                        if($modele_champ_libre->type == 10){
                            $base_liaison.= '|pivot';

                            if(!in_array($valeur_a_publiposter, $valeurs_multiples))
                                $valeurs_multiples[] = $valeur_a_publiposter;

                            if($index == sizeof($liaisons) - 1){
                                $champs_libres[$valeur_a_publiposter] = $champ_libre;
                                $selects[] = "GROUP_CONCAT( DISTINCT ".$liaison_precedente['alias'].".valeur SEPARATOR ',') as `".$valeur_a_publiposter."`";
                            }
                        }

                        $liaison_precedente = $joins[$base_liaison];
                    }
                    else{
                        $nom_sql = $modele_champ_libre->nom_sql;

                        if($modele_champ_libre->type == 10){
                            $index_alias++;
                            $alias_liaison_nouveau = 't_'.$index_alias;
                            $liaison_precedente = [
                                'alias' => $alias_liaison_nouveau,
                                'parent' => $liaison_precedente['alias'],
                                'type_element' => $modele_champ_libre->table_pivot,
                                'nom_sql' => 'id',
                                'index' => 'cle_locale',
                            ];
                            $joins[$base_liaison] = $liaison_precedente;

                            $nom_sql = 'valeur';

                            $base_liaison.= '|pivot';

                            if(!in_array($valeur_a_publiposter, $valeurs_multiples))
                                $valeurs_multiples[] = $valeur_a_publiposter;

                            if($index == sizeof($liaisons) - 1){
                                $champs_libres[$valeur_a_publiposter] = $champ_libre;
                                $selects[] = "GROUP_CONCAT( DISTINCT ".$liaison_precedente['alias'].".valeur SEPARATOR '|') as `".$valeur_a_publiposter."`";
                            }
                        }

                        if($modele_champ_libre->type == 42 || $modele_champ_libre->type_reference == 42){
                            $index_alias++;
                            $alias_liaison_nouveau = 't_'.$index_alias;
                            $liaison_precedente = [
                                'alias' => $alias_liaison_nouveau,
                                'parent' => $liaison_precedente['alias'],
                                'type_element' => $modele_champ_libre->type_element_ajax,
                                'nom_sql' => $nom_sql,
                                'index' => 'id',
                            ];
                            $joins[$base_liaison] = $liaison_precedente;
                        }
                    }
                }
            }
        }

        if(empty($champs_libres) && empty($autres_variables_a_publiposter))
            return $valeurs_publipostes;

        $requete = modele($type_element)->selectRaw(implode(',',$selects))
            ->whereIn($type_element.'.id', $element_ids)
            ->groupBy($type_element.'.id');

        foreach($joins as $liaison){
            $requete->leftJoin($liaison['type_element'].' as '.$liaison['alias'], function($join) use ($liaison) {
                $join->on($liaison['parent'].'.'.$liaison['nom_sql'], $liaison['alias'].'.'.$liaison['index']);

                if(Schema::hasColumn($liaison['type_element'], 'inactif'))
                    $join->whereRaw('COALESCE('.$liaison['alias'].'.inactif,0) = 0');
            });
        }

        $elements = $requete->get()->toArray();

        foreach($champs_libres as $valeur_a_publiposter => $champ_libre){

            $elements_champs = array_filter($elements, function($element) use ($valeur_a_publiposter) {
                return !empty($element[$valeur_a_publiposter]);
            });

            if(!$valeur_brute)
                $elements_champs = array_map(function($element) use ($valeur_a_publiposter, $champ_libre, $valeurs_multiples) {

                    if(in_array($valeur_a_publiposter, $valeurs_multiples)){
                        if($champ_libre->modele->type == 10)
                            return $champ_libre->champ->affiche(explode('|', $element[$valeur_a_publiposter]));
                        else
                            return implode(', ', array_map(function($valeur) use ($champ_libre) {
                                return $champ_libre->champ->affiche($valeur);
                            }, explode('|', $element[$valeur_a_publiposter])));
                    }
                    else
                        return $champ_libre->champ->affiche($element[$valeur_a_publiposter]);
                }, $elements_champs);

            $valeurs_publipostes[$valeur_a_publiposter] = implode(', ',array_unique($elements_champs));
        }

        foreach($autres_variables_a_publiposter as $balise_a_publiposter){

            $valeur_a_publiposter = $balise_a_publiposter['valeur_a_publiposter'];
            $liaison = $balise_a_publiposter['liaison'];
            $type_element = $balise_a_publiposter['type_element'];

            $elements = array_filter($elements, function($element) use ($valeur_a_publiposter) {
                return !empty($element[$valeur_a_publiposter]);
            });

            $elements = array_map(function($element) use ($valeur_a_publiposter) {
                return $element[$valeur_a_publiposter];
            }, $elements);

            $elements = array_unique(explode(',',implode(',', $elements)));

            if($liaison == '#id')
                $valeurs_publipostes[$valeur_a_publiposter] = implode(', ', $elements);
            else if($liaison == '#lien_element')
                $valeurs_publipostes[$valeur_a_publiposter] =  implode(', ', array_map(function($element) use ($type_element) {
                    return management($type_element, $element)->affiche_lien();
                }, $elements));
            else if($liaison == '#url_lien_element')
                $valeurs_publipostes[$valeur_a_publiposter] =  implode(', ', array_map(function($element) use ($type_element) {
                    return management($type_element, $element)->lien_vers_element();
                }, $elements));
            else{
                $balise_id = str_replace('#balise_','',explode('/', $liaison)[0]);

                $balise_parametrage = modele('parametrage_balise_publipostage')->find($balise_id);

                if(empty($balise_parametrage))
                    continue;

                $valeurs_publipostes[$valeur_a_publiposter] = management('parametrage_balise_publipostage',$balise_id,$balise_parametrage)
                    ->publipostage($elements,$type_element);
            }
        }

        return $valeurs_publipostes;
    }

    public function publipostage_texte($texte, $type_element, $elements_ids, &$valeurs_publiposter = []){

        $valeurs_a_publiposter = [];

        $regex = '/\{\{\s*([a-zA-Z0-9_\s\[\]\#\/]+)\s*\}\}/';

        preg_match_all($regex, $texte, $matches);

        $valeurs_a_publiposter = $matches[1] ?? [];

        if(empty($valeurs_a_publiposter))
            return $texte;

        $valeurs_publiposter = $this->publipostage($valeurs_a_publiposter, $type_element, $elements_ids);

        foreach($matches[0] as $index => $match){
            $texte = str_replace($match, $valeurs_publiposter[$valeurs_a_publiposter[$index]] ?? '', $texte);
        }

        return $texte;
    }
}