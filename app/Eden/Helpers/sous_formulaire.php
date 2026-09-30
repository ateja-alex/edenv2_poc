<?php

use App\Eden\Managements\Formulaire_management;
use App\Eden\Models\Formulaire;

function sous_formulaire($type_element_origine, $type_element_parent, $nom_formulaire, $name_remplacement_js, $type_element_remplacement = null, $remplacement_suplementaire = null){

    if(empty($type_element_remplacement))
        $type_element_remplacement = $type_element_origine;

    $tableau_a_remplacer = [
        '~name="(\w+)"~',
        '~name="(\w+)(\W+)"~',
        '~affichage_valeur_liste_libre\(' . $type_element_origine . '~',
        '~\:modele="' . $type_element_origine . '"~',
        '~([^.|_])' . $type_element_origine . '\.~',
        '~formulaire_champ_(\w+)~'
    ];

    $tableau_remplacement = [
        'name="' . $name_remplacement_js . '[${1}]"',
        'name="' . $name_remplacement_js . '[${1}]${2}"',
        'affichage_valeur_liste_libre(' . $type_element_parent . '.' . $type_element_origine,
        ':modele="' . $type_element_parent . '.' . $type_element_remplacement . '"',
        '${1}' . $type_element_parent . '.' . $type_element_remplacement . '.',
        'formulaire_champ_'.$name_remplacement_js.'_${1}'
    ];

    $formulaire = formulaire($nom_formulaire,'','',[],false, $type_element_parent);

    $formulaire = preg_replace($tableau_a_remplacer,$tableau_remplacement,$formulaire);

    $valeurs_a_remplacer = [];
    $valeurs_remplacement = [];

    if(isset($remplacement_suplementaire[0])) {
        foreach ($remplacement_suplementaire[0] as $valeur_a_remplacer) {

            $valeur_a_remplacer = preg_replace_callback("~#(.*?)#~", "remplace_traduction", $valeur_a_remplacer);

            $valeurs_a_remplacer[] = $valeur_a_remplacer;

        }
    }

    if(isset($remplacement_suplementaire[1])) {
        foreach ($remplacement_suplementaire[1] as $valeur_remplacement) {

            $valeur_remplacement = preg_replace_callback("~#(.*?)#~", "remplace_traduction", $valeur_remplacement);

            $valeurs_remplacement[] = $valeur_remplacement;

        }
    }

    if($remplacement_suplementaire !== null)
        $formulaire = str_replace($valeurs_a_remplacer, $valeurs_remplacement, $formulaire);


    return $formulaire;
}

function remplace_traduction($matches){

    if(!empty($matches[1]))
        return traduction_blade($matches[1]);

    return "";

}

function retraite_sous_formulaire($nom_sous_formulaire, $sous_formulaire = null){

    if(empty($sous_formulaire)) {
        $sous_formulaire = modele('eden_sous_formulaire')->where('nom_sous_formulaire', $nom_sous_formulaire)->first();

        if(empty($sous_formulaire))
            return false;

        $sous_formulaire = $sous_formulaire->toArray();
    }

    $remplacement_supplementaire = [];

    if(isset($sous_formulaire['remplacement_supplementaire']) && !is_array($sous_formulaire['remplacement_supplementaire']))
        $remplacement_supplementaire = json_decode($sous_formulaire['remplacement_supplementaire']);

    if(isset($sous_formulaire['data_vue']) && !is_array($sous_formulaire['data_vue']))
        $sous_formulaire['data_vue'] = json_decode($sous_formulaire['data_vue']);

    $remplacements_supplementaires_formate = [
        [],
        []
    ];

    foreach ($remplacement_supplementaire as $remplacement_supplementaire){

        $remplacements_supplementaires_formate[0][] = $remplacement_supplementaire[0];
        $remplacements_supplementaires_formate[1][] = $remplacement_supplementaire[1];

    }

    $sous_formulaire['remplacements_supplementaires'] = $remplacements_supplementaires_formate;

    $sous_formulaire['type_element_pour_nom'] = $sous_formulaire['type_element_enfant'];

    if(!empty($sous_formulaire['type_element_remplacement']))
        $sous_formulaire['type_element_pour_nom'] = $sous_formulaire['type_element_remplacement'];

    $formulaire_parametrable = Formulaire::where('nom_formulaire', $sous_formulaire['type_element_enfant'])->first();

    $formulaire_management = new Formulaire_management($formulaire_parametrable);

    $champs_obligatoires = $formulaire_management->champs_obligatoires();

    foreach($champs_obligatoires as $index_champ => $champ_obligatoire){

        if($champ_obligatoire['nom_sql'] == $sous_formulaire['champ_liaison'])
            unset($champs_obligatoires[$index_champ]);
    }

    $sous_formulaire['champs_obligatoires'] = array_values($champs_obligatoires);

    return $sous_formulaire;
}