<?php

namespace App\Eden\Managements\Fiches\Independant;

use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Fiche_management;

class Fiche_independant_management extends Fiche_management {

    public $independant = true;
    public $type_element = null;

    public function modules_disponibles(){


        $emplacements = array(
            'standard' => app_path('Eden/Views/fiches/include/'.$this->type_element),
            'specifique' => resource_path('views/vendor/eden/fiches/include/'.$this->type_element)
        );

        $modules = array();

        foreach($emplacements as $type_emplacement => $chemin){

            if (is_dir($chemin)) {

                $repertoire = scandir($chemin);

                foreach ($repertoire as $fichier) {

                    if ($fichier == '.' || $fichier == '..' || !is_file($chemin.'/'.$fichier))
                        continue;

                    $fichier = str_replace('.blade.php', '', $fichier);

                    $index_traduction = 'module_sur_fiche_independant.'.$this->type_element.'.' . $fichier . '.titre';

                    $traduction = traduction($index_traduction);

                    if ((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction) {

                        $nom_module = str_replace('_', ' ', $fichier);

                        $nom_module = ucfirst($nom_module);

                        service('traduction')->calcul_index_traduction(
                            18,
                            array(
                                'module_sur_fiche_independant',
                                $this->type_element,
                                $fichier
                            ),
                            array(
                                'titre' => $nom_module,
                            ),
                            $type_emplacement == 'standard'
                        );

                        $traduction = $nom_module;

                        Cache_management::partage_oublie_traductions();
                        Cache_management::invalide();
                    }

                    $modules[$fichier] = array(
                        'nom' => $traduction . ' (' .$fichier. ')',
                    );
                }
            }
        }

        ksort($modules);

        return $modules;
    }
}