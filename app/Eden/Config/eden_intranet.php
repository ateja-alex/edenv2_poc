<?php

$structure_intranet = [
    'lignes' => array(
        [
            'id' => 1,
            'modules' => array(
                array(
                    'id' => 5,
                    'type_module' => 'module_par_defaut',
                    'module_par_defaut' => 'annuaire',
                    'taille_avant' => 0,
                    'taille' => 3,
                    'taille_apres' => 0,
                    'icone' => 'fas fa-phone',
                    'couleur' => '#68AF27',
                ),
                array(
                    'id' => 6,
                    'type_module' => 'module_par_defaut',
                    'module_par_defaut' => 'mon_compte',
                    'taille_avant' => 0,
                    'taille' => 6,
                    'taille_apres' => 0,
                    'icone' => 'fas fa-cog',
                    'couleur' => '#C22439',
                ),
                array(
                    'id' => 7,
                    'type_module' => 'redirection',
                    'route' => 'deconnexion',
                    'taille_avant' => 0,
                    'taille' => 3,
                    'taille_apres' => 0,
                    'icone' => 'fas fa-power-off',
                    'couleur' => '#D53F26',
                ),
            ),
        ]
    ),
    'parametrage' => array(
        'bloc' => array(
            'taille' => '200',
            'taille_icone' => '80',
            'taille_texte' => '24'
        ),
    )
];

if (file_exists(storage_path('app/eden_intranet.php'))) {
    $structure_intranet_specifique = json_decode(file_get_contents(storage_path('app/eden_intranet.php')), true);

    $structure_intranet_specifique['parametrage'] = array_merge_recursive_distinct($structure_intranet['parametrage'],$structure_intranet_specifique['parametrage']);

    $structure_intranet = $structure_intranet_specifique;
}

return $structure_intranet;