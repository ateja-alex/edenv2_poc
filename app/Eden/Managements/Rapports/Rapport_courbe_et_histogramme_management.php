<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Champ_libre;
use DateTime;

/**
* Gestion des rapports
*/
class Rapport_courbe_et_histogramme_management extends Rapport_base_management {

	/**
	 *
	 * On génère un rapport libre paramétré via l'interface
	 *
	 */
	public function genere($ajax = false) {

		if(empty($this->parametrage_rapport_libre))
			return parent::genere($ajax);

		// les filtres génériques du rapport
        $type_element = $this->rapport_libre->type_element;
        $management_element = management($type_element);
        $this->applique_filtres($management_element);
        $this->recupere_valeurs_filtres($management_element);

        if(!empty(request()->all()['afficher_periode_precedente']) || !empty($this->parametrage_rapport_libre['filtres_rapport_afficher_n_moins_1']) || !empty($this->parametrage_rapport_libre['filtre_applique_rapport_n_moins_1']))
            $this->parametrage_rapport_libre['afficher_n_moins_1'] = 1;

		$champ_axe_x = $this->parametrage_rapport_libre['axe_x'];

        $champ_groupe_par = false;

		$series = collect($this->parametrage_rapport_libre['series'])->keyBy('id')->toArray();

		$champ_management = $management_element->champ($champ_axe_x);

        $this->champ_axe_x_date = in_array($champ_management->modele->type,[4,5,18]);

        if($this->champ_axe_x_date && empty($this->parametrage_rapport_libre['periodicite']))
            $this->parametrage_rapport_libre['periodicite'] = 'mensuelle';

        $series_infos = [];

        $filtres = modele('recherche_avancee')
            ->where('type',$this->rapport_libre->id_rapport.'.serie')
            ->get()->keyBy('id_cible');

        foreach($filtres as $serie_index => $filtre){
            if(isset($series[$serie_index]))
                $series[$serie_index]['filtre'] = management('recherche_avancee',$filtre->id,$filtre)->structure();
        }

		foreach($series as $id => $serie) {

            $serie_management = new Serie_management($serie,$this);

            $nom_serie = traduction($serie['index_traduction'] . '.nom');
            $nom_serie_n_moins_1 = traduction($serie['index_traduction'] . '.nom') . ' (N-1)';

            $this->serie($nom_serie);

            // on récupère les résultats de la série
            $resultats = $serie_management->recupere_resultats();

            $resultats_pour_legende = $resultats;

            if (!empty($this->parametrage_rapport_libre['afficher_n_moins_1'])){

                $this->serie($nom_serie_n_moins_1);

                $this->champ_date_n_moins_1 = $this->champ_axe_x_date ? $champ_axe_x : explode('.', $this->parametrage_rapport_libre['filtres_rapport_afficher_n_moins_1']['nom'] ?? '')[1] ?? null;

                if(!empty($this->champ_date_n_moins_1))
                    $management_champ_date_n_moins_1 = $management_element->champ($this->champ_date_n_moins_1);

                $filtre_n_moins_1_id = null;

                foreach($this->options as $option){
                    if($option['nom_sql'] == $this->champ_date_n_moins_1)
                        $filtre_n_moins_1_id = $option['id'];
                }

                $valeurs_interdites = ['renseigne', 'pas_renseigne', 'passe', 'pas_passe'];

                $valeurs_filtre_n_moins_1 = [];

                // On gère le cas où on a des filtres de série qui s'appliquent à N-1

                if(!empty($serie_management->serie['filtre'])){

                    $valeurs_filtre_appliques_n_moins_1 = $this->gestion_n_moins_1_filtres_serie($serie_management, $management_element, $serie, $valeurs_interdites, $management_champ_date_n_moins_1);

                    if (!empty($valeurs_filtre_appliques_n_moins_1)) 
                        $this->valeurs_filtre_appliques_n_moins_1 = $valeurs_filtre_appliques_n_moins_1;
                }

                // On gère le cas où on a un filtre actif sur le champ N-1

                if (empty($valeurs_filtre_appliques_n_moins_1)) {

                    foreach($this->valeurs_filtre as $valeurs){

                        if($valeurs['id'] === $filtre_n_moins_1_id && $this->valeurs_filtres_n_moins_1($valeurs, $management_champ_date_n_moins_1, $valeurs_interdites, $serie_management)){
                            $valeurs_filtre_n_moins_1[$filtre_n_moins_1_id] = $valeurs['valeurs'];
                            $valeurs['valeurs']['variable'] = '';
                            break;
                        }
                    }
                }
            
                if (!empty($valeurs_filtre_appliques_n_moins_1) || !empty($valeurs_filtre_n_moins_1) || $this->champ_axe_x_date) {
                    $this->valeurs_filtre_n_moins_1 = $valeurs_filtre_n_moins_1 ?? [];
                    [$resultats_n_moins_1, $resultats_pour_legende] = $this->recupere_resultats_n_moins_1($serie_management, $resultats, $champ_axe_x, $this->champ_date_n_moins_1);
                }
            }

            $valeurs_champ_x = $serie_management->legende($type_element, $champ_axe_x, $resultats_pour_legende);

            $valeurs_champ_groupe_par = [];

            if (!empty($this->parametrage_rapport_libre['groupe_par'])) {
                $champ_groupe_par = $this->parametrage_rapport_libre['groupe_par'];
                $valeurs_champ_groupe_par = array_unique($serie_management->legende($type_element, $champ_groupe_par, $resultats) + $serie_management->legende($type_element, $champ_groupe_par, $resultats_n_moins_1 ?? collect()));

                if($this->champ_axe_x_date)
                   $resultats = $resultats->groupBy($champ_groupe_par)->sortBy(function ($groupe) use($champ_axe_x) {
                        return $groupe->sortBy($champ_axe_x)->last()->$champ_axe_x;
                    })->values();
                else
                    $resultats = $resultats->groupBy($champ_groupe_par);

                if(!empty($resultats_n_moins_1))
                    $resultats_n_moins_1 = $resultats_n_moins_1->groupBy($champ_groupe_par);
            }

            $series_infos[$id] = [
                'nom_serie' => $nom_serie ?? '',
                'nom_serie_n_moins_1' => $nom_serie_n_moins_1 ?? '',
                'resultats' => $resultats ?? collect([]),
                'resultats_n_moins_1' => $resultats_n_moins_1 ?? collect([]),
                'valeurs_champ_x' => is_array($valeurs_champ_x) ? $valeurs_champ_x : $valeurs_champ_x->toArray(),
                'valeurs_champ_groupe_par' => is_array($valeurs_champ_groupe_par) ? $valeurs_champ_groupe_par : $valeurs_champ_groupe_par->toArray(),
            ];

        }

        $valeurs_champ_x = [];
        $valeurs_champ_groupe_par = [];

        foreach ($series_infos as $infos) {
            $valeurs_champ_x = array_unique($valeurs_champ_x + $infos['valeurs_champ_x']);
            $valeurs_champ_groupe_par = array_unique($valeurs_champ_groupe_par + $infos['valeurs_champ_groupe_par']);
        }

        $valeurs_champ_x = $this->recupere_valeurs_axe($series_infos, $champ_axe_x, $valeurs_champ_x, $champ_groupe_par !== false, $this->champ_axe_x_date);

        if($champ_groupe_par !== false)
            $valeurs_champ_groupe_par = $this->recupere_valeurs_axe($series_infos, $champ_groupe_par, $valeurs_champ_groupe_par, $champ_groupe_par !== false, $this->champ_axe_x_date);

        foreach($series_infos as $id => $serie) {

            if($champ_groupe_par === false)
                $this->traite_resultat_rapport($serie['resultats'], $serie['resultats_n_moins_1'], $serie['nom_serie'], $serie['nom_serie_n_moins_1'], $champ_axe_x, $champ_management, $valeurs_champ_x);
            else if(!empty($this->rapport_libre['type_rapport']) && $this->rapport_libre['type_rapport'] == 'histogramme')
                $this->traite_resultat_histogramme_cumule($serie['resultats'], $serie['resultats_n_moins_1'], $serie['nom_serie'], $serie['nom_serie_n_moins_1'], $champ_axe_x, $champ_groupe_par, $champ_management, $valeurs_champ_x, $valeurs_champ_groupe_par);
            else
                return traduction('messages.php.rapport.champ_groupe_par_renseigne_pas_histogramme');

		}

		return parent::genere($ajax);
	}

