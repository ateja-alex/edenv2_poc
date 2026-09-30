<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Traduction;
use App\Eden\Variables;

use App\Eden\Managements\Listes_management;

use DB;
use Illuminate\Support\Facades\Log;
use TypeError;
use ZipArchive;

class Element_controller extends Controller {

	/**
	 *
	 * Enregistre les données d'un élément (qui doit déjà exister)
	 *
	 * Route: /eden/element/{type_element}/{id}/enregistrer
	 *
	 */
    public function enregistrer(Request $formulaire, $type_element, $id_element) {
        
		// on va chercher l'élément
		$management = management($type_element, $id_element);

        if(isset($management->modele->inactif) && $management->modele->inactif === 1)
            return response()->json(array('retour' => traduction('messages.php.erreur.enregistrement_element_supprime')));

		$donnees_recues = $formulaire->all();

		unset($donnees_recues["id"]);

		$retour = $management->enregistre($donnees_recues);

        $champs_obligatoires = array();

        if($retour !== true)
            $champs_obligatoires = $management->retourne_champs_obligatoires();

        $management->charge_valeurs_champs_multiselection();

		return response()->json(array(

			'retour' => $retour,
			'element' => $management->modele,
			'champs_obligatoires' => $champs_obligatoires,
			'changement_enregistrement' => $management->changement_enregistrement,
		));
    }

    /**
	 *
	 * Teste l'enregistre des données d'un élément
	 *
	 * Route: /eden/element/{type_element}/{id}/test_enregistrement
	 *
	 */
    public function test_enregistrement(Request $formulaire, $type_element, $id_element) {

		// on va chercher l'élément
		$management = management($type_element, $id_element);

		$donnees_recues = $formulaire->all();

		unset($donnees_recues["id"]);

		$retour = $management->test_enregistre($donnees_recues);

        if($retour == 'test_ok')
            $retour = true;

		return response()->json(array(
			'retour' => $retour,
		));
    }

    /**
     *
     * Modifier en masse des éléments
     *
     **/
    public function modifier_en_masse(Request $formulaire) {
    	$data = $formulaire->all();

    	$ids_element = $data['parametres']['ids_elements'];
    	$type_element = $data['parametres']['type_element'];
        $modifications_en_masse = $data['form'];

        //Gestion des vues
        $type_element= service('vue_sql')->recupere_type_element($type_element);
        $elements = modele($type_element)->whereIn('id', $ids_element)->get();

        $ajout_multiple = $data['parametres']['ajouts_multiples'] ?? [];
        $valeur_multiselection = [];

        foreach($ajout_multiple as $champ_multiple){

            $champ_libre = champ_libre_modele($type_element,$champ_multiple);

            $modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);

            $valeur_multiselection[$champ_multiple] = $modele_table_pivot
                                            ->whereIn('cle_locale', $ids_element)
                                            ->get()
                                            ->groupBy('cle_locale')
                                            ->map(function($valeur){
                                                return $valeur->pluck('valeur');
                                            })
                                            ->toArray();
        }

        $succes = 0 ;
    	$erreurs = 0 ;
    	$messages = array();

		$champs_libres = champs_libres($type_element);

    	foreach ($elements as $element) {

    		$management = management($type_element, $element->id, $element);

            $modifications_pour_element = $modifications_en_masse;

			foreach($modifications_pour_element as $nom_champ => $valeur) {

				$valeur_actuelle = $element->{$nom_champ} ?? null;

				try{
					$deltas = json_decode($valeur, true);
				}
				catch(TypeError $e){
					continue;
				}

				if(!is_array($deltas))
					continue;

				$champ_libre = $champs_libres->where('nom_sql', $nom_champ)->first();

				if(in_array($champ_libre->type,[4,5])){
					
					if(empty($valeur_actuelle))
						continue;

					$date = \Carbon\Carbon::parse($valeur_actuelle);
					if ($deltas['annees']  !== 0) $date->addYears($deltas['annees']);
                    if ($deltas['mois']    !== 0) $date->addMonths($deltas['mois']);
                    if ($deltas['jours']   !== 0) $date->addDays($deltas['jours']);
                    if ($deltas['heures']  !== 0) $date->addHours($deltas['heures']);
                    if ($deltas['minutes'] !== 0) $date->addMinutes($deltas['minutes']);
					$modifications_pour_element[$nom_champ] = $champ_libre->type == 4 ? $date->format('Y-m-d') : $date->format('Y-m-d H:i:s');
				}
				else if(in_array($champ_libre->type,[2,3]))
					$modifications_pour_element[$nom_champ] = ($valeur_actuelle ?? 0) + $deltas['delta'];
			}

            foreach($ajout_multiple as $champ_multiple){
				if(!isset($modifications_pour_element[$champ_multiple]))
					continue;

                $modifications_pour_element[$champ_multiple] = array_unique(array_merge(
                    $modifications_pour_element[$champ_multiple],
                    $valeur_multiselection[$champ_multiple][$element->id] ?? []
                ));
            }

            try{
                $retour = $management->enregistre($modifications_pour_element);
            } catch (\Exception $e){

                Log::error('Erreur lors de la modification en masse sur l\'élément ' . $element->id .
                    ' (type_element : ' . $type_element . '). Message d\'erreur : ' .
                    $e->getMessage() .
                    ' Stacktrace :' . $e->getTraceAsString());

                $retour = 'Erreur 500';
            }

    		if ($retour === true) {
    			$succes++;
    		} else {
    			$erreurs++;
    			if (!isset($messages[$retour])) $messages[$retour] = 0;
    			$messages[$retour]++;
    		}
    	}

    	$retour = '' ;
    	foreach ( $messages as $message => $nombre ) {
    		$retour .= '<br>'.$nombre.'x '.$message ;
    	}

