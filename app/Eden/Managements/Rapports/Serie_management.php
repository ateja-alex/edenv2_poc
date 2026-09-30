<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Champ_libre;
use DateTime;
use App\Eden\Variables;


/**
 *
 * Management pour gérer la récupération des données pour les rapports paramétrables
 *
 */
class Serie_management {

    public $serie = [];
    private $rapport = [];

    public function __construct($serie = [],$rapport = []){
        $this->serie = $serie;
        $this->rapport = $rapport;
    }

    /**
	 *
	 * Retourne la liste de valeurs pour les légendes (ou titres pour les tableaux) du rapport
	 *
	 */
	public function recupere_resultats($n_moins1 = false) {

		$type_element = $this->rapport->rapport_libre->type_element;

        if(isset($this->rapport->parametrage_rapport_libre['axe_x'])){
		    $champ_axe_x = $this->rapport->parametrage_rapport_libre['axe_x'];

            if($champ_axe_x == 'serie')
                $champ_axe_x = $this->rapport->parametrage_rapport_libre['axe_y'];
        }
        else if($this->rapport->parametrage_rapport_libre['variable'])
            $champ_axe_x = $this->rapport->parametrage_rapport_libre['variable'];

        $management_element = management($type_element);

		$champ_management = $management_element->champ($champ_axe_x);

        if(!empty($this->rapport->parametrage_rapport_libre['axe_y'])){  
            $champ_axe_y = $this->rapport->parametrage_rapport_libre['axe_y'];

            $champ_management_y = $management_element->champ($champ_axe_y);
        }

		// on va chercher les données
		$requete = modele($type_element);

        if(empty(moi()) && !empty(moi_extranet()))
			$requete = $requete->avec_filtre_extranet();

        $periode = !empty($this->rapport->parametrage_rapport_libre['periodicite']) && $this->periodicite_valide($this->rapport->parametrage_rapport_libre['periodicite']) ? $this->rapport->parametrage_rapport_libre['periodicite'] : null;

        if($champ_management->modele->type == 20){
            $champ_axe_x_pour_requete = $champ_axe_x_pour_group_by = 'COALESCE('.$type_element.'.'.$champ_axe_x.',0)';
            $champ_axe_x_pour_requete .= ' as '.$champ_axe_x;
        } 
        else if(in_array($champ_management->modele->type, array(4,5)))
            list($champ_axe_x_pour_requete,$champ_axe_x_pour_group_by) = $this->select_requete_pour_periode($periode,$type_element.'.'.$champ_axe_x,$champ_axe_x);
        else
		    $champ_axe_x_pour_requete = $champ_axe_x_pour_group_by = $type_element.'.'.$champ_axe_x;
        
        if(!empty($champ_axe_y)){

            if($champ_management_y->modele->type == 20){
                $champ_axe_y_pour_requete = $champ_axe_y_pour_group_by = 'COALESCE('.$type_element.'.'.$champ_axe_y.',0)';
                $champ_axe_y_pour_requete .= ' as '.$champ_axe_y;
            }
            else if(in_array($champ_management_y->modele->type, array(4,5)))
                list($champ_axe_y_pour_requete,$champ_axe_y_pour_group_by) = $this->select_requete_pour_periode($periode,$type_element.'.'.$champ_axe_y,$champ_axe_y);
            else
                $champ_axe_y_pour_requete = $champ_axe_y_pour_group_by = $type_element.'.'.$champ_axe_y;
            
            $champ_axe_x_pour_requete .= ','.$champ_axe_y_pour_requete;
            $champ_axe_x_pour_group_by .= ','.$champ_axe_y_pour_group_by;
        }

        if(!empty($this->rapport->parametrage_rapport_libre['groupe_par'])){

            $champ_groupe_par = $this->rapport->parametrage_rapport_libre['groupe_par'];
            $champ_groupe_par_management = $management_element->champ($this->rapport->parametrage_rapport_libre['groupe_par']);

            if (in_array($champ_groupe_par_management->modele->type, array(4, 5)) && !empty($periode))
                list($champ_groupe_par_pour_requete,$champ_groupe_par_group_by) = $this->select_requete_pour_periode($periode,$type_element.'.'.$champ_groupe_par,$champ_groupe_par);
            else 
                $champ_groupe_par_pour_requete = $champ_groupe_par_group_by = $type_element.'.'.$champ_groupe_par;
        }

		if($this->serie['type_calcul'] == 'sum')
            $select_pour_requete = 'SUM('.$this->serie['champ_calcul'].') as total_1, '.$champ_axe_x_pour_requete;

		elseif($this->serie['type_calcul'] == 'count')
            $select_pour_requete = 'COUNT(*) as total_1, '.$champ_axe_x_pour_requete;

		elseif($this->serie['type_calcul'] == 'avg')
            $select_pour_requete = 'AVG('.$this->serie['champ_calcul'].') as total_1, '.$champ_axe_x_pour_requete;

        if($n_moins1 && in_array($champ_management->modele->type, array(4,5))) {
            list($champ_n_groupe_par_pour_requete, $champ_n_groupe_par_group_by) = $this->select_requete_pour_periode(
                $periode, 'DATE_ADD(' . $champ_axe_x . ',INTERVAL '.$this->interval_date_n($this->rapport->parametrage_rapport_libre['periodicite_n_moins_1'] ?? $this->rapport->parametrage_rapport_libre['periodicite']).')', 'date_n');

            $select_pour_requete .= ',' . $champ_n_groupe_par_pour_requete;
        }

        if(!empty($champ_groupe_par_pour_requete) && isset($select_pour_requete))
            $requete = $requete->select(\DB::raw($select_pour_requete . ',' . $champ_groupe_par_pour_requete));
        else if(isset($select_pour_requete))
            $requete = $requete->select(\DB::raw($select_pour_requete));

        if(isset($champ_groupe_par_group_by))
		    $requete = $requete->groupBy(\DB::raw($champ_groupe_par_group_by . ',' . $champ_axe_x_pour_group_by));
        else
            $requete = $requete->groupBy(\DB::raw($champ_axe_x_pour_group_by));

        if(in_array($champ_management->modele->type, array(4,5)))
            $requete->where($type_element.'.'.$champ_axe_x,'!=','0000-00-00');

        if($champ_management->modele->type != 20)
            $requete->whereNotNull($champ_axe_x);
            
        if(!empty($champ_axe_y)){

            if(in_array($champ_management_y->modele->type, array(4,5)))
                $requete->where($type_element.'.'.$champ_axe_y,'!=','0000-00-00');

            if($champ_management_y->modele->type != 20)
                $requete->whereNotNull($type_element.'.'.$champ_axe_y);

        }

        $requete = $this->applique_filtres_du_rapport($requete, $management_element, $n_moins1);

        if(!empty($this->serie['filtre']))
            $this->applique_filtres_serie($n_moins1, $requete, $type_element, $champ_axe_x);

        if(request()->has('filtres_pour_fiche'))
            $this->applique_filtres_pour_fiche(request()->get('filtres_pour_fiche'), $management_element, $type_element, $requete, $n_moins1);

        return $requete->get();
	}
    
