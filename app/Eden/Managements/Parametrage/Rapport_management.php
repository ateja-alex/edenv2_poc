<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Maintenance_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_parametre;
use App\Eden\Managements\Rapports\Rapports_management;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class Rapport_management {

    public function enregistrer_nouveau_rapport($parametres) {

		// On vérifie si l'id_rapport n'est pas vide
		if(empty($parametres['id_rapport']))
			return array('retour' => traduction('messages.php.rapport.id_rapport_vide'));

		$id_rapport = strtolower($parametres['id_rapport']);

		// On doit remplir la catégorie
		if(empty($parametres['categorie']))
			return array('retour' => traduction('messages.php.rapport.categorie_vide'));

		// On doit remplir le type élement
		if(empty($parametres['type_element']) && $parametres['type_rapport'] != 'carte')
            return array('retour' => traduction('messages.php.rapport.type_element_vide'));

		// On vérifie si le rapport n'a bien que des caractères autorisés
		if(preg_match('/(\w+)/', $id_rapport, $matches) !== 1 || $matches[0] !== $id_rapport)
			return array('retour' => traduction('messages.php.rapport.erreur_format_id_rapport'));

		// Commenté par Mathis le 12/07/2021 : bloqué tout enregistrement de modification de rapport, je le laisse là si c'était nécessaire.
		// return response()->json(array('retour' => 'OK'));

		if(empty($parametres['id'])) {

			$count = Rapport_libre::where('id_rapport', $id_rapport)->count();

			if($count > 0)
				return (array('retour' => traduction('messages.php.rapport.id_rapport_existant')));

			$nouveau_rapport = new Rapport_libre;

            if(!empty($parametres["type_element_fiche"])) {

                if(empty($parametres["cle_primaire"]))
                    return (array('retour' => traduction('messages.php.rapport.cle_primaire_obligatoire')));

                if(empty($parametres["cle_etrangere"]))
                    return (array('retour' => traduction('messages.php.rapport.cle_etrangere_obligatoire')));

                if($parametres['type_rapport'] == 'carte') {
                    if (!isset($parametres["parametrage_rapport_libre"]))
                        $parametres["parametrage_rapport_libre"] = [];

                    if (!isset($parametres["parametrage_rapport_libre"]["types_elements_carte"]))
                        $parametres["parametrage_rapport_libre"]["types_elements_carte"] = [];

                    $parametres["parametrage_rapport_libre"]["types_elements_carte"][] = $parametres["type_element_fiche"];
                }
            }
		}
		else {

			$nouveau_rapport = Rapport_libre::find($parametres['id']);

            if($parametres['type_rapport'] == 'indicateur' && empty($parametres['parametrage_rapport_libre']['type_calcul']))
                return array('retour' => traduction('messages.php.rapport.erreur_type_calcul_obligatoire'));
		}

		$parametres = $this->nettoie_parametres_rapport_libre($parametres);

		$type_element = $parametres['type_element'] ?? 'adresse';

        if(!empty($parametres['id']))
            $id_rapport_numerique = $parametres['id'];

        $id_rapport_dupliquer = $parametres['id_rapport_dupliquer'] ?? null;

		unset($parametres['id_rapport_dupliquer']);
		unset($parametres['id_rapport']);
		unset($parametres['id']);

        if(in_array($parametres['type_rapport'],['indicateur','pdf']) && $id_rapport_dupliquer != null) {

            $recherche_avancee = modele('recherche_avancee')
                ->where('type', 'rapport')
                ->where('id_cible', $id_rapport_dupliquer)
                ->first();

            if(!empty($recherche_avancee))
                $parametres['parametrage_rapport_libre']['filtres']['structure'] = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)
                    ->structure(true);
        }

        $type_formulaire = $parametres['type_formulaire'] ?? null;

        if(!empty($parametres['type_formulaire']))
            unset($parametres['type_formulaire']);

        foreach($parametres as $cle => &$valeur) {

            if($cle == 'parametrage_rapport_libre') {
                if(($type_formulaire == 'creation' && $id_rapport_dupliquer == null) || empty($valeur))
                    continue;

                if(!in_array($parametres['type_rapport'],['courbe','histogramme','tableau']))
                    unset($valeur['series']);

                if(!empty($valeur['serie']) && isset($valeur['serie']['type_calcul'])){

                    if(!isset($valeur['serie']['index_traduction'])) {
                        $index_traduction = 'rapport.'.$id_rapport.'.serie';

                        $valeur['serie']['index_traduction'] = service('traduction')->calcul_index_traduction(
                            10,
                            explode('.',$index_traduction),
                            array(
                                'nom' => $valeur['serie']['nom'],
                            )
                        );
                    }

                    if($id_rapport_dupliquer != null) {

                        $recherche_avancee = modele('recherche_avancee')
                            ->where('type', 'rapport')
                            ->where('id_cible', $id_rapport_dupliquer)
                            ->first();

                        if(!empty($recherche_avancee))
                            $valeur['serie']['filtres']['structure'] = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)
                                ->structure(true);
                    }

                    if(!empty($valeur['serie']['filtres'])) {

                        $recherche_avancee = modele('recherche_avancee')
                            ->where('type','rapport')
                            ->where('id_cible',$id_rapport)
                            ->first();

                        $filtres = $valeur['serie']['filtres'];
                        $filtres['type_element'] = $type_element;
                        $filtres['type'] = 'rapport';
                        $filtres['id_cible'] = $id_rapport;

                        $management = management('recherche_avancee');

                        if (!empty($recherche_avancee))
                            $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                        if(empty($filtres['structure']) && !empty($recherche_avancee))
                            $management->supprime();
                        else
                            $management->enregistre($filtres);

                        unset($valeur['serie']['filtres']);
                    }

                }
                else if(!empty($valeur['series']) && !in_array($parametres['type_rapport'],['carte','indicateur','pdf'])){

                    $recherches_avancees = modele('recherche_avancee')
                        ->where('type',$id_rapport . '.serie')
                        ->get()->keyBy('id');

                    if($id_rapport_dupliquer != null)
                        $recherches_avancees_duplication = modele('recherche_avancee')
                            ->where('type',$id_rapport_dupliquer . '.serie')
                            ->get()->keyBy('id_cible');

                    $index_traduction_supprimer = modele('traduction_index')
                        ->where('index','Like','rapport.'.$id_rapport.'.serie%')
                        ->get()->pluck('index')->toArray();

                    foreach($valeur['series'] as $index => &$serie){

                        $compteur_index_serie = modele('traduction_index')
                            ->avec_inactifs()
                            ->where('index','Like','rapport.'.$id_rapport.'.serie%')
                            ->count();

                        if(isset($serie['Nom']))
                            $serie['nom'] = $serie['Nom'];

                        if(isset($serie['index_traduction']))
                            unset($index_traduction_supprimer[array_search($serie['index_traduction'].'.nom', $index_traduction_supprimer)]);
                        else {
                            $index_traduction = 'rapport.'.$id_rapport.'.serie_'.$compteur_index_serie;

                            $valeur['series'][$index]['index_traduction'] = service('traduction')->calcul_index_traduction(
                                10,
                                explode('.',$index_traduction),
                                array(
                                    'nom' => $serie['nom'],
                                )
                            );
                        }

                        if($id_rapport_dupliquer != null && !empty($recherches_avancees_duplication[$serie['id']]))
                            $serie['filtres']['structure'] = management('recherche_avancee', $recherches_avancees_duplication[$serie['id']]->id, $recherches_avancees_duplication[$serie['id']])
                                ->structure(true);

                        if(!empty($serie['filtres'])) {

                            $filtres = $serie['filtres'];
                            $filtres['type_element'] = $type_element;
                            $filtres['type'] = $id_rapport . '.serie';
                            $filtres['id_cible'] = $serie['id'];

                            $management = management('recherche_avancee');

                            if (isset($filtres['id']) && isset($recherches_avancees[$filtres['id']])) {
                                $management = management('recherche_avancee', $filtres['id'], $recherches_avancees[$filtres['id']]);

                                unset($recherches_avancees[$filtres['id']]);
                            }

                            if(empty($filtres['structure']) && isset($recherches_avancees[$filtres['id']]))
                                $management->supprime();
                            else
                                $management->enregistre($filtres);

                            unset($serie['filtres']);
                        }
                    }

                    foreach($index_traduction_supprimer as $index_a_supprimer){

                        $index_a_supprimer = str_replace('.nom','',$index_a_supprimer);
                        service('traduction')->supprime_index_traduction($index_a_supprimer);
                    }

                    foreach($recherches_avancees as $recherche_avancee){
                        management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)->supprime();
                    }
                }
                else if($parametres['type_rapport'] == 'carte'){

                    $recherches_avancees = modele('recherche_avancee')
                        ->where('type', $id_rapport . '.type_element')
                        ->get()->keyBy('id');

                    if($id_rapport_dupliquer != null)
                        $recherches_avancees_duplication = modele('recherche_avancee')
                            ->where('type', $id_rapport_dupliquer . '.type_element')
                            ->get()->keyBy('id_cible');


                    if(!empty($valeur['types_elements_carte']))
                        $valeur['types_elements_carte'] = array_values(
                            array_filter(
                                $valeur['types_elements_carte'],
                                fn($v) => !empty($v)
                            )
                        );

                    $types_elements_cartes = array_merge(
                        ['adresse'],
                        ($valeur['types_elements_carte'] ?? []));

                    foreach ($types_elements_cartes as $type_element) {

                        if($id_rapport_dupliquer != null && !empty($recherches_avancees_duplication[$type_element]))
                            $valeur['filtres_' . $type_element]['structure'] = management('recherche_avancee', $recherches_avancees_duplication[$type_element]->id, $recherches_avancees_duplication[$type_element])
                                ->structure(true);

                        if (!empty($valeur['filtres_' . $type_element])) {

                            $filtres = $valeur['filtres_' . $type_element];
                            $filtres['type_element'] = $type_element;
                            $filtres['type'] = $id_rapport . '.type_element';
                            $filtres['id_cible'] = $type_element;

                            $management = management('recherche_avancee');

                            if (isset($filtres['id']) && isset($recherches_avancees[$filtres['id']])) {
                                $management = management('recherche_avancee', $filtres['id'], $recherches_avancees[$filtres['id']]);

                                unset($recherches_avancees[$filtres['id']]);
                            }

                            if (empty($filtres['structure']) && isset($recherches_avancees[$filtres['id']]))
                                $management->supprime();
                            else
                                $management->enregistre($filtres);

                            unset($valeur['filtres_' . $type_element]);
                        }
                    }

                    foreach ($recherches_avancees as $recherche_avancee) {
                        management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
                    }

                    if(!empty($valeur['couleurs'])) {
                        $recherches_avancees = modele('recherche_avancee')
                            ->where('type', $id_rapport . '.couleur')
                            ->get()->keyBy('id');

                        if($id_rapport_dupliquer != null)
                            $recherches_avancees_duplication = modele('recherche_avancee')
                                ->where('type', $id_rapport_dupliquer . '.couleur')
                                ->get()->keyBy('id_cible');


                        foreach ($valeur['couleurs'] as $index => &$couleur) {

                            if($id_rapport_dupliquer != null && !empty($recherches_avancees_duplication[$couleur['id']]))
                                $couleur['filtres']['structure'] = management('recherche_avancee', $recherches_avancees_duplication[$couleur['id']]->id, $recherches_avancees_duplication[$couleur['id']])
                                    ->structure(true);

                            if (!empty($couleur['filtres'])) {

                                $filtres = $couleur['filtres'];
                                $filtres['type_element'] = $couleur['type_element'];
                                $filtres['type'] = $id_rapport . '.couleur';
                                $filtres['id_cible'] = $couleur['id'];

                                $management = management('recherche_avancee');

                                if (isset($filtres['id']) && isset($recherches_avancees[$filtres['id']])) {
                                    $management = management('recherche_avancee', $filtres['id'], $recherches_avancees[$filtres['id']]);

                                    unset($recherches_avancees[$filtres['id']]);
                                }

                                if (empty($filtres['structure']) && isset($recherches_avancees[$filtres['id']]))
                                    $management->supprime();
                                else
                                    $management->enregistre($filtres);

                                unset($couleur['filtres']);
                            }
                        }

                        foreach ($recherches_avancees as $recherche_avancee) {
                            management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->supprime();
                        }
                    }
                }
                else if(in_array($parametres['type_rapport'],['indicateur','pdf']) && !empty($valeur['filtres'])) {

                    $recherche_avancee = modele('recherche_avancee')
                        ->where('type', 'rapport')
                        ->where('id_cible', $id_rapport)
                        ->first();

                    $filtres = $valeur['filtres'];
                    $filtres['type_element'] = $type_element;
                    $filtres['type'] = 'rapport';
                    $filtres['id_cible'] = $id_rapport;

                    $management = management('recherche_avancee');

                    if (!empty($recherche_avancee))
                        $management = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

                    if (empty($filtres['structure']) && !empty($recherche_avancee))
                        $management->supprime();
                    else
                        $management->enregistre($filtres);

                    unset($valeur['filtres']);
                }

                $valeur = json_encode($valeur);
            }

            $nouveau_rapport->$cle = $valeur;
        }
        
		$nouveau_rapport->inactif = 0;

		// pour éviter les modifications d'id_rapport
		if(empty($nouveau_rapport->id))
			$nouveau_rapport->id_rapport = $id_rapport;

        if(empty($nouveau_rapport->index_traduction)) {
            $nouveau_rapport->index_traduction = service('traduction')->calcul_index_traduction(
                10,
                array(
                    'rapport',
                    $id_rapport
                ),
                array(
                    'description' => $nouveau_rapport->description,
                    'titre' => $nouveau_rapport->titre,
                )
            );
        }

		$nouveau_rapport->save();

        if(in_array($nouveau_rapport->type_rapport,['indicateur', 'pdf', 'histogramme', 'courbe', 'tableau', 'diagramme_circulaire', 'graphique_funnel', 'carte'])) {
            return array(
                'retour' => true,
                'redirect' => route('parametrage.rapport.parametrer', ['id_rapport' => $nouveau_rapport->id_rapport]),
                'id_rapport' => $nouveau_rapport->id_rapport
            );
        }

		// on enregistre la liste libre
		if($nouveau_rapport->type_rapport == 'liste_libre' && empty($id_rapport_numerique)) {

            $liste = array(
                'type_element' => $type_element,
                'id_rapport' => $id_rapport,
            );

            if(!empty($id_rapport_dupliquer)){

                $liste_libre_dupliquer = Liste_libre::where('id_rapport',$id_rapport_dupliquer)->first();

                if(!empty($liste_libre_dupliquer)) {

                    foreach ($liste_libre_dupliquer->getAttributes() as $cle => $valeur){

                        if(!in_array($cle,['id','type_element','id_rapport']))
                            $liste[$cle] = $valeur;
                    }

                    $liste['colonnes'] = [];

                    $les_colonnes = Colonne::where('liste_libre_id',$liste_libre_dupliquer->id)->get();

                    foreach($les_colonnes as $colonne){

                        unset($colonne['id']);
                        unset($colonne['liste_libre_id']);
                        unset($colonne['index_traduction']);

                        $liste['colonnes'][] = $colonne;
                    }

                    $liste['filtres'] = [];

                    $les_filtres = Liste_libre_filtre::where('liste_libre_id',$liste_libre_dupliquer->id)->get();

                    foreach($les_filtres as $filtre){

                        unset($filtre['id']);
                        unset($filtre['liste_libre_id']);

                        $liste['filtres'][] = $filtre;
                    }

                    $liste['calculs'] = [];

                    $les_calculs = Liste_libre_calcul::where('liste_libre_id',$liste_libre_dupliquer->id)->get();

                    foreach($les_calculs as $calcul){

                        unset($calcul['id']);
                        unset($calcul['liste_libre_id']);
                        unset($calcul['index_traduction']);

                        $liste['calculs'][] = $calcul;
                    }

                    $les_couleurs = Liste_libre_couleur::where('liste_libre_id',$liste_libre_dupliquer->id)->get();

                    $filtres_couleurs = modele('recherche_avancee')
                        ->where('type','listes_libres_couleur')
                        ->whereIn('id_cible',$les_couleurs->pluck('id')->toArray())
                        ->get()->keyBy('id_cible');

                    foreach($les_couleurs as $couleur){

                        if(!empty($filtres_couleurs[$couleur->id]))
                            $couleur['filtres'] = management('recherche_avancee',$filtres_couleurs[$couleur->id]->id,$filtres_couleurs[$couleur->id])
                                ->structure(true);

                        unset($couleur['id']);
                        unset($couleur['liste_libre_id']);

                        $liste['couleurs'][] = $couleur;
                    }
                    
                    $recherche_avancee = modele('recherche_avancee')
                        ->where('type','filtres_appliques')
                        ->where('id_cible',$liste_libre_dupliquer->id)
                        ->first();

                    if(!empty($recherche_avancee))
                        $liste['filtres_appliques'] = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)->structure(true);
                }
            }

            $liste_libre = Liste_libre::where('id_rapport',$id_rapport)->first();

            $elements_enfants = [];

            if(empty($liste_libre))
                $liste_libre = new Liste_libre();
            else{
                $couleurs = Liste_libre_couleur::whereIn('liste_libre_id', $liste_libre->id)->get();
                $filtres_couleurs = modele('recherche_avancee')
                    ->where('type','listes_libres_couleur')
                    ->whereIn('id_cible', $couleurs->pluck('id')->toArray())
                    ->get()
                    ->keyBy('id_cible');

                foreach($couleurs as $couleur){
                    if(!empty($filtres_couleurs[$couleur->id]))
                        $couleur->filtres = $filtres_couleurs[$couleur->id];
                }

                $elements_enfants = [
                    'rapport' => $nouveau_rapport,
                    'colonnes' => Colonne::where('liste_libre_id', $liste_libre->id)
                        ->select(DB::raw("IF(nom = '#' OR nom IS NULL OR nom = '',nom,index_traduction) as identifiant_colonne,listes_libres_colonnes.*"))
                        ->get()->keyBy('identifiant_colonne'),
                    'calculs' => Liste_libre_calcul::where('liste_libre_id', $liste_libre->id)->get()->keyBy('index_traduction'),
                    'filtres' => Liste_libre_filtre::where('liste_libre_id', $liste_libre->id)
                        ->select(DB::raw("CONCAT(type_element,'|',nom_sql) as identifiant_filtre,eden_listes_libres_filtres.*"))
                        ->get()->keyBy('identifiant_filtre'),
                    'couleurs' => $couleurs->keyBy('couleur'),
                ];
            }

            $nouvelle_liste_libre = Maintenance_management::cree_infos_liste_libre($liste,$liste_libre,[],$elements_enfants);

            $redirection = route('parametrage.liste_libre.index',[$nouvelle_liste_libre->id]);

            Cache_management::generation_liste_libre($nouvelle_liste_libre->id);
            Liste_libre_management::generer_fichier_migration_liste_libre($nouvelle_liste_libre->id);

			return array('retour' => true, 'id' => $nouvelle_liste_libre->id, 'redirection' => $redirection);
		}
        else if($nouveau_rapport->type_rapport == 'liste_libre' && !empty($id_rapport_numerique)){

            foreach (Liste_libre::where('id_rapport', $id_rapport)->where('type_element',$type_element)->get() as $liste){
                Cache_management::generation_liste_libre($liste->id);
                Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);
            }
        }

		return array(
            'retour' => true,
            'id_rapport' => $nouveau_rapport->id_rapport
        );
	}


    public static function generer_fichier_migration_rapport($id_rapport) {

        // Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if(defined('migration_en_cours'))
            return true;


        $le_rapport = Rapport_libre::where('id', $id_rapport)->first();
        $la_liste_libre = Liste_libre::where('id_rapport', $le_rapport->id_rapport)->first();


        // si le rapport n'existe pas, on passe
        if($le_rapport == null) 
            return false;

        // Si c'est un rapport de type liste libre, on le gère déjà dans Liste_libre_management
        if($la_liste_libre != null) 
            return false;
        

        // On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Rapports';

        if(!\File::isDirectory($chemin_dossier_migrations))
            \File::makeDirectory($chemin_dossier_migrations, 0777, true, true);


        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
            return false;


        $texte = 
            '<?php
                return [
                    "categorie" => "'.$le_rapport->categorie.'",
                    "icone" => "'.$le_rapport->icone.'",
                    "titre" => "'.$le_rapport->titre.'",
                    "description" => "'.$le_rapport->description.'",
                    "ordre" => "'.$le_rapport->ordre.'",
                    "inactif" => "'.$le_rapport->inactif.'",
                ];';


        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = $chemin_dossier_migrations.'/'.$le_rapport->id_rapport.'.php';

        // Si le fichier existe, on le supprime
	    if (file_exists($chemin_avec_nom_document) == true)
            unlink($chemin_avec_nom_document);

        // Enregistrement du fichier
        $fichier = fopen($chemin_avec_nom_document, "x+");
        fputs($fichier, $texte );
        fclose($fichier);

        return true;

    }

    /**
     *
     * Vérifie l'utilisation d'un rapport
     *
     */
    public static function verification_utilisation($id_rapport,$liste_libre = null){

        $tableau_de_bord = modele('tableau_de_bord_contenu')->where('element',$id_rapport)->where('type',1)->first();

        if($tableau_de_bord != null)
            return traduction('messages.php.rapport.present_tableau_de_bord').' '.$tableau_de_bord->id;

        $rapport_cible = Rapport_libre::where('id_rapport_cible',$id_rapport)->first();

        if($rapport_cible != null)
            return traduction('messages.php.rapport.rapport_cible_indicateur').' ('.$rapport_cible->id_rapport.')';

        if($liste_libre != null && !empty($liste_libre->fiche)){

            $management = fiche($liste_libre->fiche, 0);

            $modules_utilises = $management->modules_utilises();

            if(in_array($id_rapport,$modules_utilises))
                 return traduction('messages.php.liste.present_fiche').' '.$liste_libre->fiche;

        }

        return true;
    }

    /**
     *
     * Indique si un rapport est standard ou non
     *
     */
    public static function rapport_standard($rapport,$liste_libre = null){

        // on regarde si c'est un rapport standard
		$rapport_standard = false;

        $repertoire = self::rapport_repertoire_stockage($rapport);

		if(!empty($rapport->id_rapport)) {

			if(file_exists(app_path('Eden/'.$repertoire.$rapport->id_rapport.'.php')))
				$rapport_standard = true;
		}
        else if($liste_libre !== null){
            if(file_exists(app_path('Eden/'.$repertoire.$liste_libre->type_element.'.php')))
				$rapport_standard = true;
        }

        return $rapport_standard;
    }

    /**
     *
     * Récupére le répertoire dans lequel le rapport est stocké
     *
     */
    public static function rapport_repertoire_stockage($rapport){

        $repertoire = 'Migrations/Rapports/';

        if(empty($rapport->id_rapport))
            $repertoire = 'Migrations/Listes_libres/';

        else if(!empty($rapport->liste_sur_fiche))
            $repertoire = 'Migrations/Listes_libres_fiches/';

        else if(!empty($rapport->export))
            $repertoire = 'Migrations/Listes_libres_export/';

        return $repertoire;
    }

    /**
	 *
	 * Supprime un rapport spécifique
	 *
	 */
	public static function supprimer($rapport,$liste_libre = null) {

		if($rapport == null)
            return false;

        $repertoire = Rapport_management::rapport_repertoire_stockage($rapport);

        // on supprime le fichier de migrations dans app/Migrations/Rapports
        if(file_exists(app_path($repertoire.$rapport->id_rapport.'.php')))
            unlink(app_path($repertoire.$rapport->id_rapport.'.php'));

        if($liste_libre != null) {

            // on supprime la ligne en bdd
            $liste_libre->delete();

            $id_liste_libre = $liste_libre->id;

            Colonne::where('liste_libre_id', $id_liste_libre)->delete();
            Liste_libre_filtre::where('liste_libre_id', $id_liste_libre)->delete();
            Liste_libre_calcul::where('liste_libre_id', $id_liste_libre)->delete();
            Liste_libre_couleur::where('liste_libre_id', $id_liste_libre)->delete();
            Liste_libre_autresvues::where('liste_libre_id_1', $id_liste_libre)->delete();

        }

        Rapport_parametre::where('id_rapport',$rapport->id_rapport)->delete();

        if(!empty($rapport->index_traduction))
            service('traduction')->supprime_index_traduction($rapport->index_traduction);

        $rapport->delete();

		return true;
	}

    /**
	 *
	 * Néttoie les paramétres du rapport libre (retire les filtres appliqués vides)
	 *
	 */
	public function nettoie_parametres_rapport_libre($parametres) {

		$type_element = $parametres['type_element'];

        if(isset($parametres['parametrage_rapport_libre'])) {
            $parametrage_rapport_libre = $parametres['parametrage_rapport_libre'];

            // les filtres du rapport
            if (!empty($parametrage_rapport_libre['filtres_rapport'])) {
                foreach ($parametrage_rapport_libre['filtres_rapport'] as $index => $filtre) {
                    if (empty($filtre))
                        unset($parametrage_rapport_libre['filtres_rapport'][$index]);
                }

                $parametres_rapport = Rapports_management::recupere_parametres($parametres['id_rapport']);

                foreach ($parametres_rapport as $index => $parametre) {
                    $nom_sql = explode('.', $index);

                    if (isset($nom_sql[1]) && !in_array($nom_sql[1], $parametrage_rapport_libre['filtres_rapport']))
                        Rapports_management::enregistre_parametres($parametres['id_rapport'], [$index => false], true);
                }
            } else {
                    Rapports_management::enregistre_parametres($parametres['id_rapport'], []);
            }

            $parametres['parametrage_rapport_libre'] = $parametrage_rapport_libre;
        }

        if(isset($parametres['type_liste']))
            unset($parametres['type_liste']);

		return $parametres;
	}
}