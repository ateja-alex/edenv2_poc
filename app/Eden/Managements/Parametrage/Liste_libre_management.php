<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Rapport_libre;
use App\Eden\Managements\Parametrage\Champ_libre_management;

class Liste_libre_management {

    /**
     *
     * Permet d'enregistrer une nouvelle liste libre
     *
     */
    public function enregistrer_nouvelle_liste($parametres)
    {
        $liste = new Liste_libre;

        $type_liste = $parametres['type_liste'];

        $liste->type_element = $parametres['type_element'];

        if (!empty($type_element))
            $liste->type_element = $type_element;

        if ($type_liste == 'export' || $type_liste == 'fiche') {

            if (empty($parametres['titre']))
                return array('retour' => traduction('messages.php.liste_libres.titre_vide'));

            $nouveau_rapport = new Rapport_libre();

            if ($type_liste == 'fiche') {

                if (empty($parametres['cle_etrangere']))
                    return array('retour' => traduction('messages.php.liste_libres.cle_etrangere_vide'));

                $liste->fiche = $parametres['fiche'];
                $liste->cle_etrangere = $parametres['cle_etrangere'];
                $liste->cle_primaire = $parametres['cle_primaire'];

                if(!empty($parametres['type_element_primaire']))
                    $liste->type_element_primaire = $parametres['type_element_primaire'];

                $id_rapport = 'fiche_' . $liste->fiche . '_' . $liste->type_element;
                $nouveau_rapport->liste_sur_fiche = 1;
            } else {
                $nouveau_rapport->export = 1;
                $liste->export = 1;
                $id_rapport = 'export_' . $liste->type_element;
            }

            $titre = $parametres['titre'];

            $id_rapports_similaires = Rapport_libre::where('id_rapport', 'like', $id_rapport . '%')->get()->pluck('id_rapport')->toArray();

            $id_rapport_inexistant = $id_rapport;

            $compteur = 1;

            while (in_array($id_rapport_inexistant, $id_rapports_similaires)) {

                $id_rapport_inexistant = $id_rapport . '_' . $compteur;
                $compteur++;
            }

            $id_rapport = $id_rapport_inexistant;

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
        }

        $liste->save();

        if ($liste->export == 1) {
            $liste_libres = Liste_libre::where('type_element', $liste->type_element)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }
        } else
            Cache_management::generation_liste_libre($liste->id);

        // On génère la migration spécifique
        Liste_libre_management::generer_fichier_migration_liste_libre($liste->id);