    /**
     * 
     * On applique les filtres de la série (filtres de la recherche avancée dans le paramétrage du rapport).
     * On n'applique pas le filtre en n-1 s'il concerne le champ de l'axe x ou un champ pour lequel on affiche n-1 dans le rapport pour éviter d'avoir des résultats incohérents entre n et n-1
     *
     */
    public function applique_filtres_serie($n_moins1, $requete, $type_element, $champ_axe_x){
        $filtre = $this->serie['filtre'];

        foreach($filtre as $cle_brut => $filtre_brut){

            foreach($filtre_brut['filtres'] as $cle_valeur => $valeur_filtre){

                if($n_moins1 && (($valeur_filtre['nom_sql'] == $champ_axe_x && $this->rapport->champ_axe_x_date == true) || 
                    (!empty($this->rapport->parametrage_rapport_libre['filtres_rapport_afficher_n_moins_1']) && 
                    $valeur_filtre['nom_sql'] == (explode('.', $this->rapport->parametrage_rapport_libre['filtres_rapport_afficher_n_moins_1']['nom'])[1] ?? null)) ||
                    ($this->serie['id'] == ($this->rapport->parametrage_rapport_libre['filtre_applique_rapport_n_moins_1'] ?? null) && $valeur_filtre['nom_sql'] == $this->rapport->champ_date_n_moins_1)
                )){
                    unset($filtre[$cle_brut]['filtres'][$cle_valeur]);
                }
            }
        }

        management('recherche_avancee')->applique_filtrage($filtre, $requete, $type_element);
    }

