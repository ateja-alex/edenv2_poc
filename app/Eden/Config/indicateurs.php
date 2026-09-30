<?php

$indicateurs_specifiques = array();

if(file_exists(storage_path('app/eden_indicateurs.php'))) {
	
	$indicateurs_specifiques = include(storage_path('app/eden_indicateurs.php'));
}

$indicateurs_standard = [

    'client_ca' => false,
    'client_ca_12_mois_glissants' => false,
    'client_ca_depuis_janvier' => false,
    'client_date_de_derniere_facture' => false,
    'client_date_de_dernier_echange' => false,
    'client_encours' => false,
    'temps_transfo_premier_devis' => false,
    'delai_reponse_client' => false,
];

$indicateurs = array();

foreach($indicateurs_standard as $index => $valeur) {
	
	if(isset($indicateurs_specifiques[$index])) {
		
		$indicateurs[$index] = $indicateurs_specifiques[$index];
	}
	else {
		
		$indicateurs[$index] = $valeur;
	}
}

return $indicateurs;
