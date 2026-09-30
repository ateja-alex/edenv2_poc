<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Champs\Champ_recherche_element;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use Illuminate\Database\Eloquent\Collection;

class Recherche_avancee_management extends Element_management {

    private $managements_questionnaire = [];

    public ?object $requete_avant_recherche_avancee = null;

    /**
     * @return array
     *
     * Récupére la structure d'une recherche avancée
     */
    public function structure($sans_id = false){

        $this->blocs_existants = modele('recherche_avancee_bloc')
            ->select('id','recherche_avancee_id','recherche_avancee_bloc_id','operateur','exclu')
            ->where('recherche_avancee_id', $this->modele->id)
            ->get()->keyBy('id');

        $this->filtres_existants = modele('recherche_avancee_filtre')
            ->select('id','recherche_avancee_id','recherche_avancee_bloc_id',
                'type_element','champ_liaison','valeurs','nom_sql','operateur')
            ->where('recherche_avancee_id', $this->modele->id)
            ->get()->keyBy('id');

        $blocs = array_values($this->blocs_existants->where('recherche_avancee_bloc_id',null)->toArray());

        foreach($blocs as &$bloc){
            $this->recuperer_informations_bloc($bloc, $sans_id);
        }

        return $blocs;
    }

    /**
     * @param $bloc
     * @return void
     *
     * Fonction récursive qui permet de récupérer les blocs et les filtres
     *
     */
    public function recuperer_informations_bloc(&$bloc, $sans_id = false){

        $bloc['blocs'] = array_values($this->blocs_existants->where('recherche_avancee_bloc_id',$bloc['id'])->toArray());

        $filtres = $this->filtres_existants->where('recherche_avancee_bloc_id',$bloc['id']);

        if($sans_id)
            $filtres = $filtres->map(function ($filtre) {
                return collect($filtre)->except(['id','recherche_avancee_id','recherche_avancee_bloc_id'])->toArray();
            });

        $bloc['filtres'] = array_values($filtres->toArray());

        foreach($bloc['filtres'] as &$filtre){
            $filtre['valeurs'] = isset($filtre['valeurs']) ? json_decode($filtre['valeurs'],true) : null;
        }

        foreach($bloc['blocs'] as &$bloc){
             $this->recuperer_informations_bloc($bloc);
        }

        if($sans_id)
            $bloc = collect($bloc)->except(['id','recherche_avancee_id','recherche_avancee_bloc_id'])->toArray();

    }

    /**
     * @param $modifications
     * @param $modele
     * @return string|true
     *
     * Gestion de la structure lors de l'enregistrement d'une recherche avancée
     *
     */
    public function enregistre($modifications = array(), $modele = false){

        if(isset($modifications['structure'])) {
            $this->structure = $modifications['structure'];

            unset($modifications['structure']);
        }
        else if(!empty($this->modele->id))
            $this->supprime();

        return parent::enregistre($modifications, $modele);
    }

    /**
     * @param $modele
     * @param $modele_avant
     * @param $modifications
     * @return void
     *
     * Gestion de la structure lors de l'enregistrement d'une recherche avancée
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications){
        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        if(isset($this->structure))
            $this->enregistrement_structure($this->structure);

        if($this->modele->type == 'filtres_appliques')
            Liste_libre_management::generer_fichier_migration_liste_libre($this->modele->id_cible);
    }

    /**
     * @param $modele
     * @return bool
     *
     * Gestion de la suppresion des filtres et des blocs lors de la suppression d'une recherche avancée
     *
     */
    public function supprime($modele = false){

        $blocs_existants = modele('recherche_avancee_bloc')
            ->where('recherche_avancee_id', $this->modele->id)
            ->get();

        $filtres_existants = modele('recherche_avancee_filtre')
            ->where('recherche_avancee_id', $this->modele->id)
            ->get();

        foreach($blocs_existants as $bloc){
            management('recherche_avancee_bloc',$bloc->id,$bloc)->supprime();
        }

        foreach($filtres_existants as $filtre){
            management('recherche_avancee_filtre',$filtre->id,$filtre)->supprime();
        }

        return parent::supprime($modele);
    }

