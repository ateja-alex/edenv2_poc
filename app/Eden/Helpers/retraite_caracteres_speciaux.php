<?php

/**
 *
 * Helper pour enlever les accents et caractères spéciaux d'une chaine
 *
 * @param $chaine string
 * @param $separateur string
 *
 */
function retraite_caracteres_speciaux($chaine, $separateur = '') {

    $accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å',
            'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'é','é', // (le dernier 'é' n'est pas un doublon, c'est un caractère spécial)
            'Ç', 'ç',
            'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï',
            'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü',
            'ÿ',
            'Ñ', 'ñ');

    $sans_accents = str_split('AAAAAAaaaaaaOOOOOOooooooEEEEeeeeeeCcIIIIiiiiUUUUuuuuyNn');

    $nouvelle_chaine = str_replace($accents, $sans_accents, $chaine);

    $nouvelle_chaine = preg_replace("[^A-Za-z0-9]", '', $nouvelle_chaine);

    // autres caractères spéciaux
    $a_remplacer = array(
        "&",
        "-",
        "–",
        "()",
        "{",
        "}",
        "!",
        "?",
        ",",
        ";",
        ".",
        "…",
        ":",
        "/",
        "\#",
        "[",
        "]",
        "'",
        "\"",
        " ",
        "~",
        "`",
        "^",
        "@",
        "°",
        "+",
        "=",
        "£",
        "€",
        "¤",
        "*",
        "%",
        "§",
        "<",
        ">",
        "«",
        "»",
        "\$",
        "²",
        " ");

    $a_retirer = str_split('’̀');

    $nouvelle_chaine = str_replace($a_remplacer, $separateur, $nouvelle_chaine);

    $nouvelle_chaine = str_replace($a_retirer, '', $nouvelle_chaine);

	// pour éviter d'avoir 2 séparateurs à la suite
	if(!empty($separateur)) {

		$nouvelle_chaine = strtolower(str_replace($separateur.$separateur, $separateur, $nouvelle_chaine));
	}

    return $nouvelle_chaine;
}
