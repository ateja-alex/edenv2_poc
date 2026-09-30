<?php

function synchronisation_service_externe($type_element){

    return cache_eden('synchronisations.'.$type_element, function() use ($type_element) {

        $synchronisations = modele('synchronisation_service_element')
            ->select('synchronisation_service_element.*')
            ->where('synchronisation_service_element.type_element',$type_element)
            ->whereIn('synchronisation_service_element.type_synchronisation', [0, 3])
            ->join('synchronisation_service', 'synchronisation_service.id', '=', 'synchronisation_service_element.synchronisation_service_id')
            ->whereRaw('COALESCE(synchronisation_service.desactive,0) = 0')
            ->whereRaw('COALESCE(synchronisation_service_element.desactive,0) = 0')
            ->get();

        $synchronisation_champs = modele('synchronisation_service_champs')
            ->whereIn('synchronisation_service_element_id',$synchronisations->pluck('id'))
            ->get()->groupBy('synchronisation_service_element_id');

        $synchronisation_origine = modele('synchronisation_service')
            ->whereIn('id',$synchronisations->pluck('synchronisation_service_id'))
            ->get()->keyBy('id');

        foreach($synchronisations as $synchronisation){
            $synchronisation->origine = $synchronisation_origine[$synchronisation->synchronisation_service_id] ?? null;
            $synchronisation->champs = $synchronisation_champs->get($synchronisation->id,collect());
        }

        return $synchronisations;
    });
}

/*
*
* Helper pour savoir si une synchronisation service est en cours d'exécution sur le processus
*
* @param $valeur bool
*
*/
function synchronisation_service_en_cours($valeur = null) {

    static $en_cours = false;

    if($valeur !== null)
        $en_cours = $valeur;

    return $en_cours;
}