    public function traitement_filtrage_n_moins1(&$blocs){

        foreach($blocs as &$bloc){

            if(!empty($bloc['blocs']))
                $this->traitement_filtrage_n_moins1($bloc['blocs']);

            if(isset($bloc['filtres'])) {
                foreach($bloc['filtres'] as &$filtre){

                    $champ = champ_libre($filtre['type_element'],$filtre['nom_sql']);

                    if(!in_array($champ->modele->type, [4,5,18]))
                        continue;

                    if(!empty($filtre['valeurs']['variable']))
                        list($variable_debut, $variable_fin) = $champ->champ->transforme_variable_date($filtre['valeurs']['variable']);
                    else{
                        $variable_debut = $filtre['valeurs']['debut'];
                        $variable_fin = $filtre['valeurs']['fin'];
                    }

                    $filtre['valeurs']['debut'] = date('Y-m-d', strtotime($variable_debut . ' -1 year'));
                    $filtre['valeurs']['fin'] = date('Y-m-d', strtotime($variable_fin . ' -1 year'));
                    $filtre['valeurs']['variable'] = null;
                }
            }
        }
    }

	/**
	 *
	 * Applique les filtres du rapport
	 *
	 */
	public function applique_filtres_du_rapport($requete, $management_element, $n_moins_1 = false) {
        
        if(!empty($this->rapport->valeurs_filtre_appliques_n_moins_1)){
            if ($management_element->_type_element != $this->rapport->valeurs_filtre_appliques_n_moins_1['type_element'])
                $management_element = management($this->rapport->valeurs_filtre_appliques_n_moins_1['type_element']);

            $champ = $management_element->champ($this->rapport->valeurs_filtre_appliques_n_moins_1['nom_sql']);

            $requete = $champ->applique_filtre_sur_requete($this->rapport->valeurs_filtre_appliques_n_moins_1['valeurs'], $requete);
        }
            
        foreach ($this->rapport->valeurs_filtre as $valeurs_brutes) {

            $id_filtre = $valeurs_brutes['id'];
            $valeurs = $valeurs_brutes['valeurs'];
            $filtre = $this->rapport->options[$id_filtre];

            if($n_moins_1 && !empty($this->rapport->valeurs_filtre_n_moins_1[$id_filtre]))
                $valeurs = $this->rapport->valeurs_filtre_n_moins_1[$id_filtre];

            if (empty($filtre['nom_sql']))
                continue;

            if ($management_element->_type_element != $filtre['type_element'])
                $management_element = management($filtre['type_element']);

            $champ = $management_element->champ($filtre['nom_sql']);

            if (!empty($filtre['alias']))
                $champ->modele->alias_champ = $filtre['alias'];

            if (!$filtre)
                continue;

            $requete = $champ->applique_filtre_sur_requete($valeurs, $requete);
        }

        return $requete;
	}