    	// Si tout s'est bien passé
    	if ( $erreurs == 0 ) {

    		return json_encode(['success' => true]);

    	// Sinon
    	} else {

    		return json_encode([
    					'success' => false,
    					'type' => ( $succes > 0 ? 'warning' : 'danger' ),
    					'lignes_modifiees' => $succes,
    					'message' => $retour
    				]);
    	}
	}

    /**
     *
     * Créer en masse les tâches sur les éléments
     *
     **/
    public function taches_en_masse(Request $formulaire) {
    	$data = $formulaire->all();

    	$ids_element = $data['parametres']['ids_elements'];

    	$succes = 0 ;
    	$erreurs = 0 ;
    	$messages = array();

    	foreach ( $ids_element as $id_element ) {

    		$management = management($data['parametres']['type_element'], $id_element);
    		$modifications = [
    							'titre' 			=> $data['form']['titre'].' - '.$management->affiche(),
    							'type_tache_todo' 	=> $data['form']['type_tache_todo'],
    							'type_element'	 	=> $data['parametres']['type_element'],
    							'element_id'	 	=> $id_element,
    						];

    		$retour = management('tache')->enregistre($modifications);
    		if($retour === true) {
    			$succes++;
    		} else {
    			$erreurs++;
    			
    			if (!isset($messages[$retour])) $messages[$retour] = 0;
    			$messages[$retour]++;
    		}
    	}


    	$retour = '' ;
    	foreach ( $messages as $message => $nombre ) {
    		$retour .= '<br>'.$nombre.'x '.$message ;
    	}

    	// Si tout s'est bien passé
    	if ( $erreurs == 0 ) {

    		return json_encode(['success' => true, 'lignes_modifiees' => $succes]);

    	// Sinon
    	} else {

    		return json_encode([
    					'success' => false,
    					'type' => ( $succes > 0 ? 'warning' : 'danger' ),
    					'lignes_modifiees' => $succes,
    					'message' => $retour
    				]);
    	}
	}

	/**
	 *
	 *
	 * Supprime en masse des éléments
	 *
	 */
	public function supprimer_en_masse(Request $formulaire) {
		$data = $formulaire->all();

		$ids_element = $data['parametres']['ids_elements'];
    	$type_element = $data['parametres']['type_element'];

        //Gestion des vues
        $type_element= service('vue_sql')->recupere_type_element($type_element);

		$succes = 0 ;
    	$erreurs = 0 ;
    	$messages = array();

    	foreach($ids_element as $id_element) {

			$management = management($type_element, intval($id_element));
			$retour = $management->supprime();


			if ($retour === true) {
    			$succes++;
    		} else {
    			$erreurs++;
    			if (!isset($messages[$retour])) $messages[$retour] = 0;
    			$messages[$retour]++;
    		}
		}

		$retour = '' ;
    	foreach ( $messages as $message => $nombre ) {
    		$retour .= '<br>'.$nombre.'x '.$message ;
    	}

    	// Si tout s'est bien passé
    	if ( $erreurs == 0 ) {

    		return json_encode(['success' => true]);

    	// Sinon
    	} else {

    		return json_encode([
    					'success' => false,
    					'type' => ( $succes > 0 ? 'warning' : 'danger' ),
    					'lignes_modifiees' => $succes,
    					'message' => $retour
    				]);
		}
	}

    /**
     *
     * Retourne les pdf des éléments selectionnés
     *
     */
    public function imprimer_en_masse(Request $formulaire){

        $elements = json_decode($formulaire->ids_elements);
        $modele_de_document = $formulaire->modele_de_document ?? false;
		$champ_source_fichier = $formulaire->champ_source_fichier ?? '';

        $pdf_separe = isset($formulaire->pdf_separe) ? true :false;
        $pdf_utiliser_nom_fichier = isset($formulaire->pdf_utiliser_nom_fichier) ? true : false;
		
        $fichiers_temporaires_a_supprimer = array();

        if(!$pdf_separe) {

            $merger = \PDFMerger::init();

            // on merge tout..
            foreach ($elements as $id_element) {

                $management = management($formulaire->type_element, $id_element);

				if(!empty($champ_source_fichier) && empty($management->modele->$champ_source_fichier))
					continue;
				else if(!empty($champ_source_fichier))
                	$chemin_pdf = 'public/' . $management->modele->$champ_source_fichier;
				else
                	$chemin_pdf = $management->recupere_chemin_pdf(false, $modele_de_document);
                
				$chemin_pdf = service('pdf')->convertit_image_en_pdf_temporaire($chemin_pdf, $fichiers_temporaires_a_supprimer);

                if (!empty($chemin_pdf) && file_exists(storage_path('app/' . $chemin_pdf)))
                    $merger->addPDF(storage_path('app/' . $chemin_pdf));
            }

            $merger->setFileName('impression_'.traduction('tables_libres.'.$formulaire->type_element.'.element_pluriel').'_' . date('Ymd_His').'.pdf');

            $merger->merge();

            $merger->download();
        }
        else{

            $elements_a_retourner = array();

            foreach ($elements as $id_element) {

                $management = management($formulaire->type_element, $id_element);

				if(!empty($champ_source_fichier) && empty($management->modele->$champ_source_fichier))
					continue;
				else if(!empty($champ_source_fichier))
                	$chemin_pdf = 'public/' . $management->modele->$champ_source_fichier;
				else
                	$chemin_pdf = $management->recupere_chemin_pdf(false, $modele_de_document);

				$nom_fichier = $pdf_utiliser_nom_fichier ? $management->modele->$champ_source_fichier : $management->modele->reference_document . '.pdf';

				if (!empty($chemin_pdf) && file_exists(storage_path('app/' . $chemin_pdf)))
					$elements_a_retourner[] = [storage_path('app/'.$chemin_pdf), $nom_fichier];
            }

            // Si il y a plus d'un document, on retourne une fichier zip
            if(count($elements_a_retourner) >= 1) {

                $nom_zip = 'export_en_masse_'.time().'.zip';

                $zip = new ZipArchive();

                if ($zip->open(storage_path('app/public/'.$nom_zip), ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE ) === TRUE) {

                    foreach ($elements_a_retourner as $element_pdf)
                        $zip->addFile($element_pdf[0], $element_pdf[1]);
                    
                }

                $zip->close();

                return response()->download(storage_path('app/public/'.$nom_zip));
            }

            foreach($fichiers_temporaires_a_supprimer as $fichier_temporaire)
                if(file_exists($fichier_temporaire))
                    unlink($fichier_temporaire);
        }

    }

    /**
     * Affiche un document PDF
     *
     * @param STRING $type_element le type element du document
     * @param INT $document_id l'id du document
     *
     * @return Response
     */
    public function afficher_pdf($type_element, $element_id) {

        $formulaire = request()->all();

        $forcer_regeneration = false;

        if(isset($formulaire['regenerer']) && $formulaire['regenerer'] == 1)
            $forcer_regeneration = true;

        $element = management($type_element, $element_id);
        
        $chemin_pdf = $element->recupere_chemin_pdf($forcer_regeneration);

        $headers = ['Content-Disposition' => 'inline; filename="'.str_replace(["\r", "\n"], '', $element->affiche()).'.pdf"'];

        // On retourne une réponse
        return response()->file(storage_path('app/'.$chemin_pdf),$headers);
    }

	/**
	 *
	 * Crée les données d'un nouvel élément
	 *
	 */
    public function creer(Request $formulaire, $type_element) {

        $donnees_recues = $formulaire->all();

		unset($donnees_recues["id"]);

        $mode = $donnees_recues['mode'] ?? null;
        $id_a_dupliquer = $donnees_recues["id_element"] ?? 0;
        $liste_elements_a_dupliquer = $donnees_recues["liste_elements_a_dupliquer"] ?? [];

        if(isset($donnees_recues['mode']))
            unset($donnees_recues['mode']);

        if(isset($donnees_recues['id_element']))
            unset($donnees_recues['id_element']);

        if(isset($donnees_recues['liste_elements_a_dupliquer']))
            unset($donnees_recues['liste_elements_a_dupliquer']);

		// on va chercher le management
		$management = management($type_element);

        $donnees_recues = $management->retouche_donnees_creation_depuis_extranet($donnees_recues);

        $retour = $management->enregistre($donnees_recues);

		$champs_obligatoires = array();

		if($retour === true) {

			$affichage_pour_recherche = management($type_element, $management->modele->id)->affiche();

			if ($mode == 'duplication' && sizeof($liste_elements_a_dupliquer) > 0) {

				// On récupère le nouvel ID
				$id_nouveau = $management->modele->id;

                $elements_a_copier = array_filter(management($type_element)->sous_elements_a_copier_avec_duplication(),function($element) use ($liste_elements_a_dupliquer){
                   return in_array($element['type_element'],$liste_elements_a_dupliquer);
                });

				// On boucle sur les elements à dupliquer
				foreach ($elements_a_copier as $array_clef_hierarchie) {

					if (!isset($array_clef_hierarchie['clef']))
						continue;

                    $clef = $array_clef_hierarchie['clef'];
                    $table = $array_clef_hierarchie['type_element'];

                    $valeurs_duplication = $array_clef_hierarchie['valeurs_duplication'] ?? [];

                    // On va chercher sur cette table, les élements de la liste à dupliquer
                    $modele_table = modele($table)->where($clef, $id_a_dupliquer);


                    if($array_clef_hierarchie['hierarchie']){
                        $hierarchie = $array_clef_hierarchie['hierarchie'];
                        $modele_table->where($hierarchie, null);
                    }

                    $modele_table = $modele_table->get();

                    if (!$modele_table->isEmpty()) {

                        foreach ($modele_table as $ligne_a_dupliquer) {

                            // On boucle sur chaques colonnes et on remplis le tableau $informations_a_dupliquer

                            $informations_a_dupliquer = array();
                            $management_table = management($table);

                            foreach ($ligne_a_dupliquer->getAttributes() as $colonne => $valeur) {

                                if ($colonne == "id" || $colonne == "modifie_par" || $colonne == "modifie_le" || $colonne == "cree_par" || $colonne == "cree_le" || $colonne == "inactif" || $colonne == "chaine_tags_recherche")
                                    continue;

                                // Si colonne == $clef, alors colonne = $id_nouveau
                                if ($colonne == $clef)
                                    $informations_a_dupliquer[$colonne] = $id_nouveau;

                                else
                                    $informations_a_dupliquer[$colonne] = $ligne_a_dupliquer->$colonne;

                            }

                            foreach($valeurs_duplication as $colonne => $valeur){
                                $informations_a_dupliquer[$colonne] = $valeur;
                            }

                            $management_table->enregistre($informations_a_dupliquer);

                            if($array_clef_hierarchie['hierarchie']) {
                                // On vérifie si cette ligne possède un enfant / des enfants
                                $lignes_enfant = modele($table)->where($clef, $id_a_dupliquer)->where($hierarchie, $ligne_a_dupliquer->id)->get();
                                $id_nouvelle_ligne = $management_table->modele->id;

                                // On a un ou des enfants
                                if (!$lignes_enfant->isEmpty()) {

                                    foreach ($lignes_enfant as $ligne) {

                                        $informations_a_dupliquer = array();
                                        $management_table = management($table);

                                        foreach ($ligne->getAttributes() as $colonne => $valeur) {

                                            if ($colonne == "id" || $colonne == "modifie_par" || $colonne == "modifie_le" || $colonne == "cree_par" || $colonne == "cree_le" || $colonne == "inactif" || $colonne == "chaine_tags_recherche")
                                                continue;

                                            // Si colonne == $clef, alors colonne = $id_nouveau
                                            if ($colonne == $clef)
                                                $informations_a_dupliquer[$colonne] = $id_nouveau;
                                            else if ($colonne == $hierarchie)
                                                $informations_a_dupliquer[$colonne] = $id_nouvelle_ligne;
                                            else
                                                $informations_a_dupliquer[$colonne] = $ligne->$colonne;

                                        }

										$management_table->enregistre($informations_a_dupliquer);
                                    }
                                }
                            }
                        }
                    }
				}
			}

			if (isset($mode))
				$management->traitements_supplementaires_duplication($management->modele->id,$id_a_dupliquer,$type_element);
		}
		else {

			$affichage_pour_recherche = '';
			$champs_obligatoires = $management->retourne_champs_obligatoires();

		}

        $lien_vers_element = null;

        if($retour === true)
            $lien_vers_element = $management->lien_vers_element();

        $management->charge_valeurs_champs_multiselection();

		return response()->json(array(

			'retour' => $retour,
			'element' => $management->modele,
			'affichage_pour_recherche' => $affichage_pour_recherche,
			'lien_vers_element' => $lien_vers_element,
			'champs_obligatoires'=> $champs_obligatoires,
		));
    }

    /**
	 *
	 * Teste la création d'un nouvel élément
	 *
	 */
    public function test_creation(Request $formulaire, $type_element){

        $donnees_recues = $formulaire->all();

        // on va chercher le management
        $management = management($type_element);

        $retour = $management->test_enregistre($donnees_recues);

        if($retour == 'test_ok')
            $retour = true;

		return response()->json(array(
			'retour' => $retour,
		));
    }


	/**
	 *
	 * Affiche Le formulaire générique correspondant au type_element
	 *
	 */
    public function afficher_formulaire_creation_rapide($type_element) {

        if(!profil_creation($type_element))
            abort(403);

		$management_element = management($type_element);

		return view('eden::formulaires.generique', [

			'type_element' => $type_element,
			'management_element' => $management_element,
		]);
    }


	/**
	 *
	 * Enregistre le commentaire global sur une fiche
	 *
	 * @param $type_element
	 * @param $id_element
	 *
	 *
	 */
    public function enregistrer_commentaires_fiche(Request $request, $type_element, $id_element) {

        // on va chercher le management
		$management = management($type_element, $id_element);

		$management->enregistre_modele(array('commentaire_fiche' => $request->commentaire_fiche));

		return response()->json(array('retour' => true));
    }

	/**
	 *
	 * Supprime un élément
	 *
	 * @param $type_element
	 * @param $id_element
	 *
	 * return void : true si tout s'est bien passé, un string (message d'erreur) si il y a une erreur
	 *
	 */
    public function supprimer($type_element, $id_element) {
        
        // on va chercher le management
		$management = management($type_element, $id_element);

        if(isset($management->modele->inactif) && $management->modele->inactif === 1)
            return response()->json(array('retour' => traduction('messages.php.erreur.suppression_element_supprime')));

		$retour = $management->supprime();

		return response()->json(array('retour' => $retour));
    }

	/**
     *
     * Permet d'rétablir un element supprimé
     *
     */
    public function retablir($type_element, $element_id) {

		// On annule la suppression
        $retour = management($type_element, $element_id)->annule_suppression();

        return json_encode(array('retour' => $retour));

    }

	/**
	 *
	 * On génère la facture pour une intervention de maintenance
	 *
	 */
    public function generer_facture_maintenance_intervention($type_element, $id_element) {

        // on va chercher le management
		$management = management('maintenance_intervention', $id_element);

		$retour = $management->generer_facture();

		return response()->json(array('retour' => $retour));
    }

	/**
	 *
	 * Récupère les données d'un paramètre
	 *
	 */
    public function recuperer($type_element, $id) {

		// on va chercher l'élément
		$management = management($type_element, $id);

        $management->charge_valeurs_champs_multiselection();

		if(!empty($management->modele)) {
            $management->modele->affiche_lien = $management->affiche_lien();
            $management->modele->affiche_lien_pour_select = $management->affiche_lien_pour_select();
        }

        $management->retraite_modele_recuperation();

		$champs_libres = champs_libres_management(champs_libres($type_element));

		$affichages = [];

		foreach($champs_libres as $champ_libre){

			$affichages[$champ_libre->modele->nom_sql] = $champ_libre->affiche($management->modele->{$champ_libre->modele->nom_sql});
		}

		$management->modele->affichages = $affichages;

		return response()->json($management->modele);
    }

    /**
	 *
	 * Récupère les données de plusieurs lignes
	 *
	 */
    public function recuperer_elements(Request $request,$type_element) {

        $elements = modele($type_element);

        if(isset($request->elements_id))
            $elements = $elements->whereIn('id', $request->elements_id);

        $filtrage = $request->filtrage ?? false;

        $management = management($type_element);

        $elements = $elements->where(function($query) use($management, $filtrage){
            return $management->conditions_specifiques_recherche($query, $filtrage);
        });
        
        $elements = $elements->get();

        foreach($elements as $element) {

            // on va chercher l'élément
            $management = management($type_element, $element->id, $element);

            $management->charge_valeurs_champs_multiselection();
            $management->modele->affiche_lien = $management->affiche_lien();
            $management->modele->affiche_lien_pour_select = $management->affiche_lien_pour_select();
            $management->modele->affichage_pour_recherche = $management->affichage_pour_select();
        }

		return response()->json($elements);
    }

	/**
	 *
	 * Récupère une liste d'éléments via un terme recherché
	 *
	 */
    public function rechercher(Request $formulaire,$type_element, $recherche) {

		$formulaire = $formulaire->all();

        $management = management($type_element);

		$recherches = explode(' ', $recherche);

        $nombre_elements = 100;

        if(!empty($formulaire['nombre_elements']))
            $nombre_elements = $formulaire['nombre_elements'];

        $elements = modele($type_element);

        if(!empty(moi_extranet()))
            $elements->avec_filtre_extranet();

        $filtrage = $formulaire['filtrage'] ?? false;

		$elements = $elements->where(function($query) use($management, $filtrage){
			return $management->conditions_specifiques_recherche($query, $filtrage);
		});

		if(!empty($formulaire['source']['type_element'] ?? '')){
			$type_element_source = $formulaire['source']['type_element'];
			$nom_sql_source = $formulaire['source']['nom_sql'];

			$recherche_avancee = modele('recherche_avancee')
				->where('type', 'champs_libres.'.$type_element_source.'.'.$nom_sql_source)
				->where('id_cible',$type_element)
				->first();

			if($recherche_avancee != null){

				$management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);

				$structure = $management_recherche_avancee->structure();

				$structure = $management_recherche_avancee->remplacement_lien_champ(
					$structure,
					$formulaire['source']['modele'] ?? []
				);

				$management_recherche_avancee->applique_filtrage(
					$structure,
					$elements,
					$type_element
				);
			}
		}

        if(!empty($formulaire['ids_a_eviter']))
             $elements = $elements->whereNotIn('id', $formulaire['ids_a_eviter']);

		// on va chercher l'élément
		if(empty($formulaire['sans_limite']))
			$elements = $elements->take($nombre_elements);

        $select = $type_element.'.*';

        if(!empty($recherches) && (count($recherches) > 1 || $recherches[0] != '%') ) {

            $select.= ",(
                cast(".$type_element.".chaine_tags_recherche LIKE '%" . $recherche . "%' as int) + 
                cast(".$type_element.".chaine_tags_recherche LIKE '%#" . $recherche . "%' as int) + 
                cast(".$type_element.".chaine_tags_recherche LIKE '%" . $recherche . "#%' as int) + 
                cast(".$type_element.".chaine_tags_recherche LIKE '%#" . $recherche . "#%' as int))
                as ordre_chaine_complete,(";

            foreach ($recherches as $index => $recherche_tmp) {

                if($index > 0)
                    $select.= '+';

                $select .="MATCH (".$type_element.".chaine_tags_recherche) AGAINST ('" . $recherche_tmp . "')";

                $elements->where($type_element.".chaine_tags_recherche", 'like', '%' . $recherche_tmp . '%');
            }

            $select.=')/'.sizeof($recherches).' as ordre';

            $elements->orderBy('ordre_chaine_complete','DESC')
                ->orderBy('ordre','DESC');
        }
        else
            $elements->orderBy($type_element.".chaine_tags_recherche");
		
        $elements->select(DB::raw($select));

		$elements = $elements->groupBy($type_element.'.id')->get();

        $donnees_pour_recherche = [];

		foreach($elements as $element) {

            $management = management($type_element, $element->id, $element);

            $management->charge_valeurs_champs_multiselection();

			/**
			@note frédéric : plutot que d'utiliser un strip_tags(), il faudrait pouvoir utiliser le html dans les typehead
			*/
			$donnees_pour_recherche[] = array(
                'id' => $element->id,
                'affichage_pour_recherche' => $management->affichage_pour_select(),
                'ordre' => isset($element->ordre) ? $element->ordre : 1,
                'ordre_chaine_complete' => isset($element->ordre_chaine_complete) ? $element->ordre_chaine_complete : 1,
				'modele' => $element,
            );
		}

        usort($donnees_pour_recherche, function($a, $b){

            if($a['ordre_chaine_complete'] == $b['ordre_chaine_complete']){

                if($a['ordre'] == $b['ordre'])
                    return $a['affichage_pour_recherche'] > $b['affichage_pour_recherche'] ? 1 : -1;

                return $a['ordre'] > $b['ordre'] ? -1 : 1;
            }

            return $a['ordre_chaine_complete'] > $b['ordre_chaine_complete'] ? -1 : 1;
        });


		return response()->json($donnees_pour_recherche);
    }

	/**
	 *
	 * Affiche les logs de n'importe quel élément
	 *
	 */
    public function afficher_logs($type_element, $id) {

		// a priori l'utilisateur n'a pas accès à l'élément
		if(modele($type_element, $id) === null)
			exit;

		$management = management($type_element, $id);

        // On va chercher l'ensemble de l'historique
        $historique = $management->historique();

		foreach($historique as $evenement) {

			if(empty($evenement->id_utilisateur)) {

				$evenement->id_utilisateur = "Utilisateur inconnu";
			}
			else {

				$utilisateur = modele('utilisateur', $evenement->id_utilisateur);

				$evenement->id_utilisateur = $utilisateur->prenom.' '.$utilisateur->nom.' ('.$evenement->id_utilisateur.')';
			}

			$evenement->type_action_affichage = traduction(Variables::$historique_intitule[$evenement->type_action][1]);
		}

		// on va chercher les utilisateurs et les droits d'accès
		$utilisateurs = modele('utilisateur')->get();

		$droits_acces = array();

		foreach($utilisateurs as $utilisateur) {

			if(isset($management->modele->{'utilisateur_'.$utilisateur->id})) {

				if($management->modele->{'utilisateur_'.$utilisateur->id} == 1) {

					$droits_acces[$utilisateur->prenom.' '.$utilisateur->nom] = true;
				}
				else {

					$droits_acces[$utilisateur->prenom.' '.$utilisateur->nom] = false;
				}
			}
			else {

				$droits_acces[$utilisateur->prenom.' '.$utilisateur->nom] = '?';
			}
		}

		return view('eden::logs_element', [
			'management' => $management,
            'historique' => $historique,
            'droits_acces' => $droits_acces,

		]);
    }

	/**
	 *
	 * Récupère les traductions
	 *
	 */
	public function recupere_traductions($type_element, $element_id, $langue) {

		$donnees_traduites = array();

		// on va chercher les champs libres multilingues
		$champs_libres = Champ_libre::where('type_element', $type_element)->where('multilingue', 1)->get();

		if(empty($champs_libres))
			return response()->json(true);

		foreach($champs_libres as $champ_libre) {

			$traduction = Traduction::where('type_element', $type_element)->where('nom_sql', $champ_libre->nom_sql)->where('element_id', $element_id)->where('langue_id', $langue)->first();

			if(!empty($traduction))
				$donnees_traduites[$champ_libre->nom_sql] = $traduction->traduction;
			else
				$donnees_traduites[$champ_libre->nom_sql] = '';
		}

		return response()->json(array('retour' => true, 'donnees_traduites' => $donnees_traduites));
	}

	/**
	 *
	 * Enregistre les traductions
	 *
	 */
	public function enregistre_traductions(Request $formulaire, $type_element, $element_id, $langue) {

		$donnees_traduites = array();

		// on va chercher les champs libres multilingues
		$champs_libres = Champ_libre::where('type_element', $type_element)->where('multilingue', 1)->get();

		if(empty($champs_libres))
			return response()->json(true);

		foreach($champs_libres as $champ_libre) {

			$traduction = Traduction::where('type_element', $type_element)->where('nom_sql', $champ_libre->nom_sql)->where('element_id', $element_id)->where('langue_id', $langue)->first();

			if(empty($traduction)) {

				$traduction = new Traduction;
				$traduction->type_element = $type_element;
				$traduction->element_id = $element_id;
				$traduction->langue_id = $langue;
				$traduction->nom_sql = $champ_libre->nom_sql;
			}

			if(!empty($formulaire->get($champ_libre->nom_sql)))
				$traduction->traduction = $formulaire->get($champ_libre->nom_sql);
			else
				$traduction->traduction = '';

			$traduction->save();
		}

		return response()->json(array('retour' => true));
	}

	/*
	@note frédéric 04/10/2019 : je soupçonne que ce code ne soit pas utilisé, je commente pour le moment
	public function nouveau_element($type_element){

		$management_element = management($type_element);

		if(view()->exists("eden::formulaires.$type_element"))

			return view("eden::formulaires.$type_element", [
				'management_element' => $management_element,
			]);

		else

			return view("eden::formulaires.formulaire_generique", [
				'management_element' => $management_element,
			]);


	}
	*/


	/**
	 *
	 * Modification de l'etat d'ub kanban
	 *
	 */
    public function changer_etat_kanban(Request $formulaire) {

		$donnees_recues = $formulaire->all();

		// // on va chercher l'élément
		$management = management($donnees_recues["type_element"], $donnees_recues["id_element"]);
		$kanban = $donnees_recues["kanban"];
		$management->modele->$kanban = $donnees_recues["id_liste_kanaban_finale"];
		$management->enregistre();

		return response()->json(array(

			'retour' => true,
		));
	}

	/**
	 *
	 * Modification de l'etat de l'ordre dans une colonne kanban
	 *
	 */
    public function ordre_dans_colonne_kanban() {
		
		$nouvelles_positions = collect(request()->nouvel_ordre)->pluck('id_element')->toArray();
		
		// on doit enregistrer des modifications d'ordre que si l'élément déplacé est dans la colonne actuellement traitée
		if(!in_array(request()->id_element, $nouvelles_positions))
			return;
		
		$liste_libre = Liste_libre::find(request()->id_liste);
		
		// on va chercher l'ordre existant
		$ordre_existant = modele('ordre_dans_kanban')->where('id_rapport', $liste_libre->id_rapport)->whereIn('element_id', $nouvelles_positions)->orderBy('ordre')->get()->pluck('element_id', 'ordre')->toArray();
		
		// on va créer l'ordre pour les éléments manquants dans la liste
		$valeur_actuelle = false;
		
		foreach($nouvelles_positions as $id_element) {
			
			if(!in_array($id_element, $ordre_existant)) {
				
				// on va chercher le max de l'ordre
				if($valeur_actuelle === false) {
					
					$valeur_actuelle = modele('ordre_dans_kanban')->where('id_rapport', $liste_libre->id_rapport)->max('ordre');
					
					if(empty($valeur_actuelle)) {
						
						$valeur_actuelle = 0;
					}
					else {
						
						$valeur_actuelle++;
					}
					
				}
				
				// on doit l'insérer en BDD pour l'initialisation (quand la bdd est vide)
				// sinon on passe dans la condition du return ci dessous et l'ordre n'est jamais inséré
				$infos = array(
					'element_id' => $id_element,
					'ordre' => $valeur_actuelle,
					'id_rapport' => $liste_libre->id_rapport,
				);
				
				management('ordre_dans_kanban')->enregistre($infos);
				
				$ordre_existant[$valeur_actuelle] = $id_element;
				
				$valeur_actuelle++;
			}
		}
		
		// on doit regarder si l'élément a été déplacé vers le haut ou vers le bas.
		// on va déterminer sa position actuelle
		$position_actuelle = 0;

		foreach($ordre_existant as $id_element) {
			
			if($id_element == request()->id_element) {
				
				break;
			}
			
			$position_actuelle++;
		}
		
		// on va déterminer sa nouvelle position
		$nouvelle_position = 0;
		
		foreach($nouvelles_positions as $id_element) {
			
			if($id_element == request()->id_element) {
				
				break;
			}
			
			$nouvelle_position++;
		}
		
		
		// rien à faire
		if($nouvelle_position == $position_actuelle)			
			return;

		// on l'a déplacé vers le bas,
		// on va prendre le précédent, et on va faire +1
		if($nouvelle_position > $position_actuelle) {
			
			// on a l'ordre du précédent, qui est le Xeme index du tableau $ordre_existant
			// on va donc chercher son ordre réel mtn
			$array_slice = array_slice($ordre_existant, $nouvelle_position, 1, true);
			
			if(!empty($array_slice))
				$ordre_reel_precedent = array_keys($array_slice)[0];
			else {
				
				// on  doit prendre le dernier et faire +1
				$dernier_element = array_slice($ordre_existant, -1, 1, true);
				
				$ordre_reel_precedent = array_keys($dernier_element)[0];
			}
			
			$nouvel_ordre_element = $ordre_reel_precedent + 1;
			
			// on modifie $ordre existant en conséquence
			$ordre_existant_temporaire = array();
			
			foreach($ordre_existant as $ordre => $id_element) {
				
				if($ordre >= $nouvel_ordre_element) {
					
					$ordre_existant_temporaire[$ordre+1] = $id_element;
				}
				else {
					
					$ordre_existant_temporaire[$ordre] = $id_element;
				}
			}
			
			$ordre_existant = $ordre_existant_temporaire;
		}
		
		// on l'a déplacé vers le haut,
		// on va prendre le suivant, et on va faire -1
		if($nouvelle_position < $position_actuelle) {
			
			
			// il faut récupérer l'ordre actuel de l'élément #$nouvelle_position du tableau $ordre_existant
			$ordre_reel_precedent = array_keys(array_slice($ordre_existant, $nouvelle_position, 1, true))[0];
			
			$nouvel_ordre_element = $ordre_reel_precedent - 1;
			
			// on modifie $ordre existant en conséquence
			foreach($ordre_existant as $ordre => $id_element) {
				
				if($ordre <= $nouvel_ordre_element) {
					
					$ordre_existant[$ordre - 1] = $id_element;
					
					unset($ordre_existant[$ordre]);
				}
			}
		}
		
		if(!empty($ordre_existant)) {
			
			$ordre_a_enregistrer = $ordre_existant;
			
			// on va recréer le tableau
			foreach($ordre_existant as $ordre => $id_element) {
				
				if($id_element != request()->id_element)
					continue;
					
				unset($ordre_a_enregistrer[$ordre]);
				
				$ordre_a_enregistrer[$nouvel_ordre_element] = request()->id_element;
				break;
			}
		}
		else {
			
			$ordre_a_enregistrer = $nouvelles_positions;
		}
		
		modele('ordre_dans_kanban')->whereIn('element_id', $nouvelles_positions)->where('id_rapport', $liste_libre->id_rapport)->delete();
		
		if(is_array($ordre_a_enregistrer)) {
			
			foreach($ordre_a_enregistrer as $ordre => $id_element) {
				
				$infos = array(
					'element_id' => $id_element,
					'ordre' => $ordre,
					'id_rapport' => $liste_libre->id_rapport,
				);
				
				management('ordre_dans_kanban')->enregistre($infos);
			}
		}
		
	}

	/**
	 *
	 * Modification de l'etat d'un kanban
	 *
	 */
    public function changer_ordre_kanban($type_element, $id_element, $position) {

		// On attends pour laisser le temps à l'autre requete de se terminer, si on a changé de colonne.
		sleep(1);

		$fin_item   = modele($type_element, request()->fin_item);
		$debut_item = modele($type_element, request()->debut_item);

		// Si on monte un élément
		if(request()->sens == 'asc') {

			$elements = modele($type_element)
							->where(request()->kanban, request()->id_colonne)
							->where('ordre_kanban', '>=', $fin_item->ordre_kanban)
							->where('ordre_kanban', '<=', $debut_item->ordre_kanban)
							->orderBy('ordre_kanban')
							->get() ;


			foreach($elements as $element) {

				// Si on remonte un ticket
				if($element->id == request()->debut_item) {

					//management($type_element, request()->debut_item)->enregistre_modele(['ordre_kanban' => $ordre]);
					management($type_element, $element->id)->enregistre_modele(['ordre_kanban' => $fin_item->ordre_kanban]);
					//echo($element->id.'=>'. ($fin_item->ordre_kanban)."\n");
				} else {

					management($type_element, $element->id)->enregistre_modele(['ordre_kanban' => $element->ordre_kanban+1]);
					//echo($element->id.'=>'. ($element->ordre_kanban+1)."\n");
				}
			}

		// Si on descends un élément
		} else {

			$elements = modele($type_element)
							->where(request()->kanban, request()->id_colonne)
							->where('ordre_kanban', '>=', $debut_item->ordre_kanban)
							->where('ordre_kanban', '<=', $fin_item->ordre_kanban)
							->orderBy('ordre_kanban')
							->get();

			foreach($elements as $element) {

				// Si on remonte un ticket
				if($element->id == request()->debut_item) {

					management($type_element, $element->id)->enregistre_modele(['ordre_kanban' => $fin_item->ordre_kanban]);
					//echo('!'.$element->id.'=>'. ($fin_item->ordre_kanban)."\n");
				} else {

					management($type_element, $element->id)->enregistre_modele(['ordre_kanban' => $element->ordre_kanban-1]);
					//echo($element->id.'=>'. ($element->ordre_kanban-1)."\n");
				}
			}
		}

		return response()->json(array(

			'retour' => true,
		));
	}

	/**
	 *
	 * Retourne résultat en fonction des paramètres
	 *
	 */
	public function rechercher_avec_requete($type_element, Request $formulaire) {

		$parametres = $formulaire->all()['donnees'];

		$champ_libre = Champ_libre::where('type_element',$type_element)->where('nom_sql',$parametres['nom_sql'])->first();

		if($champ_libre == null)
		    return array();
		
		if($champ_libre->type == 10) {

            $modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);
			
			$ids = $modele_table_pivot->where('cle_locale', $parametres['valeur'])->get()->pluck('valeur')->toArray();
			
			$retour = modele($type_element)->whereIn('id', $ids)->get();
		}
		else {
			
			$retour = modele($type_element)->where($parametres['nom_sql'], $parametres['valeur'])->get();
		}


		$elements = array();

		foreach($retour as $element) {

			$element->affichage_element = management($type_element, $element->id)->affichage_pour_select();

			$elements[] = $element;
		}

		// on trie éléments
		usort($elements, function($a, $b) {

            if(isset($a->ordre))
                return $a->ordre <=> $b->ordre;

			if($a->affichage_element >= $b->affichage_element)
				return 1;

			return -1;
		});

        return response()->json(['succes' => true, 'retour' => $elements]);
	}

	/**
	 *
	 * Récupère les détails d'une ligne pour une liste
	 *
	 */
	public function recuperer_details_ligne_pour_liste(Request $formulaire) {
		// Cas particulier des approbations ( multi type )
		if ($formulaire->type_element == "approbation")
			$retour_management = management('approbation')->recuperer_details_ligne_pour_liste($formulaire->id_element,$formulaire->type_element,$formulaire->type_element_secondaire);

		else
			$retour_management = management($formulaire->type_element)->recuperer_details_ligne_pour_liste($formulaire->id_element,$formulaire->type_element,$formulaire->id_liste_parent);

		return response()->json(['succes' => true, 'retour' => $retour_management]);
	}

	/**
	 *
	 * Donne l'approbation (acceptation d'approbation)
	 *
	 */
	public function valider_action(Request $formulaire) {

		return management($formulaire->type_element)->valider_action($formulaire);
	}

	/**
	 *
	 * Refuser l'approbation
	 *
	 */
	public function refuser_action(Request $formulaire) {

		return response()->json(['succes' => management($formulaire->type_element)->refuser_action($formulaire)]);
	}

	/**
	 *
	 * Demander une approbation manuelle sur un element
	 *
	 */
	public function demander_approbation_manuelle($type_element, $id_element) {

		$management = management($type_element, $id_element);

		// on regarde s'il y a un workflow de demande d'approbation
		$workflow_approbation = modele('approbation_workflow')->where('type_element', $type_element)->where('action', 3)->first();

		if($workflow_approbation === null)
			return redirect()->back()->withErrors(traduction('messages.php.element.workflow_approbation_non_prevu'));

		// on regarde si l'approbation a déjà été faite
		$approbation_existante = modele('approbation')
									->where('type_element', $type_element)
									->where('element_id', $id_element)
									->where('action', 3)
									->first();

		if($approbation_existante !== null && !$management->verification_document_approbation_refuse())
			return redirect()->back()->withErrors(traduction('messages.php.element.approbation_deja_faite'));

		// on va chercher le destinataire de l'approbation
		$destinataires_ids = $management->recupere_destinataire_approbation();

		if(empty($destinataires_ids))
			return redirect()->back()->withErrors(traduction('messages.php.element.approbation_destinataire_introuvable'));

		foreach($destinataires_ids as $destinataire_id){
			
			// on crée l'approbation
			$approbation_management = management('approbation');

			$infos = array(

				'utilisateur_id' => moi()->id,
				'demandeur_initial' => moi()->id,
				'destinataire_id' => $destinataire_id,
				'type_element' => $type_element,
				'element_id' => $id_element,
				'action' => 3,
			);

			$retour_verification = $management->verifications_si_approbation_possible();
			if($retour_verification !== true)
				return redirect()->back()->withErrors($retour_verification);


			// On permet de surcharger les infos pour les cas spéciaux
			$infos = $management->verifications_informations_approbation($infos);

			$approbation_management->enregistre($infos);
		}

		// ok on revient sur la page précédente
		return redirect()->back();
	}


    /**
     *
     * Valider des elements en masse
     *
     */
    public function valider_en_masse(Request $formulaire) {

        $elements = $formulaire->ids;

        //Gestion des vues
        $type_element = service('vue_sql')->recupere_type_element($formulaire->type_element);

        $retours = array();

        foreach($elements as $id_element) {

            $management = management($type_element, $id_element);
            $retour = $management->enregistre(['valide' => 1]);

            if($retour !== true)
                $retours[] = $retour;
        }

        if(!empty($retours))
            return response()->json(array('retour' => false,'message' => implode("\n",$retours)));

        return response()->json(array(true, $retours));
    }

    /**
     *
     * Permet de récupérer le modele par défaut
     *
     */
    public function modele_par_defaut($type_element){

        return response()->json(modele_par_defaut($type_element));
    }

    /**
     *
     * Permet de récupérer le modele par défaut
     *
     */
    public function recuperer_tous_les_elements_ajax($type_element){

        $ordre = "id";

        if(request()->has('ordre'))
            $ordre = request()->get('ordre');

        $elements = modele($type_element)->orderBy($ordre)->get();

        if(request()->has('valeurs_champs_multiselection') && request()->get('valeurs_champs_multiselection')) {

            $elements_avec_valeurs = collect();
            $champs_libres_multi_selection = champs_libres_multiselection($type_element);
            $valeurs_multiselection = array();

            // on va chercher les valeurs pour les multi sélections
            foreach($champs_libres_multi_selection as $champ_libre) {

                $modele_table_pivot = table_libre_existe($champ_libre->table_pivot) ? modele($champ_libre->table_pivot) : \DB::table($champ_libre->table_pivot);

                $valeurs_multiselection[$champ_libre->nom_sql] = $modele_table_pivot->get()
                    ->groupBy('cle_locale')->map(function($valeurs){
                        return $valeurs->pluck('valeur');
                    })->toArray();
            }

            foreach($elements as $element){

                foreach($champs_libres_multi_selection as $champ_libre) {

                    if(isset($valeurs_multiselection[$champ_libre->nom_sql][$element->id]))
                        $element->{$champ_libre->nom_sql} = $valeurs_multiselection[$champ_libre->nom_sql][$element->id];
                }

                $elements_avec_valeurs->push($element);
            }
            
            $elements = $elements_avec_valeurs;
        }

        return response()->json($elements);
    }

    /**
     *
     * Permet de récupérer les valeurs reliées à un élément de manière récursif avec en paramétre les types de champs à rechercher
     *
     */
    public function valeurs_champs_relies(Request $request,$type_element,$element_id){

        $parametres_champ = $request->all();

        $valeurs_champs_relies = management($type_element,$element_id)->valeurs_champs_relies($parametres_champ);

        return response()->json($valeurs_champs_relies);
    }

    /*
     *
     * Convertit un élément en autre type_element à partir données reçues dans la requête
     *
     */
    public function convertir(Request $requete){

        $donnees = $requete->all();
        $mappage_table = modele('mappage_table_conversion')->where('id', $donnees['mappage_table_id'])->first();

        $retour = management($donnees['type_element_depart'], $donnees['element_id'])->convertir($donnees,$mappage_table);

        if(gettype($retour) === 'array')
            return response()->json(['succes' => true, 'donnees' => $retour]);
        else
            return response()->json(['succes' => false, 'message' => $retour]);
    }

	/**
	 * 
	 * Permet de récupérer le modéle des éléments notamment pour l'affichage des valeurs
	 * 
	 */
	public function affichage_elements(Request $request) {

		$elements_par_type = [];

		foreach($request->all()['donnees'] as $element){

			if(!isset($elements_par_type[$element['type_element']]))
				$elements_par_type[$element['type_element']] = [];

			$elements_par_type[$element['type_element']][] = $element['element_id'];
		}

		$retour = [];

		foreach($elements_par_type as $type_element => $elements_ids){

			$elements = modele($type_element)->whereIn('id', array_unique($elements_ids))->groupBy('id')->get();

			$champs_libres = champs_libres_management(champs_libres($type_element));

			$valeurs_multiselection = [];

			foreach($champs_libres as $champ_libre){

				if($champ_libre->modele->type != 10)
					continue;

				$modele_table_pivot = table_libre_existe($champ_libre->modele->table_pivot) ? modele($champ_libre->modele->table_pivot) : \DB::table($champ_libre->modele->table_pivot);

				$valeurs_multiselection[$champ_libre->modele->nom_sql] = $modele_table_pivot
                                            ->whereIn('cle_locale', array_unique($elements_ids))
                                            ->get()
											->groupBy('cle_locale')->map(function($valeurs){
												return $valeurs->pluck('valeur');
											})->toArray();

			}

			foreach($elements as $element){

				foreach($champs_libres as $champ_libre){

					if($champ_libre->modele->type != 10)
						continue;

					$element->{$champ_libre->modele->nom_sql} = $valeurs_multiselection[$champ_libre->modele->nom_sql][$element->id] ?? [];
				}

				$management = management($type_element, $element->id, $element);

				if(!empty($management->modele)) {
					$management->modele->affiche_lien = $management->affiche_lien();
					$management->modele->affiche_lien_pour_select = $management->affiche_lien_pour_select();
				}

        		$management->retraite_modele_recuperation();

				$affichages = [];

				foreach($champs_libres as $champ_libre){						
					$affichages[$champ_libre->modele->nom_sql] = $champ_libre->affiche($management->modele->{$champ_libre->modele->nom_sql});
				}

				$management->modele->affichages = $affichages;

				$retour[$type_element.'_'.$element->id] = $management->modele;
			}
		}

		return response()->json($retour);
	}
}