        return array('retour' => true, 'liste_id' => $liste->id);
    }

	/**
	 * 
	 * Récupère la liste des colonnes d'une liste libre
	 * 
	 */
	public static function recuperer_liste_colonnes($liste_libre_id) {
		
		$colonnes = Colonne::where('liste_libre_id', $liste_libre_id)->orderBy('ordre')->get();
		
		// on retouche les colonnes pour la rétrocompatibilité
		foreach($colonnes as $colonne) {

			if(empty($colonne->type) && !empty($colonne->methode)) {
				
				$colonne->type = 'methode';
			}
			elseif(empty($colonne->type)) {
				
				$colonne->type = 'standard';
			}
		}
		
		return $colonnes;
	}
	
	/**
	 * 
	 * Génère le nouveau fichier de migration pour les listes libres
	 * 
	 */
	public static function generer_fichier_migration_liste_libre($liste_libre_id, $forcer_generation = false) {

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if(defined('migration_en_cours') && $forcer_generation === false)
			return true;
		
        $la_liste_libre = Liste_libre::where('id', $liste_libre_id)->first();
		
		// est ce que cette liste libre est un rapport ?
		// si oui, on doit générer un fichier particulier pour les rapports
		if(!empty($la_liste_libre->id_rapport))
			return self::generer_fichier_migration_liste_libre_rapport($liste_libre_id,$forcer_generation);
		
		
		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Listes_libres';

		if(!\File::isDirectory($chemin_dossier_migrations))
        	\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
        	return false;

		$les_colonnes = Colonne::where('liste_libre_id',$liste_libre_id)->get()->toArray();
		$les_filtres = Liste_libre_filtre::where('liste_libre_id',$liste_libre_id)->get()->toArray();

		$les_filtres = collect($les_filtres)->unique(function($le_filtre) {
			return ($le_filtre['type_element'] ?? '').'|'.$le_filtre['nom_sql'];
		})->values()->all();

		$les_calculs = Liste_libre_calcul::where('liste_libre_id',$liste_libre_id)->get()->toArray();
        $les_couleurs = Liste_libre_couleur::where('liste_libre_id',$liste_libre_id)->get();
		$recherche_avancee = modele('recherche_avancee')
            ->where('type','filtres_appliques')
            ->where('id_cible',$liste_libre_id)
            ->first();

        $filtres_couleurs = modele('recherche_avancee')
            ->where('type','listes_libres_couleur')
            ->whereIn('id_cible',$les_couleurs->pluck('id')->toArray())
            ->get()->keyBy('id_cible');

        $filtres_colonnes_calculs = modele('recherche_avancee')
            ->whereIn('type', array_map(function($colonne_calcul) { 
                return 'liste_libre_colonne_calcul_'.$colonne_calcul['id']; }, 
                $les_colonnes))
            ->get()->groupBy('type');

		// On crée a le contenu du nouveau fichier de migration
		$texte = '<?php'."\n\n".'return ['."\n\t";

        foreach ($la_liste_libre->getAttributes() as $nom_champ => $valeur){

            if($nom_champ !== 'filtres_appliques' && $nom_champ !== 'id')
                $texte .= "'" . $nom_champ . "' => '" . $valeur . "',\n\t";

        }

		$texte .= '\'colonnes\' => [';

		foreach ($les_colonnes as $la_colonne) {

			$texte = $texte."\n\t\t".'array(';

			foreach ($la_colonne as $clef => $valeur) {

				if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
					continue;
			
				$texte = $texte.'"'.$clef.'" => "'.str_replace('"', '\\"', $valeur).'", ';
				
			}

            if(!empty($filtres_colonnes_calculs['liste_libre_colonne_calcul_'.$la_colonne['id']])){

                $texte .= "\n\t\t\t\"filtres_calcul\" => [\n";

                foreach($filtres_colonnes_calculs['liste_libre_colonne_calcul_'.$la_colonne['id']] as $filtre_colonne_calcul){

                        $structure = management('recherche_avancee',$filtre_colonne_calcul->id,$filtre_colonne_calcul)
                        ->structure(true);

                    $texte .= "\t\t\t\t".$filtre_colonne_calcul->id_cible." => ".str_replace("\n","\n\t\t\t\t",var_export($structure, true)). ",\n";
                }

                $texte .= "\t\t\t]\n\t\t";
            }

			$texte = $texte.'),';
		}
		$texte = $texte."\n\t".'],'."\n\t".'\'calculs\' => [';
		
		foreach ($les_calculs as $le_calcul) {

			$texte = $texte."\n\t\t".'array(';

			foreach ($le_calcul as $clef => $valeur) {

				if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
					continue;
							
				$texte = $texte.'"'.$clef.'" => "'.str_replace('"', '\\"', $valeur).'", ';

			}
			$texte = $texte.'),';
		}
		$texte = $texte."\n\t".'],'."\n\t".'\'filtres\' => [';
		
		foreach ($les_filtres as $le_filtre) {

			$texte = $texte."\n\t\t".'array(';

			foreach ($le_filtre as $clef => $valeur) {

				if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
					continue;
				
				$texte = $texte.'"'.$clef.'" => "'.str_replace('"', '\\"', $valeur).'", ';
				
			}
			$texte = $texte.'),';
		}

        $texte = $texte."\n\t".'],'."\n\t".'\'couleurs\' => [';

		foreach ($les_couleurs as $la_couleur) {

			$texte .= "\n\t\t".'array(';

            $texte .= '"couleur" => \''.$la_couleur->couleur. '\', ';

            if(!empty($filtres_couleurs[$la_couleur->id])){
                $structure = management('recherche_avancee',$filtres_couleurs[$la_couleur->id]->id,$filtres_couleurs[$la_couleur->id])
                    ->structure(true);

                $texte .= '"filtres" => '.str_replace("\n","\n\t\t",var_export($structure, true)). ', ';
            }

			$texte .= '),';
		}

        $texte .= "\n\t".'],';

        if(!empty($recherche_avancee)) {
            $structure = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)
                ->structure(true);

            $texte .= "\n\t'filtres_appliques' => ".str_replace("\n","\n\t",var_export($structure, true));
        }

		$texte = $texte."\n".'];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Listes_libres/'.$la_liste_libre['type_element'].'.php';

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
	 * Génère le nouveau fichier de migration pour les listes libres (rapports)
	 * 
	 */
	public static function generer_fichier_migration_liste_libre_rapport($liste_libre_id, $forcer_generation = false) {
		
		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours') && $forcer_generation === false)
			return true;
		
        $la_liste_libre = Liste_libre::where('id', $liste_libre_id)->first();
        $le_rapport = Rapport_libre::where('id_rapport', $la_liste_libre->id_rapport)->first();
		
		
		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Rapports';
		$rapport = true;

        if($le_rapport === null)
            return false;

		if($le_rapport->liste_sur_fiche == 1) {
			
			$chemin_dossier_migrations = app_path().'/Migrations/Listes_libres_fiches';
			$rapport = false;
		}

        else if($le_rapport->export == 1) {

			$chemin_dossier_migrations = app_path().'/Migrations/Listes_libres_export';
			$rapport = false;
		}

		
		if(!\File::isDirectory($chemin_dossier_migrations))
        	\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

		// On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
        	return false;
		
		$les_colonnes = Colonne::where('liste_libre_id',$liste_libre_id)->get()->toArray();
		$les_filtres = Liste_libre_filtre::where('liste_libre_id',$liste_libre_id)->get()->toArray();

		$les_filtres = collect($les_filtres)->unique(function($le_filtre) {
			return ($le_filtre['type_element'] ?? '').'|'.$le_filtre['nom_sql'];
		})->values()->all();

		$les_calculs = Liste_libre_calcul::where('liste_libre_id',$liste_libre_id)->get()->toArray();
        $les_couleurs = Liste_libre_couleur::where('liste_libre_id',$liste_libre_id)->get();

        $recherche_avancee = modele('recherche_avancee')
            ->where('type','filtres_appliques')
            ->where('id_cible',$liste_libre_id)
            ->first();

        $filtres_couleurs = modele('recherche_avancee')
            ->where('type','listes_libres_couleur')
            ->whereIn('id_cible',$les_couleurs->pluck('id')->toArray())
            ->get()->keyBy('id_cible');

		if($rapport) {
			
			$texte = 
		'<?php
			return [';
            foreach ($le_rapport->getAttributes() as $nom_champ => $valeur){

                if($nom_champ !== 'id')
                    $texte .= '"' . $nom_champ . '" => "' . str_replace('"','\"',$valeur) . "\",\n\t";
            }

            $texte.="\"liste_libre\" => [\n\t\t";

            foreach ($la_liste_libre->getAttributes() as $nom_champ => $valeur){

                if($nom_champ !== 'filtres_appliques' && $nom_champ !== 'id')
                    $texte .= '"' . $nom_champ . '" => "' . str_replace('"','\"',$valeur) . "\",\n\t\t";

            }

            $texte .= "'colonnes' => [
					";
					foreach ($les_colonnes as $la_colonne) {

						$texte = $texte.'array(';

						foreach ($la_colonne as $clef => $valeur) {

							if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
								continue;

							$texte = $texte.'"'.$clef.'" => "'.str_replace('"', '\\"', $valeur).'", ';
						}
						$texte = $texte.'),
						';
					}
			$texte = $texte.'],
				\'calculs\' => [
					';
					foreach ($les_calculs as $le_calcul) {

						$texte = $texte.'array(';

						foreach ($le_calcul as $clef => $valeur) {

							if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
								continue;
							
							$texte = $texte.'"'.$clef.'" => "'.$valeur.'", ';
						}
						$texte = $texte.'),
						';
					}
			$texte = $texte.'],
				\'filtres\' => [
					';
					foreach ($les_filtres as $le_filtre) {

						$texte = $texte.'array(';

						foreach ($le_filtre as $clef => $valeur) {

							if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
								continue;
							
							$texte = $texte.'"'.$clef.'" => "'.$valeur.'", ';
						}
						$texte = $texte.'),
						';
					}
					$texte = $texte.'],';

                $texte .= "\n\t".'\'couleurs\' => [';

                foreach ($les_couleurs as $la_couleur) {

                    $texte .= "\n\t\t".'array(';

                    $texte .= '"couleur" => \''.$la_couleur->couleur. '\', ';

                    if(!empty($filtres_couleurs[$la_couleur->id])){
                        $structure = management('recherche_avancee',$filtres_couleurs[$la_couleur->id]->id,$filtres_couleurs[$la_couleur->id])
                            ->structure(true);

                        $texte .= '"filtres" => '.str_replace("\n","\n\t\t",var_export($structure, true)). ', ';
                    }

                    $texte .= '),';
                }

                $texte .= "\n\t".'],';

                if(!empty($recherche_avancee)) {
                    $structure = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)
                        ->structure(true);

                    $texte .= "\n\t'filtres_appliques' => ".str_replace("\n","\n\t",var_export($structure, true));
                }

                $texte .= "]];";
		}
		else {

            $texte = '<?php'."\n\n".'return ['."\n\t";
            $texte .= '\'id_rapport\' => \''.$la_liste_libre->id_rapport.'\','."\n\t";
            $texte .= '\'type_element\' => \''.$la_liste_libre->type_element.'\','."\n\t";
            $texte .= '\'index_traduction\' => \''.$le_rapport->index_traduction.'\','."\n\t";
            $texte .= '\'inactif\' => \''.$le_rapport->inactif.'\','."\n\t";

            if(!empty($la_liste_libre->fiche)) {

                foreach ($la_liste_libre->getAttributes() as $nom_champ => $valeur){

                    if(in_array($nom_champ, ['id_rapport', 'type_element','filtres_appliques', 'id']))
                        continue;

                    $texte .= "'" . $nom_champ . "' => '" . $valeur . "', \n\t";

                }

            }

			$texte .= '"colonnes" => [
					';
					foreach ($les_colonnes as $la_colonne) {

						$texte = $texte.'array(';

						foreach ($la_colonne as $clef => $valeur) {

							if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
								continue;

							$texte = $texte.'"'.$clef.'" => "'.str_replace('"', '\\"', $valeur).'", ';
							
						}
						$texte = $texte.'),
						';
					}

            if(!empty($la_liste_libre->fiche)) {
                $texte .= '],
				\'calculs\' => [
					';
                foreach ($les_calculs as $le_calcul) {

                    $texte = $texte . 'array(';

                    foreach ($le_calcul as $clef => $valeur) {

                        if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
                            continue;

                        $texte = $texte . '"' . $clef . '" => "' . $valeur . '", ';
                    }
                    $texte = $texte . '),
						';
                }
                $texte .= '],
				\'filtres\' => [
					';
                foreach ($les_filtres as $le_filtre) {

                    $texte = $texte . 'array(';

                    foreach ($le_filtre as $clef => $valeur) {

                        if ($clef == 'id' || $clef == 'liste_libre_id' || $clef == 'profils')
                            continue;

                        $texte = $texte . '"' . $clef . '" => "' . $valeur . '", ';
                    }
                    $texte = $texte . '),
						';
                }
            }

            $texte = $texte . '],';

            $texte .= "\n\t".'\'couleurs\' => [';

            foreach ($les_couleurs as $la_couleur) {

                $texte .= "\n\t\t".'array(';

                $texte .= '"couleur" => \''.$la_couleur->couleur. '\', ';

                if(!empty($filtres_couleurs[$la_couleur->id])){
                    $structure = management('recherche_avancee',$filtres_couleurs[$la_couleur->id]->id,$filtres_couleurs[$la_couleur->id])
                        ->structure(true);

                    $texte .= '"filtres" => '.str_replace("\n","\n\t\t",var_export($structure, true)). ', ';
                }

                $texte .= '),';
            }

            $texte .= "\n\t".'],';

            if(!empty($recherche_avancee)) {
                $structure = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)
                    ->structure(true);

                $texte .= "\n\t'filtres_appliques' => ".str_replace("\n","\n\t",var_export($structure, true));
            }

            $texte .= '];';
		}

		

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = $chemin_dossier_migrations.'/'.$la_liste_libre->id_rapport.'.php';

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
	 * Génère le nouveau fichier de migration pour les autres vues libres
	 * 
	 */
	public static function generer_fichier_migration_autresvues() {

		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours'))
			return true;		
		
		// On vérifie que le dossier migration existe bien en spécifique
		$chemin_dossier_migrations = app_path().'/Migrations/Listes_libres_autresvues';

		if(!\File::isDirectory($chemin_dossier_migrations))
        	\File::makeDirectory($chemin_dossier_migrations, 0777, true, true);

        // On vérifie les droits sur le dossier app/Migrations
        if(!is_writable($chemin_dossier_migrations))
        	return false;

        $autresvues = Liste_libre_autresvues::orderBy('id')->get()->toArray();

		// On crée a le contenu du nouveau fichier de migration

		$texte = 
		'<?php
			return [
					';
					foreach ($autresvues as $autrevue) {

						$texte = $texte.'array(';

						foreach ($autrevue as $clef => $valeur) {

							if ($clef == 'id')
								continue;
							
							else {
								$la_liste = Liste_libre::find($valeur);
								$chaine_liste = $la_liste->type_element;
								if ($la_liste->id_rapport != null) {
									$chaine_liste = $chaine_liste."+".$la_liste->id_rapport;
								}
								$texte = $texte.'"'.$clef.'" => "'.$valeur.'", "chaine_'.$clef.'" => "'.$chaine_liste.'", ';
							}
						}
						$texte = $texte.'),
						';
					}
				$texte = $texte.'];';

        // On enregistre le nouveau fichier de migration et on écrase le fichier si il existe déjà
        $chemin_avec_nom_document = app_path().'/Migrations/Listes_libres_autresvues/autresvues.php';

		// Si le fichier existe, on le supprime
	    if (file_exists($chemin_avec_nom_document) == true)
	    	unlink($chemin_avec_nom_document);

	    // Enregistrement du fichier
	    $fichier = fopen($chemin_avec_nom_document, "x+");
	    fputs($fichier, $texte );
	    fclose($fichier);

	    return true;
	}

    public static function creer_liste_libre($donnees){

        $accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å',
            'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'é', // (le dernier 'é' n'est pas un doublon, c'est un caractère spécial)
            'Ç', 'ç',
            'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï',
            'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü',
            'ÿ',
            'Ñ', 'ñ');

        $sans_accents = str_split('AAAAAAaaaaaaOOOOOOooooooEEEEeeeeeCcIIIIiiiiUUUUuuuuyNn');

        $nom_table_sql = str_replace($accents, $sans_accents, $donnees['element']);

        $nom_table_sql = preg_replace("[^A-Za-z0-9]", '', $nom_table_sql);

        // autres caractères spéciaux
        $a_remplacer = str_split("&-(){}!?,;.:/\#[]'\" ~`^@°+=£€¤*%§<>\$²");

        $a_retirer = str_split('’̀');

        $nom_table_sql = str_replace($a_remplacer, '_', $nom_table_sql);
        $nom_table_sql = str_replace($a_retirer, '', $nom_table_sql);

        $nom_table_sql = strtolower(str_replace('__', '_', $nom_table_sql));

        $nom_table_sql_tmp = $nom_table_sql;

        $count = 1;

        while(Liste_libre::where('type_element', $nom_table_sql_tmp)->count() > 0) {

            $nom_table_sql_tmp = $nom_table_sql.'_'.$count;
            $count++;

        }

        $liste = new Liste_libre;
        $liste->type_element = $nom_table_sql_tmp;

        $retour = $liste->save();

        return $retour;

    }

    public static function informations_recherche_avancee($parametres){

        $recherche_avancee = modele('recherche_avancee');

        foreach($parametres as $champ => $valeur){

            if(is_array($valeur))
                $recherche_avancee = $recherche_avancee->whereIn($champ,$valeur);
            else if($champ != 'type_element')
                $recherche_avancee = $recherche_avancee->where(function($requete) use ($champ,$valeur){
                    $requete->where($champ,$valeur)
                        ->orWhereNull($champ);
                });
            else
                $recherche_avancee = $recherche_avancee->where($champ,$valeur);
        }

        $recherche_avancee = $recherche_avancee->orderBy('nom')->get();

        $recherches_par_categories = array(
            array(
                'id' => 'eden',
                'nom' => 'EDEN',
                'recherches_avancees' => array_values($recherche_avancee->where('utilisateur_id',null)->toArray())
            ),
        );

        if(isset($parametres['utilisateur_id']))
            $recherches_par_categories[] = array(
                'id' => 'personnel',
                'nom' => 'Personnel',
                'recherches_avancees' => array_values($recherche_avancee->where('utilisateur_id','>',0)->toArray())
            );

        return $recherches_par_categories;
    }

}