	/**
	 *
	 * Retourne la liste de valeurs pour les légendes (ou titres pour les tableaux) du rapport
	 *
	 */
	public function legende($type_element, $champ_axe, $valeurs) {

        $valeurs_legendes = array_unique(array_values($valeurs->pluck($champ_axe)->toArray()));

        sort($valeurs_legendes);

        if(empty($this->rapport->parametrage_rapport_libre['afficher_sans_valeur']))
            $valeurs_legendes = array_values($valeurs_legendes);

        $management_element = management($type_element);

		$champ_management = $management_element->champ($champ_axe);

        if(empty($this->rapport->parametrage_rapport_libre['afficher_sans_valeur']) && in_array($champ_management->modele->type, array(1,20,42))) {
            $valeurs_legendes_affichage = [];
            foreach ($valeurs_legendes as $valeur_legende) {
                $valeurs_legendes_affichage[$valeur_legende] = strip_tags($champ_management->affiche($valeur_legende));
            }
            return $valeurs_legendes_affichage;
        }

		// c'est un champ type liste
		if(in_array($champ_management->modele->type, array(1,20))) {

            // C'est la liste utilisateur et on est pas super_admin
            if($champ_management->modele->liste_choix === 1)
                $valeurs_champ_x = modele('utilisateur')->liste_utilisateurs_visibles()->toArray();
            else
                $valeurs_champ_x = $management_element->champ($champ_axe)->valeurs_possibles;

            if(!isset($valeurs_champ_x[0]))
                $valeurs_champ_x[0] = "Sans valeur";
		}
        else if(in_array($champ_management->modele->type, array(42))){

            $valeurs_champ_x = $champ_management->recuperation_options_select();

            if(!isset($valeurs_champ_x[0]))
                $valeurs_champ_x[0] = "Sans valeur";
        }
		else {
            $valeurs_champ_x = array();

            if(!empty($this->serie['filtre'])) {
                foreach($this->serie['filtre'] as $filtre_brut){
                    foreach($filtre_brut['filtres'] as $valeur_filtre){
                        if($valeur_filtre['nom_sql'] == $champ_axe && in_array($champ_management->modele->type, [4,5,18])){
                            list($debut, $fin) = $this->recupere_bornes($valeur_filtre['valeurs'], $champ_management);
                            $this->ajoute_bornes_legendes($debut, $fin, $valeurs_legendes);
                        }
                    }
                }
            }

            foreach($this->rapport->valeurs_filtre as $valeurs_brutes) {
    
                $id_filtre = $valeurs_brutes['id'];
                $valeurs = $valeurs_brutes['valeurs'];
                $filtre = $this->rapport->options[$id_filtre];
    
                if($filtre['nom_sql'] == $champ_axe && in_array($champ_management->modele->type, [4,5,18])){
                    list($debut, $fin) = $this->recupere_bornes($valeurs, $champ_management);
                    $this->ajoute_bornes_legendes($debut, $fin, $valeurs_legendes);
                }
            }

            $date = date('Y-m-01', strtotime($valeurs_legendes[0] ?? '2000-01-01'));
            $fin = date('Y-m-t', strtotime($valeurs_legendes[count($valeurs_legendes) - 1] ?? 'Y-12-31'));

            if(!empty($this->rapport->parametrage_rapport_libre['periodicite']) && $this->periodicite_valide($this->rapport->parametrage_rapport_libre['periodicite'])) {
                $debut = $date;
                $this->rapport->periodes = $this->periodes($this->rapport->parametrage_rapport_libre['periodicite'], $debut, $fin);
                foreach($this->rapport->periodes as $date) {

                    if(in_array($this->rapport->parametrage_rapport_libre['periodicite'],['hebdomadaire','quotidienne']))
                        $valeurs_champ_x[$date['date_debut']] = $date['nom'];
                    else
                        $valeurs_champ_x[formate_date('Y-m-01', $date['date_debut'])] = $date['nom'];
                }
            } else {
                while($date <= $fin) {
                    $valeurs_champ_x[formate_date('Y-m', $date)] = formate_date('m/Y', $date);
                    $date = date('Y-m-d', strtotime("$date +1 month"));
                }
            }
		}

		return $valeurs_champ_x;
	}

