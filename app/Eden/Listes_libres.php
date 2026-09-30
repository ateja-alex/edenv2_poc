<?php

namespace App\Eden;


/**
 *
 * Liste des rapports standards
 *
 */
class Listes_libres {
	
	/**
	 * 
	 * Récupère la liste des listes libres par défaut
	 * 
	 */
    public static function listes_libres_defaut() {
		
		$listes_libres = [];
		
		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres'));
		
		foreach($repertoire as $fichier) {
			
			if($fichier == '.' || $fichier == '..')
				continue;
			
			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);
			
			$listes_libres[$index_tableau] = $contenu_tmp;
		}
		
		// on vient ajouter les listes libres spécifiques
		if(is_dir(app_path('Migrations/Listes_libres'))) {
			
			$repertoire = scandir(app_path('Migrations/Listes_libres'));
			
			foreach($repertoire as $fichier) {
				
				if($fichier == '.' || $fichier == '..')
					continue;
				
				$contenu_tmp = require(app_path('Migrations/Listes_libres/'.$fichier));
				
				$index_tableau = str_replace('.php', '', $fichier);
				
				$listes_libres[$index_tableau] = $contenu_tmp;
			}
		}
		
		return $listes_libres;
	}
	
	/**
	 * 
	 * Récupère la liste des listes libres fiches par défaut
	 * 
	 */
    public static function listes_libres_fiches_defaut() {
		
		$listes_libres = [];
		
		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres_fiches'));
		
		foreach($repertoire as $fichier) {
			
			if($fichier == '.' || $fichier == '..')
				continue;
			
			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres_fiches/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);
			
			$listes_libres[$index_tableau] = $contenu_tmp;
		}
		
		// on vient ajouter les tables libres spécifiques
		if(is_dir(app_path('Migrations/Listes_libres_fiches'))) {
			
			$repertoire = scandir(app_path('Migrations/Listes_libres_fiches'));
			
			foreach($repertoire as $fichier) {
				
				if($fichier == '.' || $fichier == '..')
					continue;
				
				$contenu_tmp = require(app_path('Migrations/Listes_libres_fiches/'.$fichier));
				
				$index_tableau = str_replace('.php', '', $fichier);
				
				$listes_libres[$index_tableau] = $contenu_tmp;
			}
		}
		
		return $listes_libres;
	}

    /**
	 *
	 * Récupère la liste des listes libres fiches standard
	 *
	 */
    public static function listes_libres_fiches_standard() {

		$listes_libres = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres_fiches'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres_fiches/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

			$listes_libres[] = $index_tableau;
		}

		return $listes_libres;
	}

    /**
	 *
	 * Récupère la liste des listes libres export par défaut
	 *
	 */
    public static function listes_libres_export_defaut() {

		$listes_libres = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres_export'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres_export/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

			$listes_libres[$index_tableau] = $contenu_tmp;
		}

		// on vient ajouter les tables libres spécifiques
		if(is_dir(app_path('Migrations/Listes_libres_export'))) {

			$repertoire = scandir(app_path('Migrations/Listes_libres_export'));

			foreach($repertoire as $fichier) {

				if($fichier == '.' || $fichier == '..')
					continue;

				$contenu_tmp = require(app_path('Migrations/Listes_libres_export/'.$fichier));

				$index_tableau = str_replace('.php', '', $fichier);

				$listes_libres[$index_tableau] = $contenu_tmp;
			}
		}

		return $listes_libres;
	}

    /**
	 *
	 * Récupère la liste des listes libres export standard
	 *
	 */
    public static function listes_libres_export_standard() {

		$listes_libres = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres_export'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres_export/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

			$listes_libres[] = $index_tableau;
		}

		return $listes_libres;
	}
	
	
	/**
	 * 
	 * Retourne la liste des catégories standards pour les rapports
	 * 
	 */
    public static function categories() {
		
		return array(
			
			'gestion_commerciale' => array('nom' => 'Gestion commerciale'),
			'crm' => array('nom' => 'CRM'),
			'rh' => array('nom' => 'RH'),
			'activite_operationnelle' => array('nom' => 'Activité opérationnelle'),
			'animation_reseau' => array('nom' => 'Animation réseau'),
		);
    }

    /**
     *
     * Récupération des colonnes standard des listes
     *
     */
    public static function listes_colonnes_et_calculs_standard(){

        $informations_par_liste = [];
        $colonnes_par_liste = [];
        $calculs_par_liste = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

            $colonnes_par_liste[$index_tableau] =  $contenu_tmp['colonnes'];

            if(!empty($contenu_tmp['calculs']))
                $calculs_par_liste[$index_tableau] =  $contenu_tmp['calculs'];
		}

        foreach($colonnes_par_liste as $liste => $colonnes){

            $colonnes_formatees = [];

            foreach($colonnes as $colonne){

                $nom_colonne = $colonne['nom'];

                if($nom_colonne == '#')
                    continue;

                $type = !empty($colonne['type']) ? $colonne['type'] : "standard";

                if(!empty($colonne['methode']))
                    $type = 'methode';

                else if(!empty($colonne['champ']))
                    $type = 'champ';

                $nom_colonne = strtolower(retraite_caracteres_speciaux($nom_colonne,'_'));

                $index_traduction = 'liste.' . $liste . '.colonne.' . $nom_colonne;

                if($type == 'standard' || $type == 'liaison') {
                    $colonnes_formatees['type_1_' . $colonne['valeur']] = $index_traduction;
                }
                else if($type == 'methode')
                    $colonnes_formatees['type_2_'.$colonne['methode']. (isset($colonne['arguments']) ? $colonne['arguments'] : '')] = $index_traduction;
                else if($type == 'champ')
                    $colonnes_formatees['type_3_'.$colonne['champ']] = $index_traduction;

            }

            $informations_par_liste[$liste]['colonnes'] = $colonnes_formatees;
        }

        foreach($calculs_par_liste as $liste => $calculs){

            $calculs_formatees = [];

            foreach($calculs as $calcul){

                $nom_calcul = $calcul['nom'];

                $nom_calcul = strtolower(retraite_caracteres_speciaux($nom_calcul,'_'));

                $index_traduction = 'liste.' . $liste . '.calcul.' . $nom_calcul;

                $identifiant ='';

                if(!empty($calcul['nom_sql']))
                    $identifiant .= $calcul['nom_sql'].'_';

                if(!empty($calcul['type_calcul']))
                    $identifiant .= $calcul['type_calcul'].'_';

                if(!empty($calcul['unite']))
                    $identifiant .= $calcul['unite'].'_';

                if(!empty($calcul['split']))
                    $identifiant .= $calcul['split'].'_';

                if(!empty($calcul['top']))
                    $identifiant .= $calcul['top'];

                $calculs_formatees[$identifiant] = $index_traduction;
            }

            $informations_par_liste[$liste]['calculs'] = $calculs_formatees;
        }

        return $informations_par_liste;
    }

    /**
     *
     * Récupération des colonnes standard des listes sur fiche
     *
     */
    public static function listes_fiche_colonnes_et_calculs_standard(){

        $informations_par_liste = [];
        $colonnes_par_liste = [];
        $calculs_par_liste = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres_fiches'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres_fiches/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

			$colonnes_par_liste[$index_tableau] =  $contenu_tmp['colonnes'];

            if(!empty($contenu_tmp['calculs']))
                $calculs_par_liste[$index_tableau] =  $contenu_tmp['calculs'];
		}

        foreach($colonnes_par_liste as $liste => $colonnes){

            $colonnes_formatees = [];

            foreach($colonnes as $colonne){

                $nom_colonne = $colonne['nom'];

                if($nom_colonne == '#')
                    continue;

                $type = !empty($colonne['type']) ? $colonne['type'] : "standard";

                if(!empty($colonne['methode']))
                    $type = 'methode';

                else if(!empty($colonne['champ']))
                    $type = 'champ';

                $nom_colonne = strtolower(retraite_caracteres_speciaux($nom_colonne,'_'));

                $index_traduction = 'rapport.' . $liste . '.colonne.' . $nom_colonne;

                if($type == 'standard' || $type == 'liaison') {
                    $colonnes_formatees['type_1_' . $colonne['valeur']] = $index_traduction;
                }
                else if($type == 'methode')
                    $colonnes_formatees['type_2_'.$colonne['methode']] = $index_traduction;
                else if($type == 'champ')
                    $colonnes_formatees['type_3_'.$colonne['champ']] = $index_traduction;

            }

            $informations_par_liste[$liste]['colonnes'] = $colonnes_formatees;
        }

        foreach($calculs_par_liste as $liste => $calculs){

            $calculs_formatees = [];

            foreach($calculs as $calcul){

                $nom_calcul = $calcul['nom'];

                $nom_calcul = strtolower(retraite_caracteres_speciaux($nom_calcul,'_'));

                $index_traduction = 'rapport.' . $liste . '.calcul.' . $nom_calcul;

                $identifiant ='';

                if(!empty($calcul['nom_sql']))
                    $identifiant .= $calcul['nom_sql'].'_';

                if(!empty($calcul['type_calcul']))
                    $identifiant .= $calcul['type_calcul'].'_';

                if(!empty($calcul['unite']))
                    $identifiant .= $calcul['unite'].'_';

                if(!empty($calcul['split']))
                    $identifiant .= $calcul['split'].'_';

                if(!empty($calcul['top']))
                    $identifiant .= $calcul['top'];

                $calculs_formatees[$identifiant] = $index_traduction;
            }

            $informations_par_liste[$liste]['calculs'] = $calculs_formatees;
        }

        return $informations_par_liste;
    }

    /**
     *
     * Récupération des colonnes standard des listes sur export
     *
     */
    public static function listes_export_colonnes_standard(){

        $informations_par_liste = [];
        $colonnes_par_liste = [];
        $calculs_par_liste = [];

		// on vient ajouter les listes libres standards
		$repertoire = scandir(app_path('Eden/Migrations/Listes_libres_export'));

		foreach($repertoire as $fichier) {

			if($fichier == '.' || $fichier == '..')
				continue;

			$contenu_tmp = require(app_path('Eden/Migrations/Listes_libres_export/'.$fichier));

			$index_tableau = str_replace('.php', '', $fichier);

			$colonnes_par_liste[$index_tableau] =  $contenu_tmp['colonnes'];
		}

        foreach($colonnes_par_liste as $liste => $colonnes){

            $colonnes_formatees = [];

            foreach($colonnes as $colonne){

                $nom_colonne = $colonne['nom'];

                if($nom_colonne == '#')
                    continue;

                $type = !empty($colonne['type']) ? $colonne['type'] : "standard";

                if(!empty($colonne['methode']))
                    $type = 'methode';

                else if(!empty($colonne['champ']))
                    $type = 'champ';

                $nom_colonne = strtolower(retraite_caracteres_speciaux($nom_colonne,'_'));

                $index_traduction = 'rapport.' . $liste . '.colonne.' . $nom_colonne;

                if($type == 'standard' || $type == 'liaison') {
                    $colonnes_formatees['type_1_' . $colonne['valeur']] = $index_traduction;
                }
                else if($type == 'methode')
                    $colonnes_formatees['type_2_'.$colonne['methode']] = $index_traduction;
                else if($type == 'champ')
                    $colonnes_formatees['type_3_'.$colonne['champ']] = $index_traduction;

            }

            $informations_par_liste[$liste]['colonnes'] = $colonnes_formatees;
        }

        return $informations_par_liste;
    }
}






