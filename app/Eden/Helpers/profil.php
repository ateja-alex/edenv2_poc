<?php

use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre;

/**
 *
 * Indique si l'utilisateur a le droit de création pour un type élément donné
 *
 */
function profil_creation($type_element, $id_entite = 0) {

	return profil($type_element, $id_entite, 'creation');
}

/**
 * 
 * Indique si l'utilisateur a le droit de modifier pour un type élément donnée
 *
 */
function profil_modification($type_element, $id_entite, $element) {

	return profil($type_element, $id_entite, 'modification', $element);
}

/**
 *
 * Indique si l'utilisateur a le droit de suppression pour un type élément donnée
 *
 */
function profil_suppression($type_element, $id_entite, $element) {

    return profil($type_element, $id_entite, 'suppression', $element);
}

/**
 *
 * Indique si l'utilisateur a le droit de comptabilisation pour un type élément donné
 *
 */
function profil_comptabilisation($type_element, $id_entite, $element) {

    return profil($type_element, $id_entite, 'comptabilisation', $element);
}

/**
 *
 * Indique si l'utilisateur a le droit de lecture pour un type élément donné
 *
 */
function profil_lecture($type_element, $id_entite, $element) {

	return profil($type_element, $id_entite, 'lecture', $element);
}

/** 
 * 
 * Vérifie le profil pour une action donnée
 * 
 */
function profil($type_element, $id_entite, $action, $element = false) {

    if(!session()->has('cache.droits_profils.element'))
        return true;

    $droits_profils_entites = session()->get('cache.droits_profils.element')[$type_element] ?? [];

    if(empty($droits_profils_entites))
        return true;

    $id_entite = empty($id_entite) ? 0 : $id_entite;

    $droit_valide = true;

    $calcul_droits = function($type_element,$action,$element,$droits_profils){

        $chargement_valeurs_multi_selection = false;

        $id_utilisateur = !empty(moi_extranet()) ? moi_extranet()->contact_selectionne->id : moi()->id;

        if(in_array($action,['creation','modification_en_masse','suppression_en_masse']))
            $droits_profils = $droits_profils->whereInStrict('nom_sql', [null, '']);

        if($droits_profils->isEmpty())
            return true;

        $droit_valide = false;

        foreach($droits_profils as $droit_profil){

            if($droit_profil[$action] == 1) {

                if(!empty($droit_profil['nom_sql']) && !empty($element->id)) {

                    $champ_libre = champ_libre_modele($type_element, $droit_profil['nom_sql']);

                    if($champ_libre->type == 42 && $element->{$droit_profil['nom_sql']} == $id_utilisateur)
                        $droit_valide = true;
                    elseif($champ_libre->type == 10) {

                        if(is_array($element->{$droit_profil['nom_sql']}) && !$chargement_valeurs_multi_selection)
                            $chargement_valeurs_multi_selection = true;
                        else if(!$chargement_valeurs_multi_selection){
                            $element = clone $element;
                            management($type_element,$element->id,$element)->charge_valeurs_champs_multiselection();
                            $chargement_valeurs_multi_selection = true;
                        }

                        if(in_array($id_utilisateur,$element->{$droit_profil['nom_sql']}))
                            $droit_valide = true;
                    }
                }
                elseif(empty($droit_profil['nom_sql']))
                    $droit_valide = true;
            }
        }

        return $droit_valide;
    };

    if(in_array($action,['creation','modification_en_masse','suppression_en_masse']) && $id_entite == 0) {

        $droits_profils = collect([]);

        foreach($droits_profils_entites as $collection){
            $droits_profils = $droits_profils->merge($collection);
        }

        $droit_valide = $calcul_droits($type_element, $action, $element, $droits_profils);
    }
    else if(isset($droits_profils_entites[$id_entite]))
        $droit_valide = $calcul_droits($type_element,$action,$element,$droits_profils_entites[$id_entite]);
    elseif(isset($droits_profils_entites[0]))
        $droit_valide = $calcul_droits($type_element,$action,$element,$droits_profils_entites[0]);

    return $droit_valide;
}