    /**
     * 
     * Ajoute les bornes de filtrage aux légendes si elles ne sont pas déjà présentes dans le cas de filtrage via la recherche avancée dans le paramétrage du rapport
     * 
     */
    public function ajoute_bornes_legendes($debut, $fin, &$valeurs_legendes) {
        if(!empty($debut) && !in_array($debut, $valeurs_legendes))
            array_unshift($valeurs_legendes, $debut);

        if(!empty($fin) && !in_array($fin, $valeurs_legendes))
            $valeurs_legendes[] = $fin;
    }

    /**
     * 
     * Retourne les bornes de filtrage à partir des valeurs du filtre
     * 
     */
    public function recupere_bornes($valeurs, $champ_management) {
        if(!empty($valeurs['variable']))
            return $champ_management->transforme_variable_date($valeurs['variable']);

        return [
            $valeurs['debut'] ?? null,
            $valeurs['fin'] ?? null,
        ];
    }

    /**
     * @param $periode
     *
     * Retourne le select pour obtenir le bon résultat
     *
     */
    public function select_requete_pour_periode($periode,$champ,$alias){

        $champ_pour_group_by = 'LEFT('.$champ.', 7)';

        if($periode == 'quotidienne')
            $champ_pour_group_by = 'LEFT('.$champ.', 10)';
        else if($periode == 'annuelle')
            $champ_pour_group_by = 'CONCAT(LEFT('.$champ.', 4),"-01-01")';
        else if($periode == 'mensuelle')
            $champ_pour_group_by = 'CONCAT(LEFT('.$champ.', 7),"-01")';
        else if($periode == 'semestrielle')
            $champ_pour_group_by = 'CONCAT(LEFT('.$champ.', 4),"-0",IF(QUARTER('.$champ.') <= 2, 1, 7),"-01")';
        else if($periode == 'trimestrielle')
            $champ_pour_group_by = 'CONCAT(LEFT('.$champ.', 4),"-",IF(QUARTER('.$champ.') = 4,"",0),QUARTER('.$champ.')*3-2,"-01")';
        else if($periode == 'hebdomadaire')
            $champ_pour_group_by = 'DATE('.$champ.' - INTERVAL COALESCE(NULLIF(DAYOFWEEK('.$champ.')-2,-1),6) DAY)';

        $champ_pour_requete = $champ_pour_group_by.' as '.$alias;
        
        return array($champ_pour_requete,$champ_pour_group_by);
    }

    /**
     *
     * Retourne toutes les périodes comprises entre 2 dates
     *
     */
    private function periodes_entre_deux_dates($date_debut, $date_fin) {

        $dates = array();
        $dates_sans_weekends = array();

        $date_courante = $date_debut;

        while($date_courante <= $date_fin) {

            $dates[] = array(

                'periode' => date('Y-m-d', strtotime($date_courante)),
                'nom' => date('d/m', strtotime($date_courante)),
            );

            if(!in_array(date('N', strtotime($date_courante)), array(6,7))) {

                $dates_sans_weekends[] = array(

                    'periode' => date('Y-m-d', strtotime($date_courante)),
                    'nom' => date('d/m', strtotime($date_courante)),
                );
            }

            $date_courante = date('Y-m-d', strtotime("$date_courante +1 day"));
        }

        return array($dates, $dates_sans_weekends);
    }

