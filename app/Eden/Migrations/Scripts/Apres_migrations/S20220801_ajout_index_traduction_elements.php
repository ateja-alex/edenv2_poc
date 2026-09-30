<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Parametrage\Menus_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Models\Rapport_libre;

class S20220801_ajout_index_traduction_elements implements Script {

    public function execute() {

        if(empty(parametre('S20220801_ajout_index_traduction_elements_champ_liste_libre'))) {

            $valeurs_listes_libres = Champ_libre_liste::get();
            $champs = Champ_libre::get()->keyBy('id_cl')->toArray();

            foreach ($valeurs_listes_libres as $valeur_liste_libre) {

                if (!isset($champs[$valeur_liste_libre->id_cl]) || !empty($valeur_liste_libre->index_traduction))
                    continue;

                $champ = $champs[$valeur_liste_libre->id_cl];

                $nom_valeur = strtolower(retraite_caracteres_speciaux($valeur_liste_libre->valeur, '_'));

                $valeur_liste_libre->index_traduction = service('traduction')->calcul_index_traduction(
                    8,
                    array(
                        'valeurs_listes_libres',
                        $champ['type_element'],
                        $champ['nom_sql'],
                        $nom_valeur
                    ),
                    array(
                        'nom' => $valeur_liste_libre->valeur,
                        'categorie' => $valeur_liste_libre->categorie,
                    )
                );

                $valeur_liste_libre->save();

            }

            parametre('S20220801_ajout_index_traduction_elements_champ_liste_libre',1);
        }

        if(empty(parametre('S20220801_ajout_index_traduction_elements_rapport'))) {
            $rapports = Rapport_libre::get();

            foreach ($rapports as $rapport) {

                if(empty($rapport->index_traduction) && $rapport->liste_sur_fiche != 1 && $rapport->export != 1) {
                    $rapport->index_traduction = service('traduction')->calcul_index_traduction(
                        10,
                        array(
                            'rapport',
                            $rapport->id_rapport,
                        ),
                        array(
                            'description' => $rapport->description,
                            'titre' => $rapport->titre,
                        )
                    );
                }

                if (!empty($rapport->parametrage_rapport_libre)) {

                    $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre, true);

                    if (isset($parametrage_rapport_libre['serie']) && isset($parametrage_rapport_libre['serie']['type_calcul'])) {

                        if (!isset($parametrage_rapport_libre['serie']['index_traduction'])) {
                            $index_traduction = 'rapport.' . $rapport->id_rapport . '.serie';

                            $parametrage_rapport_libre['serie']['index_traduction'] = service('traduction')->calcul_index_traduction(
                                10,
                                explode('.', $index_traduction),
                                array(
                                    'nom' => $parametrage_rapport_libre['serie']['nom'],
                                )
                            );
                        }
                    }

                    if (isset($parametrage_rapport_libre['series'])) {

                        $compteur_index_serie = 0;

                        foreach ($parametrage_rapport_libre['series'] as $index => $serie) {

                            if (!isset($serie['index_traduction']) && isset($serie['nom'])) {
                                $index_traduction = 'rapport.' . $rapport->id_rapport . '.serie_' . $compteur_index_serie;

                                $parametrage_rapport_libre['series'][$index]['index_traduction'] = service('traduction')->calcul_index_traduction(
                                    10,
                                    explode('.', $index_traduction),
                                    array(
                                        'nom' => $serie['nom'],
                                    )
                                );

                                $compteur_index_serie++;
                            }
                        }

                    }

                    $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);
                }

                $rapport->save();

            }

