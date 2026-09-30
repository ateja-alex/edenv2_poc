<?php

namespace App\Eden\Champs;

use DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Validation\Rules\Iban;
use Intervention\Validation\Rules\Bic;
use Danielebarbaro\LaravelVatEuValidator\Rules\VatNumberFormat;
use App\Eden\Rules\SirenSiret;
use App\Eden\Rules\Numero_securite_sociale;

class Champ_texte extends Champ {

    public string $type_filtre = 'filtre-texte';

    public string $nom_composant = 'champ-texte';

    public function cree(){

        if($this->modele->format_champ == 'code_postal')
            $this->nom_composant = "champ-code-postal";
        else if($this->modele->format_champ == 'adresse')
            $this->nom_composant = "champ-adresse";
        else if($this->modele->format_champ == 'mdp_systeme'){
            $this->nom_composant = "champ-mdp-systeme";
            $this->attr('index_traduction', $this->modele->index_traduction);
        }
        else if($this->modele->format_champ == 'icone')
            $this->nom_composant = "champ-icone";

        if(!empty($this->placeholder))
            $this->attr('placeholder', $this->placeholder);

        $this->attr('ref', $this->modele->nom_sql);

        if($this->modele->format_champ == 'code_postal' || $this->modele->format_champ == 'adresse'){
            if(!empty($this->modele->contenu))
                $this->attr('mappage', json_decode($this->modele->contenu,true), 1);

        } else {

            $this->attr('scribens_active', fonctionnalite("scribens_activation"));

            $this->attr('scribens_cle_api', fonctionnalite("scribens_cle_api"));

            $this->attr('type_element', $this->modele->type_element);

            if(!empty($this->colonne_champ))
                $this->attr('colonne_champ', $this->colonne_champ);
        }

        return $this->cree_champ();
    }

    /**
     *
     * Affiche proprement la valeur d'un champ
     *
     */
    public function affiche($valeur = false) {

        if($valeur === false)
            $valeur = $this->valeur;

        if(isset($this->modele->format_champ) && $this->modele->format_champ == "password")
            $valeur = '<strong>' . preg_replace(array('/./'), '&#8901;', $valeur) . '</strong>';
        elseif(isset($this->modele->format_champ) && in_array($this->modele->format_champ,['siren','siret'])){

            $format_champ_nombres_caractres = array(
                'siren' => 9,
                'siret' => 14,
            );

            if(!empty($valeur) && strlen($valeur) == $format_champ_nombres_caractres[$this->modele->format_champ]) {

                $restant = '';

                if($this->modele->format_champ == 'siret') {
                    $restant = substr($valeur, 9, 14);
                    $valeur = substr($valeur,0,9);
                }

                $valeur = chunk_split($valeur, 3, ' ').$restant;
            }
        }

        return $valeur;
    }

    /**
     *
     * Applique les filtres sur les listes (les listes d'éléments génériques)
     *
     */
    public function applique_filtre_sur_requete($filtre, $requete) {

        $alias_champ = $this->alias_champ_requete();

        if(isset($filtre['variable']) && !empty($filtre['variable'] && $filtre['variable'] == 'vide')) {
            $requete = $requete->where(function($condition) use ($alias_champ){
                $condition->whereNull($alias_champ)->orWhere($alias_champ,'');
            });
        }
        elseif(isset($filtre['variable']) && !empty($filtre['variable'] && $filtre['variable'] == 'non_vide')) {
            $requete = $requete->whereNotNull($alias_champ)->where($alias_champ,'!=','');
        }

        // pas de variable, on traite le cas classique
        elseif(!empty($filtre['texte']) || $filtre['texte'] === '0') {

            $variable_dynamique = false;

            if (preg_match('/#(.*?)#/', $filtre['texte'], $match) == 1) {
                $filtre['texte'] = $match[1];
                $variable_dynamique = true;
            }
            if(!$variable_dynamique){
                if(in_array($filtre['variable'], ['commence_par', 'ne_contient_pas', 'contient']))
                    $filtre['texte'] = str_replace('\\', '\\\\', $filtre['texte']);

                $filtre['texte'] = addcslashes($filtre['texte'], "\\%'");
            }
           
            if (!empty($filtre['variable']) && $filtre['variable'] == 'commence_par') {
                if($variable_dynamique)
                    $requete = $requete->where($alias_champ, 'LIKE', DB::raw('CONCAT('.$filtre['texte'].', "%")'));
                else
                    $requete = $requete->where($alias_champ, 'LIKE', $filtre['texte'] . '%');
            }
            else if(!empty($filtre['variable']) && $filtre['variable'] == 'egal_a') {
                if($variable_dynamique)
                    $requete = $requete->where($alias_champ,DB::raw('CONCAT('.$filtre['texte'].')'));
                else
                    $requete = $requete->where($alias_champ, $filtre['texte']);
            }
            else if(!empty($filtre['variable']) && $filtre['variable'] == 'non_egal_a'){
                if($variable_dynamique)
                    $requete = $requete->whereRaw($alias_champ.' != CONCAT('.$filtre['texte'].')');
                else
                    $requete = $requete->where($alias_champ,'!=', $filtre['texte']);
            }
            else if(!empty($filtre['variable']) && $filtre['variable'] == 'ne_contient_pas'){
                if($variable_dynamique)
                    $requete = $requete->where($alias_champ, 'NOT LIKE', DB::raw('CONCAT("%",'.$filtre['texte'].', "%")'));
                else
                    $requete = $requete->where($alias_champ, 'NOT LIKE', '%' . $filtre['texte'] . '%');
            }
            else {
                if($variable_dynamique)
                    $requete = $requete->whereRaw($alias_champ . ' LIKE CONCAT("%", ' . $filtre['texte'] . ', "%")');
                else
                    $requete = $requete->where($alias_champ, 'LIKE', '%' . $filtre['texte'] . '%');
            }

        }

        return $requete;
    }

    public function valider(){

        switch($this->modele->format_champ){

            case 'iban' : 
                $regle_validation = new Iban();
                break;
            case 'bic' : 
                $regle_validation = new Bic();
                break;
            case 'tva_intra' : 
                $regle_validation = new VatNumberFormat();
                break;
            case 'siret' :
                $regle_validation = new SirenSiret();
                $regle_validation->setType('siret');
                break;
            case 'siren' :
                $regle_validation = new SirenSiret();
                $regle_validation->setType('siren');
                break;
            case 'secu_sociale' :
                $regle_validation = new Numero_securite_sociale();
                break;
        }
        
        $validator = Validator::make([$this->modele->nom_sql => $this->valeur], [
            $this->modele->nom_sql => $regle_validation,
        ]);
        
        if($validator->fails())
            return [
                'succes' => false,
                'message' => traduction('messages.php.champ_texte.erreur_validation_' . $this->modele->format_champ, null, [
                    traduction($this->modele->index_traduction . '.nom')
                ]),
            ];

        return ['succes' => true];
    }

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $type= 'text';

        if($this->modele->format_champ == 'email')
            $type= 'email';
        else if($this->modele->format_champ == 'numero_telephone')
            $type= 'tel';
        else if($this->modele->format_champ == 'url')
            $type= 'url';

        $input = '<input type="'.$type.'" '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

    public function affiche_liste($element, $colonne) {

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        $affichage = $this->affiche($element->{$colonne_valeur});

        if(!empty($colonne->caracteres_max))
            $affichage = substr($affichage, 0, $colonne->caracteres_max). '...';

        return [
            'type' => 'contenu',
            'contenu' => $affichage,
        ];
    }
}