    public function traite_resultat_rapport($resultats, $resultats_n_moins_1, $nom_serie, $nom_serie_n_moins_1, $champ_axe_x, $champ_management, $valeurs_champ_x){

        $resultats_definitifs = [];
        $resultats_definitifs_n_moins_1 = [];

        $serie_management = new Serie_management();

        foreach($resultats as $resultat) {

            if(empty($resultat['total_1'])) {

                if(empty($this->parametrage_rapport_libre['afficher_sans_valeur']))
                    continue;
                else
                    $resultat['total_1'] = 0;
            }

            // on gère le cas particulier de la périodicité
            if(!empty($this->parametrage_rapport_libre['periodicite']) && $serie_management->periodicite_valide($this->parametrage_rapport_libre['periodicite']) && in_array($champ_management->modele->type, array(4,5))) {

                if(!isset($this->periodes)) {
                    $debut = $resultats->min($champ_axe_x);
                    $fin = $resultats->max($champ_axe_x);

                    $this->periodes = $serie_management->periodes($this->parametrage_rapport_libre['periodicite'], $debut, $fin);
                }

                // on doit remettre en forme les résultats
                foreach($this->periodes as $periode) {

                    // N
                    if(!isset($resultats_definitifs[$periode['date_debut']]))
                        $resultats_definitifs[$periode['date_debut']] = 0;

                    if($resultat[$champ_axe_x] >= $periode['date_debut'] && $resultat[$champ_axe_x] <= $periode['date_fin']) {

                        $resultats_definitifs[$periode['date_debut']] += $resultat['total_1'];
                    }

                    // N-1
                    if(!isset($resultats_definitifs_n_moins_1[$periode['date_debut']]))
                        $resultats_definitifs_n_moins_1[$periode['date_debut']] = 0;

                }

                continue;
            }

            // N
            if(!isset($resultats_definitifs[$resultat[$champ_axe_x]]))
                $resultats_definitifs[$resultat[$champ_axe_x]] = 0;
            
            $resultats_definitifs[$resultat[$champ_axe_x]] += $resultat['total_1'];

        }

        // on crée la série avec les résultats calculés
        $ancienne_valeur = 0;
        $ancienne_valeur_moins_1 = 0;

        foreach($valeurs_champ_x as $id_valeur => $valeur) {

            if(empty($resultats_definitifs[$id_valeur]))
                $resultats_definitifs[$id_valeur] = 0;

            if(!in_array($valeur, $this->legende))
                $this->legende($valeur);

            $ancienne_valeur += $resultats_definitifs[$id_valeur];

            if(!empty($this->parametrage_rapport_libre['resultats_cumules']))
                $resultats_definitifs[$id_valeur] = $ancienne_valeur;

            $this->valeur($nom_serie, $resultats_definitifs[$id_valeur]);

            if(!empty($this->parametrage_rapport_libre['afficher_n_moins_1'])) {

                if(!in_array($champ_management->modele->type, array(4,5))) {

                    foreach($resultats_n_moins_1 as $resultat_n_moins_1) {

                        if($resultat_n_moins_1->$champ_axe_x == $id_valeur) {

                            $resultats_definitifs_n_moins_1[$id_valeur] = floatval($resultat_n_moins_1['total_1']);
                        }
                    }
                }
                else {

                    $periode_n = $id_valeur;

                    if(!isset($resultats_definitifs_n_moins_1[$periode_n]))
                        $resultats_definitifs_n_moins_1[$periode_n] = 0;

                    foreach($resultats_n_moins_1 as $resultat_n_moins_1) {

                        if($resultat_n_moins_1->date_n == $periode_n) {

                            $resultats_definitifs_n_moins_1[$periode_n] = floatval($resultat_n_moins_1['total_1']);
                        }
                    }

                }

                if(empty($resultats_definitifs_n_moins_1[$id_valeur]))
                    $resultats_definitifs_n_moins_1[$id_valeur] = 0;
                
                $ancienne_valeur_moins_1 += $resultats_definitifs_n_moins_1[$id_valeur];
                
                if(!empty($this->parametrage_rapport_libre['resultats_cumules']))
                    $resultats_definitifs_n_moins_1[$id_valeur] = $ancienne_valeur_moins_1;

                $this->valeur($nom_serie_n_moins_1 , $resultats_definitifs_n_moins_1[$id_valeur]);

            }
        }

        return array($resultats_definitifs, $resultats_definitifs_n_moins_1);

    }

