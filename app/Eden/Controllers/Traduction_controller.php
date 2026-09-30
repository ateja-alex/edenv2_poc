<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cache_management;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Models\Traduction_interface;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champ_libre_liste;
use App\Eden\Models\Table_libre;

use App\Eden\Managements\Traduction_management;
use Illuminate\Support\Facades\DB;


class Traduction_controller extends Controller {

    protected $index_traduction = '';

    function __construct(){

        $this->index_traduction = env('BASE_TRADUCTION') === true ? 'traduction_standard' : 'traduction_specifique';
    }

    /**
     *
     * Affichage des traductions par catégorie
     *
     */
    public function afficher(){

        $categories = management('traduction_index')->champ('categorie')->valeurs_possibles;

        $categorie_a_afficher = null;

        if(isset(request()->categorie))
            $categorie_a_afficher = request()->categorie;

        $recherche = null;

        if(isset(request()->recherche))
            $recherche = request()->recherche;

        return view('eden::traduction.index',
            array(
                'categories' => $categories,
                'categorie_a_afficher' => $categorie_a_afficher,
                'recherche' => $recherche,
            )
        );
    }

    /**
     *
     * Récupération des traductions par langue d'une catégorie ou de toutes les catégories
     *
     */
    public function traductions_categorie($id_categorie = null){

        $filtrage_index = null;

        if(request()->filtrage_index != "undefined" && !empty(request()->filtrage_index))
            $filtrage_index=request()->filtrage_index;

        $service_traduction = service('traduction');

        $traductions_categorie = $service_traduction->traductions_categorie($id_categorie,$filtrage_index);

        return response()->json($traductions_categorie);
    }

    /**
     * 
     * Permet de rechercher une traduction globale
     *
     */
    public function recherche_globale(Request $formulaire) {
        
        $chaine_de_charactere_recherche = $formulaire->recherche_texte;

        $traductions_existantes = modele('traduction_valeur')->join('traduction_index', 'traduction_index.index', 'traduction_valeur.index')
            ->where(function($r) {
                $r->where('traduction_index.inactif','0')->orWhereNull('traduction_index.inactif');
            })
            ->where(function($r) use ($chaine_de_charactere_recherche){
                $r->where(DB::raw('coalesce(traduction_specifique, traduction_standard)'), 'like', '%'.$chaine_de_charactere_recherche.'%')
                ->orWhere('traduction_valeur.index', 'like', '%'.$chaine_de_charactere_recherche.'%');
            })
            ->select('traduction_index.categorie','traduction_valeur.index','traduction_valeur.traduction_standard','traduction_valeur.traduction_specifique','traduction_valeur.id')->get()->toArray();

        $index_categorie = [];

        foreach ($traductions_existantes as $traduction_index) {
            $categorie = $traduction_index['categorie'];
            $index = $traduction_index['index'];

            if (!isset($index_categorie[$categorie])) {
                $index_categorie[$categorie] = [
                    'categorie_id'=>$categorie,'valeurs'=>[]
                ];
            }

            $index_categorie[$categorie]['valeurs'][] = $index;
        }

        $resultat_recherche = array_values($index_categorie);
        
        return response()->json($resultat_recherche);
    }

