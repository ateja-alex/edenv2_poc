<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaire;
use App\Eden\Variables;

class Intranet_management {

    public function structure_intranet($tables_libres_disponibles){

        $structure_intranet = config('eden_intranet');

        if(!empty($structure_intranet['lignes'])) {

            foreach ($structure_intranet['lignes'] as &$ligne) {

                if(!empty($ligne['modules'])){

                    foreach($ligne['modules'] as &$module){

                        if($module['type_module'] == 'formulaire' && !empty($tables_libres_disponibles[$module['type_element']]))
                            $module['champs_libres_disponibles'] = $tables_libres_disponibles[$module['type_element']];
                    }
                }
            }
        }

        return $structure_intranet;
    }

    public function autres_modules(){

        $autres_modules = [];

        $repertoire_standard = scandir(app_path('Eden/Views/intranet/modules'));

        foreach ($repertoire_standard as $fichier) {

            if (in_array($fichier, array('.', '..', 'base_module.blade.php','formulaire')))
                continue;

            $fichier = str_replace('.blade.php','',$fichier);

            $autres_modules[$fichier] = array(
                'type_module' => 'module_par_defaut',
                'module_par_defaut' => $fichier,
                'taille_avant' => 0,
                'taille' => 3,
                'taille_apres' => 0,
                'icone' => '',
                'couleur' => '',
                'nom_module' => '',
            );
        }

        if(is_dir('resources/views/vendor/eden/intranet/modules')) {

            $repertoire_specifique = scandir('resources/views/vendor/eden/intranet/modules');

            foreach ($repertoire_specifique as $fichier) {

                if (in_array($fichier, array('.', '..', 'base_module.blade.php','formulaire')))
                    continue;

                $fichier = str_replace('.blade.php','',$fichier);

                $autres_modules[$fichier] = array(
                    'type_module' => 'module_par_defaut',
                    'module_par_defaut' => $fichier,
                    'taille_avant' => 0,
                    'taille' => 3,
                    'taille_apres' => 0,
                    'icone' => '',
                    'couleur' => '',
                    'nom_module' => '',
                );
            }
        }

        $autres_modules[] = array(
            'type_module' => 'redirection',
            'route' => 'deconnexion',
            'module_par_defaut' => 'deconnexion',
            'taille_avant' => 0,
            'taille' => 3,
            'taille_apres' => 0,
            'icone' => 'fas fa-power-off',
            'couleur' => '#D53F26',
            'nom_module' => '',
        );

        $autres_modules = array_values($autres_modules);

        return $autres_modules;
    }

    public function derniers_ids($structure_intranet){

        $dernier_id_module = -1;
        $dernier_id_ligne  = -1;

        if(empty($structure_intranet['lignes']))
            return array(
                'dernier_id_module' => $dernier_id_module,
                'dernier_id_ligne' => $dernier_id_ligne
            );

        foreach($structure_intranet['lignes'] as $ligne){

            if($ligne['id'] > $dernier_id_ligne)
                $dernier_id_ligne = $ligne['id'];

            if(empty($ligne['modules']))
                continue;

            foreach($ligne['modules'] as $module){

                if($module['id'] > $dernier_id_module)
                    $dernier_id_module = $module['id'];
            }
        }

        return array(
            'dernier_id_module' => $dernier_id_module,
            'dernier_id_ligne' => $dernier_id_ligne
        );
    }

    public function modules_par_defaut(){
        $modules_par_defaut = [[
                'type_module' => 'liste',
                'type_element' => '',
                'taille_avant' => 0,
                'taille' => 3,
                'taille_apres' => 0,
                'icone' => 'far fa-eye',
                'couleur' => '',
                'nom_module' => '',
            ],
            [
                'type_module' => 'formulaire',
                'type_element' => '',
                'champs_libres_disponibles' => '',
                'champ_utilisateur' => '',
                'taille_avant' => 0,
                'taille' => 3,
                'taille_apres' => 0,
                'icone' => 'far fa-edit',
                'couleur' => '',
                'nom_module' => '',
            ]
        ];

        return $modules_par_defaut;
    }

}