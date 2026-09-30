<?php

namespace App\Eden\Managements;

use App\Eden\Variables;

use DB;

class Colonne_liste_management {
    
    private $colonne;
    private $liste_management;
    private $table_libre;

    public function __construct($colonne, $liste_management, $table_libre){
        $this->colonne = $colonne;
        $this->liste_management = $liste_management;
        $this->table_libre = $table_libre;
    }

    public function chargement_donnees(){

        $colonne = $this->colonne;
        $type_element = $this->table_libre->type_element;

        if(!empty($colonne->index_traduction))
            $colonne->nom = strtoupper(traduction($colonne->index_traduction.'.nom'));

        if($colonne->type == 'calcul'){
            $type_element_calcul = service('lien_champ')->type_element_lien_champ($colonne->source_calcul);

            $colonne->champ_libre = !empty($colonne->champ_calcul) ? champ_libre($type_element_calcul, $colonne->champ_calcul) : null;

            $colonne->champ_libre_groupement = !empty($colonne->groupement_calcul) ? champ_libre($type_element_calcul, $colonne->groupement_calcul) : null;
        }
        else if($colonne->type == 'methode' || $colonne->type == 'champ' || !empty($colonne->methode)){
            $colonne->champ_libre = !empty($colonne->champ) ? champ_libre($this->table_libre->type_element, $colonne->champ) : null;

            $management_element = management($this->table_libre->type_element);

            $management_element->liste_id = $this->colonne->liste_libre_id;
            $management_element->management_liste = $this->liste_management;

            $colonne->management_methode = $management_element;
        }else if(!empty($this->liste_management->rapport) && $this->liste_management->rapport->type == 'requete_sql'){
            if(!empty($colonne->champ)){
                $colonne->champ_libre = champ_libre($this->table_libre->type_element, $colonne->champ);
                $colonne->champ_libre->modele->nom_sql = $colonne->valeur;
            }
            else
                $colonne->champ_libre = null;
        }
        else{

            $champs_libres = [];

            if($colonne->type == 'concatenation'){
                $valeur = $colonne->valeur;

                $matches = array();
                preg_match_all('/#([A-Za-z0-9_.|]+)#/', $valeur, $matches);
                $champs = $matches[1];
            }
            else
                $champs = [$colonne->valeur];

            foreach($champs as $champ) {

                if(str_contains($champ, '.')) {
                    list($champ_liaison, $nom_sql) = explode('.', $champ);

                    if(str_contains($champ_liaison, '|')){
                        list($champ_liaison, $type_element_champ) = explode('|', $champ_liaison);
                        $champ_liaison = champ_libre_modele($type_element, $champ_liaison);
                    }
                    else{
                        $champ_liaison = champ_libre_modele($type_element, $champ_liaison);
                        $type_element_champ = $champ_liaison->type_element_ajax;
                    }

                    if(empty($champ_liaison) || $nom_sql == 'id')
                        continue;

                    $champ_libre = champ_libre($type_element_champ, $nom_sql);
                    $champ_libre->modele->champ_liaison = $champ_liaison;
                }
                elseif($champ != 'id')
                    $champ_libre = champ_libre($type_element, $champ);

                if(empty($champ_libre))
                    continue;

                $champ_libre->modele->valeur_colonne = $champ;
                $champs_libres[] = $champ_libre;
            }

            if($colonne->type == 'concatenation')
                $colonne->champs_libres = $champs_libres;
            else
                $colonne->champ_libre = $champs_libres[0] ?? null;
        }
    }

