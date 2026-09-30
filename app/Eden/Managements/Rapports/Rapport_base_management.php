<?php

namespace App\Eden\Managements\Rapports;

use App\Eden\Models\Rapport_libre;

/**
* Gestion des rapports
*/
class Rapport_base_management {
	
	/**
	 * 
	 * On initialise les données du rapport
	 * 
	 */
	function __construct($id_rapport = '', $titre = '', $sous_titre = '', $libelle_du_rapport = '', $activer_scroll = false) {
		
		if(empty($id_rapport)) {
			
			$backtrace = debug_backtrace();
			
			$id_rapport = str_replace(array('_management.php'), '', strtolower(basename($backtrace[1]['file'])));
		}
		
		
		$this->options = array();
		$this->valeurs_filtre = array();


		// on va chercher le titre en base
		$rapport_libre = Rapport_libre::where('id_rapport', $id_rapport)->first();
		
		if(empty($titre) && !empty($rapport_libre)) {
			
			// on va chercher le titre en base
			$titre = $rapport_libre->titre;
			
		}
		
		$this->id_rapport = $id_rapport;
		$this->titre = $titre;
		$this->sous_titre = $sous_titre;
		$this->libelle_du_rapport = $libelle_du_rapport;
		$this->parametres_pour_vue = array();
		$this->activer_scroll = $activer_scroll;
		
		if(!empty($rapport_libre) && !empty($rapport_libre->parametrage))
			$this->parametrage = json_decode($rapport_libre->parametrage, true);
		else
			$this->parametrage = [];
		
		if(!empty($rapport_libre) && !empty($rapport_libre->parametrage_rapport_libre))
			$this->parametrage_rapport_libre = json_decode($rapport_libre->parametrage_rapport_libre, true);
		else
			$this->parametrage_rapport_libre = [];

        if(!empty($rapport_libre->objectif))
            $this->objectif = json_decode($rapport_libre->objectif, true);
        else if(!empty($this->parametrage_rapport_libre['afficher_objectif'])) {
            $this->objectif = [
                'valeur_objectif' => $this->parametrage_rapport_libre['valeur_objectif'] ?? null,
                'couleur_ligne_objectif' => $this->parametrage_rapport_libre['couleur_ligne_objectif'] ?? null,
                'affichage_ligne_objectif' => $this->parametrage_rapport_libre['affichage_ligne_objectif'] ?? null,
                'texte_objectif' => $this->parametrage_rapport_libre['texte_objectif'] ?? null,
            ];
        } else
            $this->objectif = [];
		
		$this->rapport_libre = $rapport_libre;

        $this->vue_standard = "rapport_base";
        $this->vue_standard_js = null;

	}
	
	/**
	 * 
	 * Permet d'ajouter une option sur un rapport (un filtre par exemple)
	 * 
	 */
	public function option($id_option, $options = array()) {

		$this->options[$id_option] = $options;
		
		return true;
	}

	/**
	 *
	 * Permet d'ajouter une option sur un rapport (un filtre par exemple)
	 *
	 */
	public function valeurs_filtre($id_filtre, $valeurs = array()) {

		$this->valeurs_filtre[] = [
            'id' => $id_filtre,
            'valeurs' => $valeurs,
        ];

		return true;
	}
	
	/**
	 * 
	 * Récupère les données pour la vue
	 * 
	 */
	public function parametres_pour_vue($ajax = false) {
		
		$this->parametres_pour_vue['id_rapport'] = $this->id_rapport;
		$this->parametres_pour_vue['titre_du_rapport'] = $this->titre;
		$this->parametres_pour_vue['sous_titre_du_rapport'] = $this->sous_titre;
		$this->parametres_pour_vue['libelle_du_rapport'] = $this->libelle_du_rapport;
		$this->parametres_pour_vue['activer_scroll'] = $this->activer_scroll;

		$this->parametres_pour_vue['options'] = $this->options;
		$this->parametres_pour_vue['ajax'] = $ajax;

		if(isset($this->valeur))
			$this->parametres_pour_vue['valeur'] = $this->valeur;

		if (isset($this->theme))
			$this->parametres_pour_vue['theme'] = $this->theme;
		else
			$this->parametres_pour_vue['theme'] = null;

        $this->parametres_pour_vue['props_composant'] = [
            'id' => 'graphique_highcharts_' . $this->id_rapport,
            'legendes' => $this->legende ?? [],
            'series_rapport' =>  (object) ($this->series ?? []),
            'titre' => traduction($this->rapport_libre->index_traduction . '.titre'),
        ];

	}
	
