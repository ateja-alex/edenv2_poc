<?php

/*
*
* Helper pour aller chercher le management pour une fiche
*
* @param $type_element string
*
*/
function fiche($type_element, $id_element = 0) {

    $documents = \App\Eden\Variables::$documents_gescom;
    $documents_vente = \App\Eden\Variables::$documents_vente_gescom;
    $documents_achat = \App\Eden\Variables::$documents_achat_gescom;

    if (in_array($type_element, $documents)) {
        $classes = array(
            "\\App\\Managements\\Fiches\\Document\\Fiche_" . $type_element . "_management",
            "\\App\\Eden\\Managements\\Fiches\\Document\\Fiche_" . $type_element . "_management",
        );
    } else {
        $classes = array(
            "\\App\\Managements\\Fiches\\Independant\\Fiche_" . $type_element . "_management",
            "\\App\\Eden\\Managements\\Fiches\\Independant\\Fiche_" . $type_element . "_management",
            "\\App\\Managements\\Fiches\\Fiche_" . $type_element . "_management",
            "\\App\\Eden\\Managements\\Fiches\\Fiche_" . $type_element . "_management",
        );
    }

    if (in_array($type_element, $documents_vente))
        $classes[] = "\\App\\Eden\\Managements\\Fiches\\Document\\Fiche_document_vente_management";

    if (in_array($type_element, $documents_achat))
        $classes[] = "\\App\\Eden\\Managements\\Fiches\\Document\\Fiche_document_achat_management";

    $classes[] = "\\App\\Eden\\Managements\\Fiche_management";

    $classe = classe_existante($classes);

    if(!empty($classe)) {

    	$management = new $classe();
    	$management->type_element = $type_element;
    	$management->id_element = $id_element;

    	return $management;
    }

}