    /**
     * @param Request $request 
     * Permet d'enregistrer une traduction
     *
     */
    public function enregistrer(Request $request){

        $informations = $request->all();

        if(empty($informations['index']))
            return array('retour' => false,'message' => traduction('messages.php.traduction.champ_index_obligatoire'));

        $traduction_index = modele('traduction_index')->where('index',$informations['index'])->first();

        $codes_langues = modele('traduction_langue')->get()->pluck('code')->toArray();

        if(!empty($informations['creation'])) {

            if(!empty($traduction_index))
                return array('retour' => false, 'message' => traduction('messages.php.traduction.index_deja_cree'));

            if (empty($informations['categorie']))
                return array('retour' => false, 'message' => traduction('messages.php.traduction.champ_categorie_obligatoire'));

            $valeur_par_defaut_presente = false;

            foreach($informations as $colonne => $information) {

                if(in_array($colonne,$codes_langues)){
                    if (!empty($information[$this->index_traduction]))
                        $valeur_par_defaut_presente = true;
                }
            }

            if($valeur_par_defaut_presente === false)
                return array('retour' => false,'message' => traduction('messages.php.traduction.valeur_neccessaire'));

        }

        if(empty($traduction_index)){

            $management_traduction_index = management('traduction_index');

            $retour = $management_traduction_index->enregistre(
                array(
                    'index' => $informations['index'],
                    'categorie' => $informations['categorie']
                )
            );

            if($retour !== true)
                return array('retour' => false,'message' => $retour);

        }

        $langues_traduites = modele('traduction_valeur')->avec_inactifs()->where('index',$informations['index'])->get()->keyBy('langue','id');

        foreach($informations as $colonne => $information){

            if(in_array($colonne,$codes_langues)){

                $code_langue = $colonne;

                $valeur = $information[$this->index_traduction];

                if(isset($langues_traduites[$code_langue])){

                    $management_traduction_valeur = management('traduction_valeur',$langues_traduites[$code_langue]);
                    $donnees_a_sauvegarder = array();

                    if($valeur === null && env('BASE_TRADUCTION') === true) {
                        if(sizeof($langues_traduites) == 1)
                            return array('retour' => false,'message' => traduction('messages.php.traduction.valeur_neccessaire'));
                        else {
                            $management_traduction_valeur->supprime();
                            continue;
                        }
                    }
                    else if($valeur === null && env('BASE_TRADUCTION') !== true && $management_traduction_valeur->modele->traduction_standard == null){

                        $management_traduction_valeur->supprime();
                        continue;
                    }
                }
                else {
                    if($valeur === null)
                        continue;

                    $management_traduction_valeur = management('traduction_valeur');
                    $donnees_a_sauvegarder = array(
                        'index' => $informations['index'],
                        'langue' => $code_langue,
                    );
                }

                $donnees_a_sauvegarder[$this->index_traduction] = $valeur;

                if(!empty($management_traduction_valeur->modele) && $management_traduction_valeur->modele->inactif == 1)
                    $management_traduction_valeur->modele->inactif = 0;

                $management_traduction_valeur->enregistre($donnees_a_sauvegarder);
            }
        }

        Cache_management::genere_traductions();

        return array('retour' => true);
    }

    /**
     * @param Request $request
     *
     * Permet d'importer du json pour ajouter des traductions
     *
     */
    public function importer(Request $request){

        $informations = $request->all();

        $import = $informations['import'];

        if(empty($import))
            return array('retour' => false, 'message' => traduction('messages.php.traduction.import_vide'));

        $import_decode = null;

        try{
            $import_decode = json_decode($import,true);
        }
        catch(\Exception $e){

            return array('retour' => false, 'message' => traduction('messages.php.traduction.format_json_non_respecte'));
        }

        if(empty($import_decode))
            return array('retour' => false, 'message' => traduction('messages.php.traduction.format_json_non_respecte'));

        $categories = management('traduction_index')->champ('categorie')->valeurs_possibles;
        $traductions_existantes = modele('traduction_index')->get()->pluck('index')->toArray();

        foreach($import_decode as $id_categorie => $traductions){

            foreach($traductions as $traduction) {

                if (!is_array($traduction) || !isset($categories[$id_categorie]))
                    return array('retour' => false, 'message' => traduction('messages.php.traduction.format_json_non_respecte'));

                if (empty($traduction['index']))
                    return array('retour' => false, 'message' => traduction('messages.php.traduction.index_manquant'));

                if (in_array($traduction['index'], $traductions_existantes))
                    return array('retour' => false, 'message' => traduction('messages.php.traduction.index_existant',null,[$traduction['index']]));

                if (empty($traduction['valeur']))
                    return array('retour' => false, 'message' => traduction('messages.php.traduction.valeur_neccessaire'));

                $management_traduction_index = management('traduction_index');

                $retour = $management_traduction_index->enregistre(
                    array(
                        'index' => $traduction['index'],
                        'categorie' => $id_categorie
                    )
                );

                if ($retour !== true)
                    return array('retour' => false, 'message' => $retour);

                $management_traduction_valeur = management('traduction_valeur');

                $donnees_a_sauvegarder = array(
                    'index' => $traduction['index'],
                    'langue' => 'fr',
                    'traduction_standard' => $traduction['valeur']
                );

                $retour = $management_traduction_valeur->enregistre($donnees_a_sauvegarder);

                if ($retour !== true)
                    return array('retour' => false, 'message' => $retour);

            }
        }

        return array('retour' => true);
    }

    /**
     *
     * Synchronisation avec la base de données de référence
     *
     */
    public function synchroniser(){

        $service_traduction = service('traduction');

        $service_traduction->synchroniser();

        return response()->json(array('retour' => true));
    }

    /**
     *
     * Synchronisation avec la base de données de préprod ou prod
     *
     */
    public function synchronisation_environnement(){

        $service_traduction = service('traduction');

        $retour = $service_traduction->synchronisation_environnement();

        return response()->json($retour);
    }

    /**
     *
     * Validation de la synchronisation avec la base de données de préprod ou prod
     *
     */
    public function validation_synchronisation_environnement(Request $request){

        $service_traduction = service('traduction');

        $retour = $service_traduction->validation_synchronisation_environnement($request->all());

        return response()->json($retour);
    }

