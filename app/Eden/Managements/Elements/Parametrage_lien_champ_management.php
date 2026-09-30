<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Table_libre;

class Parametrage_lien_champ_management extends Element_management{

    public $type_element = false;
    public $modele_email = false;
    public $notification_manuelle = false;

    public $champ_valeur_dur = null;
    public $filtres_valeur_final = [];

    public function type_element(){

        if($this->type_element === false){
            $type_element_id = $this->notification_manuelle()->type_element_id ?? $this->modele_email()->type_element_id ?? $this->modele->type_element_id ?? null;
            $this->type_element = Table_libre::find($type_element_id)->type_element ?? null;
        }

        return $this->type_element;
    }

    public function modele_email(){

        if($this->modele_email === false)
            $this->modele_email = modele('modele_email')->find($this->modele->modele_email_id);

        return $this->modele_email;
    }

    public function notification_manuelle(){

        if($this->notification_manuelle === false)
            $this->notification_manuelle = modele('notification_manuelle')->find($this->modele->notification_manuelle_id);

        return $this->notification_manuelle;
    }

    public function enregistre($modifications = array(), $modele = false){

        if(!isset($modifications['lien_champ']) && !isset($modifications[$this->champ_valeur_dur]) && !isset($modifications['parametrage_existant']))
            return traduction('interface.parametrage_lien_champ.messages.destinataire_incomplet');

        if(!empty($modifications['parametrage_existant'])){
            $modifications['type'] = null;
            $modifications['niveau'] = null;
        }
        else if(!empty($modifications['niveau']) && $modifications['niveau'] == 1)
            $modifications['type'] = null;

        $filtrages = false;

        if(isset($modifications['filtrages'])){
            $filtrages = $modifications['filtrages'] ?? [];

            $filtrages = array_map(function($filtrage){
                return json_decode($filtrage, true);
            },$filtrages);

            unset($modifications['filtrages']);
        }
        
        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true || $filtrages === false)
            return $retour;

        $recherches_avancees = modele('recherche_avancee')
            ->where('type', $this->_type_element.'_'.$this->modele->id)
            ->get()->keyBy('id_cible');

