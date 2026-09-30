<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;

class Import_sur_mesure_management extends Element_management{

    /**
     * @return mixed
     *
     * On surchage modéle par défaut pour avoir des array à la place de text
     *
     */
    public function modele_par_defaut(){

        $modele_par_defaut = parent::modele_par_defaut();

        $modele_par_defaut['tables_jointes'] = [];
        $modele_par_defaut['champs'] = [];
        $modele_par_defaut['valeurs_par_defaut'] = [];
        $modele_par_defaut['ordre_lancement_import'] = [];

        return $modele_par_defaut;
    }

    /**
     * @return mixed
     *
     * Permet de récupérer le modéle d'un import avec les champs textes décodés en array et la totalité des valeurs par défaut
     *
     */
    public function modele_formate(){

        $modele = clone $this->modele;
        $modele->tables_jointes = $this->tables_jointes();
        $modele->champs = $this->champs();

        // On enregistre que les valeurs par défaut remplis, mais pour l'affichage on a besoin de toutes les valeurs par défaut
        $modele->valeurs_par_defaut = $this->valeurs_par_defaut();
        $modele->valeurs_par_defaut_remplis = $modele->valeurs_par_defaut;
        $modeles_par_defaut = $this->modeles_par_defaut();

        foreach($modeles_par_defaut as $type_element => &$valeurs){

            if(isset($modele->valeurs_par_defaut[$type_element])) {
                foreach ($modele->valeurs_par_defaut[$type_element] as $nom_sql => $valeur_par_defaut)
                    $valeurs[$nom_sql] = $valeur_par_defaut;
            }
        }

        $modele->valeurs_par_defaut = $modeles_par_defaut;

        return $modele;
    }

    /**
     * @param array $modifications
     * @param false $modele
     * @return mixed
     *
     * Gére l'enregistrement des arrays au format json
     *
     */
    public function enregistre($modifications = array(), $modele = false)
    {
        if(isset($modifications['tables_jointes']) && is_array($modifications['tables_jointes']))
            $modifications['tables_jointes'] = json_encode($modifications['tables_jointes']);

        if(isset($modifications['champs']) && is_array($modifications['champs']))
            $modifications['champs'] = json_encode($modifications['champs'],JSON_INVALID_UTF8_IGNORE);

        if(isset($modifications['valeurs_par_defaut']) && is_array($modifications['valeurs_par_defaut']))
            $modifications['valeurs_par_defaut'] = json_encode($modifications['valeurs_par_defaut'],JSON_INVALID_UTF8_IGNORE);

        if(isset($modifications['ordre_lancement_import']) && is_array($modifications['ordre_lancement_import']))
            $modifications['ordre_lancement_import'] = json_encode($modifications['ordre_lancement_import']);

        return parent::enregistre($modifications, $modele);
    }

    /**
     * @return array|mixed
     *
     * Permet de récupérer les tables jointes au bon format
     *
     */
    public function tables_jointes(){

        if(empty($this->modele->tables_jointes))
            return [];

        return json_decode($this->modele->tables_jointes,true);
    }

    /**
     * @return array|mixed
     *
     * Permet de récupérer les types éléments des tables jointes
     *
     */
    public function tables_jointes_types_element(){

        $tables_jointes_types_element = array();

        foreach($this->tables_jointes() as $table_jointe)
            $tables_jointes_types_element[] = $table_jointe['type_element'];

        return $tables_jointes_types_element;
    }

    /**
     * @return array|mixed
     *
     * Permet de récupérer l'ordre de lancement des créations
     *
     */
    public function ordre_lancement_import(){

        if(empty($this->modele->ordre_lancement_import))
            return [];

        return json_decode($this->modele->ordre_lancement_import,true);
    }

