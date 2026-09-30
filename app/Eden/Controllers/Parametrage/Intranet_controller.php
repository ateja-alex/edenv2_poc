<?php

namespace App\Eden\Controllers\Parametrage;

use App\Eden\Controllers\Rapports\Rapport_controller;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Parametrage\Intranet_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Table_libre;
use App\Eden\Rapports_libres;
use App\Eden\Models\Rapport_libre;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Intranet_controller extends Controller {

    public function parametrage(){

        $tables_libres_disponibles = Champ_libre::
            whereNotIn('nom_sql',['cree_par','modifie_par'])
            ->whereNotIn('type_element',Variables::$documents_gescom)
            ->where('type_element_ajax','utilisateur')
            ->whereIn('type',[42,10])
            ->select('type_element','nom_sql')
            ->get()->groupBy('type_element')->toArray();

        $intranet_management = new Intranet_management();

        $categories = Rapports_libres::categories();

        $id_rapports = Rapport_libre::get()->pluck('id_rapport')->toArray();

        $structure_intranet = $intranet_management->structure_intranet($tables_libres_disponibles);

        $modules_par_defaut = $intranet_management->modules_par_defaut();

        $types_element_disponibles = Table_libre::whereIn('type_element', array_keys($tables_libres_disponibles))->get(['type_element', 'index_traduction'])->values()->toArray();

        return view('eden::parametrage.intranet',array(
            'structure_intranet' => $structure_intranet,
            'types_element_disponibles' => $types_element_disponibles,
            'tables_libres_disponibles' => $tables_libres_disponibles,
            'autres_modules' => $intranet_management->autres_modules(),
            'derniers_ids' => $intranet_management->derniers_ids($structure_intranet),
            'modules_par_defaut' => $modules_par_defaut,
            'categories'=> $categories,
            'id_rapports' => $id_rapports,
        ));
    }

    public function enregistrer_nouvelle_structure(Request $requete){

        $structure_intranet = $requete->structure_intranet;

        $index_traductions_a_creer = array();

        $index_traductions_a_supprimer = modele('traduction_index')
            ->whereIn('index',function($requete){
                $requete->select('index')
                    ->from('traduction_valeur')
                    ->where('categorie',15)
                    ->where(function($sous_requete){
                        $sous_requete->whereNull('inactif')
                            ->orWhere('inactif',0);
                    })
                    ->whereNotNull('traduction_specifique');
            })
            ->get()->pluck('index')->toArray();


        if(!empty($structure_intranet['lignes'])) {
            foreach ($structure_intranet['lignes'] as &$ligne){

                if(!empty($ligne['modules'])){

                    foreach ($ligne['modules'] as &$module){

                        if(!isset($module['nouvel_element'])) {
                            unset($index_traductions_a_supprimer[array_search('intranet.modules.module_'.$module['id'].'.nom',$index_traductions_a_supprimer)]);
                            continue;
                        }

                        $nom_module = $module['nom_module'];

                        $index_traductions_a_creer[] = array(
                            'categorie' => 15,
                            'informations_traductions' => array(
                                'intranet',
                                'modules',
                                'module_'.$module['id']
                            ),
                            'index_a_creer' => array(
                                'nom' => $nom_module,
                            )
                        );

                        unset($module['nouvel_element']);
                        unset($module['nom_module']);
                    }
                }
            }
        }

        $structure_intranet_a_enregistrer = $structure_intranet;

        if(!empty($structure_intranet_a_enregistrer['lignes'])) {
            foreach ($structure_intranet_a_enregistrer['lignes'] as $id_ligne => $ligne_a_enregistrer) {

                if (!empty($ligne_a_enregistrer['modules'])) {

                    foreach ($ligne_a_enregistrer['modules'] as $id_module => $module_a_enregistrer) {

                        unset($structure_intranet_a_enregistrer[$id_ligne][$id_module]['champs_libres_disponibles']);
                    }
                }
            }
        }

        Storage::put('eden_intranet.php', json_encode($structure_intranet_a_enregistrer));

        service('traduction')->calcul_multiple_index_traduction($index_traductions_a_creer);

        foreach($index_traductions_a_supprimer as $index_traduction_a_supprimer) {
            $index_traduction_a_supprimer = str_replace('.nom','',$index_traduction_a_supprimer);
            service('traduction')->supprime_index_traduction($index_traduction_a_supprimer,false);
        }

        Cache_management::genere_traductions();
        
        return response()->json($structure_intranet);
    }

    public function gestion_liste($type_element,$id_liste = false){

        if($id_liste === false) {

            $liste = new Liste_libre;

            $liste->type_element = $type_element;
            $liste->desactiver_creation = 1;
            $liste->desactiver_actions = 1;

            $nouveau_rapport = new Rapport_libre();

            $id_rapport = 'intranet_'.$liste->type_element;

            $titre = 'Intranet '.$liste->type_element;

            $liste->id_rapport = $id_rapport;

            $nouveau_rapport->id_rapport = $id_rapport;
            $nouveau_rapport->type_rapport = 'liste_libre';

            $nouveau_rapport->index_traduction = service('traduction')->calcul_index_traduction(
                10,
                array(
                    'rapport',
                    $nouveau_rapport->id_rapport,
                ),
                array(
                    'titre' => $titre,
                )
            );

            $nouveau_rapport->save();

            $liste->save();

            Cache_management::generation_liste_libre($liste->id);

            // On génère la migration spécifique
            Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);

            $id_liste = $liste->id;

        }

        return redirect()->route('parametrage.liste_libre.index',[$id_liste]);
    }

    public function recupere_listes_type_element($type_element){
        $listes = new Collection();

        $liste_libre_principale = Liste_libre::select('eden_listeslibres.*','eden_tableslibres.index_traduction as table_index_traduction',DB::raw('\'principale\' as type_liste'))
            ->join('eden_tableslibres','eden_listeslibres.type_element','eden_tableslibres.type_element')
            ->where('eden_listeslibres.type_element',$type_element)
            ->where(function($requete){
                $requete->where('id_rapport','');
                $requete->orWhereNull('id_rapport');
            })
            ->get();

        $listes = $listes->merge($liste_libre_principale);

        $liste_libre_rapport = Liste_libre::select('eden_listeslibres.*','eden_rapports.titre','eden_rapports.liste_sur_fiche','eden_rapports.inactif','eden_rapports.index_traduction as rapport_index_traduction')
            ->join('eden_rapports','eden_listeslibres.id_rapport','eden_rapports.id_rapport')
            ->where('eden_listeslibres.type_element',$type_element)
            ->whereNotNull('eden_listeslibres.id_rapport')
            ->where('eden_listeslibres.id_rapport','!=','')
            ->where(function($q){
                $q->where(function($q2){
                        $q2->whereNull('eden_rapports.liste_sur_fiche')
                        ->orWhere('eden_rapports.liste_sur_fiche','!=',1);
                    })
                ->where(function($q2){
                        $q2->whereNull('eden_rapports.export')
                        ->orWhere('eden_rapports.export','!=',1);
                    })
                ->where(function($q2){
                        $q2->whereNull('eden_listeslibres.export')
                        ->orWhere('eden_listeslibres.export','!=',1);
                    })
                ->where(function($q2){
                        $q2->whereNull('eden_listeslibres.fiche')
                        ->orWhere('eden_listeslibres.fiche','!=',1);
                    });
            })
            ->orderBy('inactif')
            ->get();

        $listes = $listes->merge($liste_libre_rapport);

        return response()->json($listes);
    }

    public function recupere_formulaires_type_element($type_element){
       $formulaires = Formulaire::where(function ($requete) use ($type_element) {
            $requete->where('nom_formulaire', $type_element)
                ->orWhere(function ($requete) use ($type_element) {
                    $requete->where('type_formulaire', 'fiche')
                        ->where('nom_formulaire', 'fiche_'.$type_element);
                })
                ->orWhere(function ($requete) use ($type_element) {
                    $requete->where('type_element', $type_element)
                        ->where('type_formulaire', 'intranet');
                });
        })->get();
        
        return response()->json($formulaires);
    }

    public function recupere_liste($liste_id){
        $liste = Liste_libre::select('eden_listeslibres.*','eden_rapports.index_traduction as rapport_index_traduction')
        ->join('eden_rapports','eden_listeslibres.id_rapport','eden_rapports.id_rapport')
        ->where('eden_listeslibres.id', $liste_id)->first();

        return $liste;
    }
}