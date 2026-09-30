<?php

namespace App\Eden\Managements\Services;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre_parametre_requete;
use Illuminate\Support\Facades\DB;

class Liste_requete_sql_service {

    private $parametres = [];
    private $type_element = [];
    private $rapport = [];
    private $liste_libre = [];
    private $remplacements_requete = [];
    private $colonnes = [];

    public function transformation_requete($rapport,$liste_libre,$type_element,$parametres,$colonnes){

        $this->parametres = $parametres;
        $this->type_element = $type_element;
        $this->rapport = $rapport;
        $this->liste_libre = $liste_libre;
        $this->colonnes = $colonnes;

        $this->gestion_tri();
        $this->gestion_recherche();
        $this->gestion_joins();
        $this->gestion_filtres();
        $this->gestion_filtres_pour_fiche();
        $this->gestion_profils_et_inactif();

        return str_replace(array_keys($this->remplacements_requete),$this->remplacements_requete,$rapport->requete_sql);
    }

    public function gestion_tri(){

        if(!empty($this->parametres['tri']) && !empty($this->colonnes)) {

            $colonne = $this->colonnes->where('id', $this->parametres['tri'])->first();

            if(!empty($colonne))
                $this->remplacements_requete['#eden_order_by#'] = 'ORDER BY ' . $colonne->valeur . ($this->parametres['direction_tri'] == 1 ? ' DESC' : ' ASC');
        }

        if(empty($this->remplacements_requete['#eden_order_by#']))
            $this->remplacements_requete['#eden_order_by#'] = '';
    }


    public function gestion_recherche(){

        if(!empty($this->parametres['recherche'])) {

            $termes_a_remplacer = array(
                '+', '-', '<', '>', '(', ')', '~', '*', '"', '&', '|', ':', '@'
            );

            $termes = array_diff(explode(' ', str_replace($termes_a_remplacer, ' ', $this->parametres['recherche'])), array(""));

            $this->remplacements_requete['#eden_recherche#'] = "MATCH(" . $this->type_element . ".chaine_tags_recherche) AGAINST (\"+" . implode('* +', $termes) . "*\" In BOOLEAN MODE)";
        }
        else
            $this->remplacements_requete['#eden_recherche#'] = 'TRUE';
    }

    public function gestion_joins(){

        $this->joins = [];
        $alias_requete_compte = 0;

        $join_requete = '';

        if(isset($this->parametres['filtres'])) {

            $filtres = collect($this->parametres['filtres_affichage'])->where('type','!=','parametres_requete');

            foreach ($this->parametres['filtres'] as $filtre_valeur) {

                // on va chercher les infos du filtre
                $filtre = (object)$filtres->where('id', $filtre_valeur['id'])->first();

                if (!empty($filtre->type_element) && $filtre->type_element != $this->type_element) {

                    $type_element_tmp = $filtre->type_element;

                    $champ_de_liaison = $filtre->champ_de_liaison;

                    if (empty($filtre->champ_de_liaison)) {

                        $champ_libre = Champ_libre::where('type_element', $this->type_element)->where('type_element_ajax', $filtre->type_element)->first();

                        if (!empty($champ_libre))
                            $champ_de_liaison = $champ_libre->nom_sql;
                    }

                    if (!isset($this->joins[$type_element_tmp])) {

                        $this->joins[$type_element_tmp] = array(
                            'champs_de_liaison' => array(),
                        );
                    }

                    if (!isset($this->joins[$type_element_tmp]['champs_de_liaison'][$champ_de_liaison]) && $champ_de_liaison != null) {
                        $alias_requete_compte++;
                        $this->joins[$type_element_tmp]['champs_de_liaison'][$champ_de_liaison] = array(
                            'alias' => 'liaison_' . $alias_requete_compte
                        );
                    }

                }
            }

            foreach ($this->joins as $type_element_tmp => $join) {

                $champs_de_liaison = $join['champs_de_liaison'];

                foreach ($champs_de_liaison as $champ_de_liaison => $informations) {
                    $join_requete .= ' LEFT JOIN ' . $type_element_tmp . ' AS ' . $informations['alias'] . ' ON ' . $this->type_element . '.' . $champ_de_liaison . ' = ' . $informations['alias'] . '.id';
                }
            }
        }

        $this->remplacements_requete['#eden_join#'] = $join_requete;
    }

