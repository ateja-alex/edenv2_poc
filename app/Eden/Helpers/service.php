<?php

/*
*
* Helper pour aller chercher un service
*
* @param $nom_de_la_classe string
*
*/
function service($nom_de_la_classe,$uniquement_standard = false) {

    if($uniquement_standard === true) {
        $tests = ["\\App\\Eden\\Managements\\Services\\" . ucfirst($nom_de_la_classe) . "_service",];
    }
    else {
        $tests = [

            "\\App\\Managements\\Services\\" . ucfirst($nom_de_la_classe) . "_service",
            "\\App\\Eden\\Managements\\Services\\" . ucfirst($nom_de_la_classe) . "_service",
        ];
    }

	$classe = classe_existante($tests);

	if(!empty($classe)) {

		$service = new $classe($nom_de_la_classe);

		return $service;
	}

}