<?php

namespace App\Eden\Champs;

use App\Eden\Variables;
use DateTime;

class Champ_date extends Champ {

	public string $nom_composant = 'input';

    public string $type_filtre = 'filtre-date';
	
	public function __construct($champ_libre, $valeur = false) {
		
		parent::__construct($champ_libre, $valeur);
		
		$this->type = 'text';
		
		if($valeur == '#MAINTENANT#')
			$valeur = date('Y-m-d');
		
		$this->formate_valeur_initiale($valeur);
	}

    public function cree(){

        $this->attr('type', 'date');

        return $this->cree_champ();
    }

	public function formate_valeur_initiale($valeur) {
		
		if(!empty($valeur) && $valeur != '0000-00-00' && $valeur != '00/00/0000')
			$this->valeur = formate_date('Y-m-d', $valeur);
		else
			$this->valeur = '';
	}
	
	public function value($valeur) {
		
		if($valeur == '#MAINTENANT#')
			$valeur = date('Y-m-d');
		
		if(!empty($valeur) && $valeur != '0000-00-00' && $valeur != '00/00/0000')
			$this->valeur = formate_date('Y-m-d', $valeur);
		else
			$this->valeur = '';
		
		return $this;
	}
	
	/**
	 *
	 * Affiche la valeur d'un champ de type date, notamment au format français
	 *
	 */
	public function affiche($valeur = false) {

        $format = 'd/m/Y';

        if($this->modele->format_champ != ""){
            $format = $this->modele->format_champ ;
        }

		if($valeur === false)
			return formate_date($format, $this->valeur);
		else
			return formate_date($format, $valeur);
	}
	
	/**
	 *
	 * Retourne le html pour le champ pour les workflows
	 *
	 */
	public function cree_pour_workflow($nom = 'champ') {
		
		$html = '<select name="parametrage['.$nom.'_'.$this->modele->nom_sql.']" v-model="workflow.parametrage.'.$nom.'_'.$this->modele->nom_sql.'">
					<option value="j_plus_0">Aujourd\'hui</option>
					<option value="j_plus_1">Demain</option>
					<option value="j_plus_2">Après Demain</option>
					<option value="j_plus_3">Dans 3 jours</option>
					<option value="j_plus_4">Dans 4 jours</option>
					<option value="j_plus_5">Dans 5 jours</option>
					<option value="j_plus_6">Dans 6 jours</option>
					<option value="j_plus_7">Dans 1 semaine</option>
					<option value="j_plus_14">Dans 2 semaines</option>
					<option value="j_plus_21">Dans 3 semaines</option>
					<option value="m_plus_1">Dans 1 mois</option>
					<option value="m_plus_2">Dans 2 mois</option>
				</select>';
		
		return $html;		
	}
	
	/**
	 *
	 * Applique les filtres sur les listes (les listes d'éléments génériques)
	 * 
	 */
	public function applique_filtre_sur_requete($filtre, $requete) {
        
		if(is_string($filtre)) {
			$filtre = ['variable' => $filtre];
		}

        $alias_champ = $this->alias_champ_requete();

        $variable_debut = null;
        $variable_fin = null;

        if((empty($filtre['variable']) ||
            $filtre['variable'] == 'annuler_la_variable')
            && empty($filtre['debut']) && empty($filtre['fin']))
            return $requete;

		// est-ce qu'il y a une variable ?
		if(!empty($filtre['variable']))
            list($variable_debut, $variable_fin) = $this->transforme_variable_date($filtre['variable']);
        
        if(!empty($filtre['debut']) || !empty ($variable_debut))
			$debut = date('Y-m-d', strtotime(str_replace('/', '-', ($filtre['debut'] ?? $variable_debut))));

		if(!empty($filtre['fin']) || !empty($variable_fin))
        	$fin = date('Y-m-d 23:59:59', strtotime(str_replace('/', '-', ($filtre['fin'] ?? $variable_fin))));

		// pas de variable, on traite le cas classique
		if(!empty($debut) && !in_array($variable_debut, ['pas_renseigne', 'passe']))
            $requete = $requete->where($alias_champ, '>=', $debut);

		if(!empty($fin) && (empty($debut) || $variable_debut != 'pas_renseigne') && !in_array($variable_fin, ['pas_passe', 'renseigne']))
            $requete = $requete->where($alias_champ, '<=', $fin);

        if($variable_debut == 'passe')
            $requete = $requete->whereNotNull($alias_champ);
        if($variable_debut == 'pas_renseigne') {

            $requete = $requete->where(function($r) use($alias_champ) {

                $r->where($alias_champ, '<', '1970-01-01')->orWhereNull($alias_champ);
            });
        }

		return $requete;
	}