	/**
	 * 
	 * A surcharger dans les rapports pour retourner un chiffre indicateur pour le rapport
	 * 
	 */
	public function indicateur() {
		
		return '';
	}

    /**
     *
     * On récupère les filtres du rapport
     *
     */
    public function applique_filtres($management_element = false) {

        if(!$management_element || empty($this->parametrage_rapport_libre['filtres_rapport']))
            return;

        foreach ($this->parametrage_rapport_libre['filtres_rapport'] as $id_filtre => $filtre) {

            if (empty($filtre))
                continue;
            
            $management_champ = $management_element;
            $id_filtre += 1;

            $type_element_champ = $management_element->_type_element;

            if(strpos($filtre, ".") !== false) {
                $filtre = explode(".", $filtre);
                if(!empty($filtre[0]))
                    $type_element_champ = $filtre[0];
                $filtre = $filtre[1];
            }

            if($management_champ->_type_element !=  $type_element_champ)
                $management_champ = management($type_element_champ);

            $champ_management = $management_champ->champ($filtre);

            $this->option($id_filtre, [
                'id' => $id_filtre,
                'nom_sql' => $filtre,
                'type' => $champ_management->modele->type,
                'type_filtre' => $champ_management->type_filtre,
                'type_element' => $champ_management->modele->type_element,
                'type_element_ajax' => $champ_management->modele->type_element_ajax,
                'nom_champ' => $champ_management->modele->nom,
                'index_traduction' => $champ_management->modele->index_traduction.'.nom',
                'modele' => $champ_management->modele,
            ]);
        }
    }
	
	/**
	 * 
	 * Applique les options du rapport pour un rapport paramétrable
	 * 
	 */
	public function recupere_valeurs_filtres($management_element = false) {

        if(!$management_element || empty($this->parametrage_rapport_libre['filtres_rapport']))
            return;

        // on récupère les paramètres
        if(isset($this->parametres))
            $parametres = $this->parametres;
        else
            $parametres = Rapports_management::recupere_parametres($this->id_rapport);

        $filtres = request()->has('filtres') ? collect(request()->get('filtres'))->pluck('valeurs','id')->toArray() : [];

        foreach ($this->parametrage_rapport_libre['filtres_rapport'] as $id_filtre => $filtre) {

            if(empty($filtre))
                continue;
            
            $management_champ = $management_element;
            $id_filtre += 1;

            $valeur = false;

            $type_element_champ = $management_element->_type_element;

            if(strpos($filtre, ".") !== false) {
                $filtre = explode(".", $filtre);
                if(!empty($filtre[0]))
                    $type_element_champ = $filtre[0];
                $filtre = $filtre[1];
            }

            if($management_champ->_type_element !=  $type_element_champ)
                $management_champ = management($type_element_champ);

            $champ_management = $management_champ->champ($filtre);
            $type_element = $champ_management->modele->type_element;

            if(isset($parametres[$type_element . '.' . $filtre]) && request()->has('initialisation'))
                $valeur = $parametres[$type_element . '.' . $filtre];

            if(!empty($filtres[$id_filtre]))
                $valeur = $filtres[$id_filtre];

            // on va enregistrer les valeurs du filtre
            $parametre = array($type_element . '.' . $filtre => $valeur);

            Rapports_management::enregistre_parametres($this->id_rapport, $parametre, true);

            if($valeur !== false)
                $this->valeurs_filtre($id_filtre, $valeur);
        }
	}
	
	/**
	 * 
	 * Génère le rapport
	 * 
	 */
	public function genere($ajax = false) {
		
		// on génère les paramètres pour la vue
		$this->parametres_pour_vue($ajax);

        $parametres_pour_vue = array_merge($this->parametres_pour_vue, ['rapport' => $this]);

		if(view()->exists('eden::rapports.'.$this->id_rapport)){
			
			$view = view('eden::rapports.'.$this->id_rapport, $parametres_pour_vue);
		}
		else {
			$view = view('eden::rapports.'.$this->vue_standard, $parametres_pour_vue);
		}

		return $view;
	}
	
	/**
	 * 
	 * Pour les rapports paramétrables
	 * 
	 */	
    public function genere_dans_bloc($ajax = false) {

    	return $this->genere($ajax);
    }
}
