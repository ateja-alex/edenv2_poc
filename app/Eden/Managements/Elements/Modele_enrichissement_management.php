<?php

namespace App\Eden\Managements\Elements;

class Modele_enrichissement_management extends Element_management {

    private $champs = false;

    public function enregistre($modifications = array(), $modele = false){

        if(array_key_exists('champs', $modifications)){
            $this->champs = $modifications['champs'];
            unset($modifications['champs']);
        }

        parent::enregistre($modifications, $modele);
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        parent::methodes_post_modification($modele, $modele_avant, $modifications);

        if($this->champs !== false){
            $this->enregistre_champs($this->champs);
        }
    }

    public function enregistre_champs($champs){

        $champs_existants = modele('modele_enrichissement_champ')
            ->where('modele_enrichissement_id', $this->modele->id)
            ->get()->keyBy('nom_sql');

        foreach($this->champs as $champ){

            $management = management('modele_enrichissement_champ');

            if(isset($champs_existants[$champ['nom_sql']])){
                $modele = $champs_existants[$champ['nom_sql']];
                $management = management('modele_enrichissement_champ',$modele->id,$modele);
                unset($champs_existants[$champ['nom_sql']]);
            }

            $management->enregistre([
                'modele_enrichissement_id' => $this->modele->id,
                'type' => $champ['type'],
                'nom_sql' => $champ['nom_sql'],
            ]);

        }

        foreach($champs_existants as $champ){
            management('modele_enrichissement_champ', $champ->id, $champ)->supprime();
        }
    } 

    public function methodes_post_suppression($modele){

        parent::methodes_post_suppression($modele);

        $elements = modele('modele_enrichissement_champ')
            ->where('modele_enrichissement_id', $modele->id)
            ->get();

        foreach($elements as $element){
            management('modele_enrichissement_champ', $element->id, $element)->supprime();
        }
    }
}