<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;

class Parametrage_balise_publipostage_management extends Element_management{

    public function enregistre($modifications = array(), $modele = false){

        $filtrages_par_boucle = false;

        if(isset($modifications['filtrages'])){
            $filtrages_par_boucle = $modifications['filtrages'] ?? [];

            $filtrages_par_boucle = array_map(function($filtrage){
                return json_decode($filtrage, true);
            },$filtrages_par_boucle);

            unset($modifications['filtrages']);
        }
        
        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true || $filtrages_par_boucle === false)
            return $retour;

        $recherches_avancees = modele('recherche_avancee')
            ->where('type', $this->_type_element.'_'.$this->modele->id)
            ->get()->keyBy('id_cible');

        foreach($filtrages_par_boucle as $filtrages_boucle){

            foreach($filtrages_boucle['filtrages'] as $filtrage){

                $id_cible = $filtrages_boucle['id_boucle'].'_'.$filtrage['id_cible'];
                $type_element = $filtrage['type_element'];
                $structure = $filtrage['structure'];

                if(empty($structure))
                    continue;

                if(isset($recherches_avancees[$id_cible])){
                    $recherche_avancee = $recherches_avancees[$id_cible];
                    $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);
                    unset($recherches_avancees[$id_cible]);
                }
                else
                    $management_recherche_avancee = management('recherche_avancee');

                $management_recherche_avancee->enregistre([
                    'type' => $this->_type_element.'_'.$this->modele->id,
                    'type_element' => $type_element,
                    'id_cible' => $id_cible,
                    'structure' => $structure
                ]);
            }
        }

        foreach($recherches_avancees as $recherche_avancee){
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

        return true;
    }

    public function publipostage($elements_ids, $type_element, $valeur = false,$valeurs_publiposter = [],$base_id = '', $recherches_avancees = false){

        if($recherches_avancees === false){
            $recherches_avancees = modele('recherche_avancee')
                ->where('type', $this->_type_element.'_'.$this->modele->id)
                ->get()->keyBy('id_cible');
        }

        if($valeur === false)
           $valeur = $this->modele->valeur;

        $valeur_publipostee = '';
        $index_curseur = 0;
        $publipostage_service = service('publipostage');

        $pattern = '/\{\{@(boucle|condition)_' . preg_quote($base_id, '/') . '(\d+)\(([^)]*)\)\}\}/';
        preg_match_all($pattern, $valeur, $matches, PREG_SET_ORDER);

        foreach ($matches as $ouverture) {

            $type = $ouverture[1];
            $id_complet = $base_id . $ouverture[2];
            $id = intval($ouverture[2]);
            $parametre = $ouverture[3];

            $index_debut = strpos($valeur, $ouverture[0]);
            $index_debut_interieur = $index_debut + strlen($ouverture[0]);
            $balise_fin = '{{@fin_'.$type.'_'.$id_complet.'}}';
            $index_debut_balise_fin = strpos($valeur, $balise_fin, $index_debut_interieur);

            if ($index_debut_balise_fin === false) {
                continue;
            }

            $valeur_avant =  $index_debut == 0 ? '' : substr($valeur, $index_curseur, $index_debut - $index_curseur - 1);

            if($valeur_avant != '')
                $valeur_publipostee .= $publipostage_service->publipostage_texte($valeur_avant, $type_element, $elements_ids, $valeurs_publiposter);

            $valeur_bloc = substr(
                $valeur,
                $index_debut_interieur + 1,
                $index_debut_balise_fin - $index_debut_interieur - 2
            );

            if($type == 'condition'){

                if($this->evaluation_condition($parametre, $type_element, $elements_ids)){
                    $valeur_publipostee_bloc = $this->publipostage($elements_ids, $type_element, $valeur_bloc ,$valeurs_publiposter, $id_complet.'_', $recherches_avancees);

                    if($valeur_publipostee_bloc != ''){

                        if(!str_ends_with($valeur_publipostee_bloc, '>') && !str_starts_with($valeur_publipostee_bloc, '<'))
                            $valeur_publipostee_bloc = '<p>'.$valeur_publipostee_bloc.'</p>';

                        else if(!str_starts_with($valeur_publipostee_bloc, '<') && $valeur_avant != '')
                            $valeur_publipostee_bloc = '<br>'.$valeur_publipostee_bloc;

                        else if(!str_ends_with($valeur_publipostee_bloc, '>'))
                            $valeur_publipostee_bloc .= '<br>';

                        $valeur_publipostee .= $valeur_publipostee_bloc;
                    }
                }
            }
            else{

                $type_boucle = $parametre == '#elements_publipostes' ? 'elements_publipostes' : 'lien_champ';
                $lien_champ = $type_boucle == 'lien_champ' ? $parametre : null;
                $type_element_bloc = $type_element;

                if($type_boucle == 'lien_champ'){

                    $recherches_avancees_blocs = [];

                    foreach ($recherches_avancees as $recherche) {

                        $recherche = clone $recherche;
                        if (strpos($recherche->id_cible, $base_id.$id) !== 0) continue;

                        $id_cible = str_replace($base_id.$id.'_', '', $recherche['id_cible']);

                        if (strpos($id_cible, '_') === false) {
                            $recherche->id_cible = $id_cible;
                            $recherches_avancees_blocs[$id_cible] = $recherche;
                        }
                    }

                    $elements = service('lien_champ')->valeurs($lien_champ, [
                        'type_element' => $type_element,
                        'elements_ids' => $elements_ids,
                        'filtrages' => $recherches_avancees_blocs,
                    ], true);

                    $elements_ids = collect($elements)->pluck('id')->toArray();

                    if(!empty($elements_ids))
                        $type_element_bloc = $elements[0]->getTable();
                }

                foreach($elements_ids as $element_id){
                    $valeur_publipostee_bloc = $this->publipostage([$element_id], $type_element_bloc, $valeur_bloc ,[], $id_complet.'_', $recherches_avancees);

                    if($valeur_publipostee_bloc != ''){

                        if(!str_ends_with($valeur_publipostee_bloc, '>') && !str_starts_with($valeur_publipostee_bloc, '<'))
                            $valeur_publipostee_bloc = '<p>'.$valeur_publipostee_bloc.'</p>';

                        else if(!str_starts_with($valeur_publipostee_bloc, '<') && $valeur_avant != '')
                            $valeur_publipostee_bloc = '<br>'.$valeur_publipostee_bloc;

                        else if(!str_ends_with($valeur_publipostee_bloc, '>'))
                            $valeur_publipostee_bloc .= '<br>';

                        $valeur_publipostee .= $valeur_publipostee_bloc;
                    }
                }
            }

            $index_curseur = $index_debut_balise_fin + strlen($balise_fin) + 1;
        }

        $valeur_apres =  $index_curseur == strlen($valeur) ? '' : substr($valeur, $index_curseur);

        if($valeur_apres != '')
            $valeur_publipostee .= $publipostage_service->publipostage_texte($valeur_apres, $type_element, $elements_ids, $valeurs_publiposter);

        return $valeur_publipostee;

    }

    public function evaluation_condition($condition, $type_element, $elements_ids){

        // Sécurité : Interdire les caractères et mots clefs dangereux
        $blacklist = [
            'require', 'include', 'system', 'exec', 'shell_exec', 'passthru', 'assert',
            'eval', 'phpinfo', 'curl', 'file_get', 'fopen', 'file_put', 'glob',
            'mysqli', 'PDO', '->', '__', ';', '$', '\'', '"', '.', '::', '\\'
        ];

        foreach ($blacklist as $mot_blackliste) {
            if (stripos($condition, $mot_blackliste) !== false)
                return false;
        }

        $valeurs_a_publiposter = [];

        $regex = '/\{\{\s*([a-zA-Z0-9_\s\[\]\#\/]+)\s*\}\}/';

        preg_match_all($regex, $condition, $matches);

        $valeurs_a_publiposter = $matches[1] ?? [];

        $valeurs_publiposter = array();

        foreach($valeurs_a_publiposter as $index => $valeur_a_publiposter){

            if($valeur_a_publiposter == '#elements_publipostes_multiple')
                $valeurs_publiposter[$valeur_a_publiposter] = sizeof($elements_ids) > 1 ? 'true' : 'false';

            if($valeur_a_publiposter == '#elements_publipostes_unique')
                $valeurs_publiposter[$valeur_a_publiposter] = sizeof($elements_ids) == 1 ? 'true' : 'false';
        }

        $valeurs_a_publiposter_champ = array_filter($valeurs_a_publiposter, function($valeur) {
            return !in_array($valeur,['#elements_publipostes_multiple', '#elements_publipostes_unique']);
        });

        if(!empty($valeurs_a_publiposter_champ))
            $valeurs_publiposter = array_merge($valeurs_publiposter ,service('publipostage')->publipostage($valeurs_a_publiposter_champ, $type_element, $elements_ids, true));

        foreach($matches[0] as $index => $match){
            $condition = str_replace($match, $valeurs_publiposter[$valeurs_a_publiposter[$index]] ?? '', $condition);
        }

        return eval('return '.$condition.';');
    }
    
}