    /**
     *
     * Supprime un index et les traductions existantes
     *
     */
    public function supprimer(Request $request){

        $informations = $request->all();

        $index = $informations['index'];

        $traductions_valeurs = modele('traduction_valeur')->where('index',$index)->get();

        foreach($traductions_valeurs as $traduction_valeur){

            management('traduction_valeur',$traduction_valeur->id)->supprime();
        }

        return response()->json(array('retour' => true));
    }

    /**
     *
     * Permet de récupérer les informations de traduction en fonction d'un index de traduction
     *
     */
    public function recuperation_element(){

        $index_traduction = request()->index_traduction;

        $traduction_index = modele('traduction_index')->where('index',$index_traduction)->first();

        if($traduction_index === null)
            return response()->json(array('retour' => false, 'message'=> traduction('messages.php.traduction.index_introuvable')));

        $element = array(
            'index' => $traduction_index->index,
            'categorie' => $traduction_index->categorie,
        );

        $langues = langues();

        $traductions_valeurs = modele('traduction_valeur')->where('index',$index_traduction)->get()->keyBy('langue')->toArray();

        $type = 'specifique';

        foreach($langues as $langue){

            if(isset($traductions_valeurs[$langue->code])){

                if($traductions_valeurs[$langue->code]['traduction_standard'] !== null)
                    $type = 'standard';

                $element[$langue->code] = array(
                    'traduction_standard' => $traductions_valeurs[$langue->code]['traduction_standard'],
                    'traduction_specifique' => $traductions_valeurs[$langue->code]['traduction_specifique'],
                );
            }
            else{
                $element[$langue->code] = array(
                    'traduction_standard' => null,
                    'traduction_specifique' => null,
                );
            }
        }

        $element['type'] = $type;

        return response()->json(array('retour' => true, 'element'=> $element));
    }

    /**
     *
     * Permet de modifier en masse les valeurs d'un index
     *
     */
    public function modifier_en_masse(){

        $donnees = request()->all();

        $modification = $donnees['modification'];
        $langue = $donnees['langue'];

        if(empty($modification))
            return response()->json(array('retour' => false, 'message' => traduction('messages.php.traduction.modification_vide')));

        foreach($donnees['index'] as $index){

            $traduction_valeur = modele('traduction_valeur')
                ->avec_inactifs()
                ->where('index',$index)
                ->where('langue',$langue)
                ->first();

            $donnees_a_sauvegarder = array();

            if($traduction_valeur !== null) {

                $management_traduction_valeur = management('traduction_valeur',$traduction_valeur->id);

                if($management_traduction_valeur->modele->inactif == 1)
                    $management_traduction_valeur->modele->inactif = 0;
            }
            else {
                $management_traduction_valeur = management('traduction_valeur');

                $donnees_a_sauvegarder = array(
                    'index' => $index,
                    'langue' => $langue,
                );
            }

            $donnees_a_sauvegarder[$this->index_traduction] = $modification;

            $management_traduction_valeur->enregistre($donnees_a_sauvegarder);

        }

        return response()->json(array('retour' => true));
    }

    /**
     *
     * On renvoie les valeurs des fichiers de traduction
     *
     */
    public function mise_a_jour_traductions_valeurs(){

        $langue = 'fr';

        if(moi() !== null)
            $langue = empty(moi()->langue) ? 'fr' : moi()->langue;
        else if(!empty($langues_traductions)){
            foreach ($langues_traductions as $langue_traduction){
                if($langue_traduction['id'] == maquette('langue_par_defaut'))
                    $langue = $langue_traduction['code'];
            }
        }

        $traductions_valeurs = json_decode(file_get_contents(storage_path('app/public/traductions/langue_'.$langue.'.json')));

        return response()->json($traductions_valeurs);
    }

    /**
     *
     * On met à jour le mode de traduction
     *
     */
    public function mode_traduction($valeur){

        session()->put('mode_traduction',$valeur);

        return response()->json(true);
    }

    /**
     *
     * Permet d'envoyer des traductions et resynchroniser la base de données des traductions
     *
     */
    public function envoie_base_modele(Request $request){

        $formulaire = $request->all();

        $elements = $formulaire['elements'];

        $url_index = env('EDEN_MODEL_API_URL').'api/ajout_traductions';

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nx-client-id: " . env('EDEN_MODEL_API_KEY') . "\r\n",
                'method'  => 'POST',
                'content' => http_build_query($elements)
            )
        );

        $contexte  = stream_context_create($options);

        try {
            $requete = file_get_contents($url_index, false, $contexte);
        }
        catch(\Exception $e){
            return response()->json(array('retour' => false, 'message' => $e->getMessage()));
        }

        $requete = json_decode($requete);

        if($requete->retour !== true)
            return response()->json($requete);

        service('traduction')->synchroniser();

        return response()->json(array('retour' => true));
    }
}

