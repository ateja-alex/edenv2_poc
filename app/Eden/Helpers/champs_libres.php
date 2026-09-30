<?php

use \App\Eden\Models\Table_libre;
use \App\Eden\Models\Champ_libre;

use \App\Eden\Managements\Parametrage\Champ_libre_management;

/*
*
* Helper pour aller chercher les champs libres d'une table libre
*
* @param $type_element string
*
*/
function champs_libres($type_element) {

	return clone cache_eden('champs_libres_objet.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where(function($r) { $r->where('inactif', 0)->orWhereNull('inactif'); })->get();

        foreach($champs_libres as $champ_libre){
            $champ_libre->index_traduction_type_element = 'tables_libres.'.$champ_libre->type_element.'.nom_table';
        }

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres uniques d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_uniques($type_element) {

	return clone cache_eden('champs_libres_uniques.'.$type_element, function() use ($type_element) {

        $champs_libres = Champ_libre::where('type_element', $type_element)
            ->where(function ($requete) {
                $requete->where('unique', 1)->orWhere('unique_entite', 1);
            })->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres PJ d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_pj($type_element) {

	return clone cache_eden('champs_libres_pj.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('type', 7)->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres de type sous formulaire d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_sous_formulaire($type_element) {

	return clone cache_eden('champs_libres_sous_formulaire.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('type', -5)->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres obligatoires d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_obligatoires($type_element) {

	return clone cache_eden('champs_libres_obligatoires.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('obligatoire', 1)->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres recherche d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_recherche($type_element) {

	return clone cache_eden('champs_libres_recherche.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('recherche', 1)->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres de type liste d'utilisateurs d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_liste_utilisateurs($type_element) {

	return clone cache_eden('champs_libres_liste_utilisateurs.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where(function($r) {

			$r->where('liste_choix', 1)->orWhere('type_element_ajax', 'utilisateur');
		})->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres de type textaera d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_textarea($type_element) {

	return clone cache_eden('champs_libres_textarea.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('type', 6)->get();

		return $champs_libres;
	});
}

function champs_libres_management($champs_libres){

    $champs_libres_management = [];

    foreach($champs_libres as $champ_libre){

        $type_element = $champ_libre->type_element;
        $nom_sql = $champ_libre->nom_sql;

        $champs_libres_management[$type_element.'.'.$nom_sql] = management($type_element)->champ($nom_sql);
    }

    return $champs_libres_management;
}

/*
 *
 * Helper pour aller chercher les champs libres de type numérotation automatique d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_numerotation_automatique($type_element) {

	return clone cache_eden('champs_libres_numerotation_automatique.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('type', 14)->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres de type multiselect d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_multiselection($type_element) {

	return clone cache_eden('champs_libres_multiselection.'.$type_element, function() use ($type_element) {

		$champs_libres = Champ_libre::where('type_element', $type_element)->where('type', 10)->get();

		return $champs_libres;
	});
}

/*
 *
 * Helper pour aller chercher les champs libres de type numérotation automatique d'une table libre
 *
 * @param $type_element string
 *
 */
function champs_libres_elements($types_elements) {

    $champs_libres = [];

    foreach ($types_elements as $index => $type_element){

        $en_cache = lit_cache_eden('champs_libres_elements.'.$type_element);

        if($en_cache !== null) {
            $champs_libres[$type_element] = $en_cache;
            unset($types_elements[$index]);
        }

    }

    if(empty($types_elements))
        return $champs_libres;

    $champs_libres_tmps = Champ_libre::select('eden_champslibres.index_traduction','eden_champslibres.nom_sql','eden_champslibres.contenu','eden_champslibres.type','eden_champslibres.type_element_ajax','ec2.contenu as contenu_ec2','eden_champslibres.type_element')
        ->whereIn('eden_champslibres.type_element', $types_elements)
        ->leftJoin('eden_champslibres as ec2',function($join){
            $join->on('ec2.nom_sql', 'eden_champslibres.contenu')
                ->on('ec2.type_element', 'eden_champslibres.type_element');
        })
        ->get()->groupBy(['type_element', 'type']);

    foreach ($types_elements as $type_element){

        if(!empty($champs_libres_tmps[$type_element])){

            ecrit_cache_eden('champs_libres_elements.'.$type_element, $champs_libres_tmps[$type_element]);
            $champs_libres[$type_element] = $champs_libres_tmps[$type_element];

        }

    }

    return collect($champs_libres);
}

/*
 *
 * Helper pour aller chercher les triggers d'un type élément
 *
 * @param $type_element string
 *
 */
function triggers($type_element) {

    try {

        return clone cache_eden('triggers.'.$type_element, function() use ($type_element) {

            $log_champs = \DB::table('trigger_eden_log_champ')
                ->select('trigger_eden.id', 'champ')
                ->join('trigger_eden', 'trigger_eden.id', 'trigger_eden_log_champ.trigger_eden_id')
                ->where('trigger_eden.enregistrement_log',1)
                ->get()
                ->groupBy('id')
                ->map(fn($action) => $action->pluck('champ'))
                ->toArray();

            $triggers = modele('trigger_eden')
                ->select('trigger_eden.*')
                ->join('eden_tableslibres', 'eden_tableslibres.id', 'trigger_eden.type_element_id')
                ->where('eden_tableslibres.type_element', $type_element)
                ->orderBy('ordre')
                ->get();

            foreach ($triggers as $trigger) {
                $trigger->logs_champ = isset($log_champs[$trigger->id]) ? $log_champs[$trigger->id] : [];
            }

            return $triggers;
        });
    }
    catch(Exception $e){
        return [];
    }
}

/*
 *
 * Helper pour aller chercher les modèles de document d'un type élément
 *
 * @param $type_element string
 *
 */
function modeles_de_documents($type_element) {

    try {

        return clone cache_eden('modeles_de_documents.'.$type_element, function() use ($type_element) {

            return modele('modele_de_document')
                ->where('type_element_autres', $type_element)
                ->get();
        });
    }
    catch(Exception $e){
        return [];
    }
}

/*
 *
 * Helper pour vérifier si une valeur est vide
 *
 */
function valeur_vide($champ_libre,$valeur){
    if (in_array($champ_libre->type, [0, 6, 2, 3, 17]) && $valeur == "0") {
        return false;
    }else if(empty($valeur)){
        return true;
    }else{
        return false;
    }
}