    public function traite_resultat_histogramme_cumule($resultats_cumule, $resultats_cumule_n_moins_1, $nom_serie, $nom_serie_n_moins_1, $champ_axe_x, $champ_groupe_par, $champ_management,$valeurs_champ_axe_x, $valeurs_champ_groupe_par){

        $resultats_definitifs = [];
        $resultats_definitifs_n_moins_1 = [];
        $id_champ_axe_x_avec_valeur = [];

        $serie_management = new Serie_management();

        foreach($resultats_cumule as $resultats) {

            foreach ($resultats as $resultat) {

                //On vérifie s'il y a des valeurs pour cet élément
                if (empty($resultat[$champ_groupe_par])) {
                    $resultat[$champ_groupe_par] = 0;
                }

                //Si le tableau définitif n'a pas encore l'élément dans ses lignes
                if (!isset($resultats_definitifs[$resultat[$champ_groupe_par]]))
                    $resultats_definitifs[$resultat[$champ_groupe_par]] = [];

                // on gère le cas particulier de la périodicité
                if (!empty($this->parametrage_rapport_libre['periodicite']) && $serie_management->periodicite_valide($this->parametrage_rapport_libre['periodicite']) && isset($this->periodes) && in_array($champ_management->modele->type, array(4, 5))) {

                    if (!isset($resultats_definitifs[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]]))
                        $resultats_definitifs[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]] = 0;

                    // on doit remettre en forme les résultats
                    foreach ($this->periodes as $periode) {

                        // N

                        if (
                            date('Y-m-d', strtotime($resultat[$champ_axe_x])) >= date('Y-m-d', strtotime($periode['date_debut']))
                            && date('Y-m-d', strtotime($resultat[$champ_axe_x])) <= date('Y-m-d', strtotime($periode['date_fin']))
                        ) {
                            $resultats_definitifs[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]] += $resultat['total_1'];
                        }

                        // N-1
                        if (!isset($resultats_definitifs_n_moins_1[$resultat[$champ_groupe_par]]))
                            $resultats_definitifs_n_moins_1[$resultat[$champ_groupe_par]] = [];

                        if (!isset($resultats_definitifs_n_moins_1[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]]))
                            $resultats_definitifs_n_moins_1[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]] = 0;

