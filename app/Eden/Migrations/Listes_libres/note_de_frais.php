<?php

return [
	'type_element' => 'note_de_frais',
    'filtres_appliques' => [
	  [
        'operateur' => 0,
	    'blocs' => [],
	    'filtres' => [[
	        'type_element' => 'note_de_frais',
            'element_id' => null,
            'champ_liaison' => null,
	        'valeurs' => ["#utilisateur_connecte#"],
	        'nom_sql' => 'utilisateur_id',
        ]]
      ],
	],
	'desactiver_actions_individuelle' => '["modifier","supprimer","valider_les_notes_note_de_frais","refuser_les_notes_note_de_frais","comptabiliser_notes_de_frais"]',
	'lignes_par_page' => '50',
	'creation_taches_en_masse' => '1',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Saisie le", "valeur" => "cree_le", "ordre" => "2", "lien_vers_element" => "1", "type" => "standard", ),
		array("nom" => "Date NDF", "valeur" => "date", "ordre" => "3", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Projet", "valeur" => "projet_id", "ordre" => "4", "type" => "standard", ),
		array("nom" => "Catégorie de dépenses", "valeur" => "article_id", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Montant HT", "valeur" => "montant_ht", "ordre" => "6", "lien_vers_element" => "1", "type" => "standard", ),
		array("nom" => "Total TVA", "valeur" => "total_tva", "ordre" => "7", "type" => "standard", ),
		array("nom" => "Montant TTC", "valeur" => "montant_ttc", "ordre" => "8", "type" => "standard", ),
		array("nom" => "Pré-validé N+1", "valeur" => "pre_valide_dp", "ordre" => "9", "lien_vers_element" => "1", "type" => "standard", ),
		array("nom" => "Date pré validation par N+1", "valeur" => "date_pre_validation", "ordre" => "10", "type" => "standard", ),
		array("nom" => "Date prévi. remboursement", "valeur" => "date_previ_remboursement", "ordre" => "11", "type" => "standard", ),
		array("nom" => "Statut (compta)", "ordre" => "12", "methode" => "tags_pour_liste", "tri_desactive" => "1", "type" => "methode", ),
		array("nom" => "Date réelle de remboursement", "valeur" => "date_reelle_de_remboursement", "ordre" => "13", "type" => "standard", ),
		array("nom" => "Montant remboursé", "valeur" => "montant_rembourse", "ordre" => "14", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "montant_rembourse", "type_calcul" => "SUM", "nom" => "Total TTC selon date réelle remboursement", "split" => "date_reelle_de_remboursement", "unite" => "€", "ordre" => "1", ),
		array("nom_sql" => "montant_rembourse", "type_calcul" => "SUM", "nom" => "Total TTC par catégorie", "split" => "article_id", "unite" => "€", "ordre" => "2", ),
		array("nom_sql" => "montant_rembourse", "type_calcul" => "SUM", "nom" => "Montant global TTC remboursé", "unite" => "€", "ordre" => "3", ),
	],
	'filtres' => [
		array("nom_sql" => "projet_id", "ordre" => "2", ),
		array("nom_sql" => "date", ),
		array("nom_sql" => "accepte", "ordre" => "5", ),
		array("nom_sql" => "article_id", "ordre" => "3", ),
		array("nom_sql" => "pre_valide_dp", "ordre" => "4", ),
		array("nom_sql" => "rembourse", "ordre" => "6", ),
		array("nom_sql" => "montant_rembourse", "ordre" => "7", ),
		array("nom_sql" => "date_reelle_de_remboursement", "ordre" => "8", ),
	],
];