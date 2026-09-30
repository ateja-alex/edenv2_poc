<?php

namespace App\Eden\Controllers\Parametrage;

use App\Http\Controllers\Controller;

use App\Eden\Models\Table_libre;
use App\Eden\Models\Champ_libre;

use App\Eden\Managements\Parametrage\Champ_libre_management;
use App\Eden\Managements\Parametrage\Table_libre_management;

use App\Eden\Managements\Parametrage\Liste_libre_management;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Managements\Cache_management;
use App\Eden\Models\Liste_libre;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use DB;

class Tables_libres_controller extends Controller {

	/**
	 *
	 * Affiche la liste des tables libres
	 *
	 */
    public function index() {

		$tables_libres = Table_libre::orderBy('module')->orderBy('nom_table');

        if(!editeur())
            $tables_libres->where(function($condition){
                    $condition->where('table_systeme',0)
                        ->orWhereNull('table_systeme');
            });

        $tables_libres = $tables_libres->get();

		$liste_tables_libres = Table_libre_management::classer_tables_libres_par_module($tables_libres);

		return view('eden::parametrage.tables_libres', [

			'liste_tables_libres' => collect($liste_tables_libres),
		]);
    }
    public function index_editer_table($type_element) {

    	// ID du type element pour l'ouverture de la modal d'édition
		$id_modal = Arr::first(Table_libre::where('type_element', $type_element)->first()->only('id'));

		$tables_libres = Table_libre::orderBy('module')->orderBy('nom_table')->get();

		$liste_tables_libres = Table_libre_management::classer_tables_libres_par_module($tables_libres);

		return view('eden::parametrage.tables_libres', [

			'liste_tables_libres' => collect($liste_tables_libres),
			'id_modal' => $id_modal,
		]);
    }

    /**
	 *
	 * Récupère les détails d'une table libre
	 *
	 */
    public function afficher_table_libre() {

		$tables_libres = Table_libre::where('id_table', $id_table)->orderBy('ordre')->get();

		return view('eden.tables_libres', [

			'tables_libres' => $tables_libres,
		]);
    }

    /**
	*
	* Enregistre une table libre (nouvelle ou modification)
	*
	*/
	public function enregistrer_table_libre(Request $formulaire) {
	    // Quand il s'agit d'une nouvelle table, donc sans id
		if(empty($formulaire->id_table)){

			$table_libre = Table_libre_management::enregistre($formulaire->input('element'), $formulaire->all());

            if($table_libre == true)
                $retour_liste_libre = Liste_libre_management::creer_liste_libre($formulaire->all());

			if(isset($retour_liste_libre) && $retour_liste_libre != true)
                return json_encode(array('retour' => $retour_liste_libre));


            return json_encode(array('retour' => $table_libre));

		}

		// Quand il s'agit d'une modification avec un id existant

		else {

		    $retour =  Table_libre_management::enregistre($formulaire->input('element'), $formulaire->all());

			// Permet d'afficher les messages d'erreurs pour compléter les champs

		    if ($retour != true) {

				return json_encode(array('retour' => $retour));
			}

			else {

		        $table_libre = Table_libre::find($formulaire->id_table);
		        $table_libre->nom_table = $formulaire->input('nom_table');
		        $table_libre->element = $formulaire->input('element');
		        $table_libre->element_pluriel = $formulaire->input('element_pluriel');
				$table_libre->creation_rapide = $formulaire->input('creation_rapide');
				$table_libre->template_responsive = $formulaire->input('template_responsive');

				if(fonctionnalite('utiliser_extranet')){

					$table_libre->type_profil_extranet = $formulaire->input('type_profil_extranet');
					$table_libre->champ_profil_extranet = $formulaire->input('champ_profil_extranet');
					$table_libre->acces_extranet = $formulaire->input('acces_extranet');

				}

		        $table_libre->save();
			}
		}

		$tables_libres = Table_libre::where('id_table', $formulaire->input('id_table'))->get();

		Table_libre_management::generer_fichier_migration($table_libre->type_element);

        Cache_management::partage_oublie_table($table_libre->type_element);

		return json_encode(array('retour' => $retour, 'tables_libres' => $tables_libres));
    }

