<?php

$dernier_temps_pour_log_eden = 0;
$temps_par_action_log_eden = array();

/**
 *
 * Permet de loguer un texte dans le fichier de log du storage,
 * et indique le temps entre deux appels et depuis le début
 *
 * @param $texte string ou array
 * @param $decalage int permet visuellement de décaler le texte dans le fichier de log
 *
 */
function log_eden($texte, $decalage = 0) {

    // les logs ne sont pas activés dans le .env
	if(env('EDENPME_LOG_LEVEL', -1) < $decalage)
		return;
	
	global $dernier_temps_pour_log_eden;
	global $temps_par_action_log_eden;

	$decimales = 3;
	
	$temps = round(microtime(true) - LARAVEL_START, $decimales);
	
	if(is_array($texte))
		\Log::info($texte);
	else {

        // on calcule le temps depuis le dernier appel à log_eden
		$temps_depuis_dernier = round($temps - $dernier_temps_pour_log_eden, $decimales);
		
		$dernier_temps_pour_log_eden = $temps;
		
		$texte_avant_decalage = $texte;

        // on ajoute des .. pour créer un décalage visuel dans le fichier de log
		if($decalage > 0) {
			
			for($i=1; $i<=$decalage; $i++)
				$texte = '...........'.$texte;
		}

        // permet d'aligner correctement les valeurs dans le fichier de log pour plus de lisibilité
		$temps_string = $temps;
		$temps_string = (string) $temps_string;
		$temps_string = substr($temps_string.'0000', 0, 6);
		
		if(!empty($temps_depuis_dernier)) {
			
			$temps_depuis_dernier_string = $temps_depuis_dernier;
			$temps_depuis_dernier_string = (string) $temps_depuis_dernier_string;
			$temps_depuis_dernier_string = substr($temps_depuis_dernier_string.'0000', 0, 6);
		}
		else {
			
			$temps_depuis_dernier_string = '      ';
		}
		
		if(!isset($temps_par_action_log_eden[$texte_avant_decalage]))
			$temps_par_action_log_eden[$texte_avant_decalage] = array('temps' => 0, 'appels' => 0);
		
		$temps_par_action_log_eden[$texte_avant_decalage]['temps'] += $temps_depuis_dernier;
		$temps_par_action_log_eden[$texte_avant_decalage]['appels']++;
		
		$texte = "___ $temps_string ___ $temps_depuis_dernier_string ___ $texte";
		
		\Log::info($texte);
	}
}

function affiche_logs_temps() {

    global $temps_par_action_log_eden;

    $temps = array();

    $temps_total = 0;

    foreach($temps_par_action_log_eden as $id => $infos) {

        $temps[$id] = $infos['temps'];
        $temps_total += $infos['temps'];
    }

    arsort($temps);

    $definitif = array();

    foreach($temps as $id => $un_temps) {

        $definitif[$id] = $un_temps.' ('.$temps_par_action_log_eden[$id]['appels'].')';
    }

    dd(array($definitif, $temps_total));
}

/**
 *
 * Permet de loguer une erreur, l'utilisateur lié et le titre de l'action en erreur
 *
 * @param $titre_erreur string Titre qui sera affiché au début et à la fin du Log
 * @param $erreur object ou string Soit on passe une erreur sous forme de string ou alors un objet erreur
 *
 */
function log_mis_en_forme($titre_erreur, $erreur) {

    // On récupère l'utilisateur si il existe
    if(empty(moi()))
        $utilisateur = 'utilisateur_x';
    else
        $utilisateur = 'utilisateur_'.moi()->id;

    // on génère un "id" de log
    $id_log = $utilisateur.'_'.time();

    \Log::info('('.$id_log.') ----- DEBUT '.strtoupper($titre_erreur).' -----');

    // Cas où $erreur est un simple texte
    if(is_string($erreur)){

        \Log::info('('.$id_log.') MESSAGE : '.$erreur);
    }
    // Cas où c'est une exception
    else {

        \Log::info('('.$id_log.') FICHIER : '.$erreur->getFile());
        \Log::info('('.$id_log.') LIGNE   : '.$erreur->getLine());
        \Log::info('('.$id_log.') MESSAGE : '.$erreur->getMessage());
        \Log::info('('.$id_log.') TRACE   : '.$erreur->getTraceAsString());
    }

    \Log::info('('.$id_log.') ----- FIN '.strtoupper($titre_erreur).' -----');
}

