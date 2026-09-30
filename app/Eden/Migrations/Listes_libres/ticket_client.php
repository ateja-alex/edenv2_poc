<?php

return [
	'type_element' => 'ticket_client',
	'desactiver_actions_individuelle' => '[]',
	'formulaire_modale' => '1',
	'colonnes' => [
		array("nom" => "Titre", "valeur" => "titre", "ordre" => "7", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "9", ),
		array("nom" => "Date de prise en charge", "valeur" => "date", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Numéro", "valeur" => "numero", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Affecté à", "valeur" => "utilisateur_id", "ordre" => "8", "type" => "standard", ),
		array("nom" => "Type de problème", "valeur" => "type_probleme", "ordre" => "6", "type" => "standard", ),
		array("nom" => "Priorité", "valeur" => "priorite", "ordre" => "9", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "id", "type_calcul" => "COUNT", "nom" => "Tickets", "unite" => "tickets", "ordre" => "1", ),
	],
	'filtres' => [
		array("nom_sql" => "utilisateur_id", ),
		array("nom_sql" => "statut", "ordre" => "2", ),
		array("nom_sql" => "date", "ordre" => "3", ),
	],
    'couleurs' => [
        array(
            "couleur" => "#b8eaff",
            "filtres" => [
              [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'ticket_client',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => ["1"],
                        'nom_sql' => 'nouvel_echange',
                    ],
                ]
              ],
            ],
        ),
    ],
];