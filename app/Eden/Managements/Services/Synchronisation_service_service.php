<?php

namespace App\Eden\Managements\Services;


class Synchronisation_service_service {

    protected $tables_externes = [];

    public function defini_parametres($parametres){}

    public function tables_externes(){

        return $this->tables_externes;
    }

    public function table_externe($type_table_externe){

        return collect($this->tables_externes)->where('id', $type_table_externe)->first();
    }

    public function champs_externes($type_table_externe){

        $table_externe = collect($this->tables_externes)->where('id', $type_table_externe)->first();

        if(empty($table_externe))
            return [];

        return $this->gestion_champs_externes($type_table_externe,$table_externe['champs']);
    }

    public function champ_externe($type_table_externe, $champ){

        $champs_externes = $this->champs_externes($type_table_externe);

        $champs_partie = explode('.',$champ);

        foreach($champs_partie as $index => $champ){

            if($index == sizeof($champs_partie)-1)
                return collect($champs_externes)->where('nom', $champ)->first();
            else
                $champs_externes = collect($champs_externes)->where('nom', $champ)->first()['valeurs'] ?? [];
        }
    }

    public function gestion_champs_externes($type_table_externe, $champs_externes, $champ_parent = null){
            
        $champs = [];

        foreach($champs_externes as $champ_externe){

            $champ = [
                'nom' => $champ_externe['name'],
                'disponibilites' => $champ_externe['disponibilites'] ?? $champ_parent['disponibilites'] ?? []
            ];

            $types = [];

            if(empty($champ_externe['type']))
                $champ_externe['type'] = null;

            foreach((is_array($champ_externe['type']) ? $champ_externe['type'] : [$champ_externe['type']]) as $type){

                if(empty($type)){
                    if(isset($champ_externe['enumeration'])){
                        $types = array_merge($types,[['type' => 20],['type' => 1],['type' => 42]]);
                        $champ['valeurs'] = array_map(function($valeur){
                            return [
                                'nom' => $valeur['label'],
                                'valeur' => $valeur['value']
                            ];
                        },$champ_externe['enumeration']);
                    }
                }
                else if($type === 'array'){
                    $champ['valeurs'] = $this->gestion_champs_externes($type_table_externe,$this->{$champ_externe['name'].'_'.$type_table_externe}(), $champ_externe);
                    $champ['categorie'] = true;
                }
                else if($type === 'id')
                    $types = array_merge($types,[['type' => 42]]);
                else if($type === 'string')
                    $types = array_merge($types,[['type' => 0],['type' => 14]]);
                else if($type === 'text')
                    $types = array_merge($types,[['type' => 0],['type' => 6],['type' => 14]]);
                else if($type === 'float')
                    $types = array_merge($types,[['type' => 2],['type' => 3]]);
                else if($type === 'integer')
                    $types = array_merge($types,[['type' => 2]]);
                else if($type === 'date')
                    $types = array_merge($types,[['type' => 4],['type' => 5]]);
                else if($type === 'datetime')
                    $types = array_merge($types,[['type' => 5]]);
                else if($type === 'file')
                    // un champ "fichier" peut être un vrai champ de type 7, ou le champ "pdf" (stocké en texte)
                    // des documents de gestion commerciale
                    $types = array_merge($types,[
                        ['type' => 7],
                        ['nom_sql' => 'pdf', 'type_element' => \App\Eden\Variables::$documents_gescom],
                    ]);
            }

            $champ['types'] = $types;

            $champs[] = $champ;
        }

        return $champs;
    }
}