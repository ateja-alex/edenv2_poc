<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use Illuminate\Support\Facades\DB;
use App\Eden\Migrations\Scripts\Script;

class S20260216_transfert_menus_dans_bdd implements Script {

    public function execute() {

        $menus = modele('menus')->get();

        if($menus->isNotEmpty())
            return true;

        $menus = collect();

        foreach(['Menu principal', 'Menu principal extranet'] as $nom_menu) {

            $management_menu = management('menus');
            $management_menu->enregistre(['nom' => $nom_menu, 'extranet' => str_contains($nom_menu, 'extranet') ? 1 : null]);

            $menus->push(clone $management_menu->modele);
        }

        $management_categorie = management('menus_categories');
        $management_lien = management('menus_liens');

        $correspondance_types_lien = [
            'liste' => 1,
            'rapport' => 2,
            'url_simple' => 3,
            'url_externe' => 4,
            'autre' => 5,
        ];

        foreach ($menus as $menu) {

            if($menu->extranet && file_exists(storage_path('app/eden_menus_extranet.php')))
                $menus_fichier = include(storage_path('app/eden_menus_extranet.php'));
            else if($menu->extranet)
                $menus_fichier = config('eden_menus_extranet');
            else if(file_exists(storage_path('app/eden_menus.php')))
                $menus_fichier = include(storage_path('app/eden_menus.php'));
            else
                $menus_fichier = config('eden_menus');

            $ancien_type_profil = $menu->extranet ? 'menu_extranet' : 'menu';

            foreach($menus_fichier as $menu_fichier){

                $modifications_communes = [
                    'id_menu_parent' => $menu->id,
                    'nom' => $menu_fichier['nom'] ?? null,
                    'icone' => $menu_fichier['icone'] ?? null,
                    'ordre' => $menu_fichier['ordre'] ?? null,
                    'desactive' => !empty($menu_fichier['inactif']) ? 1 : null,
                ];

                if(isset($menu_fichier['sous_menus'])) {

                    $management_categorie->enregistre($modifications_communes);

                    foreach($menu_fichier['sous_menus'] as $sous_menu) {

                        $management_lien->enregistre([
                            'nom' => $sous_menu['nom'] ?? null,
                            'icone' => $sous_menu['icone'] ?? null,
                            'ordre' => $sous_menu['ordre'] ?? null,
                            'desactive' => !empty($sous_menu['inactif']) ? 1 : null,
                            'id_categorie_parent' => $management_categorie->modele->id,
                            'type_lien' => $correspondance_types_lien[$sous_menu['type_2']] ?? null,
                            'route' => $sous_menu['route'][0] ?? null,
                            'ordre' => $sous_menu['ordre_sous_menu'] ?? null,
                            'parametres' => !empty($sous_menu['route'][1]) ? $sous_menu['route'][1] : null,
                        ]);

                        $this->creer_index_traduction($sous_menu, $management_lien, 'menus_liens');

                        $this->mise_a_jour_droits_profils($ancien_type_profil, 'menus_liens', $sous_menu['id'], $management_lien->modele->id);

                        $management_lien->modele = null;
                    }

                    $this->creer_index_traduction($menu_fichier, $management_categorie, 'menus_categories');

                    $this->mise_a_jour_droits_profils($ancien_type_profil, 'menus_categories', $menu_fichier['id'], $management_categorie->modele->id);

                    $management_categorie->modele = null;
                }
                else {

                    $management_lien->enregistre(array_merge($modifications_communes, [
                        'type_lien' => $correspondance_types_lien[$menu_fichier['type_2']] ?? null,
                        'route' => $menu_fichier['route'][0] ?? null,
                        'parametres' => !empty($menu_fichier['route'][1]) ? $menu_fichier['route'][1] : null,
                    ]));

                    $this->creer_index_traduction($menu_fichier, $management_lien, 'menus_liens');

                    $this->mise_a_jour_droits_profils($ancien_type_profil, 'menus_liens', $menu_fichier['id'], $management_lien->modele->id);

                    $management_lien->modele = null;
                }
            }
        }
        return true;
    }

    private function creer_index_traduction($menu_fichier, $management, $type) {

        $ancien_index_traduction = $menu_fichier['index_traduction']. '.nom';
        $nouvel_index_traduction = $type . '.' . $management->modele->id;
        $index_existants = modele('traduction_index')->where('index', $ancien_index_traduction)->get();

        $informations_traductions_menu = [$type, $management->modele->id];
        service('traduction')->calcul_index_traduction(7, $informations_traductions_menu, ['nom' => $menu_fichier['nom']], env('BASE_TRADUCTION'));
    }

    private function mise_a_jour_droits_profils($ancien_type, $nouveau_type, $ancien_index, $nouveau_index) {

        modele('profil_droits_divers')->where('type', $ancien_type)->where('index', $ancien_index)->update(['type' => $nouveau_type, 'index' => $nouveau_index]);
    }
}