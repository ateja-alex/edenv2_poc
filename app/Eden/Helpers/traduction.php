<?php

/*
*
* Helper qui permet de gérer les traductions
*
*/

use Illuminate\Support\Facades\DB;

function traduction_blade($index, $champ = null, $index_vue_js = false, $parametres = array()) {

    $champ_valeur = $champ === null ? 'null' : "'$champ'";

    $index_vue_js_valeur = $index_vue_js === true ? 'true' : 'false';

    $parametres_string = '[]';

    if(!empty($parametres))
        $parametres_string = '["'.implode('","',str_replace('"','\"',$parametres)).'"]';

	return \Illuminate\Support\Facades\Blade::compileString('@traduction("'.$index.'", '.$champ_valeur.', '.$index_vue_js_valeur.', '.$parametres_string.')');
}

function traduction($index,$langue = null,$parametres = array()) {

    try {

        $langue = langue_utilisateur($langue);

        if ($langue === null)
            return $index;

        $traductions = cache_eden('traductions.'.$langue, function() use ($langue) {

            return modele('traduction_valeur')
                ->select('index', DB::raw('IFNULL(COALESCE(traduction_specifique,traduction_standard),"") AS valeur'))
                ->where('langue', $langue)
                ->get()->pluck('valeur', 'index')->toArray();
        });

        if (!isset($traductions[$index])) {

            if ($langue != 'fr')
                return traduction($index, 'fr', $parametres);

            return $index;
        }

        $traduction = traduction_remplacement_patternes($traductions[$index],$parametres);

        return $traduction;

    }
    catch(Exception | Throwable $e){
        return $index;
    }
}

function langue_utilisateur($langue = null){

    $langues_codes = cache_eden('langues_codes', fn() => langues()->pluck('code','id')->toArray());

    if ($langue == null) {

        $utilisateur = moi();

        if ($utilisateur !== null)
            $langue = $utilisateur->langue;
        else {

            $utilisateur_extranet = moi_extranet();

            if($utilisateur_extranet !== null && !empty($langues_codes[$utilisateur_extranet->langue]))
                $langue = $langues_codes[$utilisateur_extranet->langue];
        }

        if (empty($langue)) {

            $langue = 'fr';

            $langue_maquette = maquette('langue_par_defaut');

            if(isset($langues_codes[$langue_maquette]))
                $langue = $langues_codes[$langue_maquette];
        }
    }

    if (!in_array($langue, $langues_codes))
        return null;

    return $langue;
}

function traduction_remplacement_patternes($traduction,$parametres){

    $nombre_valeur_a_remplacer = substr_count($traduction,'#parametre_eden');

    if(!empty($parametres) && $nombre_valeur_a_remplacer > 0){

        $patternes = array();

        for($i = 1; $i <= $nombre_valeur_a_remplacer; $i++){
            $patternes[] = '/#parametre_eden_'.$i.'#/';
        }

        $traduction = preg_replace($patternes,$parametres,$traduction);
    }

    return $traduction;
}

function langues(){

    return cache_eden('langues', fn() => modele('traduction_langue')->get());
}