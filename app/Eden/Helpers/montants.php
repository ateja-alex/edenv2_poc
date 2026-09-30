<?php

/*
*
* Fonction qui sert à formater un montant
*
*/

function montant($montant, $decimales = 2,  $separateur_decimales = ',', $separateur_milliers = ' ',$supprimer_zeros_superflus = true, $supprimer_zero_superflus_champ_montant = true) {

	// on gère le cas du n/a
	if($montant === 'n/a')
		return $montant;

	// on gère le cas du n/a
	if(empty($montant) && $montant !== 0)
		return montant(0, $decimales, $separateur_decimales, $separateur_milliers);

	if(strpos($montant, '%') !== false)
		$montant_avec_formatage =  number_format(floatval(str_replace(array('%', ' %'), '', $montant)), $decimales, $separateur_decimales, $separateur_milliers).'%';
	else
		$montant_avec_formatage = number_format(floatval($montant), $decimales, $separateur_decimales, $separateur_milliers);

	if(($decimales > 2 || $supprimer_zeros_superflus == true) && fonctionnalite('supprimer_les_zeros_superflux') === true) {

		$montant_avec_formatage = supprimer_zeros_superflus($montant_avec_formatage, $separateur_decimales,$supprimer_zeros_superflus, $supprimer_zero_superflus_champ_montant);
    }

	return $montant_avec_formatage;

}

/*
*
* Fonction qui sert à formater un montant (avec un arrondi en K€ si nécessaire)
*
*/
function montant_lisible($montant, $decimales = 2,  $separateur_decimales = ',', $separateur_milliers = ' ', $taille_unite = false) {

	// on gère le cas du n/a
	if($montant == 'n/a')
		return $montant;

	if(abs($montant) >= 100) {

		if($taille_unite !== false)
			return number_format($montant / 1000, 1, $separateur_decimales, $separateur_milliers).' <span style="font-size: '.$taille_unite.'px;">K</span>';
		else
			return number_format($montant / 1000, 1, $separateur_decimales, $separateur_milliers).' K';
	}

	$decimales = 0;

	return number_format($montant, $decimales, $separateur_decimales, $separateur_milliers);

}

/**
 *
 *
 * Fonction qui permet de supprimer les zéros supperflus, si on a plus 2 chiffres après la virgule
 *
 */
function supprimer_zeros_superflus($montant_avec_formatage, $separateur_decimales,$supprimer_zeros_superflus, $supprimer_zero_superflus_champ_montant) {

	$montant_avec_formatage_spliter = explode($separateur_decimales, $montant_avec_formatage);

	if(!isset($montant_avec_formatage_spliter[1]))
        return $montant_avec_formatage;

    // on transforme la chaine de caractère en tableau
    $decimales = str_split($montant_avec_formatage_spliter[1]);
    $decimales_a_afficher = '';

    // par défaut, on affiche tjr 2 chiffres après la virgules, donc  on récupère les 2 premiers chiffres
    if(isset($decimales[0]) && $supprimer_zeros_superflus == false) {

        $decimales_a_afficher = $decimales[0];
        unset($decimales[0]);
    }

    if(isset($decimales[1]) && $supprimer_zeros_superflus == false) {

        $decimales_a_afficher .= $decimales[1];
        unset($decimales[1]);
    }

    $decimales = array_reverse($decimales);
    if($supprimer_zero_superflus_champ_montant === true) {

        // on supprime les 0 supperflus
        foreach ($decimales as $index => $decimale) {

            if ($decimale != 0)
                break;

            unset($decimales[$index]);
        }
    }
//    else{
//        foreach ($decimales as $index => $decimale) {
//
//            $decimales_a_afficher .= $decimale;
//        }
//    }

    $decimales_a_afficher .= implode('',array_reverse($decimales));

    if($decimales_a_afficher == '')
        return "$montant_avec_formatage_spliter[0]";

    return $montant_avec_formatage_spliter[0] . $separateur_decimales . $decimales_a_afficher;
}
