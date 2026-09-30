<?php	

/**	
 * 	
 * Effectue un file_get_content en transmettant des données en post	
 * 	
 */	
function file_get_contents_post($url, $parametres) {	

	$ch = curl_init();
		
	// Configuration de l'URL et d'autres options
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_SSLVERSION, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parametres));
	curl_setopt($ch, CURLOPT_HTTPAUTH, 'CURLAUTH_NTLM'); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	
	// Récupération de l'URL et affichage sur le naviguateur
	$retour = curl_exec($ch);
	
	if(!empty(curl_error($ch)))
		dd_eden(curl_error($ch));
	
	// Fermeture de la session cURL
	curl_close($ch);
	
	return $retour;
}