    public function requete_colonne(&$requete, &$selects, &$joins, $filtres_colonnes_calculs){

        $colonne = $this->colonne;
        $type_element = $this->table_libre->type_element;

        if($colonne->type == 'calcul'){

            list($requete_calcul) = service('lien_champ')->requete($colonne->source_calcul, $type_element, ($filtres_colonnes_calculs['liste_libre_colonne_calcul_'.$colonne->id] ?? collect([]))->keyBy('id_cible'));

            $selects_calcul = [$type_element.'.id'];

            $calcul = '';

            if($colonne->type_calcul == 'sum')
                $calcul = 'SUM(tfinal.'.$colonne->champ_calcul.')';
            elseif($colonne->type_calcul == 'avg')
                $calcul = 'AVG(tfinal.'.$colonne->champ_calcul.')';
            else
                $calcul = 'COUNT(tfinal.id)';

            $selects_calcul[] = DB::raw($calcul.' as total');

            $group = [$type_element.'.id'];
            
            if(!empty($colonne->champ_libre_groupement)){

                if(empty($colonne->periodicite_calcul) || !in_array($colonne->champ_libre_groupement->modele->type, [4,5]))
                    $groupement = 'COALESCE(tfinal.'.$colonne->groupement_calcul.',0)';
                else{
                    $requete_calcul->whereNotNull('tfinal.'.$colonne->groupement_calcul);
                    $groupement = $this->periodicite_calcul_requete($colonne->periodicite_calcul, 'tfinal.'.$colonne->groupement_calcul);
                }

                $group[] = DB::raw($groupement);
                $selects[] = DB::raw('CONCAT(\'[{\',GROUP_CONCAT(DISTINCT CONCAT(\'"id":"\',calcul_'.$colonne->id.'.groupement_calcul,\'","valeur":"\',calcul_'.$colonne->id.'.total,\'"\') SEPARATOR \'},{\'),\'}]\') as calcul_'.$colonne->id);
                $selects_calcul[] = DB::raw($groupement.' as groupement_calcul');
            }
            else
                $selects[] = 'calcul_'.$colonne->id.'.total as calcul_'.$colonne->id;

            $requete_calcul->select($selects_calcul)->groupBy($group);

            $requete = $requete->leftJoinSub(
                $requete_calcul, 'calcul_'.$colonne->id, 'calcul_'.$colonne->id.'.id', '=', $type_element.'.id'
            );
        }
        else{

            if(empty($colonne->champ_libre) && empty($colonne->champs_libres))
                return;

            $champs_libres_colonne = $colonne->champs_libres ?? [$colonne->champ_libre];

            $champs_libres_autres_tables = collect($champs_libres_colonne)->pluck('modele')->where('type_element', '!=', $type_element);

            foreach($champs_libres_autres_tables as $champ_libre) {

                if(!isset($joins['jointures'][$champ_libre->type_element])) {
                    $joins['jointures'][$champ_libre->type_element] = array(
                        'champs_de_liaison' => array(),
                    );
                }

                $champ_liaison = $champ_libre->champ_liaison->nom_sql;

                $alias = '';

                if(!isset($joins['jointures'][$champ_libre->type_element]['champs_de_liaison'][$champ_liaison])) {
                    $joins['alias_requete_compte']++;
                    $alias = 'liaison_'.$joins['alias_requete_compte'];
                    $joins['jointures'][$champ_libre->type_element]['champs_de_liaison'][$champ_liaison] = array(
                        'alias' => $alias,
                        'modele_champ_liaison' => $champ_libre->champ_liaison,
                    );
                }
                else
                    $alias = $joins['jointures'][$champ_libre->type_element]['champs_de_liaison'][$champ_liaison]['alias'];
            
                $champ_libre->alias_champ = $alias.'_'.$champ_libre->nom_sql;
                $champ_libre->alias_table = $alias;

                $selects[] = $alias.'.'.$champ_libre->nom_sql.' as '.$champ_libre->alias_champ;

                if($champ_libre->type == 22){
                    $alias_champ_type_element = $alias.'_'.$champ_libre->contenu;
                    $champ_libre->alias_champ_type_element = $alias_champ_type_element;
                    $selects[] = $alias.'.'.$champ_libre->contenu.' as '.$alias_champ_type_element;
                }
            }
        }
    }

    public function periodicite_calcul_requete($periodicite,$champ){

        if($periodicite == 'quotidienne')
            $element_requete = 'LEFT('.$champ.', 10)';
        else if($periodicite == 'annuelle')
            $element_requete = 'CONCAT(LEFT('.$champ.', 4),"-01-01")';
        else if($periodicite == 'mensuelle')
            $element_requete = 'CONCAT(LEFT('.$champ.', 7),"-01")';
        else if($periodicite == 'semestrielle')
            $element_requete = 'CONCAT(LEFT('.$champ.', 4),"-0",IF(QUARTER('.$champ.') <= 2, 1, 7),"-01")';
        else if($periodicite == 'trimestrielle')
            $element_requete = 'CONCAT(LEFT('.$champ.', 4),"-",IF(QUARTER('.$champ.') = 4,"",0),QUARTER('.$champ.')*3-2,"-01")';
        else if($periodicite == 'hebdomadaire')
            $element_requete = 'DATE('.$champ.' - INTERVAL COALESCE(NULLIF(DAYOFWEEK('.$champ.')-2,-1),6) DAY)';

        return $element_requete;
    }