    /**
     * @param $blocs
     * @param $bloc_parent
     * @return void
     *
     * Gére l'enregistrement d'une structure de recherche avancée dans les tables
     * recherche_avancee_bloc et recherche_avancee_filtre
     *
     */
    public function enregistrement_structure($blocs,$bloc_parent = null){

        if($bloc_parent == null) {
            $this->blocs_existants = modele('recherche_avancee_bloc')
                ->where('recherche_avancee_id', $this->modele->id)
                ->get()->keyBy('id');

            $this->filtres_existants = modele('recherche_avancee_filtre')
                ->where('recherche_avancee_id', $this->modele->id)
                ->get()->keyBy('id');
        }

        foreach($blocs as $bloc){

            $management_bloc = null;

            // On regarde si le bloc existe déjà en bdd
            if(!empty($bloc['id'])){

                $modele = $this->blocs_existants[$bloc['id']] ?? null;

                if($modele !== null) {
                    $management_bloc = management('recherche_avancee_bloc', $modele->id, $modele);
                    $this->blocs_existants->forget($bloc['id']);
                }
                else
                    $management_bloc = management('recherche_avancee_bloc');
            }
            else
                $management_bloc = management('recherche_avancee_bloc');

            // On enregistre le bloc
            $management_bloc->enregistre([
                'recherche_avancee_id' => $this->modele->id,
                'recherche_avancee_bloc_id' => $bloc_parent,
                'operateur' => $bloc['operateur'],
                'exclu' => $bloc['exclu'] ?? 0
            ]);

            $id_bloc = $management_bloc->modele->id;

            // On enregistre ensuite les filtres associés au bloc
            if(!empty($bloc['filtres'])){

                foreach($bloc['filtres'] as $filtre){

                    $management_filtre = management('recherche_avancee_filtre');

                    if(!empty($filtre['id'])){

                        $modele = $this->filtres_existants[$filtre['id']] ?? null;

                        if($modele !== null) {
                            $management_filtre = management('recherche_avancee_filtre', $modele->id, $modele);
                            $this->filtres_existants->forget($filtre['id']);
                        }
                    }

                    $management_filtre->enregistre([
                        'recherche_avancee_id' => $this->modele->id,
                        'recherche_avancee_bloc_id' => $id_bloc,
                        'type_element' => $filtre['type_element'],
                        'nom_sql' => $filtre['nom_sql'],
                        'champ_liaison' => $filtre['champ_liaison'] ?? null,
                        'valeurs' => isset($filtre['valeurs']) ? json_encode($filtre['valeurs']) : null,
                        'operateur' => $filtre['operateur'] ?? 0
                    ]);
                }

            }

            if(!empty($bloc['blocs']))
                $this->enregistrement_structure($bloc['blocs'],$id_bloc);
        }

        // Une fois terminée on supprimer les blocs et les filtres qui ne sont plus présents
        if($bloc_parent == null){

            foreach($this->blocs_existants as $bloc){
                management('recherche_avancee_bloc',$bloc->id,$bloc)->supprime();
            }

            foreach($this->filtres_existants as $filtre){
                management('recherche_avancee_filtre',$filtre->id,$filtre)->supprime();
            }
        }
    }

    public function applique_filtrage($structure,&$requete,$type_element,&$joins = ['jointures' => [], 'alias_requete_compte' => 0]){

        $this->requete_avant_recherche_avancee = clone $requete;

        $this->recherche_avancee_gestion_liaisons($structure,$requete,$type_element,$joins);

        $requete = $requete->where(function($sous_requete) use ($structure, $type_element, $joins) {
            $this->recherche_avancee_recursivite($sous_requete,$structure,$type_element,$joins);
        });
    }

