<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Librairies\Budgea\Exception;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Rapport_libre;
use Illuminate\Support\Facades\DB;
use App\Eden\Migrations\Scripts\Script;

class S20250429_rattrapage_filtres_appliques implements Script
{

    public function execute(){

        $champ_libre = Champ_libre::where('nom_sql','id_cible')
            ->where('type_element','recherche_avancee')
            ->first();

        $champ_libre->type = null;
        $champ_libre->save();

        DB::select("ALTER TABLE recherche_avancee MODIFY id_cible varchar(300)");

        $recherches_avancees = modele('recherche_avancee')->get();

        // Gestion des filtres appliqués
        $listes_libres_filtres_appliques = Liste_libre::whereRaw('LENGTH(filtres_appliques) > 5')
            ->where('parametrage_rapport_libre','!=','null')
            ->select('eden_listeslibres.*','eden_rapports.liste_sur_fiche')
            ->leftJoin('eden_rapports','eden_rapports.id_rapport','eden_listeslibres.id_rapport')
            ->get();

        foreach($listes_libres_filtres_appliques as $liste_libre){

            $chemin_dossier_migrations = app_path().'/Migrations/Listes_libres';

            if(!empty($liste_libre->id_rapport)) {
                $chemin_dossier_migrations = app_path() . '/Migrations/Rapports';
                if ($liste_libre->liste_sur_fiche == 1)
                    $chemin_dossier_migrations = app_path() . '/Migrations/Listes_libres_fiches';
            }

            $chemin_fichier = $chemin_dossier_migrations.'/'.(!empty($liste_libre->id_rapport)
                ? $liste_libre->id_rapport : $liste_libre->type_element).'.php';

            if(file_exists($chemin_fichier)){

                $recherche_avancee = $recherches_avancees
                    ->where('type','filtres_appliques')
                    ->where('id_cible',$liste_libre->id)
                    ->first();

                $management = management('recherche_avancee');

                if(!empty($recherche_avancee))
                    $management = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee);

                $structure = $this->transformation_filtres_appliques($liste_libre->filtres_appliques,$liste_libre->type_element);

                $management->enregistre([
                    'type' => 'filtres_appliques',
                    'type_element' => $liste_libre->type_element,
                    'id_cible' => $liste_libre->id,
                    'structure' => $structure
                ]);

                Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre->id, true);
            }
        }

        // Gestion des couleurs
        $couleurs_listes = Liste_libre_couleur::get();

        if(is_file(app_path('Migrations/Listes_libres_couleurs/filtres_couleurs.php'))) {

            $couleurs = require(app_path('Migrations/Listes_libres_couleurs/filtres_couleurs.php'));

            foreach($couleurs as $couleur) {

                $nouvelle_couleur = false;

                if(isset($couleur['id_rapport']))
                    $liste_libre = Liste_libre::where('id_rapport',$couleur['id_rapport'])->first();
                else
                    $liste_libre = Liste_libre::where(function($condition){
                        $condition->whereNull('id_rapport')->orWhere('id_rapport','');
                    })->where('type_element',$couleur['type_element'])->first();

                if(empty($liste_libre))
                    continue;

                $couleur_liste = $couleurs_listes->where('liste_libre_id',$liste_libre->id)
                    ->where('couleur',$couleur['couleur'])->first();

                if(empty($couleur_liste)){
                    $couleur_liste = new Liste_libre_couleur();
                    $couleur_liste->couleur = $couleur['couleur'];
                    $couleur_liste->liste_libre_id = $liste_libre->id;
                    $couleur_liste->save();
                    $nouvelle_couleur = true;
                }

                if(!empty($couleur['filtre'])) {

                    $recherche_avancee = $recherches_avancees
                        ->where('type', 'listes_libres_couleur')
                        ->where('id_cible', $couleur_liste->id)
                        ->first();

                    $management = management('recherche_avancee');

                    if (!empty($recherche_avancee))
                        $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                    $structure = $this->transformation_filtres_appliques($couleur['filtre'], $couleur['type_element']);

                    $management->enregistre([
                        'type' => 'listes_libres_couleur',
                        'type_element' => $couleur['type_element'],
                        'id_cible' => $couleur_liste->id,
                        'structure' => $structure
                    ]);
                }

                if($nouvelle_couleur)
                    Liste_libre_management::generer_fichier_migration_liste_libre($liste_libre->id, true);
            }

            unlink(app_path('Migrations/Listes_libres_couleurs/filtres_couleurs.php'));
            rmdir(app_path('Migrations/Listes_libres_couleurs'));
        }

        $rapports = Rapport_libre::whereRaw('LENGTH(parametrage_rapport_libre) > 2')
            ->where('parametrage_rapport_libre','!=','null')->get();

        foreach($rapports as $rapport){

            $parametrage_rapport_libre = json_decode($rapport->parametrage_rapport_libre,true);

            $filtres = [];

            foreach($parametrage_rapport_libre as $cle => $valeurs){

                if(!str_contains($cle,'filtre_applique_'))
                   continue;

                if(in_array($rapport->type_rapport,['indicateur','pdf'])){

                    if(str_contains($cle,'filtre_applique_'.$rapport->type_element.'_'))
                        $nom_sql = str_replace('filtre_applique_'.$rapport->type_element.'_','',$cle);
                    else
                        $nom_sql = str_replace('filtre_applique_','',$cle);

                    $filtres[$nom_sql] = $valeurs;
                }

                unset($parametrage_rapport_libre[$cle]);
            }

            $structure = $this->transformation_filtres_appliques($filtres, $rapport->type_element);

            if(!empty($structure[0]['filtres'])) {

                $recherche_avancee = $recherches_avancees
                    ->where('type','rapport')
                    ->where('id_cible',$rapport->id_rapport)
                    ->first();

                $management = management('recherche_avancee');

                if (!empty($recherche_avancee))
                    $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                $management->enregistre([
                    'type' => 'rapport',
                    'type_element' => $rapport->type_element,
                    'id_cible' => $rapport->id_rapport,
                    'structure' => $structure
                ]);
            }

            if($rapport->type_rapport == 'carte'){
                if(!empty($parametrage_rapport_libre['couleurs'])){
                    $compteur_id_couleur = 0;
                    foreach($parametrage_rapport_libre['couleurs'] as &$couleur){

                        if(isset($couleur['id']))
                            $compteur_id_couleur = $couleur['id'];
                        else
                            $compteur_id_couleur++;

                        $couleur['id'] = $compteur_id_couleur;

                        $filtres = [];

                        $type_element_couleur = null;

                        if(!empty($couleur['filtre'])) {
                            foreach ($couleur['filtre'] as $cle => $valeurs) {

                                $cle = explode('.',$cle);
                                $filtres[$cle[1]] = $valeurs;

                                if(empty($type_element_couleur))
                                    $type_element_couleur = $cle[0];
                            }
                        }

                        if(array_key_exists('filtre',$couleur))
                            unset($couleur['filtre']);

                        if(empty($couleur['type_element']))
                            $couleur['type_element'] = $type_element_couleur;

                        $structure = $this->transformation_filtres_appliques($filtres, $type_element_couleur);

                        if(!empty($structure[0]['filtres'])) {

                            $recherche_avancee = $recherches_avancees
                                ->where('type', $rapport->id_rapport . '.couleur')
                                ->where('id_cible', $couleur['id'])
                                ->first();

                            $management = management('recherche_avancee');

                            if (!empty($recherche_avancee))
                                $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                            $management->enregistre([
                                'type' => $rapport->id_rapport . '.couleur',
                                'type_element' => $couleur['type_element'],
                                'id_cible' => $couleur['id'],
                                'structure' => $structure
                            ]);
                        }
                    }
                }

                if(!empty($parametrage_rapport_libre['series'][0])){

                    $filtres_par_type_element = [];

                    foreach($parametrage_rapport_libre['series'][0] as $cle => $valeurs) {

                        foreach(array_merge($parametrage_rapport_libre['types_elements_carte'],['adresse']) as $type_element){

                            if(str_contains($cle,'filtre_applique_'.$type_element.'_')) {
                                $nom_sql = str_replace('filtre_applique_' . $type_element . '_', '', $cle);
                                $filtres_par_type_element[$type_element][$nom_sql] = $valeurs;
                            }
                        }
                    }

                    foreach($filtres_par_type_element as $type_element => $filtres){

                        $structure = $this->transformation_filtres_appliques($filtres, $type_element);

                        if(!empty($structure[0]['filtres'])) {

                            $recherche_avancee = $recherches_avancees
                                ->where('type', $rapport->id_rapport . '.type_element')
                                ->where('id_cible', $type_element)
                                ->first();

                            $management = management('recherche_avancee');

                            if (!empty($recherche_avancee))
                                $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                            $management->enregistre([
                                'type' => $rapport->id_rapport . '.type_element',
                                'type_element' => $type_element,
                                'id_cible' => $type_element,
                                'structure' => $structure
                            ]);
                        }
                    }
                }
            }
            else if(!empty($parametrage_rapport_libre['serie'])){

                $filtres = [];

                foreach($parametrage_rapport_libre['serie'] as $cle => $valeurs){

                    if(!str_contains($cle,'filtre_applique_'))
                       continue;

                    if(str_contains($cle,'filtre_applique_'.$rapport->type_element.'_'))
                        $nom_sql = str_replace('filtre_applique_'.$rapport->type_element.'_','',$cle);
                    else
                        $nom_sql = str_replace('filtre_applique_','',$cle);

                    $filtres[$nom_sql] = $valeurs;

                    unset($parametrage_rapport_libre['serie'][$cle]);
                }

                $structure = $this->transformation_filtres_appliques($filtres, $rapport->type_element);

                if(!empty($structure[0]['filtres'])) {

                    $recherche_avancee = $recherches_avancees
                        ->where('type','rapport')
                        ->where('id_cible',$rapport->id_rapport)
                        ->first();

                    $management = management('recherche_avancee');

                    if (!empty($recherche_avancee))
                        $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                    $management->enregistre([
                        'type' => 'rapport',
                        'type_element' => $rapport->type_element,
                        'id_cible' => $rapport->id_rapport,
                        'structure' => $structure
                    ]);
                }
            }
            else if(isset($parametrage_rapport_libre['series']) && in_array($rapport->type_rapport,['courbe','histogramme','tableau'])){
                $compteur_id = 0;
                foreach($parametrage_rapport_libre['series'] as &$serie){
                    if(isset($serie['id']))
                        $compteur_id = $serie['id'];
                    else
                        $compteur_id++;

                    $serie['id'] = $compteur_id;

                    $filtres = [];

                    foreach($serie as $cle => $valeurs){

                        if(!str_contains($cle,'filtre_applique_'))
                           continue;

                        if(str_contains($cle,'filtre_applique_'.$rapport->type_element.'_'))
                            $nom_sql = str_replace('filtre_applique_'.$rapport->type_element.'_','',$cle);
                        else
                            $nom_sql = str_replace('filtre_applique_','',$cle);

                        $filtres[$nom_sql] = $valeurs;

                        unset($serie[$cle]);
                    }

                    $structure = $this->transformation_filtres_appliques($filtres, $rapport->type_element);

                    if(!empty($structure[0]['filtres'])) {

                        $recherche_avancee = $recherches_avancees
                            ->where('type', $rapport->id_rapport . '.serie')
                            ->where('id_cible', $serie['id'])
                            ->first();

                        $management = management('recherche_avancee');

                        if (!empty($recherche_avancee))
                            $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                        $management->enregistre([
                            'type' => $rapport->id_rapport . '.serie',
                            'type_element' => $rapport->type_element,
                            'id_cible' => $serie['id'],
                            'structure' => $structure
                        ]);
                    }
                }
            }

            if(!in_array($rapport->type_rapport,['courbe','histogramme','tableau']) && isset($parametrage_rapport_libre['series']))
                unset($parametrage_rapport_libre['series']);

            $rapport->parametrage_rapport_libre = json_encode($parametrage_rapport_libre);

            $rapport->save();
        }

        return true;
    }

    public function transformation_filtres_appliques($filtres_appliques,$type_element){

        $filtres = [];

        try{
            $filtres_appliques = unserialize($filtres_appliques);
        }
        catch(\Exception | \Throwable $e){
            try{
                $filtres_appliques = json_decode($filtres_appliques,true);
            }
            catch(\Exception | \Throwable $e){
            }
        }

        if(empty($filtres_appliques))
            return null;

        foreach($filtres_appliques as $nom_sql => $filtre){

            $texte = null;
            $variable = null;

            if(is_int($nom_sql)){
                $nom_sql = $filtre[0];

                if($filtre[1] == '!=' && (empty($filtre[2]) || $filtre[2] == ""))
                    $variable = 'non_vide';

                else if($filtre[1] == '{non_vide}')
                    $texte = 'non_vide';
            }
            else{
                if(isset($filtre['variable']))
                    $variable = $filtre['variable'];

                if(isset($filtre['texte']))
                    $texte = $filtre['texte'];
            }

            $modele_champ_libre = champ_libre_modele($type_element,$nom_sql);

            if(empty($modele_champ_libre))
                continue;

            if(in_array($modele_champ_libre->type,[20,1,11,12])) {

                if(in_array('true',$filtre))
                    $valeurs = array_keys(array_filter($filtre,function($valeur_filtre){
                        return $valeur_filtre === true || $valeur_filtre === 'true';
                    }));
                else
                    $valeurs = $filtre;
            }
            else if(in_array($modele_champ_libre->type,[4,5])){

                if(is_string($filtre))
                    $filtre = ['variable' => $filtre];

                if (empty($filtre['debut']))
                    $filtre['debut'] = null;
                else
                    $filtre['debut'] = date('Y-m-d',strtotime(str_replace('/', '-', $filtre['debut'])));

                if(empty($filtre['fin']))
                    $filtre['fin'] = null;
                else
                    $filtre['fin'] = date('Y-m-d',strtotime(str_replace('/', '-', $filtre['fin'])));

                if(empty($filtre['variable']))
                    $filtre['variable'] = null;

                if(empty($filtre['variable']) && empty($filtre['debut']) && empty($filtre['fin']))
                    continue;

                $valeurs = $filtre;
            }
            else if(in_array($modele_champ_libre->type,[42,10,21,22])) {

                if(!is_array($filtre) && !empty($filtre))
                    $valeurs = in_array($filtre, ['vide', 'non_vide']) ? $filtre : [$filtre];
                else if(!empty($texte))
                    $valeurs = in_array($texte, ['vide', 'non_vide']) ? $texte : [$texte];
                else
                    continue;
            }
            else if(empty($variable) && empty($texte))
                continue;
            else if(in_array($modele_champ_libre->type,[2,3])) {

                if(empty($variable))
                    $variable = 'egal_a';

                $valeurs = [
                    'montant' => $texte,
                    'variable' => $variable
                ];
            }
            else {

                if(empty($variable))
                    $variable = 'contient';

                $valeurs = [
                    'texte' => $texte,
                    'variable' => $variable
                ];
            }

            $filtres[] = [
                'type_element' => $type_element,
                'element_id' => null,
                'champ_liaison' => null,
                'valeurs' => $valeurs,
                'nom_sql' => $nom_sql,
            ];
        }

        return [
            [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => $filtres
            ]
        ];
    }
}