    /**
     *
     * Calcule les périodes entre deux dates
     *
     */
    public function periodes($periodicite, $date_debut, $date_fin) {

        $periodes = array();

        if(empty($date_debut))
            exception(traduction('messages.php.rapport.serie.periodes.tableau_periodes_sans_date_debut'));

        if(empty($date_fin))
            exception(traduction('messages.php.rapport.serie.periodes.tableau_periodes_sans_date_fin'));

        if(!$this->periodicite_valide($periodicite)) {
            \Log::error(traduction('messages.php.rapport.serie.periodes.periode_inexistante'));
            return [];
        }

        if($periodicite == 'hebdomadaire') {

            $date_debut = date('Y-m-d', strtotime("last monday", strtotime("$date_debut +1 day")));
            $date_fin = date('Y-m-d', strtotime("next sunday", strtotime("$date_fin -1 day")));
        }

        if($periodicite == 'trimestrielle') {

            $mois_debut = intval(date('m', strtotime($date_debut)));

            if($mois_debut <= 3)
                $mois_debut = '01';
            else if($mois_debut <= 6)
                $mois_debut = '04';
            else if($mois_debut <= 9)
                $mois_debut = '07';
            else
                $mois_debut = '10';

            $mois_fin = intval(date('m', strtotime($date_fin)));

            if($mois_fin <= 3)
                $mois_fin = '03';
            else if($mois_fin <= 6)
                $mois_fin = '06';
            else if($mois_fin <= 9)
                $mois_fin = '09';
            else
                $mois_fin = '12';

            $date_debut = date('Y-'.$mois_debut.'-01', strtotime($date_debut));
            $date_fin = date('Y-'.$mois_fin.'-t', strtotime($date_fin));
        }

        if($periodicite == 'semestrielle') {

            $mois_debut = intval(date('m', strtotime($date_debut)));

            if($mois_debut <= 6)
                $mois_debut = '01';
            else
                $mois_debut = '07';

            $mois_fin = intval(date('m', strtotime($date_fin)));

            if($mois_fin <= 6)
                $mois_fin = '06';
            else
                $mois_fin = '12';

            $date_debut = date('Y-'.$mois_debut.'-01', strtotime($date_debut));
            $date_fin = date('Y-'.$mois_fin.'-t', strtotime($date_fin));
        }

        if($periodicite == 'annuelle') {

            $date_debut = date('Y-01-01', strtotime($date_debut));
            $date_fin = date('Y-12-31', strtotime($date_fin));
        }

        $decalage_n_moins_1 = match($periodicite) {
            'quotidienne'   => '-1 day',
            'hebdomadaire'  => '-7 days',
            'mensuelle'     => '-1 month',
            'trimestrielle' => '-3 months',
            'semestrielle'  => '-6 months',
            'annuelle'      => '-1 year',
        };

        while($date_debut <= $date_fin) {

            $periode = array();

            $periode['date_debut'] = $date_debut;

            switch($periodicite) {

                case 'quotidienne':
                    $periode['date_fin'] = $date_debut;
                    $periode['nom'] = $date_debut;
                    $date_debut = date('Y-m-d', strtotime($date_debut." +1 day"));
                    break;

                case 'hebdomadaire':
                    $periode['date_fin'] = date('Y-m-d', strtotime($date_debut." +6 days"));
                    $periode['nom'] = "Semaine du ".date('d/m/Y', strtotime($date_debut));
                    $periode['numero_semaine'] = date('W', strtotime($date_debut));
                    $date_debut = date('Y-m-d', strtotime($date_debut." +7 day"));
                    break;

                case 'mensuelle':
                    $periode['periode'] = date('Y-m', strtotime($date_debut));
                    $periode['date_fin'] = date('Y-m-t', strtotime($date_debut));
                    $periode['nom'] = date('m/Y', strtotime($date_debut));
                    $date_debut = date('Y-m-d', strtotime($date_debut." +1 month"));
                    break;

                case 'trimestrielle':
                    $periode['date_fin'] = date('Y-m-t', strtotime($date_debut." +2 months"));

                    if(in_array(date('m', strtotime($date_debut)), array('01', '02', '03')))
                        $periode['nom'] = 'T1 '.date('Y', strtotime($date_debut));

                    if(in_array(date('m', strtotime($date_debut)), array('04', '05', '06')))
                        $periode['nom'] = 'T2 '.date('Y', strtotime($date_debut));

                    if(in_array(date('m', strtotime($date_debut)), array('07', '08', '09')))
                        $periode['nom'] = 'T3 '.date('Y', strtotime($date_debut));

                    if(in_array(date('m', strtotime($date_debut)), array('10', '11', '12')))
                        $periode['nom'] = 'T4 '.date('Y', strtotime($date_debut));

                    $date_debut = date('Y-m-d', strtotime($date_debut." +3 months"));
                    break;

                case 'semestrielle':
                    $periode['date_fin'] = date('Y-m-t', strtotime($date_debut." +5 months"));

                    if(in_array(date('m', strtotime($date_debut)), array('01', '02', '03', '04', '05', '06')))
                        $periode['nom'] = 'S1 '.date('Y', strtotime($date_debut));

                    if(in_array(date('m', strtotime($date_debut)), array('07', '08', '09', '10', '11', '12')))
                        $periode['nom'] = 'S2 '.date('Y', strtotime($date_debut));

                    $date_debut = date('Y-m-d', strtotime($date_debut." +6 months"));
                    break;

                case 'annuelle':
                    $periode['date_fin'] = date('Y-m-t', strtotime($date_debut." +11 months"));

                    $periode['nom'] = date('Y', strtotime($date_debut));

                    $date_debut = date('Y-m-d', strtotime($date_debut." +12 months"));
                    break;
            }

            if(date('m-d', strtotime($periode['date_fin'])) == '02-29')
                $date_fin_n_moins_1 = date('Y-02-28', strtotime($periode['date_fin']." $decalage_n_moins_1"));
            else
                $date_fin_n_moins_1 = date('Y-m-t', strtotime($periode['date_fin']." $decalage_n_moins_1"));

            $periode['date_debut_n_moins_1'] = date('Y-m-d', strtotime($periode['date_debut'] . " $decalage_n_moins_1"));
            $periode['date_fin_n_moins_1'] = $date_fin_n_moins_1;

            $periodes[] = $periode;
        }

        return $periodes;

    }

