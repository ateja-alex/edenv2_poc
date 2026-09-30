<?php

namespace App\Eden\Managements\Elements;


use App\Eden\Models\Element_image;
use App\Eden\Models\Article_declinaison;
use App\Eden\Models\Liste_libre;

use App\Eden\Controllers\Cron_controller;

use DB;


class Article_management extends Element_management {

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * On traite le cas particulier des coefficients
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

		if(isset($modifications['type_article']) && $modifications['type_article'] == 1){

			$modifications['stockable'] = 0;
		}

		// on vérifie les modifications de code article
		$erreur = $this->verifie_modification_code_article($modifications);

		if($erreur !== true) {

			return $erreur;
		}

		// on vérifie les doublons de code article
		$erreur = $this->verifie_doublon_code_article($modifications);

		if($erreur !== true) {

			return $erreur;
		}

        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true)
            return $retour;

        return true ;
	}

    /**
     *
     * @param $donnees array Les retours de l'enregistrement des sous_formulaires
     * @return true
     *
     */
    public function methodes_post_modification_sous_formulaire($donnees){

        $conditionnement = null;
        $article_fournisseur = null;

        foreach ($donnees['management_pour_retour'] as $management_sous_formulaire){

            if($management_sous_formulaire->_type_element == 'conditionnement')
                $conditionnement = $management_sous_formulaire;

            if($management_sous_formulaire->_type_element == 'article_fournisseur')
                $article_fournisseur = $management_sous_formulaire;

            if($management_sous_formulaire->_type_element == 'article_evolution_prix'){

                $utilisateur_connecte = moi();

                $cron_controller = new Cron_controller('comparer_prix_ventes_aux_evolutions');

                $cron_controller->comparer_prix_ventes_aux_evolutions($this->modele->id);

                unset($cron_controller);

                session()->put('utilisateur_eden', $utilisateur_connecte);

            }

        }

        if(isset($article_fournisseur) && isset($conditionnement))
            $article_fournisseur->enregistre_modele(['conditionnement_id' => $conditionnement->modele->id]);

        return true;

    }

	/**
	 *
	 * On vérifie si l'utilisateur peut modifier le code article
	 *
	 */
	protected function verifie_modification_code_article($modifications) {

		// il n'y a pas de blocage
		if(fonctionnalite('bloquer_modification_code_article') === false)
			return true;

		// il n'y a pas de code article dans les modifications
		if(!isset($modifications['code_article']))
			return true;

		// on est en création
		if(!$this->existe())
			return true;

		// pas de modification
		if($modifications['code_article'] == $this->modele->code_article)
			return true;

		return traduction('messages.php.article.code_article_non_modifiable');
	}

	/**
	 *
	 * On vérifie si avoir des doublons dans les codes articles est autorisé
	 *
	 */
	protected function verifie_doublon_code_article($modifications) {

		// il n'y a pas de blocage
		if(fonctionnalite('bloquer_doublon_code_article') === false)
			return true;

		// il n'y a pas de code article dans les modifications
		if(!isset($modifications['code_article']))
			return true;

		// on est en création
		if(!$this->existe()) {

			$code_article_existant = modele('article')->sans_profils()->avec_inactifs()->where('code_article', $modifications['code_article'])->first();
		}
		// on est en modification
		else {

			$code_article_existant = modele('article')->sans_profils()->avec_inactifs()->where('code_article', $modifications['code_article'])->where('id', '!=', $this->modele->id)->first();
		}

		// pas de modification
		if($code_article_existant === null)
			return true;

		return traduction('messages.php.article.code_article_existant');
	}

	/**
	 *
	 * Retourne les données necessaires pour afficher le contenu d'une page "article" ecommerce
	 *
	 */
	public function charge_donnees_pour_commerce() {

		$donnees = array();

		// les informations sur la famille
		$donnees['article'] = $this->modele;

		// la famille
		$donnees['famille'] = modele('famille', $this->modele->famille_id);

		// les images
		$donnees['images'] = Element_image::where('type_element', 'article')->where('element_id', $this->modele->id)->get();

		// les déclinaisons
		$donnees['declinaisons'] = Article_declinaison::where('article_id', $this->modele->id)->get()->keyBy('id');

		return $donnees;
	}

	/**
	 *
	 * Comment doit on présenter les résultats de la recherche
	 *
	 */
	public function affichage_pour_recherche() {

		return "CONCAT(code_article,' - ',designation) AS affichage_pour_recherche";
	}

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * on vide le cache à l'enregistrement d'un nouvel élément
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		// on vide le cache
        /**
         * @Lucas je commente la ligne car elle prend du temps dans l'enregistrement et n'a pas réellement de raison
         *
         * Voir avec moi si besoin de décommenter pour comprendre pourquoi
         */

        $this->mise_a_jour_prix_parents();

		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}

	/**
	 *
	 * @cf description sur Element_management
	 *
	 * on vide le cache à la suppression d'un nouvel élément
	 *
	 */
	protected function methodes_post_suppression($modele) {

        // On supprime les pack d'articles
	        $article_contenu_pack = modele('article_contenu_pack')->where('article_id', $modele->id)->orWhere('article_enfant_id', $modele->id)->get();
	        foreach($article_contenu_pack as $item) {
	        	$item->delete();
	        }

        // On supprime les compositions d'articles
	        $composition_article = modele('composition_article')->where('article_id', $modele->id)->orWhere('article_enfant_id', $modele->id)->get();
	        foreach($composition_article as $item) {
	        	$item->delete();
	        }

        // On supprime les article fournisseur
	        $article_fournisseur = modele('article_fournisseur')->where('article_id', $modele->id)->get();
	        foreach($article_fournisseur as $item) {
	        	management('article_fournisseur', $item->id, $item)->supprime();
	        }

		parent::methodes_post_suppression($modele);
	}

    /**
     *
     * Supprime un élément
     *
     * @param $modele le modèle que l'on veut supprimer (si non fourni, on se base sur le modèle lié au management)
     *
     * @return true si tout va bien, une erreur (string) si il y a une erreur (impossible de supprimer)
     *
     */
    public function supprime($modele = false) {

        if($modele === false) {

            if(isset($this->modele) && $this->modele->exists === true)
                $modele = $this->modele;
            else {

                exception("Erreur lors de la suppression : le modèle n'a pas été trouvé");
            }
        }

        $article_dans_document = false;

        foreach(\App\Eden\Variables::$documents_gescom_lignes as $document_gescom_ligne){

            $test_si_utilise = modele($document_gescom_ligne)
                ->where('article_id',$this->modele->id)
                ->get()
                ->toArray();

            if($test_si_utilise != null && !empty($test_si_utilise)) {
                $article_dans_document = true;
                break;
            }

        }

        if(fonctionnalite("gescom_bloquer_suppression_article_avec_stock") && ($this->stock_actuel() > 0 || $article_dans_document === true))
            return traduction('messages.php.article.impossible_supprimer_article_avec_stock');

        return parent::supprime($modele);

    }

	/**
	 *
	 * Retourne les champs utilisés pour la recherche ecommerce
	 *
	 */
	public function recherche_pour_ecommerce() {

		$champs = ['code_article', 'designation', 'mots_cles_seo'];

		return $champs;
	}

    /**
	 *
	 * Retourne les champs utilisés pour la recherche ecommerce
	 *
	 */
    public function requete_recherche_pour_ecommerce($champs, $mot_cle) {

       // On effectue la requête SQL sur nos articles
    	$articles = modele('article');
    	$articles = $articles->where($champs[0], 'like', '%'.$mot_cle.'%');
    	unset($champs[0]);
    	forEach($champs as $champ) {
    		$articles = $articles->orWhere($champ, 'like', '%'.$mot_cle.'%');
    	}

    	$articles = $articles->get();

    	return $articles;
    }

	/**
	 *
	 * Retourne l'url de l'image principale pour la liste des articles
	 *
	 */
	public function afficher_image_principale($modele) {

		$image = Element_image::where('type_element', 'article')->where('element_id', $modele->id)->first();

		if($image !== null)
			return '<img src="'.asset('storage/'.$image->chemin).'" style="max-width: 100px; max-height: 100px;" />';
		else
			return '<img src="'.asset('eden/images/article_sans_image.png').'" style="max-width: 100px; max-height: 100px;" />';
	}

	public function retourne_image_principale($modele) {

		$image = Element_image::where('type_element', 'article')->where('element_id', $modele->id)->first();

		if($image !== null)
			return asset('storage/'.$image->chemin);
		else
			return asset('eden/images/article_sans_image.png');
	}

	/**
	 *
	 * Retourne l'url du logo pour la liste des articles
	 *
	 */
	public function afficher_logo($modele) {

		if(!empty($modele->logo))
			return '<img src="'.asset('storage/'.$modele->logo).'" style="max-width: 100px; max-height: 100px;" />';
		else
			return '<img src="'.asset('eden/images/article_sans_image.png').'" style="max-width: 100px; max-height: 100px;" />';
	}

	/**
	 *
	 * Retourne la famille d'analyse de l'article
	 *

	 *
	 */
	public function famille_analyse($parent_id = false) {



		if($parent_id === false) {

			$famille = modele('famille', $this->modele->famille_id);


			$parent_id = $famille->id;


		}
		else {

			$famille = modele('famille', $parent_id);

			$parent_id = $famille->id;
		}


		if(empty($parent_id)) {

			return 0;
		}

		// c'est une famille d'analyse
		if(in_array($parent_id, $this->liste_familles_analyse())) {

			return $parent_id;
		}

		return $this->famille_analyse($famille->parent_id);
	}

	/**
	 *
	 * Liste des familles d'analyse
	 *
	 * Chez certains clients, il y a plusieurs grandes catégories d'articles,
	 * Mais qui ne sont pas forcément le premier niveau de famille
	 * Le but de cette méthode est de retourner cette liste de familles
	 *
	 * Par défaut, nous prenons les familles de premier niveau, mais cela peut être différent en surcharge
	 *
	 */
	public function liste_familles_analyse() {

		/**
		@todo gérer le cache
		*/
		return modele('famille')->where(function($requete) { $requete->where('parent_id', 0)->orWhereNull('parent_id'); })->pluck('id')->toArray();
	}

	/**
	 *
	 * Retourne le stock actuel de l'article, tout entrepots confondus
	 *
	 */
	public function stock_actuel($entrepot_id = false) {

		if(empty($this->modele->stockable))
			return 0;

		$stocks = modele('stocks')
			->select(DB::raw('SUM(stock_actuel) as stock_actuel'))
			->where('article_id', $this->modele->id)
			->groupBy('article_id');

		if($entrepot_id !== false)
			$stocks = $stocks->where('entrepot_id', $entrepot_id);

		$stocks = $stocks->first();

		return empty($stocks) ? 0 : $stocks->stock_actuel;
	}

	/**
	 *
	 * Calcule le stock actuel et le stock en bdd
	 *
	 */
	public function calcule_stock_actuel() {

		$stocks = modele('stocks')
                            ->select(DB::raw('SUM(stock_actuel) as stock_actuel,SUM(stock_reserve) as stock_reserve,SUM(stock_achete) as stock_achete'))
							->where('article_id', $this->modele->id)
							->groupBy('article_id')->first();

        if(empty($stocks))
            return 0;

		$this->enregistre_modele([
			'stock_actuel' => $stocks->stock_actuel,
			'stock_reserve' => $stocks->stock_reserve,
			'stock_a_recevoir' => $stocks->stock_achete,
		]);

		return $stocks->stock_actuel;
	}

	/**
	 *
	 * Retourne le stock réservé de l'article, tout entrepots confondus
	 *
	 */
	public function stock_reserve($entrepot_id = false) {

		if(empty($this->modele->stockable))
			return 0;

		$stocks = modele('stocks')
			->select(DB::raw('SUM(stock_reserve) as stock_reserve'))
			->where('article_id', $this->modele->id)
			->groupBy('article_id');

		if($entrepot_id !== false)
			$stocks = $stocks->where('entrepot_id', $entrepot_id);

		$stocks = $stocks->first();

		return empty($stocks) ? 0 : $stocks->stock_reserve;
	}

	public function stock_previsionnel($article,$stock_initial, $entrepot_id = null){

		$stock_previsionnel = $stock_initial;
		$tous_mouvements_stocks = modele('mouvement_de_stock')
			->select(DB::raw('SUM(quantite) as quantite, article_id'))
			->from('mouvement_de_stock')
			->where('entrepot_id', $entrepot_id)
			->groupBy('article_id')
			->get()
			->pluck('quantite', 'article_id');

		if(isset($tous_mouvements_stocks[$article->id]))
			$stock_previsionnel += $tous_mouvements_stocks[$article->id];

		return $stock_previsionnel;
	}

	/*
	 *
	 *	Calcule le stock prévisionnel de tous les articles d'un entrepôt donné
	 *
	 */
	public function stock_previsionnel_articles($entrepot_id = null, $articles_id = false){

		$stocks_initiaux = modele('stock_initial')->select(DB::raw('SUM(stock_initial) as stock_initial, article_id'));

		if(!empty($entrepot_id))
			$stocks_initiaux = $stocks_initiaux->where('entrepot_id', $entrepot_id);

		$stocks_initiaux = $stocks_initiaux->get()->pluck('stock_initial', 'article_id');

		$tous_mouvements_stocks = modele('mouvement_de_stock')
				->select(DB::raw('SUM(quantite) as quantite, article_id'))
				->from('mouvement_de_stock')
				->groupBy('article_id');

		if($articles_id !== false)
			$tous_mouvements_stocks = $tous_mouvements_stocks->whereIn('article_id', $articles_id);

		if(!empty($entrepot_id))
			$tous_mouvements_stocks = $tous_mouvements_stocks->where('entrepot_id', $entrepot_id);

		$tous_mouvements_stocks = $tous_mouvements_stocks->get()->pluck('quantite', 'article_id');
	    $articles = modele('article')->get();
		foreach($articles as $article){

			$stock_initial = isset($stocks_initiaux[$article->id]) ? $stocks_initiaux[$article->id] : 0;
			$stock_previsionnel[$article->id] = $stock_initial;


			if(isset($tous_mouvements_stocks[$article->id]))
				$stock_previsionnel[$article->id] += $tous_mouvements_stocks[$article->id];
		}

		return $stock_previsionnel;
	}


	/**
	 *
	 *
	 *
	 */
	public function filtres_disponibles_pour_themes_de_filtres() {

		// on va chercher les thèmes de filtres liés à la famille de l'article
		$themes = DB::table('theme_de_filtres_familles')->where('valeur', $this->modele->famille_id)->get();

		$filtres_disponibles = array();

		foreach($themes as $theme) {

			$theme_de_filtres = modele('theme_de_filtres', $theme->cle_locale);
			$filtres = modele('filtre_theme_de_filtres')->where('theme_de_filtres_id', $theme->cle_locale)->get()->pluck('nom', 'id');

			$filtres_disponibles[] = array(

				'theme_de_filtres' => $theme_de_filtres,
				'filtres_dispo' => $filtres,
				'filtres_choisis' => DB::table('article_filtre')->where('article_id', $this->modele->id)->get()->pluck('filtre_id', 'filtre_id'),
			);
		}

		return $filtres_disponibles;
	}

	public function filtre_choisis_themes_de_filtres() {

		$retour = [];

		$themes = $this->filtres_disponibles_pour_themes_de_filtres();

		foreach($themes as $theme) {

			$filtres_dispos = $theme['filtres_dispo'];
			$filtres_choisis = $theme['filtres_choisis'];

			foreach ($filtres_choisis as $id => $osef) {

				if(isset($filtres_dispos[$id]))
					$retour[$id] = $filtres_dispos[$id];
			}
		}

		return $retour;
	}

	protected function recupere_informations_pour_index_recherche(&$sql_set, &$sql_join, &$table_join_count) {

		parent::recupere_informations_pour_index_recherche($sql_set, $sql_join, $table_join_count);

        $table_join_count++;
        $table_subjoin_count = $table_join_count + 1;
        $sql_join .= " LEFT JOIN (
                SELECT 
                    t$table_join_count.id,
                    t$table_join_count.article_id,
                    group_concat(
                        CONCAT(`t$table_join_count`.`reference`, '###', `t$table_subjoin_count`.`nom`)
                        SEPARATOR '###'
                    ) as chaine_tags_recherche 
                FROM `article_fournisseur` t$table_join_count 
                LEFT JOIN `fournisseur` t$table_subjoin_count on t$table_subjoin_count.id = t$table_join_count.fournisseur_id
                GROUP BY t$table_join_count.article_id
            ) t$table_join_count on `t$table_join_count`.article_id = `$this->_type_element`.id";
        $sql_set .= "IFNULL(`t$table_join_count`.`chaine_tags_recherche`, '') , '###', ";
        $table_join_count++;
	}

	/**
	 *
	 * Retourne les codes articles possibles pour un article (interne + ceux des fournisseurs)
	 *
	 */
	public function choix_code_article() {

		$code_articles = [];
		$code_articles[$this->modele->code_article] = 'Interne : '.$this->modele->code_article;

		if(!fonctionnalite('choix_code_article_sur_saisie_document'))
			return $code_articles;


		$article_fournisseur = modele('article_fournisseur')
			->where('article_id', $this->modele->id)
			->get()
			->pluck('reference', 'fournisseur_id')->toArray();

		foreach($article_fournisseur as $fournisseur_id => $reference_fournisseur) {

			if(!empty($reference_fournisseur))
				$code_articles[$reference_fournisseur] = management('fournisseur', $fournisseur_id)->modele->nom.' : '.$reference_fournisseur;
		}

		return $code_articles;
	}

	/**
	 *
	 * On recalcule le prix d'un assemblage ou d'une nomenclature
	 *
	 */
	public function calcule_prix_assemblage_ou_nomenclature() {

		// on n'est pas sur un assemblage ou sur une nomenclature
		if($this->modele->type_article != 3 && $this->modele->type_article != 1)
			return;
        
		$retour = $this->calcule_prix_composition_article();

		// on enregistre
        $this->enregistre_modele(array('tarif' => round($retour['prix_vente'],2), 'prix_d_achat' => round($retour['prix_achat'],2)));

    }

    /**
     *
     * On recalcule le poid d'un assemblage ou d'une nomenclature
     *
     */
    public function calcul_poids_assemblage_ou_nomenclature() {
        
        // on n'est pas sur un assemblage ou sur une nomenclature
        if($this->modele->type_article != 3 && $this->modele->type_article != 1)
            return;

        $retour = $this->calcule_poids_composition_article();

        // on enregistre
        $this->enregistre_modele(array('poids' => round($retour['poids'],2)));
        
        
    }

	/**
	 *
	 *
	 * On calcule le prix d'une composition
	 *
	 */
	public function calcule_prix_composition_article() {

        $compositions = modele('composition_article')
            ->join('article', 'composition_article.article_enfant_id', '=', 'article.id')
            ->leftJoin('conditionnement', 'composition_article.conditionnement', '=', 'conditionnement.id')
            ->select('article.*', 'composition_article.tarif AS tarif_force_vente', 'composition_article.prix_achat AS tarif_force_achat', 'composition_article.quantite AS composition_article_quantite', 'conditionnement.id AS conditionnement_id', 'conditionnement.tarif AS conditionnement_tarif_vente', 'conditionnement.prix_achat AS conditionnement_tarif_achat', 'conditionnement.quantite AS conditionnement_quantite')
            ->where('composition_article.article_id', $this->modele->id)
            ->get();

		$prix_achat = 0;
		$prix_vente = 0;

		foreach($compositions as $composition) {
            $prix_d_achat = $composition->prix_d_achat;

            $quantite_pa = $composition->composition_article_quantite;
            $quantite_pv = $composition->composition_article_quantite;

            if (!empty($composition->tarif_force_achat))
                $prix_d_achat = $composition->tarif_force_achat;
            else if (!empty($composition->conditionnement_id)) {

                if ($composition->conditionnement_tarif_achat > 0)
                    $prix_d_achat = $composition->conditionnement_tarif_achat;
                else if ($composition->conditionnement_quantite > 0)
                    $quantite_pa *= $composition->conditionnement_quantite;
            }

            $tarif = $composition->tarif;

            if (!empty($composition->tarif_force_vente))
                $tarif = $composition->tarif_force_vente;
            else if (!empty($composition->conditionnement_id)) {
                if ($composition->conditionnement_tarif_vente > 0)
                    $tarif = $composition->conditionnement_tarif_vente;
                else if ($composition->conditionnement_quantite > 0)
                    $quantite_pv *= $composition->conditionnement_quantite;
            } else if (($composition->type_article == 1 || $composition->type_article == 3) && !empty($composition->tarif_force))
                $tarif = $composition->tarif_force;
            
            $prix_achat += $prix_d_achat * $quantite_pa;
            $prix_vente += $tarif * $quantite_pv;
            
        }

		return ['prix_achat' => $prix_achat, 'prix_vente' => $prix_vente];
	}

    /**
     *
     *
     * On calcule le poid d'une composition
     *
     */
    public function calcule_poids_composition_article() {
        
        $compositions = modele('composition_article')
            ->join('article', 'composition_article.article_enfant_id', '=', 'article.id')
            ->select('composition_article.*', 'article.*')
            ->where('composition_article.article_id', $this->modele->id)
            ->get();

        $poids_total = 0;

        foreach($compositions as $composition) {
            
            $poids_total += $composition->poids * $composition->quantite;
        }
        
        return ['poids' => $poids_total];
    }

	/**
	 *
	 * Retourne la promotion affectée au produit. Destinée a être surchargée
	 *
	 */
	public function recupere_promo() {

		return 0;
	}

	/**
	 *
	 * Permet de dupliquer les sous elements se rattachant aux articles
	 *
	 */
	public function sous_elements_a_copier_avec_duplication($types_elements = array()){

        $types_elements = parent::sous_elements_a_copier_avec_duplication($types_elements);

        $types_elements[] = array(
            'type_element' => 'article_categorie_comptable',
            'clef' => 'article_id',
            'hierarchie' => false,
        );

        return $types_elements;
	}

	/**
	 *
	 *
	 * Retourne la vitesse rotation de l'article
	 *
	 */
	public function retourne_vitesse_rotation() {

		$nb_jours = $this->retourne_nombre_de_jours_pour_vitesse_rotation();
		$diviseur = $this->retourne_diviseur_pour_vitesse_rotation();

		// on récupère la date
		$date = date('Y-m-d', strtotime("-$nb_jours days"));

		// on récupère les articles qui sont sortis (donc avec une quantité négative ) durant les $nb_jours derniers jours
		$nombre_articles_sortis = modele('mouvement_de_stock')
			->where(['article_id' => $this->modele->id])
			->where('quantite', '<', 0)
			->whereBetween('date', [$date, date('Y-m-d')])
			->get();


		$vitesse_rotation = round($nombre_articles_sortis->sum('quantite') / $diviseur, 2) * -1;

		// on récupère la date
		$date_12_mois = date('Y-m-d', strtotime("-365 days"));

		// on récupère les articles qui sont sortis (donc avec une quantité négative ) durant les $nb_jours derniers jours
		$nombre_articles_sortis_12_mois = modele('mouvement_de_stock')
			->where(['article_id' => $this->modele->id])
			->where('quantite', '<', 0)
			->whereBetween('date', [$date_12_mois, date('Y-m-d')])
			->get();


		$vitesse_rotation_12_mois = round($nombre_articles_sortis_12_mois->sum('quantite') / 12, 2) * -1;

		return [$nb_jours . ' ' . traduction('messages.php.article.jours') => $vitesse_rotation, '365 ' . traduction('messages.php.article.jours') => $vitesse_rotation_12_mois];
	}

	/**
	 *
	 *
	 * Retourne le nombre de jours pour le calcul de la vitesse de rotation de l'article
	 *
	 */
	public function retourne_nombre_de_jours_pour_vitesse_rotation() {

		return 90;
	}

	/**
	 *
	 *
	 * Retourne le diviseur pour le calcul de la vitesse de rotation de l'article
	 *
	 */
	public function retourne_diviseur_pour_vitesse_rotation() {

		return 3;
	}

	/**
	 *
     * Mis à jour d'invenatire lors d'une modification
     *
	 */

	public function mis_a_jour_date_inventaire(){

	    $this->enregistre([
	        'derniere_date_inventaire' => date('Y-m-d'),
        ]);
    }

	/**
	 *
	 * Retourne les stocks initiaux pour l'article
	 *
	 */
	public function stocks_initiaux() {

		return modele('stock_initial')
				->select(DB::raw('ROUND(SUM(COALESCE(`stock_initial`, 0.00)), 2) as stock, stock_initial.*'))
				->from('stock_initial')
				->where('article_id', $this->modele->id)
				->groupBy('entrepot_id')
				->get();

	}

	/**
	 *
	 * Retourne le detail du stock initial
	 *
	 */
	public function stock_initial_detail($entrepot_id){

		$stock_initiaux = modele('stock_initial')
                            ->where('article_id', $this->modele->id)
                            ->where('entrepot_id', $entrepot_id)
                            ->get();

        foreach($stock_initiaux as $key => $stock_initial){

            $documents = ['acompte_vente_lignes', 'avoir_vente_lignes', 'bl_vente_lignes', 'commande_vente_lignes', 'devis_vente_lignes', 'facture_vente_lignes','acompte_achat_lignes', 'avoir_achat_lignes', 'bl_achat_lignes', 'commande_achat_lignes', 'devis_achat_lignes', 'facture_achat_lignes'];

            foreach($documents as $document){

                $test_si_utilise = modele($document)
                    ->where('conditionnement',$stock_initial['conditionnement_id'])
                    ->where('article_id',$this->modele->id)
                    ->get()
                    ->toArray();

                if($test_si_utilise != null && !empty($test_si_utilise))
                    break;

            }

            if($test_si_utilise != null && !empty($test_si_utilise)){
                $stock_initiaux[$key]['utilise'] = true;
            }

        }

        return $stock_initiaux;

	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);

        $liste_options[] = 'zoom';

        return $liste_options;
    }

    /**
     *
     * Retourne les actions sur les listes
     * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
     *
     */
    public function actions_a_afficher($id_liste) {

        $actions = parent::actions_a_afficher($id_liste);

        if(in_array('fiche_article_categorie_comptable_article',fiche('article')->modules_utilises()))
            $actions['importer_categories_comptables'] = '<span class="dropdown-item" @click="article_a_copier = {article_id :0};modale_copie_categories_comptables = true"><i class="fa fa-fw fa-copy"></i><span v-html="$root.traduction(\'modules_sur_fiche.article_categorie_comptable.importer\')"></span></span>';

        return $actions;

    }

	/**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

		$liste_libre = Liste_libre::where('type_element','stocks')
            ->where('id_rapport','fiche_article_stocks')->first();

        if(empty($liste_libre))
            exception("Aucun rapport detail_ligne_stocks n'a été trouvé");

        // On va chercher les infos qui nous interessent
        $management = management($type_element, $id_element);

        // On appelle la vue qui affiche les infos que l'on veux via un render()
        $vue = "eden::listes.includes.details_ligne_stocks";

        $nombre_par_pages = modele('entrepot')->count() + 1;
        $liste_management = liste('stocks','fiche_article_stocks');

        $filtres_pour_fiche = array(
            'article_id' => $id_element
        );

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id,[
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => $nombre_par_pages,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

        $liste_libre->desactiver_options = 1;

		$commandes_en_cours = array();

		$commandes_clients_lignes = modele('commande_vente_lignes')
										->where('article_id', $id_element)
										->where(function($r) {

											$r->whereIn('transforme', array(0,1))->orWhereNull('transforme');
										})
										->where(function($r) {

											$r->where('quantite', '!=' , 0)->whereNotNull('quantite');
										})
										->get();

		foreach($commandes_clients_lignes as $commande) {

			$commande_entete = management('commande_vente', $commande->document_id);

			$ligne = array(

				'type' => 'Client',
				'tiers' => management('client', $commande->client_id_ligne)->affiche_lien(),
                'reference_document' => $commande_entete->affiche_lien(),
				'date' => $commande_entete->champ('date')->affiche(),
				'quantite' => $commande->quantite,
			);

			$commandes_en_cours[] = $ligne;
		}


		$commandes_fournisseurs_lignes = modele('commande_achat_lignes')
											->where('article_id', $id_element)
											->where(function($r) {

												$r->whereIn('transforme', array(0,1))->orWhereNull('transforme');
											})
											->get();

		foreach($commandes_fournisseurs_lignes as $commande) {

			$commande_entete = management('commande_achat', $commande->document_id);

			$ligne = array(

				'type' => 'Fournisseur',
				'tiers' => management('fournisseur', $commande->fournisseur_id_ligne)->affiche_lien(),
                'reference_document' => $commande_entete->affiche_lien(),
				'date' => $commande_entete->champ('date')->affiche(),
				'quantite' => $commande->quantite,
			);

			$commandes_en_cours[] = $ligne;
		}

		$appro_fournisseur = array();

		$articles_fournisseurs = modele('article_fournisseur')->where('article_id', $id_element)->orderBy('conditionnement_id')->get();

		foreach($articles_fournisseurs as $article_fournisseur) {

			$management_article_fournisseur = management('article_fournisseur', $article_fournisseur->id);

			$ligne = array(

				'conditionnement' => $management_article_fournisseur->champ('conditionnement_id')->affiche(),
				'fournisseur' => $management_article_fournisseur->champ('fournisseur_id')->affiche(),
				'tarif' => $management_article_fournisseur->champ('tarif')->affiche(),
				'disponibilite' => $management_article_fournisseur->champ('disponibilite')->affiche(),
			);

			$appro_fournisseur[] = $ligne;
		}

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_article";

		$vue_render = view($vue, array(
			'liste_stock' => array(
                'management' => $management,
                'id_liste' => $liste_libre->id,
                'type_element' => 'stocks',
                'id_element' => $id_element,
                'liste_libre' => $liste_libre,
                'donnees_liste' => $donnees_liste,
                'filtres_pour_fiche' => $filtres_pour_fiche,
                'id_liste_parent' => $id_liste_parent,
            ),
			'commandes_en_cours' => $commandes_en_cours,
			'appro_fournisseur' => $appro_fournisseur,
		))->render();

		return array('composant' => $vue_render);
	}

    /**
	 *
	 * On change le modèle par défaut pour rajouter article fournisseur
	 *
	 */
    public function modele_par_defaut() {

        $modele = parent::modele_par_defaut();

        $modele->article_fournisseur = management('article_fournisseur')->modele_par_defaut();

        $modele->article_evolution_prix = management('article_evolution_prix')->modele_par_defaut();

        $modele->conditionnement = management('conditionnement')->modele_par_defaut();

        return $modele;
    }

    /**
     *
     * Permet de récupérer le prix achat d'un article notamment via le conditionnement
     *
     */
    public function recuperation_prix_achat_via_conditionnement($conditionnement_id){

        $modele_article = $this->modele;

        $prix_achat = 0;

        // on doit aller chercher le prix, par défaut le prix d'achat de l'article
        if($modele_article->prix_d_achat > 0)
            $prix_achat = $modele_article->prix_d_achat;

        // il y a un conditionnement ? => on essaie d'aller récupérer le prix du conditionnement
        if(!empty($conditionnement_id)) {

            $conditionnement = modele('conditionnement', $conditionnement_id);

            if (!empty($conditionnement->prix_achat))
                $prix_achat = $conditionnement->prix_achat;
            else {

                $article_fournisseur = modele('article_fournisseur')->where('article_id', $modele_article->id)->where('conditionnement_id', $conditionnement_id)->first();

                if ($article_fournisseur !== null && !empty($article_fournisseur->tarif))
                    $prix_achat = $article_fournisseur->tarif;
                else {
                    $article_fournisseur_sans_conditionnement = modele('article_fournisseur')->where('article_id', $modele_article->id)
                        ->where(function ($requete) {
                            $requete->whereNull('conditionnement_id');
                            $requete->orWhere('conditionnement_id', 0);
                        })->first();

                    if ($article_fournisseur_sans_conditionnement !== null && !empty($conditionnement) && !empty($conditionnement->quantite))
                        $prix_achat = $article_fournisseur_sans_conditionnement->tarif * $conditionnement->quantite;
                }
            }
        }

        return $prix_achat;
    }

    /**
     *
     * Permet de récupérer le tarif d'un article notamment via le conditionnement
     *
     */
    public function recuperation_tarif_via_conditionnement($conditionnement_id){

    	$modele_article = $this->modele;

    	$tarif = $modele_article->tarif;

    	$conditionnement = modele('conditionnement')->where('id', $conditionnement_id)->first();

    	if($conditionnement !== null){

    		if($conditionnement->tarif !== null)
    			$tarif = $conditionnement->tarif;

    		else if($conditionnement->quantite !== null)
    			$tarif = $modele_article->tarif * $conditionnement->quantite;
    	}

   		return $tarif;
    }

    public function retourne_sous_formulaire(){

        $retour = parent::retourne_sous_formulaire();

        $retour[] = [
            'type_element_enfant' => 'article_evolution_prix',
            'type_element_remplacement' => 'article_evolution_prix_standard',
            'champ_liaison' => 'article_id',
            'optionnel' => 1,
            'unique' => 1,
        ];
        $retour[] = [
            'type_element_enfant' => 'article_fournisseur',
            'type_element_remplacement' => 'article_fournisseur_standard',
            'champ_liaison' => 'article_id',
            'optionnel' => 1,
            'unique' => 1,
        ];
        $retour[] = [
            'type_element_enfant' => 'conditionnement',
            'type_element_remplacement' => 'conditionnement_standard',
            'champ_liaison' => 'article_id',
            'optionnel' => 1,
            'unique' => 1,
        ];

        return $retour;

    }

    public function mise_a_jour_prix_parents(){

        try {
            $articles_parents = DB::select('
                    WITH RECURSIVE cte AS (
                        SELECT article_enfant_id, article_id
                        FROM composition_article
                        WHERE article_enfant_id = ' . $this->modele->id . '
                        AND COALESCE(composition_article.inactif,0) = 0
                        UNION ALL
                        SELECT t.article_enfant_id, t.article_id
                        FROM composition_article t
                        JOIN cte ON t.article_enfant_id = cte.article_id
                        WHERE COALESCE(t.inactif,0) = 0
                    )
                    SELECT article.*
                    FROM cte
                    JOIN article ON cte.article_id = article.id;
                ');
        }
        catch(\Exception $e){

            $articles_parents = [];

        }

        $articles_parents = modele('article')->hydrate($articles_parents);
        
        foreach($articles_parents as $article_parent){
            
            $management_article_parent = management('article', $article_parent->id, $article_parent);

            $management_article_parent->calcule_prix_assemblage_ou_nomenclature();
            $management_article_parent->calcul_poids_assemblage_ou_nomenclature();

        }
        
    }

    /**
     *
     * Permet de récupérer l'éco-contribution et le tarif actuel de l'éco-contribution pour cette article
     *
     */
    public function eco_contribution($date = null){

        if(empty($date))
            $date = date('Y-m-d');

        if($this->modele->type_article == 1) {

            // Gérer la composition des articles de la maniére la plus performante possible
            $montants = collect(DB::select('WITH RECURSIVE cte AS (
                        SELECT *,composition_article.quantite as quantite_total
                        FROM composition_article
                        WHERE article_id = '.$this->modele->id.'
                        AND COALESCE(composition_article.inactif,0) = 0
                        UNION ALL
                        SELECT t.*,(cte.quantite * t.quantite) as quantite_total
                        FROM composition_article t
                        JOIN cte ON t.article_id = cte.article_enfant_id
                        JOIN article a ON a.id = cte.article_enfant_id
                        WHERE a.type_article = 1
                        AND COALESCE(t.inactif,0) = 0
                    )
            SELECT COALESCE(article.type_application_eco_contribution,0) as type_application_eco_contribution,
            ROUND(SUM(IF(ROUND(montant_eco_contribution.montant * IF(categorie_eco_contribution.unite != "" AND categorie_eco_contribution.unite IS NOT NULL,COALESCE(article.quantite_unite_eco_contribution,1),1),2)< 0.01,0.01,ROUND(montant_eco_contribution.montant * IF(categorie_eco_contribution.unite != "" AND categorie_eco_contribution.unite IS NOT NULL,COALESCE(article.quantite_unite_eco_contribution,1),1),2)) * COALESCE(cte.quantite_total,1)),2) as montant
            FROM cte
            JOIN article ON cte.article_enfant_id = article.id AND article.type_article != 1
            JOIN categorie_eco_contribution ON categorie_eco_contribution.id = article.categorie_eco_contribution_id
            JOIN montant_eco_contribution ON montant_eco_contribution.categorie_eco_contribution_id = categorie_eco_contribution.id
            AND (montant_eco_contribution.inactif IS NULL || montant_eco_contribution.inactif = 0)
            AND montant_eco_contribution.date_debut <= "'.$date.'" AND montant_eco_contribution.date_fin >= "'.$date.'"
            GROUP BY COALESCE(article.type_application_eco_contribution,0);'))->pluck('montant','type_application_eco_contribution')->toArray();

            if(!isset($montants[0]))
                $montants[0] = 0;

            if(!isset($montants[1]))
                $montants[1] = 0;

            return $montants;
        }

        if(empty($this->modele->categorie_eco_contribution_id))
            return [];

        $categorie = modele('categorie_eco_contribution')
            ->where('id',$this->modele->categorie_eco_contribution_id)
            ->first();

        if(empty($categorie))
            return [];

        $eco_contribution = array(
            'categorie_eco_contribution_id' => $categorie->id,
            'application_eco_contribution' => $this->modele->type_application_eco_contribution,
            'quantite_unite_eco_contribution' => $this->modele->quantite_unite_eco_contribution,
        );

        $montant_actuel_eco_contribution = modele('montant_eco_contribution')
            ->where('categorie_eco_contribution_id',$categorie->id)
            ->where('date_debut','<=',$date)
            ->where('date_fin','>=',$date)
            ->first()->montant ?? 0;

        $eco_contribution['tarif_eco_contribution'] = round($montant_actuel_eco_contribution,2);

        return $eco_contribution;
    }

    function affichage_pour_image($champ_libre){
        return $this->modele->designation.'_'.parent::affichage_pour_image($champ_libre);
    }

    public function recupere_categorie_comptable($categorie_comptable) {
        return modele('categorie_comptable_article')
            ->where('article_id',$this->modele->id)
            ->where('categorie_comptable_id',$categorie_comptable)
            ->first();
    }

    public function applique_conditions_commerciales($articles, $parametres){

        $client_id = $parametres['client_id'] ?? null;
        $fournisseur_id = $parametres['fournisseur_id'] ?? null;
        $date_document = $parametres['date_document'] ?? null;
		$catalogue_groupement_id = $parametres['catalogue_groupement_id'] ?? 0;

        if(!empty($fournisseur_id)){

			$requete_conditions = modele('condition_commerciale')
				->select(DB::raw('
					condition_commerciale.*, 
					article_fournisseur.article_id, 
					COALESCE(condition_commerciale.palier_quantite,1) as palier_quantite,
					COALESCE(condition_commerciale.conditionnement,0) as conditionnement,
					IF(type_article IN (1,3),condition_commerciale.tarif,null) as tarif_force,
					IF(type_article IN (1,3),null,condition_commerciale.tarif) as tarif,
					IF(type_article IN (1,3),condition_commerciale.prix_achat,null) as prix_achat_force,
					IF(type_article IN (1,3),null,condition_commerciale.prix_achat) as prix_achat,
					IF(type_article IN (1,3),null,condition_commerciale.prix_achat) as prix_d_achat'
				))
				->join('article_fournisseur', 'article_fournisseur.id', '=', 'condition_commerciale.article_fournisseur_id')
				->join('article', 'article.id', '=', 'article_fournisseur.article_id')
				->join('catalogue_tarif', function($join) use ($date_document, $catalogue_groupement_id) {
					$join->on('catalogue_tarif.id', '=', 'condition_commerciale.catalogue_tarif_id');

					if(!empty($date_document))
						$join->where('catalogue_tarif.date_debut', '<=', $date_document)
							->where(function($where) use ($date_document) {
								$where->whereNull('catalogue_tarif.date_fin')
									->orWhere('catalogue_tarif.date_fin', '>=', $date_document);
							});
					
					if(!empty($catalogue_groupement_id))
						$join->where('catalogue_tarif.catalogue_groupement_id', $catalogue_groupement_id);
				})
				->where('article_fournisseur.fournisseur_id', $fournisseur_id)
				->whereIn('article_fournisseur.article_id', $articles->pluck('id')->toArray());

			$conditions_commerciales = $requete_conditions
				->get()
				->groupBy('article_id');
        }
        else if(!empty($client_id)){

            $conditions_commerciales = modele('condition_commerciale')->hydrate(DB::select("
                WITH RECURSIVE cte AS (
                    SELECT id as article_parent,'article' as type,id as id_type,famille_id,1 as ordre
                    FROM article
                    UNION ALL
                    SELECT article_parent,'famille' as type,famille.id as id_type,famille.parent_id as famille_id,1+cte.ordre as ordre
                    FROM cte
                    JOIN famille ON famille.id = cte.famille_id
                )
                SELECT condition_commerciale.*,
                       valeurs.article_id,
                       COALESCE(palier_quantite,1) as palier_quantite,
                       COALESCE(conditionnement,0) as conditionnement,
                       IF(type_article IN (1,3),condition_commerciale.tarif,null) as tarif_force,
                       IF(type_article IN (1,3),null,condition_commerciale.tarif) as tarif,
                       IF(type_article IN (1,3),condition_commerciale.prix_achat,null) as prix_achat_force,
                       IF(type_article IN (1,3),null,condition_commerciale.prix_achat) as prix_achat,
                       IF(type_article IN (1,3),null,condition_commerciale.prix_achat) as prix_d_achat
                FROM condition_commerciale
                JOIN (
                    SELECT 
                        article.id as article_id,
                        SUBSTRING_INDEX(GROUP_CONCAT(
                            cc.id ORDER BY (IF(cc.article_id IS NULL AND cc.famille_id IS NULL,max_familles.ordre + 1,familles.ordre) + IF(cc.client_id > 0,100,IF(cc.catalogue_tarif_id > 0,200,300))) SEPARATOR ','
                        ), ',', 1) as id_condition
                    FROM article 
                    JOIN cte as familles ON familles.article_parent = article.id
                    JOIN (
                        SELECT MAX(cte.ordre) as ordre, article_parent FROM cte GROUP BY cte.article_parent
                    ) max_familles ON max_familles.article_parent = article.id
                    JOIN (
                        SELECT condition_commerciale.* FROM condition_commerciale
                        LEFT JOIN catalogue_tarif ct ON ct.id = condition_commerciale.catalogue_tarif_id
                        AND COALESCE(ct.inactif,0) = 0
                        WHERE
                            (condition_commerciale.client_id = $client_id 
                            OR (
                                ct.id IS NOT NULL
                                AND ct.date_debut < '$date_document' AND COALESCE(ct.date_fin,'$date_document') >= '$date_document'
                                AND ct.catalogue_groupement_id = $catalogue_groupement_id
                            ) OR (
                                condition_commerciale.catalogue_tarif_id IS NULL AND condition_commerciale.client_id IS NULL
                            )) AND condition_commerciale.article_fournisseur_id IS NULL
                    ) cc ON 
                        (familles.id_type = IF(familles.type = 'article',cc.article_id,cc.famille_id) 
                        OR (cc.article_id IS NULL AND cc.famille_id IS NULL AND familles.type = 'article') )
                        AND COALESCE(cc.inactif,0) = 0
                    WHERE article.id IN (" . implode(',',$articles->pluck('id')->toArray()) . ")
                    GROUP BY article.id,cc.palier_quantite,cc.conditionnement
                ) AS valeurs ON condition_commerciale.id = id_condition
                JOIN article ON article.id = valeurs.article_id"
            ))->groupBy('article_id');
        }

		foreach($articles as $article) {

			$conditions_commerciales_article = $conditions_commerciales[$article->id] ?? collect([]);

			if($conditions_commerciales_article->isEmpty())
				continue;

			if($conditions_commerciales_article
				->where('palier_quantite',1)
				->where('conditionnement',0)->isEmpty()) {

				$object = modele('condition_commerciale');
				$object->tarif = $article->tarif;
				$object->prix_achat = $article->prix_d_achat;
				$object->tarif_force = $article->tarif_force;
				$object->prix_achat_force = null;
				$object->palier_quantite = 1;
				$object->conditionnement = 0;
				$object->remise = 0;

				$conditions_commerciales_article->push($object);
			}

			$article->conditions_commerciales = $conditions_commerciales_article;

			if(!empty($fournisseur_id) && !empty($article->conditionnement_id))
				$condition_a_appliquer = $conditions_commerciales_article->where('conditionnement',$article->conditionnement_id);
			else
				$condition_a_appliquer = $conditions_commerciales_article->where('conditionnement',0);

			if(!empty($article['quantite']))
				$condition_a_appliquer = $condition_a_appliquer->where('palier_quantite','<=',$article['quantite'])
					->sortByDesc('palier_quantite');
			else
				$condition_a_appliquer = $condition_a_appliquer->where('palier_quantite',1);

			$condition_a_appliquer = $condition_a_appliquer->first();
			
			if(!empty($condition_a_appliquer)){

				$condition_a_appliquer->prix_d_achat = $condition_a_appliquer->prix_achat;

				foreach($condition_a_appliquer->toArray() as $champ => $valeur){

					if($valeur === null || in_array($champ,[
							'id','modifie_le','modifie_par','inactif','cree_le','cree_par','cle_externe',
							'catalogue_tarif_id','client_id','famille_id','article_id','palier_quantite',
							'conditionnement','chaine_affichage','chaine_tags_recherche'
						]))
						continue;

					$article->{$champ} = $valeur;
				}

				if(!empty($fournisseur_id) && !empty($condition_a_appliquer->prix_achat))
					$article->tarif = $condition_a_appliquer->prix_achat;
			}
		}
    }
}
