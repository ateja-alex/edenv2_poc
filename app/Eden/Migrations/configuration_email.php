<?php

return [
    'table_libre' => [
        'nom_table' => "Configurations Emails",
        'nom_table_sql' => "configuration_email",
        'description' => "",
        'feminin' => "",
        'element' => "configuration email",
        'type_element' => "configuration_email",
        'element_pluriel' => "configurations emails",
        'fiche' => 0,
        
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#nom#',
        'table_systeme' => 0
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'recherche' => 1,
        ],
        'protocole' => [
            'nom' => "Protocole",
            'type' => 20,
            'liste_choix' => 601,
        ],
        'adresse_host' => [
            'nom' => "Adresse Host",
        ],
        'port' => [
            'nom' => "Port",
        ],
        'cryptage_utilise' => [
            'nom' => "Cryptage utilisé",
        ],
        'adresse_email_par_defaut' => [
            'nom' => "Adresse mail",
        ],
        'nom_expediteur_par_defaut' => [
            'nom' => "Nom de l'expéditeur",
        ],
        'mot_de_passe_par_defaut' => [
            'nom' => "Mot de passe par défaut",
            'format_champ' => "password",
        ],
        'login' => [
            'nom' => "Login",
        ],
        'type' => [
            'nom' => "Type",
            'liste_choix' => 592,
            'type' => 20,
        ],
        'valeur_par_defaut' => [
            'nom' => "Valeur par défaut",
            'liste_choix' => 14,
            'type' => 20,
        ],
    ],
];