    public function application_tri_requete($requete, $sens, &$joins, $groupement = null){

        $colonne = $this->colonne;
        $type_element = $this->table_libre->type_element;

        if($colonne->type == 'calcul'){
            if($groupement !== null)
                return $requete->orderBy(DB::raw('CAST(GROUP_CONCAT(IF(calcul_'.$colonne->id.'.groupement_calcul = \''.$groupement.'\',calcul_'.$colonne->id.'.total,"") SEPARATOR \'\') as FLOAT)'), $sens);
            else
                return $requete->orderBy('calcul_'.$colonne->id.'.total', $sens);
        }
        
        if(empty($colonne->champ_libre) && empty($colonne->champs_libres)){
            return $requete->orderBy($type_element.'.'.$this->champ_valeur_id(), $sens);
        }

        foreach($colonne->champs_libres ?? [$colonne->champ_libre] as $champ_libre){
            $requete = $champ_libre->champ->application_tri_requete($requete, $sens, $joins);
        }

        return $requete;
    }

    public function traitement_post_requete($elements){

        $colonne = $this->colonne;

        if($colonne->type == 'calcul' && !empty($colonne->champ_libre_groupement)){

            $champ_groupement = $colonne->champ_libre_groupement;

            if(empty($colonne->periodicite_calcul) || !in_array($champ_groupement->modele->type, [4,5]))
                $groupement = 'COALESCE(calcul_'.$colonne->id.'.groupement_calcul,0)';
            else
                $groupement = $this->periodicite_calcul_requete($colonne->periodicite_calcul,'calcul_'.$colonne->id.'.groupement_calcul');

            $groupements_calcul = (clone $this->liste_management->requete_count_sans_filtres)->select(DB::raw('DISTINCT '.$groupement.' as groupement_calcul'))
                ->groupBy(DB::raw($groupement))
                ->whereNotNull('calcul_'.$colonne->id.'.groupement_calcul')
                ->get()
                ->pluck('groupement_calcul')->toArray();

            if(in_array($champ_groupement->modele->type,[1,20])){

                $choix_possibles = $champ_groupement->champ->valeurs_possibles;

                $groupements = collect($choix_possibles)
                    ->mapWithKeys(function($groupement_calcul, $cle){
                        return [
                            $cle => [
                                'id' => $cle,
                                'nom' => $groupement_calcul,
                            ]
                        ];
                    })->whereIn('id',$groupements_calcul)->values()->toArray();
            
            }
            else if(in_array($champ_groupement->modele->type,[4,5])){

                sort($groupements_calcul);

                $groupements = array_map(function($date){
                    return [
                        'id' => $date,
                        'nom' => date('d/m/Y', strtotime($date)),
                    ];
                }, $groupements_calcul);

            }
            else if($champ_groupement->modele->type == 42){

                $elements = modele($champ_groupement->modele->type_element_ajax)->whereIn('id',$groupements_calcul)->get();

                $groupements = $elements->map(function($element) use ($champ_groupement){
                    return [
                        'id' => $element->id,
                        'nom' => management($champ_groupement->modele->type_element_ajax,$element->id, $element)->affiche(),
                    ];
                })->toArray();

            }

            $colonne->groupements_calcul = $groupements;
        }
    }

    public function traitement_colonne($element){

        $type = $this->colonne->type;

        if(empty($type)){
            if(!empty($this->colonne->methode))
                $type = 'methode';
            else 
                $type = 'standard';
        }

        return $this->{"traitement_colonne_{$type}"}($element);
    }

    public function traitement_colonne_standard($element){

        return $this->affichage_valeur($element);
    }

    public function traitement_colonne_concatenation($element){

        $contenu = $this->colonne->valeur;

        $valeurs_remplacements = [];

        $elements_a_remplacer = [];
        preg_match_all('/#([A-Za-z0-9_.|]+)#/', $contenu, $elements_a_remplacer);

        foreach($elements_a_remplacer[1] as $nom_colonne){
            $this->colonne->champ_libre = collect($this->colonne->champs_libres)->where('modele.valeur_colonne',$nom_colonne)->first() ?? null;
            $this->colonne->valeur = $nom_colonne;
            $valeur = $this->affichage_valeur($element);
            $valeurs_remplacements['#'.$nom_colonne.'#'] = 
                is_array($valeur) ? 
                    (isset($valeur['contenus']) ? implode(' ', $valeur['contenus']) : $valeur['contenu']) :
                    $valeur;
        }

        unset($this->colonne->champ_libre);
        $this->colonne->valeur = $contenu;

        $contenu = str_replace(array_keys($valeurs_remplacements), $valeurs_remplacements, $contenu);

        if(defined('export_en_cours'))
            return $contenu;

        return [
            'type' => 'contenu',
            'contenu' => $contenu
        ];
    }

