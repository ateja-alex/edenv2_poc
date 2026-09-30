<?php

namespace App\Eden\Managements\Services;


use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;

class Echange_service {

    private $type_element = null;
    private $id_element = 0;
    private $parametres = [];
    private $type_element_a_colonne_dediee = false;

    private $types_elements = [
        'client' => 'client_id',
        'fournisseur' => 'fournisseur_id',
        'lead' => 'lead_id',
        'projet' => 'projet_id',
        'contact' => 'contact_id',
    ];

    public function chargement_donnees($parametres){

        $initialisation = isset($parametres['initialisation']) && $parametres['initialisation'] == 'true';

        $this->type_element = $parametres['type_element'];
        $this->id_element = $parametres['id_element'];
        $this->type_element_a_colonne_dediee = array_key_exists($this->type_element, $this->types_elements);
        unset($this->types_elements[$this->type_element]);
        $this->parametres = $parametres;

        $page = $parametres['page'] ?? 1;
        $nb_echanges_par_page = $parametres['nb_elements_par_page'] ?? 5;

        $requete_initiale = modele('echange')->orderByDesc('date')->orderByDesc('id');

        $this->applique_filtres($requete_initiale);

        $elements = [];

        foreach($this->types_elements as $type_element => $nom_sql){

            $elements[$type_element] = modele($type_element)
                ->whereIn('id',(clone $requete_initiale)
                    ->select(DB::raw('COALESCE(IF(type_element = "'.$type_element.'",element_id,NULL),'.$nom_sql.')'))
                    ->where(function($requete) use ($type_element,$nom_sql){
                        $requete->where($nom_sql,'!=',0)
                            ->whereNotNull($nom_sql)
                            ->orWhere('type_element',$type_element)
                            ->where('element_id','>',0)
                            ->whereNotNull('element_id');
                    })
                )->groupBy('id')->get();
        }

        if(!empty($this->parametres['filtre_selectionne'])){

            $type_element = $this->parametres['filtre_selectionne']['type_element'];
            $element_id = $this->parametres['filtre_selectionne']['element_id'];
            $champ = $this->types_elements[$type_element];

            $requete_initiale = $requete_initiale->where(function($requete) use ($type_element,$element_id,$champ){
                $requete->where('type_element',$type_element)
                    ->where('element_id',$element_id)
                    ->orWhere($champ,$element_id);
            });
        }

        $pages_echanges = ceil($requete_initiale->count() / $nb_echanges_par_page);

        $echanges = $requete_initiale->skip(($page-1) * $nb_echanges_par_page)->take($nb_echanges_par_page)->get();

        foreach($echanges as $echange) {

            $management_echange = management('echange', $echange->id,$echange);

            $echange->chaine_affichage = $management_echange->affiche();
            $echange->description_affichage = $management_echange->champ('description')->affiche();

            $echange->type_txt = $management_echange->champ('type')->affiche();
            $echange->deplier = false;
            $informations_table = [];

            foreach($this->types_elements as $type_element => $nom_sql){

                $element_id = $echange->type_element == $type_element ? $echange->element_id : $echange->{$nom_sql};

                if(!empty($element_id)) {

                    $element = $elements[$type_element]->where('id',$element_id)->first();

                    if(!empty($element))
                        $informations_table[$type_element] = management($type_element,$element->id,$element)->affiche();
                }
            }

            $echange->informations_table = $informations_table;
        }

        $retour = array(
            'donnees' => $echanges,
            'elements' => $elements,
            'total_echanges' => $pages_echanges
        );

        if($initialisation) {

            $retour['filtres_a_envoyer'] = $this->filtres_timeline();

            $modele_par_defaut = modele_par_defaut('echange');

            if (!empty($filtres_appliques)) {

                foreach ($filtres_appliques as $champ_filtre_applique => $filtre_applique) {

                    $modele_par_defaut->{$champ_filtre_applique} = $filtre_applique;
                }

            }

            $retour['modele_par_defaut'] = $modele_par_defaut;
        }

        return $retour;
    }

    public function applique_filtres(&$requete_initiale){

        $requete_initiale = $requete_initiale->where(function($sous_requete){
            $sous_requete->where(function ($requete){
                $requete->where('type_element', $this->type_element)
                    ->where('element_id', $this->id_element);
            });

            if($this->type_element_a_colonne_dediee)
                $sous_requete->orWhere($this->type_element.'_id',$this->id_element);
        });

        if(!empty($this->parametres['filtres']))
            $requete_initiale->whereIn('type', $this->parametres['filtres']);

        if(!empty($this->parametres['filtres_appliques'])){
            foreach($this->parametres['filtres_appliques'] as $champ_filtre_applique => $filtre_applique){
                $requete_initiale->where($champ_filtre_applique,$filtre_applique);
            }
        }

        if(isset($this->parametres['filtres_erp'])){

            $filtres_erp = $this->parametres['filtres_erp'];

            $filtres_timeline = $this->filtres_timeline();

            foreach($filtres_erp as $filtre_erp){

                $modele_filtre = null;

                foreach($filtres_timeline as $filtre_timeline){

                    if($filtre_timeline['id'] == $filtre_erp['id'])
                        $modele_filtre = $filtre_timeline;
                }

                if(empty($modele_filtre))
                    continue;

                $champ = champ_libre('echange', $modele_filtre['nom_sql']);

                $requete_initiale = $champ->champ->applique_filtre_sur_requete($filtre_erp['valeurs'], $requete_initiale);
            }
        }
    }

    /**
     * 
     * 
     * Retourne la liste des echanges possibles
     * 
     */
    public function liste_echanges_possibles() {

        $liste = array();

        $types_element = Champ_libre::where('type_element', 'echange')->where('nom_sql', 'type_element')->where('type', 21)->value('contenu');

        if(!empty($types_element))
            $types_element = json_decode($types_element);

        foreach($types_element as $infos_type_element){

            if($infos_type_element->valeur === true)
                $liste[$infos_type_element->type_element] = traduction('tables_libres.'.$infos_type_element->type_element.'.element');
        }

        return $liste;
    }

    /**
     *
     * Renvoie les filtres classiques pour la timeline
     *
     */
    public function filtres_timeline(){

        return array(
            array(
                'id' => 1,
                'nom_sql' => 'utilisateur_id',
                'type_element' => 'echange',
                'modele' => management('echange')->champ('utilisateur_id')->modele,
                'type_filtre' => management('echange')->champ('utilisateur_id')->type_filtre,
                'index_traduction' => 'champs_libres.echange.utilisateur_id.nom',
            )
        );
    }
}