    /**
     * @return array|mixed
     *
     * Permet de récupérer les champs au bon format
     *
     */
    public function champs(){

        if(empty($this->modele->champs))
            return [];

        $champs = json_decode($this->modele->champs,true);

        // Avec le json les true et false deviennent 'true' et 'false'
        foreach($champs as &$champ){
            foreach($champ as &$informations){
                if($informations == 'true')
                    $informations = true;
                else if($informations == 'false')
                    $informations = false;
            }
        }

        return $champs;
    }
    /**
     * @return array|mixed
     *
     * Permet de récupérer les champs par type élément
     *
     */
    public function champs_par_element(){

        $champs_par_element = [];

        $champs = $this->champs();

        $champs_libres = Champ_libre::select(DB::raw('CONCAT (type_element,".",nom_sql) as type_element_nom_sql'),'nom_sql','nom','type_element','obligatoire','type','index_traduction')
            ->whereIn('type_element',$this->types_elements())
            ->where(function($requete){
                $requete->where('champ_systeme',null)
                    ->orWhere('champ_systeme',0);
            })
            ->get()->keyBy('type_element_nom_sql')->toArray();

        foreach($champs as $champ){

            if($champ['correspondance'] == 'aucune_correspondance')
                continue;

            $correspondance = explode('.',$champ['correspondance']);

            $element = $correspondance[0];
            $type_element = $this->element_type_element($element);
            $nom_sql = $correspondance[1];

            if(isset($champs_libres[$type_element.'.'.$nom_sql]))
                $champ['champ_libre'] = $champs_libres[$type_element.'.'.$nom_sql];
            else {
                // Cas particulier pour les ids
                $champ['champ_libre'] = array(
                    'type_element' => $type_element,
                    'nom_sql' => $nom_sql
                );
            }

            $champs_par_element[$element][] = $champ;
        }

        return $champs_par_element;
    }


    /**
     * @return array|mixed
     *
     * Permet de récupérer les valeurs par defaut au bon format
     *
     */
    public function valeurs_par_defaut(){

        if(empty($this->modele->valeurs_par_defaut))
            return [];

        $valeurs_par_defaut_par_type_element = json_decode($this->modele->valeurs_par_defaut,true);

        foreach($valeurs_par_defaut_par_type_element as &$valeurs_par_defaut){

            foreach($valeurs_par_defaut as &$valeur_par_defaut){
                // Avec le json les true et false deviennent 'true' et 'false'
                if(is_array($valeur_par_defaut)){
                    foreach($valeur_par_defaut as &$valeur){
                        if($valeur == 'true')
                            $valeur = true;
                        else if($valeur == 'false')
                            $valeur = false;
                    }
                }
            }
        }

        return $valeurs_par_defaut_par_type_element;
    }

    /**
     *
     * Retourne les types éléments concernés par l'import
     *
     */
    public function types_elements(){

        $types_elements = array($this->modele->type_element);

        foreach($this->tables_jointes() as $table_jointe)
            $types_elements[] = $table_jointe['type_element'];

        return $types_elements;
    }

    /**
     *
     * Retourne les éléments concernés par l'import
     *
     */
    public function elements(){

        $ordre_lancement_import = $this->ordre_lancement_import();

        $tables_jointes = collect($this->tables_jointes())->keyBy('id');

        foreach($ordre_lancement_import as $element){

            if(isset($tables_jointes[$element]))
                $elements[$element] = $tables_jointes[$element]['type_element'];
            else
                $elements[$element] = $element;

        }

        return $elements;
    }

    /**
     *
     * Retourne le type élement d'un élément
     *
     */
    public function element_type_element($element){

        if($this->modele->type_element == $element)
            return $element;

        $type_element = null;

        foreach($this->tables_jointes() as $table_jointe){
            if($table_jointe['id'] == $element)
                $type_element = $table_jointe['type_element'];
        }
        
        return $type_element;
    }

