<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Formulaire_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Formulaire_valeur_par_defaut;
use App\Eden\Models\Formulaires_champs;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Formulaire_controller extends Controller {

    /**
     *
     * Permet de récupérer un formulaire notamment pour l'affichage dans les listes et les composants
     *
     */
    public function formulaire(Request $formulaire){

        $parametres = $formulaire->all();

        $nom_formulaire = $parametres['nom_formulaire'];

        $contexte = '';
        if(!empty($parametres['contexte']))
            $contexte = $parametres['contexte'];

        if($_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet') && ($contexte == '' || $contexte == 'fiche_'))
            $contexte = 'extranet_';

        $options = (object) array();
        if(!empty($parametres['options']))
            $options = (object) $parametres['options'];

        $uniquement_champs_editables = false;

        if(!empty($parametres['uniquement_champs_editables']))
            $uniquement_champs_editables = $parametres['uniquement_champs_editables'];

        $champs_obligatoires = [];
        $champs_formulaire = [];

		$surcharger_la_vue = false;

        $formulaire_parametrable = Formulaire::where('nom_formulaire', $contexte.$nom_formulaire)->first();

        if(empty($formulaire_parametrable) && $contexte !== '')
            $formulaire_parametrable = Formulaire::where('nom_formulaire', $nom_formulaire)->first();

        if($formulaire_parametrable !== null && !empty($formulaire_parametrable->type_element))
            $type_element = $formulaire_parametrable->type_element;
        else
            $type_element = $nom_formulaire;

        $champs_type_element = champs_libres($type_element)->keyBy('nom_sql');

        $modele_par_defaut = clone modele_par_defaut($type_element);

        $valeurs_par_defaut = Formulaire_valeur_par_defaut::where('nom_formulaire',$nom_formulaire)->get()->pluck('valeur','nom_sql')->toArray();

        foreach($valeurs_par_defaut as $nom_sql => &$valeur_par_defaut){

            if(isset($champs_type_element[$nom_sql]) && $champs_type_element[$nom_sql]->type == 10 && !empty($valeur_par_defaut))
                $valeur_par_defaut= json_decode($valeur_par_defaut,true);
        }

        if($formulaire_parametrable !== null)
            $surcharger_la_vue = $formulaire_parametrable->surcharger_la_vue;

        if(!empty($surcharger_la_vue) || (!view()->exists('eden::formulaires.'.$contexte.$type_element)
            && (!view()->exists('eden::formulaires.'.$type_element) || $nom_formulaire !== $type_element))) {

            $formulaire_management = new Formulaire_management($formulaire_parametrable,null,$type_element);

            $champs_formulaire = $formulaire_management->champs_formulaires;
            $champs_obligatoires = $formulaire_management->champs_obligatoires();
        }

        $erreur = false;

        if (session()->has('cache.formulaire_vuejs.'.$type_element.'.'.$contexte.$nom_formulaire) && cache_actif() && empty($options)) {

            $formulaire = session()->get('cache.formulaire_vuejs.'.$type_element.'.'.$contexte.$nom_formulaire);
        }

        else {

            $formulaire = view('eden::formulaires.formulaire_vuejs',
                array(
                    'nom_formulaire' => $nom_formulaire,
                    'type_element' => $type_element,
                    'contexte' => $contexte,
                    'options' => $options,
                    'uniquement_champs_editables' => $uniquement_champs_editables,
                )
            )->render();

            if(empty($options))
                session()->put('cache.formulaire_vuejs.'.$type_element.'.'.$contexte.$nom_formulaire,$formulaire);
        }

        $vmodel= in_array($type_element,Variables::$documents_gescom) ? 'document' : $type_element;

        return array(
            'erreur' =>  $erreur,
            'formulaire' => $formulaire,
            'table_libre' => table_libre($type_element),
            'modele_par_defaut' =>  $modele_par_defaut,
            'valeurs_par_defaut' =>  $valeurs_par_defaut,
            'champs_obligatoires' =>  $champs_obligatoires,
            'champs_formulaire' =>  $champs_formulaire,
            'champs_type_element' =>  $champs_type_element,
            'type_element' =>  $type_element,
            'vmodel' =>  $vmodel
        );
    }

    /**
     *
     * Permet de récupérer un formulaire notamment pour l'affichage dans les listes et les composants
     *
     */
    public function sous_formulaire(Request $sous_formulaire){

        $sous_formulaire = $sous_formulaire->all();
        
        $data_vue = [];
        
        $sous_formulaire = retraite_sous_formulaire($sous_formulaire['nom_sous_formulaire'], $sous_formulaire);
        
        if(isset($sous_formulaire['data_vue']))
            $data_vue = $sous_formulaire['data_vue'];
        
        $erreur = false;

        $modele_par_defaut = modele_par_defaut($sous_formulaire['type_element_enfant']);
        
        foreach ($data_vue as $nom_champ => $valeur_defaut){

            $modele_par_defaut[$nom_champ] = $valeur_defaut;
            
        }

        $vue_sous_formulaire = null;

        $name_remplacement_js = $sous_formulaire['type_element_parent'] . '_creation_' . $sous_formulaire['type_element_pour_nom'];

        if(profil_creation($sous_formulaire['type_element_enfant']) !== true)
            $erreur = traduction_blade('composant.champ_selection_element.vous_navez_pas_les_droits');

        else {
            
            if (session()->has('cache.sous_formulaire_vuejs.'.$sous_formulaire['type_element_enfant'].'.'.$sous_formulaire['nom_sous_formulaire']) && cache_actif()) {

                $vue_sous_formulaire = session()->get('cache.formulaire_vuejs.'.$sous_formulaire['type_element_enfant'].'.'.$sous_formulaire['nom_sous_formulaire']);

            }

            else {

                $vue_sous_formulaire = view('eden::formulaires.formulaire_vuejs',
                    array(
                        'sous_formulaire' => true,
                        'type_element_enfant' => $sous_formulaire['type_element_enfant'],
                        'type_element' => $sous_formulaire['type_element_parent'],
                        'name_remplacement_js' => $name_remplacement_js,
                        'type_element_remplacement' => $sous_formulaire['type_element_remplacement'],
                        'nom_sous_formulaire' => $sous_formulaire['nom_sous_formulaire'],
                        'nom_formulaire_parent' => $sous_formulaire['nom_formulaire_parent'],
                        'remplacements_supplementaires_formate' => $sous_formulaire['remplacements_supplementaires'],
                    )
                )->render();

                session()->put('cache.formulaire_vuejs.'.$sous_formulaire['type_element_enfant'].'.'.$sous_formulaire['nom_sous_formulaire'],$vue_sous_formulaire);
            }

        }

        return array(
            'erreur' =>  $erreur,
            'formulaire' => $vue_sous_formulaire,
            'modele_par_defaut' =>  $modele_par_defaut,
            'name_remplacement_js' =>  $name_remplacement_js
        );
    }

    public function affichage_web($id){

        $formulaire = Formulaire::where('formulaire_web_id',$id)->first();

        if(empty($formulaire))
            return '';

        $champs = Formulaires_champs::where('nom_formulaire',$formulaire->nom_formulaire)->orderBy('ordre')->get();
        $valeurs_par_defaut = Formulaire_valeur_par_defaut::where('nom_formulaire',$formulaire->nom_formulaire)->get()->pluck('valeur','nom_sql')->toArray();

        foreach($champs as $champ){

            $management = management($formulaire->type_element)->champ($champ->nom_sql);
            $management->champ_formulaire = $champ;

            $champ->management = $management;
        }

        return view('eden::formulaires.formulaire_web',['champs' => $champs,'formulaire' => $formulaire,'valeurs_par_defaut' => $valeurs_par_defaut]);
    }

    public function valider_formulaire_web($formulaire_web_id,Request $donnees){

        $donnees = $donnees->all();

        try {
            $retour = json_decode(file_get_contents_post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => fonctionnalite('recaptcha_cle_prive'),
                'response' => $donnees['g-recaptcha-response'] ?? null
            ]));
        }
        catch(\Exception | \Throwable $e){
            $retour = null;
        }

        if(empty($retour->success) || (!empty($retour->score) && $retour->score < 0.8))
            return response()->json(['erreur' => true, 'message' => traduction('formulaire.formulaire_web.requete_non_abouti')]);

        unset($donnees['g-recaptcha-response']);

        $formulaire = Formulaire::where('formulaire_web_id',$formulaire_web_id)->first();

        if(empty($formulaire))
            return response()->json(['erreur' => true, 'message' => traduction('formulaire.formulaire_web.requete_non_abouti')]);

        $champs = Formulaires_champs::where('nom_formulaire',$formulaire->nom_formulaire)->get()->keyBy('nom_sql');
        $valeurs_par_defaut = Formulaire_valeur_par_defaut::where('nom_formulaire',$formulaire->nom_formulaire)->get()->pluck('valeur','nom_sql')->toArray();
        $modeles_champs_libres = Champ_libre::where('type_element',$formulaire->type_element)->get()->keyBy('nom_sql');

        foreach($donnees as &$valeur){
            if($valeur == 'on')
                $valeur = 1;
        }

        foreach($modeles_champs_libres as $modele_champ_libre){

            if($modele_champ_libre->type == 10 && !empty($valeurs_par_defaut[$modele_champ_libre->nom_sql]))
                $valeurs_par_defaut[$modele_champ_libre->nom_sql] = json_decode($valeurs_par_defaut[$modele_champ_libre->nom_sql]);
        }

        $donnees = $donnees + $valeurs_par_defaut;

        $champs_obligatoires_non_remplis = [];

        foreach($champs as $champ){

            $valeur = $donnees[$champ->nom_sql] ?? null;

            if(empty($modeles_champs_libres[$champ->nom_sql]))
                continue;

            if(empty($valeur) && (!empty($champ->condition_obligatoire) || $modeles_champs_libres[$champ->nom_sql]->obligatoire == 1)) {
                $champs_obligatoires_non_remplis[] = [
                    'nom_sql' => $champ->nom_sql,
                    'nom' => traduction('champs_libres.' . $champ->type_element . '.' . $champ->nom_sql . '.nom')
                ];
            }
        }

        if(!empty($champs_obligatoires_non_remplis))
            return response()->json(['erreur' => true, 'message' => traduction('formulaire.formulaire_web.champs_obligatoires'), 'champs_libres' => $champs_obligatoires_non_remplis]);

        $management_element = management($formulaire->type_element);

        $retour_format = $management_element->verifie_formatage_champs(null,$donnees);

        if($retour_format !== true)
            return response()->json(['erreur' => true, 'message' => $retour_format]);

        foreach($modeles_champs_libres->whereIn('type',[7,15]) as $champ){

            if(empty($donnees[$champ->nom_sql]))
                continue;

            $fichiers = [];

            if($champ->type == 7)
                $donnees[$champ->nom_sql] = [$donnees[$champ->nom_sql]];

            foreach($donnees[$champ->nom_sql] as $fichier) {

                if ($fichier->getError() > 0)
                    return response()->json(array(
                        'erreur' => true,
                        'message' => traduction('messages.php.upload.fichier_trop_volumineux')
                    ));

                if (!in_array($fichier->getMimeType(), Variables::extension_fichier_accepte()))
                    return response()->json(array(
                        'erreur' => true,
                        'message' => traduction('messages.php.upload.type_non_valide')
                    ));

                if (fonctionnalite('pieces_jointes_garder_nom_originel') === true) {

                    $nom_fichier = retraite_caracteres_speciaux(pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME), '_');

                    $extension = pathinfo($fichier->getClientOriginalName(), PATHINFO_EXTENSION);

                    $nom_original = $nom_fichier . '.' . $extension;

                    $path = $fichier->storeAs('public', $nom_original);
                } else {
                    $hash = Str::random(40);

                    $path = $fichier->storeAs('public', $hash . '.' . $fichier->getClientOriginalExtension());
                }

                $fichiers[] = [
                    'url_storage' => $path,
                    'url_public' => str_replace('public/', 'storage/', $path),
                    'type' => $fichier->extension(),
                    'nom_original' => $fichier->getClientOriginalName(),
                ];
            }

            $donnees[$champ->nom_sql] = $champ->type == 7 ? str_replace('public/', '', $fichiers[0]['url_storage']) : json_encode($fichiers);
        }

        if(isset($donnees['/url_source'])) {
            if (!empty($formulaire->champ_url_source_origine))
                $donnees[$formulaire->champ_url_source_origine] = $donnees['/url_source'];

            unset($donnees['/url_source']);
        }

        $retour_enregistrement = $management_element->enregistre($donnees);

        if($retour_enregistrement !== true)
            return response()->json(['erreur' => true, 'message' => traduction('formulaire.formulaire_web.requete_non_abouti')]);

        return response()->json(['erreur' => false, 'message' => traduction('formulaire.formulaire_web.formulaire_envoye')]);
    }
}