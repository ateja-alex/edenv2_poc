<?php

use App\Eden\Models\Champ_libre;

/*
*
* Helper pour récupérer les éléments liés à un élémént
*
* @param $type_element string
*
*/
function elements_lies($type_elements_recherche, $type_element_source, $id_element_source, $champ_liaison) {

    $champ_type_element = false;
    $elements = [];

    $champ_liaison = Champ_libre::where('nom_sql', $champ_liaison)->where('type_element', $type_elements_recherche)->first();

    if(empty($champ_liaison))
        return $elements;

    if($champ_liaison->type == 22) {
        $champ_type_element = Champ_libre::where('type_element', $type_elements_recherche)->where('nom_sql', $champ_liaison->contenu)->first();
    }else if($champ_liaison->type == 10) {
        return modele($type_elements_recherche)
            ->select("{$type_elements_recherche}.*")
            ->rightJoin($champ_liaison->table_pivot, "{$champ_liaison->table_pivot}.cle_locale", "{$type_elements_recherche}.id")
            ->where("{$champ_liaison->table_pivot}.valeur", $id_element_source)
            ->get();
    }

    $elements = modele($type_elements_recherche)->where($champ_liaison->nom_sql, $id_element_source);

    if($champ_type_element !== false)
        $elements = $elements->where($champ_type_element->nom_sql, $type_element_source);

    $elements = $elements->get();

    return $elements;
}