    /**
     * @return array
     *
     * Permet de récupérer les champs libres des tables concernés
     *
     */
    public function champs_libres(){

        $champs_libres = array();

        $tables_libres_type_element = Table_libre::whereIn('type_element',$this->types_elements())
            ->where(function($requete){
                $requete->where('table_systeme',null)
                    ->orWhere('table_systeme',0);
            })
            ->get()->keyBy('type_element');

        $champs_libres['table_import'] = Champ_libre::select('nom_sql','nom','type_element','obligatoire','type','index_traduction')
            ->where('type_element', $this->modele->type_element)
            ->where(function($requete){
                $requete->where('champ_systeme',null)
                    ->orWhere('champ_systeme',0);
            })
            ->orderBy('nom')->get();

        foreach($champs_libres['table_import'] as $champ_import){
            $champ_import->index_traduction_type_element = 'tables_libres.'.$champ_import->type_element.'.nom_table';
        }

        $champs_libres['tables_jointes'] = array();

        $type_element_principal = $this->modele->type_element;

        foreach ($this->tables_jointes() as $table_jointe) {
            if(!isset($champs_libres['tables_jointes'][$table_jointe['type_element']])) {
                $champs_libres['tables_jointes'][$table_jointe['type_element']] = Champ_libre::select('nom_sql', 'nom', 'type_element', 'obligatoire', 'type','index_traduction')
                    ->where('type_element', $table_jointe['type_element'])
                    ->where(function($requete) use ($type_element_principal) {
                        $requete->where('type_element_ajax', '!=', $type_element_principal);
                        $requete->orWhereNull('type_element_ajax');
                    })
                    ->orderBy('nom')
                    ->get();

                foreach($champs_libres['tables_jointes'][$table_jointe['type_element']] as $champ_import){
                    $champ_import->index_traduction_type_element = 'tables_libres.'.$champ_import->type_element.'.nom_table';
                }

            }
        }
        
        $this->champs_creation($champs_libres);

        return $champs_libres;
    }

    /**
     * @param $champs_libres
     *
     * Permet de récupérer les champs de création des champs libres
     *
     */
    public function champs_creation(&$champs_libres){

        foreach($champs_libres['table_import'] as $champ_libre) {
            $champ = management($champ_libre->type_element)->champ($champ_libre->nom_sql);
            $champ->modele->obligatoire = $champ_libre->obligatoire;
            $champ_libre->champ_creation = $champ->vmodel(false)->cree();
        }

        foreach($champs_libres['tables_jointes'] as $champs_libres_joints) {
            foreach($champs_libres_joints as $champ_libre_joint) {
                $champ_joint = management($champ_libre_joint->type_element)->champ($champ_libre_joint->nom_sql);
                $champ_joint->modele->obligatoire = $champ_libre_joint->obligatoire;
                $champ_libre_joint->champ_creation = $champ_joint->vmodel(false)->cree();
            }
        }
    }

    /**
     * @return array
     *
     * Permet de récupérer les modéles par défaut des éléments
     *
     */
    public function modeles_par_defaut(){

        $modeles_par_defaut = [];

        $modeles_par_defaut[$this->modele->type_element] = modele_par_defaut($this->modele->type_element)->toArray();

        foreach ($this->tables_jointes() as $table_jointe) {
            $modeles_par_defaut[$table_jointe['id']] = modele_par_defaut($table_jointe['type_element'])->toArray();
        }

        return $modeles_par_defaut;

    }

    /**
     *
     * Permet de récupérer les clés de mises à jour
     *
     */
    public function cles_mises_a_jour(){

        $champs = $this->champs();

        if(empty($champs))
            return [];

        $cles_mises_a_jour = [
            $this->modele->type_element => [],
        ];

        foreach($champs as $champ){

            if($champ['correspondance'] != 'aucune_correspondance' && isset($champ['cle_mise_a_jour'])
                && $champ['cle_mise_a_jour'] === true){
                $correspondance = explode('.',$champ['correspondance']);

                $type_element = $correspondance[0];
                $nom_sql = $correspondance[1];

                $cles_mises_a_jour[$type_element][] = $nom_sql;
            }
        }

        return $cles_mises_a_jour;
    }

