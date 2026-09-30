<?php

return [
		
	'type_element' => 'condition_commerciale',
    'fiche' => 'article_fournisseur',
    'cle_etrangere' => 'article_fournisseur_id',
	
	'colonnes' => [
		
        array("nom" => "Prix achat", "valeur" => "prix_achat", "ordre" => "5", "lien_vers_element" => ""),
        array("nom" => "Palier de quantité", "valeur" => "palier_quantite", "ordre" => "4", "lien_vers_element" => ""),
        array("nom" => "Catalogue tarif", "valeur" => "catalogue_tarif_id", "ordre" => "2", "lien_vers_element" => "1"),
        array("nom" => "Modifié le", "valeur" => "modifie_le", "ordre" => "1", "lien_vers_element" => "1", "tri_par_defaut" => "1", "sens_tri_par_defaut" => "desc"),
	],
	
	'calculs' => [],
	'filtres' => [],
];