        foreach($filtrages as $filtrage){

            $id_cible = $filtrage['id_cible'];
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

        foreach($recherches_avancees as $recherche_avancee){
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

        return true;
    }

    public function valeurs($elements_ids){

        if(!empty($this->champ_valeur_dur) && !empty($this->modele->{$this->champ_valeur_dur})){

            $affichage = $this->affichage_valeur($this->modele->{$this->champ_valeur_dur});

            return [[
                'valeur' => ($this->_type_element == 'parametrage_piece_jointe_email' ? 'storage/' : '').$this->modele->{$this->champ_valeur_dur},
                'groupe_id' => $this->modele->id,
                'obligatoire' => $this->modele->niveau == 3,
                'affichage' => $affichage,
                'affichage_select' => strip_tags($affichage)     
            ]];
        }

        $type_element = $this->type_element();

        $filtrages = modele('recherche_avancee')
                ->where('type', $this->_type_element.'_'.$this->modele->id)
                ->get()->keyBy('id_cible');

        $valeurs = service('lien_champ')->valeurs($this->modele->lien_champ, [
            'type_element' => $type_element,
            'elements_ids' => $elements_ids,
            'filtrages' => $filtrages,
        ], false, $this->filtres_valeur_final);

        foreach($valeurs as &$valeur){
            $valeur['groupe_id'] = $this->modele->id;
            $valeur['obligatoire'] = $this->modele->niveau == 3;

            if($valeur['type'] == 'element_table_libre_final'){
                $nom_element = explode('/',$valeur['valeur']);
                $titre = traduction('tables_libres.'.$valeur['type_table'].'.nom_table').' : '. end($nom_element);
            }
            else
                $titre = $valeur['valeur'];

            if($this->_type_element == 'parametrage_piece_jointe_email' && strpos($valeur['valeur'], 'public/') === 0)
                $valeur['valeur'] = str_replace('public/', 'storage/', $valeur['valeur']);

            $affichage = $this->affichage_valeur($titre, $valeur['management_element']);
            $valeur['affichage'] = $affichage;
            $valeur['affichage_select'] = strip_tags($affichage);

        }

        return $valeurs;
    }

    public function methodes_post_suppression($modele){

        $recherches_avancees = modele('recherche_avancee')
            ->where('type', $this->_type_element.'_'.$modele->id)
            ->get();

        foreach($recherches_avancees as $recherche_avancee){
            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
        }

        return parent::methodes_post_suppression($modele);
    }

    public function valeur_liste($modele = null){

        if($modele === null)
            $modele = $this->modele;

        if(!empty($modele->{$this->champ_valeur_dur}))
            return traduction('champs_libres.'.$this->_type_element.'.'.$this->champ_valeur_dur.'.nom'). ' : '.$this->champ($this->champ_valeur_dur)->affiche($modele->{$this->champ_valeur_dur});

        if(!empty($modele->parametrage_existant)){

            $management_element = management($this->_type_element, $modele->id, $modele);
            $parametrage_existant = explode('.', $modele->parametrage_existant);

            if($parametrage_existant[0] == 'type_element'){
                return traduction('champs_libres.'.$this->_type_element.'.parametrage_existant.nom'). ' : '.traduction('tables_libres.'.$management_element->type_element().'.nom_table');
            }
            else{
                $management_parametrage_existant = management($parametrage_existant[0], $parametrage_existant[1]);

                return traduction('champs_libres.'.$this->_type_element.'.parametrage_existant.nom'). ' : '.traduction('tables_libres.'.$parametrage_existant[0].'.nom_table').' - '.$management_parametrage_existant->affichage_pour_select();
            }
        }

        if(!empty($modele->nom_simplifie))
            return '<span title="'.$this->valeur_texte_lien_champ($modele).'">Variable : '.$modele->nom_simplifie.'</span>';

        return 'Variable : '.$this->valeur_texte_lien_champ($modele);
    }

    public function valeur_texte_lien_champ($modele){

        if($modele === null)
            $modele = $this->modele;

        $lien_champ = $modele->lien_champ;

        if(empty($lien_champ))
            return '';

        $liens = explode('/', $lien_champ);

        $chaine_texte = '';

        foreach($liens as $index => $lien){

            if($index > 0)
                $chaine_texte .= ' => ';

            if(strpos($lien, 'table_libre|') === 0){

                $table_libre_lien = explode('.',str_replace('table_libre|', '', $lien));

                $chaine_texte.= 'Table libre : '.traduction('tables_libres.'.$table_libre_lien[0].'.nom_table');
                
                if(!empty($table_libre_lien[1]))
                    $chaine_texte.= ' par le champ '.strtolower(traduction('champs_libres.'.$table_libre_lien[0].'.'.$table_libre_lien[1].'.nom')).' ('.$table_libre_lien[1].')';
            }
            else if(strpos($lien, 'element_table_libre_final|') === 0){ 
                $element_table_libre_final = explode('.',str_replace('element_table_libre_final|', '', $lien)); 

                $chaine_texte.= traduction('tables_libres.'.$element_table_libre_final[0].'.nom_table').' : '.(isset($element_table_libre_final[1]) ? management($element_table_libre_final[0],$element_table_libre_final[1])->affiche() : 'Tous'); 
            }
            else{

                $champ_libre_lien = explode('.',$lien);

                if($champ_libre_lien[1] == 'id')
                    $chaine_texte.='ID';
                else
                    $chaine_texte.= traduction('champs_libres.'.$champ_libre_lien[0].'.'.$champ_libre_lien[1].'.nom').' ('.$champ_libre_lien[1].')';
            }
        }

        return $chaine_texte;
    }

    public function valeur_title(){
        return 'Variable : '.(!empty($this->modele->nom_simplifie) ? $this->modele->nom_simplifie : $this->valeur_texte_lien_champ($this->modele));
    }
}