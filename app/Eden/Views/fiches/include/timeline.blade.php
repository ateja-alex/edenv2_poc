@php

    $filtres_pour_fiche = [];

    // Cas spécial des fiches en mode campagne de prospection
    if(isset($campagne_de_prospection_en_cours)){

        $champs_libres = champs_libres('echange');

        foreach($champs_libres as $champ_libre){

            if($champ_libre->type == 42 && $champ_libre->type_element_ajax == 'campagne_de_prospection') {
                $filtres_pour_fiche[$champ_libre->nom_sql] = $campagne_de_prospection_en_cours->id;
            }
        }

    }
@endphp

<div>
	<section-timeline :type_element_parent="type_element" :parent_id="element_id" :contexte="'fiche_'+type_element" :filtres_appliques="{{ collect($filtres_pour_fiche) }}" :valeurs_par_defaut="{}"></section-timeline>
</div>
