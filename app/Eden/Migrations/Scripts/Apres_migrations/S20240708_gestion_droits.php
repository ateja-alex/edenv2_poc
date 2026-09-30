<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Controllers\Parametrage\Champs_libres_controller;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaires_champs;
use Illuminate\Support\Facades\DB;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\Schema;

class S20240708_gestion_droits implements Script {

    public function execute(){

        $champs_libres = Champ_libre::where('type', 20)->where('liste_choix', 122)->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'profil',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        $champs_libres = Champ_libre::where('type', 11)->where('liste_choix', 122)->get();

        $nouvelles_informations = array(
            'type' => 10,
            'type_element_ajax' => 'profil',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        $champs_libres = Champ_libre::where('type', 20)->where('liste_choix', 9)->get();

        $nouvelles_informations = array(
            'type' => 42,
            'type_element_ajax' => 'entite',
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres);

        // Gestion des profils
        if(modele('profil')->count() == 0 && Schema::hasTable("eden_utilisateurs_profils") && DB::table('eden_utilisateurs_profils')->count() > 0) {

            $traductions_index = modele('traduction_index')
                ->where('categorie', 14)
                ->where('index', 'Like', 'profil.utilisateur.%')
                ->get();

            foreach ($traductions_index as $traduction_index) {
                $traduction_index->index = str_replace('.utilisateur', '', $traduction_index->index);
                $traduction_index->save();
            }

            $traductions_valeur = modele('traduction_valeur')
                ->where('index', 'Like', 'profil.utilisateur.%')
                ->get();

            foreach ($traductions_valeur as $traduction_valeur) {
                $traduction_valeur->index = str_replace('.utilisateur', '', $traduction_valeur->index);
                $traduction_valeur->save();
            }

            DB::select('INSERT INTO profil (id,nom) SELECT id_profil,nom FROM eden_utilisateurs_profils');
        }

        //Gestion menus pour le changement du lieu vers les profils
        $fichiers = [storage_path('app/eden_menus.php'),storage_path('app/eden_menus_extranet.php')];

        foreach ($fichiers as $fichier) {
            if (is_file($fichier)) {

                $menus_eden = include($fichier);

                foreach ($menus_eden as $index_menu => &$menu) {

                    if (isset($menu['route'][0]) && $menu['route'][0] == 'parametrage.profil_menu.liste')
                        unset($menus_eden[$index_menu]);
                    else if (isset($menu['route'][0]) && $menu['route'][0] == 'parametrage.profil.liste')
                        $menu['route'] = [
                            'base_eden.liste.index',
                            ['profil']
                        ];

                    if(isset($menu['sous_menus'])) {

                        foreach ($menu['sous_menus'] as $index_sous_menu => &$sous_menu) {

                            if (isset($sous_menu['route'][0]) && $sous_menu['route'][0] == 'parametrage.profil_menu.liste')
                                unset($menu['sous_menus'][$index_sous_menu]);
                            else if (isset($sous_menu['route'][0]) && $sous_menu['route'][0] == 'parametrage.profil.liste')
                                $sous_menu['route'] = [
                                    'base_eden.liste.index',
                                    ['profil']
                                ];
                        }
                    }
                }

                file_put_contents($fichier, "<?php\n\nreturn " . var_export($menus_eden, true) . ";\n");
            }
        }

        // On supprime les colonnes utilisateur sur les tables avec la gestion des droits
        $tables = DB::select('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = "'.env('DB_DATABASE').'" AND COLUMN_NAME RLIKE "^utilisateur_[0-9]"');

        $tables = collect($tables)->groupBy('TABLE_NAME');

        foreach($tables as $type_element => $table){

            $table_libre = Table_libre::where('type_element', $type_element)->first();

            if(empty($table_libre))
                continue;

            $champs_libres = champs_libres($type_element)->keyBy('nom_sql');

            foreach($table as $index => $champ){

                if(isset($champs_libres[$champ->COLUMN_NAME]))
                    unset($table[$index]);

            }

            DB::select('ALTER TABLE '.$type_element.' DROP COLUMN '.implode(', DROP COLUMN ',$table->pluck('COLUMN_NAME')->toArray()));
        }

        if(Schema::hasTable("eden_utilisateurs_menus"))
        {
            $menus_extranet = DB::table('eden_utilisateurs_menus')->where('extranet',1)->get();

            $droits_profil = [];

            foreach($menus_extranet as $menu){

                $management_profil = management('profil');

                $management_profil->enregistre([
                    'nom' => $menu->nom,
                    'extranet' => 1
                ]);

                DB::select('UPDATE contact SET profil_extranet = '.$management_profil->modele->id.' WHERE menus_extranet = '.$menu->id);
                DB::select('UPDATE client SET profil_extranet = '.$management_profil->modele->id.' WHERE menus_extranet = '.$menu->id);

                $droits = json_decode(base64_decode($menu->menu));

                foreach($droits as $nom => $osef){

                    if(!isset($droits_profil[$nom]))
                        $droits_profil[$nom] = [];

                    $droits_profil[$nom][] = $management_profil->modele->id;
                }
            }

            foreach($droits_profil as $menu => $droits){

                if(sizeof($droits) == sizeof($menus_extranet))
                    continue;

                management('profil_droits_divers')->enregistre([
                    'type' => 'menu_extranet',
                    'index' => $menu,
                    'profils' => $droits
                ]);
            }
        }

        $champs_menus_extranet = Formulaires_champs::where('nom_sql','menus_extranet')->get();

        foreach($champs_menus_extranet as $champ){

            $champ->nom_sql = 'profil_extranet';
            $champ->save();

            Maintenance_management::generer_fichier_migration_formulaire($champ->nom_formulaire);
        }

        return true;
    }
}
