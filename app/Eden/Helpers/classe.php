<?php

/*
*
* Helper qui retourne la premiere classe existante d'une liste de candidats
*
* @param $tests array
*
* @return string|null
*
*/
function classe_existante($tests) {

	foreach($tests as $classe)
		if(class_exists($classe))
			return $classe;

	return null;
}