    public function traitement_colonne_champ($element){

        $affichage = $this->affichage_valeur($element);

        if(defined('export_en_cours'))
            return $affichage;

        $element_id = $element->id;

        $type_element_champ = $this->table_libre->type_element;

        $management_champ = $this->colonne->champ_libre;

        if($this->table_libre->modele_vue_sql){

            // Si la table est une vueSql de type union
            if($this->table_libre->modele_vue_sql->type_de_vue == 1) {

                $type_element_champ = null;

                $champ_origine = null;

                if (empty($management_champ->modele->nom_sql_origine))
                    return [
                        'type' => 'contenu',
                        'contenu' => traduction('valeurs_listes_libres.vue_sql.modification_champ_non_compatible')
                    ];
                else {

                    $champ_origine = $this->colonne->champ_libre->modele;

                    $this->colonne->champ = $champ_origine->nom_sql_origine;

                    $attributs = array_keys($element->getAttributes());

                    foreach ($attributs as $attribut) {

                        if (!empty($type_element_champ))
                            continue;

                        $element_champ_id = null;
                        $type_element_champ = null;

                        if ($attribut == 'type_element')
                            $element_champ_id = $element_id;
                        else if (strpos($attribut, 'lien_type_element_') !== false) {

                            preg_match('/\d+/', $attribut, $matches);

                            $match = $matches[0];

                            if (isset($element->{'lien_id_' . $match}))
                                $element_champ_id = $element->{'lien_id_' . $match};
                        }

                        if($champ_origine->type_element_origine != $element->{$attribut})
                            continue;

                        $type_element_champ = $element->{$attribut};

                        $management_champ = $this->colonne->champ_libre;

                        if (empty($element->{$attribut}) || empty($element_champ_id)) {
                            $element_id = null;
                            continue;
                        }

                        $element_id = $element_champ_id;
                    }
                }
            }
            // Si la table est une vueSql de type select
            else if($this->table_libre->modele_vue_sql->type_de_vue == 0) {
                $type_element_champ = $this->colonne->champ_libre->modele->type_element_origine;
                $this->colonne->champ = $this->colonne->champ_libre->modele->nom_sql_origine;
            }
        }

        $parametres_champ = array(
            'liste_libre_id' => $this->colonne->liste_libre_id,
            'element_id' => $element_id,
            'type_element' => $type_element_champ,
            'v_model' => in_array($type_element_champ, Variables::$documents_gescom) ? 'document' : $type_element_champ,
            'champ' => $this->colonne->champ,
        );

        if(empty($element_id))
            $parametres_champ['parametres_creation'] = $this->liste_management->parametres_creation_element_colonne_champ_vide($parametres_champ,$element);

        if((empty($parametres_champ['element_id']) && empty($parametres_champ['parametres_creation'])))
            return $affichage;
        
        $management_champ->champ->modele->desactiver_creation_a_la_volee = true;
        $management_champ->champ->colonne_champ = true;

        $management_creation = clone $management_champ->champ;
        $management_creation->modele->type_element = $type_element_champ;
        $management_creation->vmodel();

        return [
            'type' => 'champ',
            'affichage' => $affichage,
            'modification' => $management_creation->cree(),
            'type_champ' => $management_champ->champ->modele->type,
            'liste_choix' => $management_champ->champ->modele->liste_choix,
            'format_champ' => $management_champ->champ->modele->format_champ,
            'parametres' => $parametres_champ
        ];
    }

    public function traitement_colonne_methode($element){

        if(empty($this->colonne->methode) || defined('export_en_cours'))
            return '';

        $methode = $this->colonne->methode;

        if(!empty($this->colonne->arguments))
            $arguments = $this->colonne->arguments;
        else
            $arguments = 'pdf';

        if(defined('export_en_cours'))
            return $this->colonne->management_methode->$methode($element, $arguments);

        return [
            'type' => 'contenu',
            'contenu' => $this->colonne->management_methode->$methode($element, $arguments)
        ];
    }