            parametre('S20220801_ajout_index_traduction_elements_rapport',1);
        }

        if(empty(parametre('S20220801_ajout_index_traduction_elements_tableau_de_bord'))) {

            $tableaux_de_bord = modele('tableau_de_bord')->get();

            foreach ($tableaux_de_bord as $tableau_de_bord) {

                service('traduction')->calcul_index_traduction(
                    11,
                    array(
                        'tableau_de_bord',
                        $tableau_de_bord->id,
                    ),
                    array(
                        'nom' => $tableau_de_bord->nom,
                        'description' => $tableau_de_bord->description,
                    )
                );

            }

            parametre('S20220801_ajout_index_traduction_elements_tableau_de_bord',1);
        }

        if(empty(parametre('S20220801_ajout_index_traduction_elements_utilisateur_modele_email'))) {

            $langue_fr = modele('traduction_langue')->where('code', 'fr')->first();

            if (!empty($langue_fr)) {

                $modeles_emails = modele('modele_email')->get();

                foreach ($modeles_emails as $modele_email) {

                    if (empty($modele_email->langue)) {
                        management('modele_email', $modele_email->id)->enregistre_modele(
                            array('langue' => $langue_fr->id)
                        );
                    }

                }
            }

            parametre('S20220801_ajout_index_traduction_elements_utilisateur_modele_email',1);
        }

        if(empty(parametre('S20220801_ajout_index_traduction_elements_utilisateur_menus'))) {
            $menu_management = new Menus_management();

            if (file_exists(storage_path('app/eden_menus_extranet.php'))) {

                $menus = include(storage_path('app/eden_menus_extranet.php'));

                $menu_management->enregistrer($menus, true);
            }

            if (file_exists(storage_path('app/eden_menus.php'))) {

                $menus = include(storage_path('app/eden_menus.php'));

                $menu_management->enregistrer($menus);
            }

            parametre('S20220801_ajout_index_traduction_elements_utilisateur_menus',1);
        }

        if(empty(parametre('S20220801_ajout_index_traduction_elements_utilisateur_valeurs_listes_formatees'))) {
            Cache_management::genere_valeurs_champs_listes();

            $valeurs_listes_formatees = Champs_liste_formatee::get();

            foreach ($valeurs_listes_formatees as $valeur_liste_formatee) {

                if (!empty($valeur_liste_formatee->valeur)) {
                    service('traduction')->calcul_index_traduction(
                        9,
                        array(
                            'valeurs_listes_formatees',
                            $valeur_liste_formatee->id_liste_choix
                        ),
                        array(
                            'valeur_' . $valeur_liste_formatee->id_valeur => $valeur_liste_formatee->valeur,
                        )
                    );
                }
            }

            parametre('S20220801_ajout_index_traduction_elements_utilisateur_valeurs_listes_formatees',1);
        }

        //modules sur fiche

        if(empty(parametre('S20220801_ajout_index_traduction_elements_utilisateur_module_sur_fiche'))) {
            if (is_dir(app_path('Eden/Views/fiches/include'))) {

                $type_elements = scandir(app_path('Eden/Views/fiches/include'));

                foreach ($type_elements as $type_element) {

                    if ($type_element == '.' || $type_element == '..')
                        continue;

                    if (is_dir(app_path('Eden/Views/fiches/include/' . $type_element))) {

                        $repertoire = scandir(app_path('Eden/Views/fiches/include/' . $type_element));

                        foreach ($repertoire as $fichier) {

                            if ($fichier == '.' || $fichier == '..')
                                continue;

                            $fichier = str_replace('.blade.php', '', $fichier);

                            $index_traduction = 'module_sur_fiche.' . $type_element . '.' . $fichier;

                            $traduction = traduction('module_sur_fiche.' . $type_element . '.' . $fichier);

                            if ((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction) {

                                $nom_module = str_replace('_', ' ', $fichier);

                                $nom_module = ucfirst($nom_module);

                                service('traduction')->calcul_index_traduction(
                                    18,
                                    array(
                                        'module_sur_fiche',
                                        $type_element
                                    ),
                                    array(
                                        $fichier => $nom_module,
                                    ),
                                    true
                                );
                            }
                        }
                    }
                }
            }

            // le spécifique
            if (is_dir(app_path('views/vendor/eden/fiches/include'))) {

                $type_elements = scandir(app_path('views/vendor/eden/fiches/include'));

                foreach ($type_elements as $type_element) {

                    if ($type_element == '.' || $type_element == '..')
                        continue;

                    if (is_dir(resource_path('views/vendor/eden/fiches/include/' . $type_element))) {

                        $repertoire = scandir(resource_path('views/vendor/eden/fiches/include/' . $type_element));

                        foreach ($repertoire as $fichier) {

                            if ($fichier == '.' || $fichier == '..')
                                continue;

                            $fichier = str_replace('.blade.php', '', $fichier);

                            $index_traduction = 'module_sur_fiche.' . $type_element . '.' . $fichier;

                            $traduction = traduction('module_sur_fiche.' . $type_element . '.' . $fichier);

                            if ((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction) {

                                $nom_module = str_replace('_', ' ', $fichier);

                                $nom_module = ucfirst($nom_module);

                                service('traduction')->calcul_index_traduction(
                                    18,
                                    array(
                                        'module_sur_fiche',
                                        $type_element
                                    ),
                                    array(
                                        $fichier => $nom_module,
                                    )
                                );
                            }
                        }
                    }
                }
            }

            parametre('S20220801_ajout_index_traduction_elements_utilisateur_module_sur_fiche',1);
        }

        return true;
    }
}
