<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Liste_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Export_compta_modele_management extends Element_management {

    private $colonnes = false;

	/**
	 * 
	 * On crée les lignes à réceptionner pour les fournisseurs
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		// on retire le par défaut des autres modèles
		if($this->modele->par_defaut == 1) {

			$exports = modele('export_compta_modele')
                ->where('type_element',$this->modele->type_element)
                ->where('par_defaut', 1)->where('entite_id', $this->modele->entite_id)->get();

			foreach($exports as $export) {

				if($export->id == $this->modele->id)
					continue;

				management('export_compta_modele', $export->id)->enregistre_modele(array('par_defaut' => 0));
			}
		}

        if(empty($modele_avant) || $this->modele->nom != $modele_avant->nom) {

            $liste_libres = Liste_libre::where('type_element', $this->modele->type_element)->get();

            foreach ($liste_libres as $liste_libre) {

                Cache_management::generation_liste_libre($liste_libre->id);
            }
        }
	}

    public function methodes_post_suppression($modele){

        parent::methodes_post_suppression($modele);

        $liste_libres = Liste_libre::where('type_element', $modele->type_element)->get();

        foreach ($liste_libres as $liste_libre) {

            Cache_management::generation_liste_libre($liste_libre->id);
        }
    }

    /**
     *
     * Récupération des colonnes pour l'export
     *
     */
    public function colonnes(){

        if($this->colonnes === false)
            $this->colonnes = modele('export_compta_colonne')
                ->where('export_compta_modele_id', $this->modele->id)
                ->orderBy('ordre')->get();

        return $this->colonnes;
    }

    /**
     * @param $ids
     * @return void
     *
     * Permet de faire un export
     *
     */
    public function export($requete,$parametres = array()){

        if ($this->modele->format_de_fichier == 5 || $this->modele->format_de_fichier == 6)        // Positionné
            return $this->exporte_ecritures_position($requete, $parametres);
        else                                                                //CSV
            return $this->exporte_ecritures_csv($requete, $parametres);
    }

    /**
     *
     * Exporte les écritures au format CSV
     *
     */
    public function exporte_ecritures_csv($requete, $parametres){

        $contenu_fichier = [];

        if (in_array($this->modele->format_de_fichier, array(1, 3)))
            $delimiteur = ';';
        else
            $delimiteur = "\t";

        $formats_dates = [1 => "Ymd", 2 => "d/m/Y", 3 => "dmy", 4 => "dmY"];

        $format_date = isset($formats_dates[$this->modele->format_de_date]) ? $formats_dates[$this->modele->format_de_date] : 'Y-m-d';

        $export_compta_colonne = $this->colonnes();

        $colonnes_par_champ_nom_sql = $this->colonnes_par_champ_nom_sql($export_compta_colonne);

        if ($this->modele->afficher_ligne_titre == 1) {

            $ligne = array();

            foreach ($export_compta_colonne as $colonne)
                $ligne[] = $colonne->titre;

            $contenu_fichier[] = implode($delimiteur, $ligne);
        }

        $colonnes = array();

        foreach ($export_compta_colonne as $export)
            $colonnes[] = $export->valeur;

        // on initialise le modèle à suivre
        $chaine = implode('[DELIMITEUR]', $colonnes);

        $informations_pour_remplacement_par_ligne = management($this->modele->type_element)->export_remplacement_valeurs_par_ligne($requete,$colonnes_par_champ_nom_sql,$format_date);

        if(!empty($informations_pour_remplacement_par_ligne)) {
            foreach ($informations_pour_remplacement_par_ligne as $valeurs_de_remplacement) {

                unset($valeurs_de_remplacement['id']);

                foreach($export_compta_colonne as $colonne){

                    $valeur_colonne = $colonne->valeur;

                    if(!isset($valeurs_de_remplacement[$valeur_colonne]) && strpos($valeur_colonne,'#') !== false)
                        $valeurs_de_remplacement[$valeur_colonne] = "";

                    else if(!empty($valeurs_de_remplacement[$valeur_colonne]) && !empty($colonne->positionne_longueur_champs))
                        $valeurs_de_remplacement[$valeur_colonne] = substr($valeurs_de_remplacement[$valeur_colonne], 0, $colonne->positionne_longueur_champs);
                }

                $ligne = str_replace(array_keys($valeurs_de_remplacement), array_values($valeurs_de_remplacement), $chaine);
                $contenu_fichier[] = implode($delimiteur, explode('[DELIMITEUR]', $ligne));
            }
        }

        if(!empty(champ_libre_modele($this->modele->type_element,'exporte'))) {

            $requete->select($this->modele->type_element.'.id');
            $requete_str = vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings());

            $requete_str = 'UPDATE '.$this->modele->type_element.' SET exporte = 1 WHERE id IN ('.$requete_str.')';

            DB::select($requete_str);
        }

        $contenu_fichier = implode("\n", $contenu_fichier);

        $extension = in_array($this->modele->format_de_fichier, array(1, 2)) ? 'csv' : 'txt';

        $fichier = management($this->modele->type_element)->nom_fichier_export($this,$parametres).'.'.$extension;

        if(in_array($this->modele->format_de_fichier, array(1, 2))){
            $contenu_fichier = "\xEF\xBB\xBF" . $contenu_fichier;
            $contenu_fichier = mb_convert_encoding($contenu_fichier, 'UTF-8');
        }

        // on crée le fichier
        \Storage::put($fichier, $contenu_fichier);

        $headers = array();

        return response()->download(storage_path('app/' . $fichier), $fichier, $headers);
    }


    /**
     *
     * Génère le fichier au format positionné
     *
     * @param $ecritures
     * @param $parametres
     * @return void
     */
    public function exporte_ecritures_position($requete, $parametres)
    {
        $contenu_fichier = [];

        $formats_dates = [1 => "Ymd", 2 => "d/m/Y", 3 => "dmy", 4 => "dmY"];

        $format_date = isset($formats_dates[$this->modele->format_de_date]) ? $formats_dates[$this->modele->format_de_date] : 'Y-m-d';

        //Chargement des infos du modèle et des datas à charger
        $export_compta_colonnes = $this->colonnes();

        $colonnes_par_champ_nom_sql = $this->colonnes_par_champ_nom_sql($export_compta_colonnes);

        $informations_pour_remplacement_par_ligne = management($this->modele->type_element)->export_remplacement_valeurs_par_ligne($requete,$colonnes_par_champ_nom_sql,$format_date);

        //Méthode qui formate les valeurs avant l'injection dans la ligne du fichier
        $format_colonne = function ($export_compta_colonne, $valeur) {
            if ($export_compta_colonne->positionne_alignement == 1) {                                                                   // Alignement à gauche
	    	$valeur = substr($valeur, 0, $export_compta_colonne->positionne_longueur_champs); // on coupe si trop long
                $valeur = str_pad($valeur, $export_compta_colonne->positionne_longueur_champs, ' ');
            } else {                                                                                                                       //Alignement à droite
                
                if (strlen($valeur) > $export_compta_colonne->positionne_longueur_champs)
                    $valeur = substr($valeur, strlen($valeur) - $export_compta_colonne->positionne_longueur_champs); // on coupe si trop long

                $valeur = str_pad($valeur, $export_compta_colonne->positionne_longueur_champs, ' ', STR_PAD_LEFT);;
            }

            return $valeur;
        };

        //On ajoute la liste des entêtes dans le fichier
        if ($this->modele->afficher_ligne_titre == 1) {
            $ligne = "";

            foreach ($export_compta_colonnes as $export_compta_colonne)
                $ligne .= $format_colonne($export_compta_colonne, $export_compta_colonne->titre);

            $contenu_fichier[] = $ligne;
        }

        if(!empty($informations_pour_remplacement_par_ligne)) {

            foreach ($informations_pour_remplacement_par_ligne as $valeurs_de_remplacement) {

                $ligne = "";

                unset($valeurs_de_remplacement['id']);

                foreach($export_compta_colonnes as $colonne){

                    $valeur = str_replace(array_keys($valeurs_de_remplacement),array_values($valeurs_de_remplacement),$colonne->valeur);

                    preg_match_all("/\#(.[^#]*)?\#/",$colonne->valeur,$correspondances);

                    if(!empty($correspondances[0]))
                        $valeur = str_replace($correspondances[0],"",$valeur);

                    $ligne .= $format_colonne($colonne, $valeur);
                }

                $contenu_fichier[] = $ligne;
            }
        }

        if(!empty(champ_libre_modele($this->modele->type_element,'exporte'))) {

            $requete->select($this->modele->type_element.'.id');
            $requete_str = vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings());

            $requete_str = 'UPDATE '.$this->modele->type_element.' SET exporte = 1 WHERE id IN ('.$requete_str.')';

            DB::select($requete_str);
        }

        // on crée le fichier
        $fichier =  management($this->modele->type_element)->nom_fichier_export($this,$parametres). '.'.($this->modele->format_de_fichier == 5 ? 'csv' : 'txt');
        $contenu_fichier = implode("\n", $contenu_fichier);
        if($this->modele->format_de_fichier == 5){
            $contenu_fichier = "\xEF\xBB\xBF" . $contenu_fichier;
            $contenu_fichier = mb_convert_encoding($contenu_fichier, 'UTF-8');
        }
        \Storage::put($fichier, $contenu_fichier);

        return response()->download(storage_path('app/' . $fichier), $fichier);
    }



    /**
     *
     * Crée une chaine de caractères de longueur délimitée
     *
     */
    public function texte($texte, $nombre_caracteres, $espaces_a_gauche = false)
    {

        for ($i = 1; $i <= $nombre_caracteres; $i++) {

            if ($espaces_a_gauche === true) {

                $texte = ' ' . $texte;
            } else {

                $texte = $texte . ' ';
            }
        }


        if ($espaces_a_gauche === true) {

            return mb_substr($texte, -1 * $nombre_caracteres);
        } else {

            return mb_substr($texte, 0, $nombre_caracteres);
        }
    }

    /**
     * @param $colonnes
     * @return array|mixed
     *
     * Récupère les colonnes triées par parent
     *
     */
    public function colonnes_par_champ_nom_sql($colonnes){

        $colonnes_par_champ_nom_sql = [];

        foreach($colonnes as $colonne){

            preg_match_all("/\#(.[^#]*)?\#/",$colonne->valeur,$correspondances);

            if(!empty($correspondances[1])){

                foreach($correspondances[1] as $index_correspondance => $correspondance) {

                    $tableau = explode('.', $correspondance);

                    if (sizeof($tableau) == 1)
                        $colonnes_par_champ_nom_sql[$correspondances[0][$index_correspondance]] = $tableau[0];
                    else {

                        $index = 0;

                        $tableau_colonne = &$colonnes_par_champ_nom_sql;

                        while (!empty($tableau[$index + 1])) {

                            if(empty($tableau_colonne['sous_table'][$tableau[$index]]))
                                $tableau_colonne['sous_table'][$tableau[$index]] = array();

                            $tableau_colonne = &$tableau_colonne['sous_table'][$tableau[$index]];

                            $index++;
                        }

                        $tableau_colonne[$correspondances[0][$index_correspondance]] = $tableau[$index];
                    }
                }
            }
        }

        return $colonnes_par_champ_nom_sql;
    }

}
