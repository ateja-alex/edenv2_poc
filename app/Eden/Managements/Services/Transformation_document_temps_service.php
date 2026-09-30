<?php

namespace App\Eden\Managements\Services;

class Transformation_document_temps_service{

    public function transformation_possible($type_element,$element_id = null){

        $champ_libre = champ_libre_modele('transformation_document_temps_modele','type_element_cible');

        $contenu = json_decode($champ_libre->contenu,true);

        if(sizeof(array_map(function($element) use ($type_element){
                return $element['type_element'] == $type_element && $element['valeur'] == true;
            },$contenu)) == 0)
            return false;

        //On vérifie qu'il y a des modèles créés pour l'élément
        $modeles = modele('transformation_document_temps_modele')
            ->where('type_element_cible',$type_element)
            ->get();

        if($modeles->isEmpty())
            return false;

        if(empty($element_id))
            return true;

        //On vérifie qu'il y a des activités associés à l'élément et que celle ci sont rattachés à un article
        $activites = modele('activite')
            ->where('type_element',$type_element)
            ->where('element_id',$element_id)
            ->get();

        if($activites->isEmpty())
            return false;

        return true;
    }
}