    public function traitement_colonne_calcul($element){

        if(!empty($this->colonne->groupements_calcul)){

            if(empty($element->{'calcul_'.$this->colonne->id}))
                $valeurs_calcul = [];
            else{
                $valeurs_calcul = json_decode($element->{'calcul_'.$this->colonne->id}, true);

                foreach($valeurs_calcul as &$valeur_calcul){

                    if($this->colonne->type_calcul == 'count')
                        $valeur_calcul['valeur'] = $valeur_calcul['valeur'] ?? 0;
                    else
                        $valeur_calcul['valeur'] = $this->colonne->champ_libre->champ->affiche($valeur_calcul['valeur']);
                }
            }

            if(defined('export_en_cours'))
                $contenu = json_encode(collect($valeurs_calcul)->pluck('valeur','id')->toArray());
            else
                $contenu = $valeurs_calcul;
        }
        else{
            if($this->colonne->type_calcul == 'count')
                $contenu = $element->{'calcul_'.$this->colonne->id}  ?? 0;
            else{
                $contenu = $this->colonne->champ_libre->champ->affiche($element->{'calcul_'.$this->colonne->id});
            }
        }

        if(defined('export_en_cours'))
            return $contenu;

        return [
            'type' => 'contenu',
            'contenu' => $contenu
        ];
    }

    public function affichage_valeur($element){

        if(empty($this->colonne->champ_libre)){

            if(defined('export_en_cours'))
                $affichage =  $element->{$this->champ_valeur_id()};
            else{
                $affichage = [
                    'type' => 'contenu',
                    'contenu' => $element->{$this->champ_valeur_id()},
                    'lien' => $this->calcul_lien($element)
                ];
            }
        }
        else{
            $management_champ = $this->colonne->champ_libre->champ;

            if(defined('export_en_cours'))
                $affichage = $management_champ->affiche_export($element);
            else{
                $affichage = $management_champ->affiche_liste($element, $this->colonne);

                if(!empty($affichage))
                    $affichage['lien'] = $this->calcul_lien($element);
            }
        }

        return $affichage;
    }

    public function calcul_lien($element){

        if($this->colonne->lien_vers_element != 1 && $this->colonne->valeur != 'id')
            return null;

        if(!empty($this->colonne->lien_vers_autre_element)){

            $id_element = $element->{$this->colonne->lien_vers_autre_element};

            if(!empty($id_element)){

                $champ_libre = champ_libre_modele($this->table_libre->type_element, $this->colonne->lien_vers_autre_element);

                if(!empty($champ_libre->type_element_origine))
                    $champ_libre->contenu = champ_libre_modele($champ_libre->type_element_origine,$champ_libre->nom_sql_origine)->contenu;

                if($champ_libre->type == 42)
                    $type_lien = $champ_libre->type_element_ajax;
                else if($champ_libre->type == 22)
                    $type_lien = $element->{$champ_libre->contenu};

                $lien = [
                    'type' => 'redirection',
                    'redirection' => management($type_lien)->lien_vers_element($id_element),
                ];
            }
            else
                $lien = null;
        }
        else
            $lien = $this->lien_vers_element($element);

        return $lien;
    }

    public function lien_vers_element($element){

        $type_element_lien = service('vue_sql')->recupere_type_element($this->table_libre->type_element);

        if(strpos($this->liste_management->liste_libre->id_rapport, 'intranet_') === 0 || $this->colonne->afficher_formulaire_element == 1)
            $lien = ['type' => 'detail'];
        else if($this->table_libre->fiche == 1 || in_array($this->table_libre->type_element, Variables::$documents_gescom))
            $lien = [
                'type' => 'redirection',
                'redirection' => management($type_element_lien)->lien_vers_element($element->id),
            ];
        else
            $lien = ['type' => 'detail'];

        return $lien;
    }

    public function champ_valeur_id(){

        if(str_contains($this->colonne->valeur, '.')){
            list($champ_valeur, $osef) = explode('.', $this->colonne->valeur);

            if(str_contains($champ_valeur, '|'))
                list($champ_valeur, $osef) = explode('|', $champ_valeur);
        }
        else
            $champ_valeur = $this->colonne->valeur;

        return $champ_valeur;
    }
}