                        if (
                            date('Y-m-d', strtotime($resultat[$champ_axe_x])) >= date('Y-m-d', strtotime($periode['date_debut_n_moins_1']))
                            && date('Y-m-d', strtotime($resultat[$champ_axe_x])) <= date('Y-m-d', strtotime($periode['date_fin_n_moins_1']))
                        ) {
                            $resultats_definitifs_n_moins_1[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]] += $resultat['total_1'];
                        }
                    }

                    continue;
                }

                //Si le tableau définitif pour la ligne de l'élément n'a pas encore le champ de l'axe X dans ses lignes
                if (!isset($resultats_definitifs[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]]))
                    $resultats_definitifs[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]] = 0;

                //Résultat pour la période N

                //On stocke les ids du champ qui créé l'axe X pour lesquels nous avons des valeurs dans le graph
                if(!in_array($resultat[$champ_axe_x], $id_champ_axe_x_avec_valeur))
                    $id_champ_axe_x_avec_valeur[] = $resultat[$champ_axe_x];
                
                $resultats_definitifs[$resultat[$champ_groupe_par]][$resultat[$champ_axe_x]] += $resultat['total_1'];

                //Résultat pour la période N-1

                //Si le champ est un champ de type date (cas rare uniquement pré-version en cours)
                
            }
        }

        //On retraite le résultat définitif pour que l'array de sortie soit correct
        foreach ($resultats_definitifs as $id_champ_groupe_par => &$valeur_definitive){

            //On boucle sur les id du champ qui fait l'axe x pour lesquels on sait qu'il y a une valeur
            //Si on ne fait pas ça on perd la cohérence entre la légende et les colonnes
            foreach ($id_champ_axe_x_avec_valeur as $id_champ_axe_x){

                //S'il n'y a pas de valeur pour cet élément à cette position de l'axe X mais qu'un autre élément a une valeur
                if(empty($valeur_definitive[$id_champ_axe_x])){

                    $resultats_definitifs[$id_champ_groupe_par][$id_champ_axe_x] = 0;

                }

            }

            ksort($valeur_definitive);

        }

        //On ajoute la valeur Sans valeur si elle n'existe pas
        if(empty($valeurs_champ_groupe_par[0]))
            $valeurs_champ_groupe_par[0] = "Sans valeur";

        //Si les valeurs ne sont pas un array on les transforme pour pouvoir les ordonner
        if(!is_array($valeurs_champ_groupe_par))
            $valeurs_champ_groupe_par = $valeurs_champ_groupe_par->toArray();

        //On trie le tableau pour ordonner par clé (principalement pour le cas des dates)
        ksort($valeurs_champ_groupe_par);

        //On ajoute la valeur Sans valeur si elle n'existe pas
        if(empty($valeurs_champ_axe_x[0]))
            $valeurs_champ_axe_x[0] = "Sans valeur";

        //Si les valeurs ne sont pas un array on les transforme pour pouvoir les ordonner
        if(!is_array($valeurs_champ_axe_x))
            $valeurs_champ_axe_x = $valeurs_champ_axe_x->toArray();

        //On trie le tableau pour ordonner par clé (principalement pour le cas des dates)
        ksort($valeurs_champ_axe_x);

        // On crée la série avec les résultats calculés
        foreach($valeurs_champ_groupe_par as $id_valeur => $valeur) {

            if($id_valeur == 0)
                continue;

            //Si la valeur n'existe pas mais qu'on affiche les sans valeur
            if(!isset($resultats_definitifs[$id_valeur]))
                $resultats_definitifs[$id_valeur] = [];

            //On ajoute les légendes dans l'ordre
            $ancienne_valeur = 0;

            $resultats_definitifs_n_moins_1[$id_valeur] = [];

            foreach ($valeurs_champ_axe_x as $id_valeur_legende => $valeur_affichage_legende){

                if($id_valeur_legende == 0)
                    continue;
                
                if(!isset($resultats_definitifs[$id_valeur][$id_valeur_legende]))
                    $resultats_definitifs[$id_valeur][$id_valeur_legende] = 0;

                if(isset($resultats_definitifs[$id_valeur][$id_valeur_legende])) {
                    $this->legende($valeur_affichage_legende);
                    $ancienne_valeur += $resultats_definitifs[$id_valeur][$id_valeur_legende];
                }
                
                if(!empty($this->parametrage_rapport_libre['resultats_cumules']) && !empty($ancienne_valeur))
                    $resultats_definitifs[$id_valeur][$id_valeur_legende] = $ancienne_valeur;

            }

            //On instancie une variable pour modifier le nom d'affichage si nécessaire sans impacter le reste du code
            $affichage_element_cumule = $valeur;

            //Si on affiche N-1 alors on spécifie que la valeur actuelle est l'année N
            //(principalement pour stacker les colonnes dans le plugin highcharts)
            if(!empty($this->parametrage_rapport_libre['afficher_n_moins_1']))
                $affichage_element_cumule .= ' (N)';

            //On ajoute la valeur
            ksort($resultats_definitifs[$id_valeur]);

            $this->valeur($nom_serie, array_values($resultats_definitifs[$id_valeur]), $affichage_element_cumule);

            //On ajoute les N-1
            if(!empty($this->parametrage_rapport_libre['afficher_n_moins_1'])) {

                if(!isset($resultats_definitifs_n_moins_1[$id_valeur]))
                    $resultats_definitifs_n_moins_1[$id_valeur] = [];

                $ancienne_valeur_moins_1 = 0;
                
                foreach ($valeurs_champ_axe_x as $id_valeur_legende => $valeur_affichage_legende){

                    if($id_valeur_legende == 0 && empty($resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende]))
                        continue;

                    if (!in_array($champ_management->modele->type, array(4, 5))) {

                        foreach ($resultats_cumule_n_moins_1 as $resultats_n_moins_1){
                            foreach ($resultats_n_moins_1 as $resultat_n_moins_1) {

                                if ($resultat_n_moins_1->$champ_groupe_par == $id_valeur && $resultat_n_moins_1->$champ_axe_x == $id_valeur_legende)
                                    $resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende] = floatval($resultat_n_moins_1['total_1']);

                            }
                        }
                    }
                    else {

                        $periode_n = $id_valeur_legende;

                        if (!isset($resultats_definitifs_n_moins_1[$id_valeur]))
                            $resultats_definitifs_n_moins_1[$id_valeur][] = [];
                        if (!isset($resultats_definitifs_n_moins_1[$id_valeur][$periode_n]))
                            $resultats_definitifs_n_moins_1[$id_valeur][$periode_n] = 0;

                        foreach ($resultats_cumule_n_moins_1 as $resultats_n_moins_1){
                            foreach ($resultats_n_moins_1 as $resultat_n_moins_1) {

                                if ($resultat_n_moins_1->$champ_groupe_par == $id_valeur && $resultat_n_moins_1->date_n == $periode_n){
                                    $resultats_definitifs_n_moins_1[$id_valeur][$periode_n] = floatval($resultat_n_moins_1['total_1']);
                                }

                            }
                        }

                    }

                    if(!isset($resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende]))
                        $resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende] = 0;

                    if(!empty($resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende]))
                        $ancienne_valeur_moins_1 += $resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende];

                    if(!empty($this->parametrage_rapport_libre['resultats_cumules']))
                        $resultats_definitifs_n_moins_1[$id_valeur][$id_valeur_legende] = $ancienne_valeur_moins_1;

                }

                //On ajoute la valeur
                ksort($resultats_definitifs_n_moins_1[$id_valeur]);
                $this->valeur($nom_serie_n_moins_1, array_values($resultats_definitifs_n_moins_1[$id_valeur]), $valeur . ' (N-1)');

            }
        }
    }

    /**
     *
     * Récupère les données pour la vue
     *
     */
    public function parametres_pour_vue($ajax = false) {

        parent::parametres_pour_vue($ajax);

        $this->parametres_pour_vue['legende'] = $this->legende;
        $this->parametres_pour_vue['series'] = $this->series;

        $this->parametres_pour_vue['props_composant']['objectif'] = (object) $this->objectif;
        $this->parametres_pour_vue['props_composant']['nombre_decimales_recap'] = $this->parametrage_rapport_libre['nombre_decimales_recap'] ?? 0;

    }

    /**
     *
     * On récupère les valeurs de l'axe x
     *
     */
    public function recupere_valeurs_axe($series, $champ_axe_x, $valeurs_champ_x, $cumule = false, $champ_axe_x_date) {
        $valeurs_axe_x = [];

        foreach ($valeurs_champ_x as $id_valeur => $valeurs) {
            foreach ($series as $valeurs_serie) {
                if($cumule) {
                    $valeurs_serie['resultats'] = array_merge(...array_values($valeurs_serie['resultats']->toArray()));
                    $valeurs_serie['resultats_n_moins_1'] = array_merge(...array_values($valeurs_serie['resultats_n_moins_1']->toArray()));
                }

                if(empty($this->parametrage_rapport_libre['filtres_rapport_afficher_n_moins_1']) && $champ_axe_x_date){
                    $valeurs_serie_resultats = collect($valeurs_serie['resultats'])->toArray();
                    $derniere_valeur_resultats = collect($valeurs_serie_resultats)->last();
                }
                
                $valeurs_series = collect($valeurs_serie['resultats'])->concat($valeurs_serie['resultats_n_moins_1'] ?? [])->values();

                foreach ($valeurs_series as $resultat) {
                    if(($resultat[$champ_axe_x] == $id_valeur || !empty($this->parametrage_rapport_libre['afficher_sans_valeur'])) && !empty($valeurs_champ_x[$resultat[$champ_axe_x]]) && !isset($valeurs_axe_x[$id_valeur]) || ((isset($resultat['date_n']) && $resultat['date_n'] == $id_valeur) && (empty($derniere_valeur_resultats) || $resultat['date_n'] < $derniere_valeur_resultats[$champ_axe_x])))
                        $valeurs_axe_x[$id_valeur] = $valeurs;
                }

                unset($derniere_valeur_resultats);
            }
        }

        ksort($valeurs_axe_x);

        return $valeurs_axe_x;
    }

    /**
     *
     * Récupère les résultats de la période N-1
     *
     */
    public function recupere_resultats_n_moins_1($serie_management, $resultats, $champ_axe_x, $champ_date_n_moins_1){
        $resultats_n_moins_1 = $serie_management->recupere_resultats($champ_date_n_moins_1);

        $resultats_pour_legende = $resultats->concat((clone $resultats_n_moins_1)->map(function ($resultat) use ($champ_axe_x) {
            $resultat = clone $resultat;
            $resultat->{$champ_axe_x} = $resultat->date_n ?? $resultat->$champ_axe_x;
            return $resultat;
        }));

        return [$resultats_n_moins_1, $resultats_pour_legende];
    }

    /**
     * 
     * On vérifie les valeurs des filtres pour N-1 et on les transforme si besoin
     * 
     */
    public function valeurs_filtres_n_moins_1(&$valeur_filtre, $management_champ_date_n_moins_1, $valeurs_interdites, $serie_management){

        list($debut, $fin) = !empty($valeur_filtre['valeurs']['variable']) ? $management_champ_date_n_moins_1->transforme_variable_date($valeur_filtre['valeurs']['variable']) : [$valeur_filtre['valeurs']['debut'], $valeur_filtre['valeurs']['fin']]; 

        if(empty($debut) || empty($fin) || in_array($fin, $valeurs_interdites) || in_array($debut, $valeurs_interdites)){
            return false;
        }
                                        
        $date_debut = new DateTime($debut);
        $date_fin = new DateTime($fin);

        if($this->champ_axe_x_date){
            $valeur_periodicite = $serie_management->interval_date_n($this->parametrage_rapport_libre['periodicite_n_moins_1'] ?? $this->parametrage_rapport_libre['periodicite']);
            $valeurs_filtre = empty($valeur_filtre['valeurs']['variable']) ? [$valeur_filtre['valeurs']['debut'], $valeur_filtre['valeurs']['fin']] : false;
                                            
            list($valeur_filtre['valeurs']['debut'], $valeur_filtre['valeurs']['fin']) = $management_champ_date_n_moins_1->transforme_variable_date($valeur_filtre['valeurs']['variable'], $valeur_periodicite, $valeurs_filtre);
        }else{
            $duree = $date_debut->diff($date_fin);
            $valeur_filtre['valeurs']['fin'] = $date_debut->modify('-1 second')->format('Y-m-d H:i:s');
            $valeur_filtre['valeurs']['debut'] = $date_debut->sub($duree)->format('Y-m-d H:i:s');
        }

        return true;
    }
    /**
     * 
     * On gère le N-1 sur les filtres de la série
     * 
     */
    public function gestion_n_moins_1_filtres_serie($serie_management, $management_element, $serie, $valeurs_interdites, &$management_champ_date_n_moins_1){
        foreach ($serie_management->serie['filtre'] as $filtre){

            foreach($filtre['filtres'] as $valeur_filtre){

                if(!empty($this->parametrage_rapport_libre['filtre_applique_rapport_n_moins_1']) && $this->parametrage_rapport_libre['filtre_applique_rapport_n_moins_1'] == $serie['id']){

                    $type_champ_filtre = Champ_libre::where('nom_sql',$valeur_filtre['nom_sql'])->where('type_element', $valeur_filtre['type_element'])->pluck('type')->first();

                    if(in_array($type_champ_filtre, [4,5,18])){
                        $this->champ_date_n_moins_1 = $valeur_filtre['nom_sql'];
                        $management_champ_date_n_moins_1 = $management_element->champ($this->champ_date_n_moins_1);
                    }   
                }

                if($valeur_filtre['nom_sql'] == $this->champ_date_n_moins_1){

                    if($this->valeurs_filtres_n_moins_1($valeur_filtre, $management_champ_date_n_moins_1, $valeurs_interdites, $serie_management)){
                        $valeurs_filtre_appliques_n_moins_1 = $valeur_filtre;
                        $valeurs_filtre_appliques_n_moins_1['variable'] = '';
                        return $valeurs_filtre_appliques_n_moins_1;
                    }
                }
            }
        }

        return array();
    }
}