    /**
    *
    * Gére la récursivité de la recherche avancée
    *
    */
    public function recherche_avancee_recursivite(&$requete,$blocs_listes,$type_element,$joins) {

        foreach($blocs_listes as $bloc) {

            if ($bloc['operateur'] == 0 || $bloc['operateur'] == 1) {

                if($bloc['exclu'] == 1){

                    $type_requete = $bloc['operateur'] == 0 ? "whereNotIn" : "orWhereNotIn";

                    $sous_requete = (clone $this->requete_avant_recherche_avancee)->select($type_element . '.id')->where(function ($sous_where) use ($bloc, $type_element,$joins) {

                        if(!empty($bloc["filtres"])) {
                            foreach ($bloc["filtres"] as $filtre) {
                                $sous_where = $this->recherche_avancee_applique_filtre($filtre,$sous_where,$joins);
                            }
                        }

                        if(!empty($bloc["blocs"]))
                            $this->recherche_avancee_recursivite($sous_where,$bloc['blocs'],$type_element,$joins);
                    });

                    $requete->{$type_requete}($type_element . '.id', $sous_requete);
                }
                else{
                    $type_requete = $bloc['operateur'] == 0 ? "where" : "orWhere";

                    $requete->{$type_requete}(function ($sous_where) use ($bloc, $type_element,$joins) {

                        if (!empty($bloc["filtres"])){
                            foreach ($bloc["filtres"] as $filtre) {
                                $sous_where = $this->recherche_avancee_applique_filtre($filtre,$sous_where,$joins);
                            }
                        }

                        if(!empty($bloc["blocs"]))
                            $this->recherche_avancee_recursivite($sous_where,$bloc['blocs'],$type_element,$joins);
                    });
                }
            }
        }

    }

    public function recherche_avancee_applique_filtre($filtre,$where,$joins){

        $type_element_filtre = $filtre['type_element'];

        $nom_sql = $filtre['nom_sql'];

        $valeurs = $filtre['valeurs'];

        $operateur = $filtre['operateur'] == 0 ? "where" : "orWhere";

        if(preg_match('/questionnaire_(\d+)/',$type_element_filtre,$match)) {

            if(empty($this->managements_questionnaire[$match[1]]))
                $this->managements_questionnaire[$match[1]] = management('questionnaire',$match[1]);

            $management = $this->managements_questionnaire[$match[1]];

            return $where->{$operateur}(function($requete) use ($management, $filtre, $joins){
                $management->applique_filtre_sur_requete($filtre, $requete, $joins['jointures'][$type_element_filtre]['champs_de_liaison'][$filtre['champ_liaison'].'|'.$filtre['nom_sql']]['alias']);
            });
        }

        if($nom_sql == 'id'){
            $champ = new Champ_recherche_element((object)[
                'id_cl' => 0,
                'type_element' => $type_element_filtre,
                'nom_sql' => 'id',
                'lecture_seule' => 0,
                'type_element_ajax' => $type_element_filtre,
                'type_element_origine' => $type_element_filtre
            ], null);
        }
        else{
            $champ_management = champ_libre($type_element_filtre, $nom_sql);
            $champ = $champ_management->champ;
        }

        if (!empty($filtre['champ_liaison'])) {
            $champ->modele->alias_champ = $joins['jointures'][$type_element_filtre]['champs_de_liaison'][$filtre['champ_liaison']]['alias'] . '.' . $nom_sql;
            $champ->modele->alias_table = $joins['jointures'][$type_element_filtre]['champs_de_liaison'][$filtre['champ_liaison']]['alias'];
        }

        return $where->{$operateur}(function($requete) use ($valeurs,$champ){
            $champ->applique_filtre_sur_requete($valeurs, $requete);
        });
    }

