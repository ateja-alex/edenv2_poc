<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Client', 'valeur' => 'client_id_ligne', 'ordre' => 1),
		array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 2),
		array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 3),
		array('nom' => 'PU', 'valeur' => 'tarif', 'ordre' => 4),
		array('nom' => 'Reste à commander', 'valeur' => 'transforme_reliquat_commande_fournisseur', 'ordre' => 5),
		array('nom' => 'Reste à recevoir', 'valeur' => 'transforme_reliquat_reception_fournisseur', 'ordre' => 6),
		array('nom' => 'Reste à livrer', 'valeur' => 'transforme_reliquat', 'ordre' => 7),
	],
	'calculs' => [
		
	],
	'filtres' => [
		
	],
];