    /**
     *
     * Permet de récupérer les clés de mises à jour du fichier d'improt
     *
     */
    public function cles_mises_a_jour_import($champs_par_element){

        $cles_mises_a_jour_import = [];

        foreach($champs_par_element as $type_element => $champs) {

            foreach ($champs as $champ) {

                if ($champ['correspondance'] != 'aucune_correspondance' && isset($champ['cle_mise_a_jour'])
                    && $champ['cle_mise_a_jour'] === true) {
                    $cles_mises_a_jour_import[$type_element][] = $champ;
                }
            }

        }

        return $cles_mises_a_jour_import;
    }

    /**
     *
     * Permet de récupérer les valeurs des champs libres pour les valeurs par défaut
     *
     */
    public function champs_libres_valeurs_par_defaut($champs_libres){

        $champs_libres_valeurs_par_defaut = array(
            'table_import' => [],
            'tables_jointes' => (object)[],
        );

        $valeurs_par_defaut_par_type_element = $this->valeurs_par_defaut();

        $champs = $this->champs();

        $champs_libres_importes_par_type_element = array();

        //On récupére les valeurs par défaut déjà afficher sur l'affichage des champs
        foreach($champs as $champ){

            if($champ['correspondance'] == null || $champ['correspondance'] == 'aucune_correspondance' || !empty($champ['cle_cree']))
                continue;

            $correspondance = explode('.',$champ['correspondance']);

            $champs_libres_importes_par_type_element[$correspondance[0]][] = $correspondance[1];
        }

        //Pour chaque valeur par défaut, si il est déjà affiché sur l'affichage des champs on les unset
        foreach($valeurs_par_defaut_par_type_element as $id => $valeurs_par_defaut){

            if(!isset($champs_libres_importes_par_type_element[$id]))
                continue;

            foreach($valeurs_par_defaut as $valeur_par_defaut => $osef){
                if(in_array($valeur_par_defaut,$champs_libres_importes_par_type_element[$id]))
                    unset($valeurs_par_defaut_par_type_element[$id][$valeur_par_defaut]);
            }
        }

        //On récupére les champs libres des tables et s'il est obligatoire et non présent dans les champs on doit l'afficher dans l'affichage des champs libres valeurs par défaut
        foreach($champs_libres['table_import'] as $champ_libre){

            if($champ_libre->obligatoire == 1 && (!isset($champs_libres_importes_par_type_element[$this->modele->type_element]) || !in_array($champ_libre['nom_sql'],$champs_libres_importes_par_type_element[$this->modele->type_element])))
                $champs_libres_valeurs_par_defaut['table_import'][] = $champ_libre;
            else if(isset($valeurs_par_defaut_par_type_element[$this->modele->type_element]) && isset($valeurs_par_defaut_par_type_element[$this->modele->type_element][$champ_libre['nom_sql']]))
                $champs_libres_valeurs_par_defaut['table_import'][] = $champ_libre;
        }

        foreach($this->tables_jointes() as $table_jointe) {

            $elements_tables_jointes = [];

            $id = $table_jointe['id'];

            foreach ($champs_libres['tables_jointes'][$table_jointe['type_element']] as $champ_libre) {

                if ($champ_libre->obligatoire == 1 && (!isset($champs_libres_importes_par_type_element[$id]) || !in_array($champ_libre['nom_sql'],$champs_libres_importes_par_type_element[$id])))
                    $elements_tables_jointes[] = $champ_libre;
                else if (isset($valeurs_par_defaut_par_type_element[$id]) && isset($valeurs_par_defaut_par_type_element[$id][$champ_libre['nom_sql']]))
                    $elements_tables_jointes[] = $champ_libre;

            }

            $champs_libres_valeurs_par_defaut['tables_jointes']->{$table_jointe['id']} = $elements_tables_jointes;
        }

        return $champs_libres_valeurs_par_defaut;
    }
}