	/**
	 *
	 * Permet d'effacer une table de la BDD (tables_libres & table)
	 *
	 */
	public function destroy(Request $formulaire){
	    $table_libre = Table_libre::find($formulaire->id_table);
		$nom_table = $table_libre->nom_table_sql;

        if(!empty($table_libre->index_traduction))
            service('traduction')->supprime_index_traduction($table_libre->index_traduction);

	   	$destruction = Table_libre_management::supprime($nom_table);
		$result = Table_libre::destroy($formulaire->id_table);
        return response()->json($result);
	}


	/**
	 *
	 * Recupère table libre en ajax
	 *
	 */
	public function recupere_table_libre($id) {

		$table = Table_libre::where('id', $id)->first();

        $champs_libres = (new Table_libre_management())->chargement_valeurs_forcees_extranet($table);

		return response()->json(array(
            'table_libre_modification' => $table,
            'champs_libres' => $champs_libres,
        ));
	}

    /**
	 *
	 * Récupère la table libre en ajax via le type_element
	 *
	 */
	public function recupere_table_libre_ajax($type_element) {

		return table_libre($type_element);
	}

	/**
	 *
	 * Url appelée depuis les pages zoom
	 *
	 * Je ne comprends par pourquoi il y a une 2eme méthode, elle me semble faire doublon avec la méthode enregistrer_table_libre ?
	 *
	 */
	public function modification_table_libre(Request $formulaire) {

		$donnee_table_libre = $formulaire->table_libre;

        if(empty($donnee_table_libre['valeurs_forcees_creation_extranet']))
            $donnee_table_libre['valeurs_forcees_creation_extranet'] = null;
        else if(is_array($donnee_table_libre['valeurs_forcees_creation_extranet']))
            $donnee_table_libre['valeurs_forcees_creation_extranet'] = json_encode($donnee_table_libre['valeurs_forcees_creation_extranet']);

        $retour = Table_libre_management::verification_affichage($donnee_table_libre);

        if($retour !== true)
             return response()->json(['success' => false,'message' => $retour]);

		$table_libre = Table_libre::where('id',$donnee_table_libre['id']);

        $table_libre_modele = $table_libre->first();

        $table_libre->update($donnee_table_libre);

        if($donnee_table_libre['affichage_dans_liste'] !== $table_libre_modele->affichage_dans_liste)
            DB::select('UPDATE `' . $donnee_table_libre['type_element'] . '` SET `chaine_affichage` = null');

		if(!empty($donnee_table_libre['envoyer_email']) && empty($table_libre_modele->envoyer_email)){

			$liste_libres = Liste_libre::where('type_element', $table_libre_modele->type_element)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }

			Cache_management::generation_module('emails.parametrage_email');
		}

		Cache_management::partage_oublie_table($donnee_table_libre['type_element']);
		Cache_management::vider();

		Table_libre_management::generer_fichier_migration( (empty($formulaire->table_libre['nom_table_sql']) ? $formulaire->table_libre['type_element'] : $formulaire->table_libre['nom_table_sql']) );

		return response()->json(['success' => true]);
	}

	/**
	 *
	 * Changement d'etat sur une table libre
	 *
	 */
    public function changement_etat(Request $formulaire, $id_table, $nom_sql) {

		if($formulaire->valeur == 1) {

			$valeur_contraire = 0;
		}
		else {

			$valeur_contraire = 1;
		}

		if (is_numeric($id_table)) {

			$table_libre = Table_libre::where('id', $id_table)->first();
			$type_element = $table_libre->type_element;
		}

		else{

			$type_element = $id_table;
			$table_libre = Table_libre::where('type_element', $type_element)->first();
		}

		$retour = Table_libre_management::changement_etat($type_element,$nom_sql,$valeur_contraire,$formulaire->parametre);

		$table_libre = Table_libre::where('type_element', $type_element)->first();

		Cache_management::partage_oublie_table($type_element);
		Cache_management::vider();

		return json_encode(array('retour' => $retour, 'table_libre' => $table_libre));
    }

    /**
	 *
	 * Vide les chaines d'affichage d'une table
	 *
	 */
    public function vider_chaine_affichage($type_element) {

    	DB::select('UPDATE `' . $type_element . '` SET `chaine_affichage` = null');

    	return response()->json(['retour' => true]);
    }

}