    public function gestion_filtres(){

        $parametres_requete = Liste_libre_parametre_requete::where('liste_id',$this->liste_libre->id)->get();

        foreach($parametres_requete as $parametre_requete){

            $filtre = collect($this->parametres['filtres'] ?? [])->filter(function($filtre) use ($parametre_requete){
                return $filtre['id'] == '#parametres_requete_'.$parametre_requete->id.'#';
            })->values()[0] ?? null;

            if(empty($filtre))
                $remplacement = 'TRUE';
            else{
                $champ_libre = new Champ_libre();
                $champ_libre->alias_champ = $parametre_requete->champ;
                $champ_libre->nom = $parametre_requete->nom;

                $correspondance_champ = [
                    'filtre-date' => 'App\Eden\Champs\Champ_date',
                    'filtre-montant' => 'App\Eden\Champs\Champ_montant',
                    'filtre-texte' => 'App\Eden\Champs\Champ_texte'
                ];

                $champ_date = new $correspondance_champ[$parametre_requete->type_filtre]($champ_libre);
                $remplacement = $this->transforme_where_eloquent_en_string($filtre['valeurs'],$champ_date);
            }

            $this->remplacements_requete[$parametre_requete->alias_requete] = $remplacement;
        }

        $remplacement = '';

        if(!empty($this->parametres['filtres'])){

            $filtres = collect($this->parametres['filtres_affichage'])->where('type','!=','parametres_requete');

            foreach ($this->parametres['filtres'] as $valeur_filtre){

                $filtre = (object) $filtres->where('id',$valeur_filtre['id'])->first();

                if(empty($filtre->nom_sql))
                    continue;

                $type_element_filtre = !empty($filtre->type_element) ? $filtre->type_element : $this->type_element;
                $nom_sql = $filtre->nom_sql;

                $champ = champ_libre($type_element_filtre, $nom_sql);

                if (!empty($filtre->champ_de_liaison)) {
                    $champ->modele->alias_champ = $this->joins[$type_element_filtre]['champs_de_liaison'][$filtre->champ_de_liaison]['alias'] . '.' . $nom_sql;
                    $champ->modele->alias_table = $this->joins[$type_element_filtre]['champs_de_liaison'][$filtre->champ_de_liaison]['alias'];
                }

                if($remplacement != '')
                    $remplacement.= ' AND ';

                $remplacement .= $this->transforme_where_eloquent_en_string($valeur_filtre['valeurs'],$champ->champ);
            }
        }

        $this->remplacements_requete['#eden_filtres#'] = empty($remplacement) ? 'TRUE' : $remplacement;
    }

    public function gestion_filtres_pour_fiche(){

        $remplacement = '';

        if(!empty($this->parametres['filtres_pour_fiche'])) {

            if(!is_array($this->parametres['filtres_pour_fiche']))
                $this->parametres['filtres_pour_fiche'] = unserialize(base64_decode($this->parametres['filtres_pour_fiche']));

            if(!empty($this->parametres['filtres_pour_fiche'])) {

                foreach ($this->parametres['filtres_pour_fiche'] as $champ => $valeur) {

                    $remplacement .= $champ.'='.$valeur.' ';
                }
            }
		}

        $this->remplacements_requete['#eden_filtres_pour_fiche#'] = empty($remplacement) ? 'TRUE' : $remplacement;
    }

    public function gestion_profils_et_inactif(){

        $requete_tmp = modele($this->type_element)->newQuery();

        $requete_tmp = vsprintf(str_replace(['?'], ['\'%s\''], $requete_tmp->toSql()), $requete_tmp->getBindings());

        $remplacement = str_replace('select * from `'.$this->type_element.'` where ','',$requete_tmp);

        $this->remplacements_requete['#eden_profils_et_inactif#'] = empty($remplacement) ? 'TRUE' : $remplacement;
    }

    public function transforme_where_eloquent_en_string($valeurs_filtre,$champ_libre){
        $requete_tmp = $champ_libre->applique_filtre_sur_requete($valeurs_filtre,DB::table('tmp'));
        $requete_tmp = vsprintf(str_replace(['?'], ['\'%s\''], $requete_tmp->toSql()), $requete_tmp->getBindings());
        return str_replace('select * from `tmp` where ','',$requete_tmp);
    }
}
