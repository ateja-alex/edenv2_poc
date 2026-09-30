<?php

$fonctionnalites_specifiques = [];

if(file_exists(storage_path('app/eden_fonctionnalites_specifiques.php'))) {
	
	$fonctionnalites_specifiques = include(storage_path('app/eden_fonctionnalites_specifiques.php'));
}

return $fonctionnalites_specifiques;