    public function applique_filtres_pour_fiche($filtres, $management, $type_element, &$requete, $n_moins_1 = false) {

        foreach ($filtres as $colonne => $valeurs) {

            if($colonne == "id") {
                $requete = $requete->where($type_element . '.id', $valeurs);
                continue;
            }

            try {
                $champ = $management->champ($colonne);
            } catch (\Exception | \Throwable $e) {
                continue;
            }

            if(in_array($champ->modele->type, [4,5,18]) && empty(array_filter($valeurs)))
                continue;

            if($n_moins_1 && in_array($champ->modele->type, [4,5,18]) && !empty($valeurs['variable'])) {
                list($debut, $fin) = $champ->transforme_variable_date($valeurs['variable']);
                if(!empty($debut))
                    $valeurs['debut'] = date('Y-m-d', strtotime($debut . ' -1 year'));
                if(!empty($fin))
                    $valeurs['fin'] = date('Y-m-d', strtotime($fin . ' -1 year'));

                $valeurs['variable'] = '';
            }
            else if(in_array($champ->modele->type, [22,42]) && !is_array($valeurs))
                $valeurs = [$valeurs];

            $requete = $champ->applique_filtre_sur_requete($valeurs, $requete);
        }

    }

    public function periodicite_valide($periodicite) {
        return in_array($periodicite, array('quotidienne', 'hebdomadaire', 'mensuelle', 'trimestrielle', 'semestrielle', 'annuelle'));
    }

    /**
     * 
     * Retourne l'intervalle à ajouter pour calculer la date n-1 en fonction de la périodicité
     * 
     */
    public function interval_date_n($periodicite){

        switch($periodicite) {
            case 'quotidienne':   $interval = "1 DAY"; break;
            case 'hebdomadaire':  $interval = "1 WEEK"; break;
            case 'mensuelle':     $interval = "1 MONTH"; break;
            case 'trimestrielle': $interval = "3 MONTH"; break;
            case 'semestrielle':  $interval = "6 MONTH"; break;
            case 'annuelle':      $interval = "1 YEAR"; break;
        }
        
        return $interval;
    }
}