    public function recherche_avancee_gestion_liaisons($blocs_listes,&$requete,$type_element,&$joins){

        foreach ($blocs_listes as $bloc) {

            if (!empty($bloc['filtres'])) {
                foreach ($bloc['filtres'] as $filtre) {

                    if(empty($filtre['champ_liaison']))
                        continue;

                    $question = false;

                    if(preg_match('/questionnaire_(\d+)/',$filtre['type_element'],$match)) {
                        $champ_de_liaison = $filtre['champ_liaison'] . '|' . $filtre['nom_sql'];
                        $question = true;
                    }
                    else
                        $champ_de_liaison = $filtre['champ_liaison'];

                    $type_element_tmp = $filtre['type_element'];

                    if (!isset($joins['jointures'][$type_element_tmp])) {

                        $joins['jointures'][$type_element_tmp] = array(
                            'champs_de_liaison' => array(),
                        );
                    }

                    if (!isset($joins['jointures'][$type_element_tmp]['champs_de_liaison'][$champ_de_liaison]) && $champ_de_liaison != null) {
                        $joins['alias_requete_compte']++;
                        $joins['jointures'][$type_element_tmp]['champs_de_liaison'][$champ_de_liaison] = array(
                            'alias' => 'liaison_' . $joins['alias_requete_compte']
                        );

                        if($question) {
                            preg_match('/question_(\d+)/',$filtre['nom_sql'],$match_question);
                            $id_question = $match_question[1];

                            $requete = $requete->leftJoin('questionnaire_reponse AS liaison_' . $joins['alias_requete_compte'], function ($join) use ($type_element,$joins, $filtre, $id_question) {
                                $join->on('liaison_' . $joins['alias_requete_compte'].'.repondant_id', $type_element . '.' . $filtre['champ_liaison']);
                                $join->where('liaison_' . $joins['alias_requete_compte'].'.question_id', $id_question);
                            });
                        }
                        else
                            $requete = $requete->leftJoin($type_element_tmp. ' AS liaison_'.$joins['alias_requete_compte'],$type_element . '.' . $champ_de_liaison,'liaison_'.$joins['alias_requete_compte'] . '.id');

                    }
                }
            }

            if (!empty($bloc["blocs"]))
                $this->recherche_avancee_gestion_liaisons($bloc['blocs'], $requete,$type_element,$joins);
        }
    }

    /**
     * 
     * Remplacement des liens champs notamment dans les champs de type 42
     * 
     */
    public function remplacement_lien_champ($structure,$modele){

        foreach($structure as &$bloc){

            foreach($bloc['filtres'] as &$filtre){

                if(is_array($filtre['valeurs']))
                    continue;

                if(strpos($filtre['valeurs'],'lien_champ|') === 0){

                    $lien_champ = str_replace('lien_champ|','',$filtre['valeurs']);

                    $liens_champs = explode('/', $lien_champ);

                    $base_lien_champ = array_shift($liens_champs);
                    $type_element = explode('.',$base_lien_champ)[0];
                    $nom_sql = explode('.',$base_lien_champ)[1];

                    $champ_libre_modele = champ_libre_modele($type_element, $nom_sql);

                    $valeur = $modele[$nom_sql] ?? [];

                    if($nom_sql == 'id' && empty($valeur)){
                        $filtre['valeurs'] = 'vide';
                        continue;
                    }

                    if(empty($valeur) || empty($liens_champs)){
                        $filtre['valeurs'] = is_array($valeur) ? $valeur : [$valeur];
                        continue;
                    }

                    $valeurs = service('lien_champ')
                            ->valeurs(implode('/',$liens_champs), [
                                'type_element' => $champ_libre_modele->type_element_ajax,
                                'elements_ids' => [$valeur]
                            ], [], true);

                    $valeurs = $valeurs instanceof Collection ? 
                        $valeurs->pluck('_valeur')->toArray() : [$valeurs[0]['valeur']];

                    $filtre['valeurs'] = $valeurs;
                }
            }

            if(!empty($bloc['blocs']))
                $bloc['blocs'] = $this->remplacement_lien_champ($bloc['blocs'], $modele);
        }

        return $structure;
    }
}