    /**
     *
     * Permet de créer un champ de création spécifique pour les colonnes sur les listes libres de type champ
     *
     */
    public function cree_pour_colonne_champ($valeur = null,$parametres) {

        $valeur = formate_date('Y-m-d', $valeur);

        $html = '<input type="date" value="'.$valeur.'" onChange="'.$parametres['lien_liste'].'.enregistre_modification_depuis_liste('.$parametres['element_id'].', \''.$parametres['type_element'].'\', \''.$parametres['champ'].'\', $(this).val(),\'\','.(isset($parametres['parametres_creation']) ? htmlspecialchars(json_encode($parametres['parametres_creation'])) : 'false').')" />';

        return $html;
    }

    /**
     *
     * Permet de retourner une date de début et de fin à partir d'une variable
     *
     * @param string $variable
     * @return array
     */
    public function transforme_variable_date($variable, $valeur_periodicite = false, $valeurs_filtre = false){

        switch ($variable) {
            case 'pas_renseigne': return [$variable, '1970-01-01'];
            case 'renseigne':     return ['0000-00-00', 'renseigne'];
            case 'passe':         return ['passe', date('Y-m-d H:i:s')];
            case 'pas_passe':     return [date('Y-m-d H:i:s'), 'pas_passe'];
        }

        if(!empty($valeurs_filtre)) 
            [$expr_debut, $expr_fin] = $valeurs_filtre;
        
        if (strpos($variable, '_plus_') !== false) {

            if (substr($variable, 0, 1) == 'j') {
                $le_jour = strtotime('+' . substr($variable, 7) . ' days');
            } elseif (substr($variable, 0, 1) == 'm') {
                $le_jour = strtotime('+' . substr($variable, 7) . ' months');
            }

            return [date('Y-m-d 00:00:00', $le_jour), date('Y-m-d 23:59:59', $le_jour)];
        }

        $map = [
            'cette_annee'        => ['first day of january this year', 'last day of december this year'],
            'annee_derniere'     => ['first day of january last year', 'last day of december last year'],
            'annee_prochaine'    => ['first day of january next year', 'last day of december next year'],
            'depuis_janvier'     => ['first day of january this year', 'today'],
            'ce_mois_ci'         => ['first day of this month',        'last day of this month'],
            'le_mois_dernier'    => ['first day of last month',        'last day of last month'],
            'le_mois_prochain'   => ['first day of next month',        'last day of next month'],
            '3_prochains_mois'   => ['first day of this month',        'last day of +2 months'],
            'aujourdhui'         => ['today',                          'today'],
            'hier'               => ['yesterday',                      'yesterday'],
            'demain'             => ['tomorrow',                       'tomorrow'],
            '7_derniers_jours'   => ['today', 'today', '-7 days', null],
            '7_prochains_jours'  => ['today', 'today', null, '+7 days'],
            '30_prochains_jours' => ['today', 'today', null, '+30 days'],
            '6_prochains_jours'  => ['today', 'today', null, '+6 days'],
            '30_derniers_jours'  => ['today', 'today', '-30 days', null],
            'semaine_precedente' => ['last week monday', 'last week sunday'],
            'cette_semaine'      => ['this week monday', 'this week sunday'],
            'semaine_prochaine'  => ['next week monday', 'next week sunday'],
            'jusqua_dimanche'    => [null, 'this week sunday'],
            'jusqu_a_aujourdhui' => [null, 'today']
        ];

        if(empty($expr_debut) && empty($expr_fin))
            [$expr_debut, $expr_fin, $modif_debut, $modif_fin] = array_pad($map[$variable], 4, null);

        $date_debut = new DateTime($expr_debut);

        if($valeur_periodicite && !empty($date_debut))
            $date_debut = $this->soustraire_periodicite($date_debut, $valeur_periodicite);

        if(!empty($modif_debut))
            $date_debut->modify($modif_debut);

        $date_fin = new DateTime($expr_fin);
        $date_fin->setTime(23, 59, 59);

        if($valeur_periodicite)
            $date_fin = $this->soustraire_periodicite($date_fin, $valeur_periodicite);

        if(!empty($modif_fin))
            $date_fin->modify($modif_fin);

        return [$date_debut->format('Y-m-d'), $date_fin->format('Y-m-d H:i:s')];
    }

    private function soustraire_periodicite($date, string $valeur_periodicite){

        if (!preg_match('/(\d+)\s*MONTH/i', $valeur_periodicite, $matches)) {
            $date->modify('-' . $valeur_periodicite);
            return $date;
        }

        $nombre_mois = (int) $matches[1];
        $jour_original = (int) $date->format('d');
        $dernier_jour_mois_courant = (int) $date->format('t');
        $etait_dernier_jour = ($jour_original === $dernier_jour_mois_courant);

        $date->modify('first day of this month');
        $date->modify('-' . $nombre_mois . ' months');

        $max_jour = (int) $date->format('t');

        if ($etait_dernier_jour) {
            $date->setDate((int) $date->format('Y'), (int) $date->format('m'), $max_jour);
        } else {
            $date->setDate((int) $date->format('Y'), (int) $date->format('m'), min($jour_original, $max_jour));
        }

        return $date;
    }

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' type="date" name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }
}