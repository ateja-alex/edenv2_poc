<?php
namespace App\Eden\Managements\Fiches;
use App\Eden\Managements\Fiche_management;
use App\Eden\Models\Article_fournisseur;
use App\Eden\Models\Article_famille;

use App\Eden\Models\Article_declinaison;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Recurrence;
use App\Eden\Managements\Listes_management;
use App\Eden\Variables;
/**
 * Gestion des fiches d'article
 */
class Fiche_article_management extends Fiche_management {
	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {

		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

		// les indicateurs liés aux documents
		$donnees['indicateurs_facture_vente'] = array();

		$requete_facture_vente = modele('facture_vente')->zero_ou_null('annule')->join('facture_vente_lignes', 'facture_vente_lignes.document_id', 'facture_vente.id')->where('facture_vente_lignes.article_id', $this->id_element);
		$requete_avoir_vente = modele('avoir_vente')->zero_ou_null('annule')->join('avoir_vente_lignes', 'avoir_vente_lignes.document_id', 'avoir_vente.id')->where('avoir_vente_lignes.article_id', $this->id_element);
		$requete_devis_vente = modele('devis_vente')->zero_ou_null('annule')->join('devis_vente_lignes', 'devis_vente_lignes.document_id', 'devis_vente.id')->where('devis_vente_lignes.article_id', $this->id_element);

		$donnees['indicateurs_facture_vente']['nombre_documents'] = $requete_facture_vente->count();
		$donnees['indicateurs_facture_vente']['solde_du'] = $requete_facture_vente->sum('facture_vente.solde_document_ttc');
		$donnees['indicateurs_facture_vente']['nombre_factures_12_mois'] = $requete_facture_vente->where('facture_vente.date', '>=', date('Y-m-d', strtotime('now - 12 months')))->count();
		$donnees['indicateurs_facture_vente']['montant_12_mois'] = $requete_facture_vente->where('facture_vente.date', '>=', date('Y-m-d', strtotime('now - 12 months')))->sum('facture_vente.montant_document_ht');
		$donnees['indicateurs_facture_vente']['montant_12_mois'] -= $requete_avoir_vente->where('avoir_vente.date', '>=', date('Y-m-d', strtotime('now - 12 months')))->sum('avoir_vente.montant_document_ht');

		$donnees['indicateurs_devis_vente']['nombre_documents'] = $requete_devis_vente->count();

		$donnees['indicateurs_avoir_vente']['nombre_documents'] = $requete_avoir_vente->count();

		// les thèmes de filtres
		if(fonctionnalite('theme_de_filtres') === true) {

			$donnees['themes_de_filtres'] = $this->themes_de_filtres();
		}

		// les familles par article
		if(true) {

			$donnees['articles_par_familles'] = $this->articles_par_familles();
		}

        $modules = $this->modules_utilises();

		// les pack d'articles
		if(in_array('pack_articles', $modules)) {

			$donnees['articles_contenu_pack'] = $this->articles_contenu_pack();
		}

		// les déclinaisons
		if(fonctionnalite('fiche_article_declinaisons') === true) {

			$donnees['articles_declinaisons'] = $this->articles_declinaisons();
		}

		if(fonctionnalite('fiche_article_historique_prix_d_achat_de_l_article') === true) {

			$donnees['historique_prix_d_achat'] = $this->historique_prix_d_achat_de_l_article();
		}

		$donnees['entrepots'] = $this->entrepots();

		// on ajoute les infos sur les documents commerciaux
		$this->listes_des_documents($donnees);

        if(in_array('commerce_vente_lignes', $modules))
            $this->listes_des_documents_lignes($donnees);

		$this->listes_des_documents_achats($donnees);

        if(in_array('commerce_achat_lignes', $modules))
		    $this->listes_des_documents_achats_lignes($donnees);

		return $donnees;
	}

	/**
	 *
	 * Récupère les informations concernant les documents commerciaux (pour les listes) + les indicateurs
	 *
	 */
	public function listes_des_documents(&$donnees) {

		// les documents
		foreach(Variables::$documents_vente_gescom as $type_element) {

			if(fonctionnalite('gescom_'.$type_element)) {

				$nombre_documents = modele($type_element)
										->join($type_element.'_lignes', $type_element.'.id', $type_element.'_lignes.document_id')
										->distinct($type_element.'.id')
                                        ->where('article_id', $this->id_element)
										->count($type_element.'.id');

				$donnees['indicateurs_'.$type_element]['nombre_documents'] = $nombre_documents;
			}
		}
	}

    /**
	 *
	 * Récupère les informations concernant les documents lignes commerciaux (pour les listes) + les indicateurs
	 *
	 */
	public function listes_des_documents_lignes(&$donnees) {

		// les lignes
		foreach(Variables::$documents_vente_gescom_lignes_classique as $type_element) {

			$nombre_documents = modele($type_element)
									->distinct($type_element.'.id')
									->where('article_id', $this->id_element)
									->count();

			$donnees['indicateurs_'.$type_element]['nombre_documents'] = $nombre_documents;
		}
	}

	/**
	 *
	 * Récupère les informations concernant les documents commerciaux (pour les listes) + les indicateurs
	 *
	 */
	public function listes_des_documents_achats(&$donnees) {

		// les documents
		foreach(Variables::$documents_achat_gescom as $type_element) {

			if(fonctionnalite('gescom_'.$type_element)) {

				$nombre_documents = modele($type_element)
										->join($type_element.'_lignes', $type_element.'.id', $type_element.'_lignes.document_id')
										->distinct($type_element.'.id')
                                        ->where('article_id', $this->id_element)
										->count($type_element.'.id');

				$donnees['indicateurs_'.$type_element]['nombre_documents'] = $nombre_documents;
			}
		}
	}

    /**
	 *
	 * Récupère les informations concernant les documents commerciaux (pour les listes) + les indicateurs
	 *
	 */
	public function listes_des_documents_achats_lignes(&$donnees) {

		foreach(Variables::$documents_achat_gescom_lignes as $type_element) {

			$nombre_documents = modele($type_element)
									->distinct($type_element.'.id')
									->where('article_id', $this->id_element)
									->count();

			$donnees['indicateurs_'.$type_element]['nombre_documents'] = $nombre_documents;

		}
	}

	/**
	 *
	 * Retourne les documents pour ce client
	 *
	 * @return collection
	 *
	 */
	public function commerce($documents_autorises = null) {

		$documents = array();

		$documents_autorises = session()->get('fiche_filtres_commerce_'.$this->type_element);

		foreach(Variables::$documents_gescom as $type_element) {

			if(in_array($type_element, array('acompte_vente', 'acompte_achat')))
				continue;

			// on n'utilise pas ce type de document
			if(fonctionnalite('gescom_'.$type_element) !== true)
				continue;

			// on regarde si le client a filtré sur ce type de document
			if($documents_autorises != null && (!isset($documents_autorises[$type_element]) || $documents_autorises[$type_element] !== true))
				continue;

			// ok on peut ajouter ces documents

			// on crée la requête par défaut
			$documents[$type_element] = modele($type_element)
					->select(\DB::raw($type_element.'.*'))
					->join($type_element.'_lignes', $type_element.'.id', '=', $type_element.'_lignes.document_id')
					->where('article_id', $this->id_element)
					->orderBy('date', 'DESC')
					->take(1000)
					->get();
		}

		return $this->prepare_donnees_commerce($documents);
	}

	/**
	 *
	 * Retourne les familles par catégorie
	 *
	 */
	public function articles_par_familles() {

		$articles_par_familles = Article_famille::where('article_id', $this->id_element)->get();

		// on recupère les informations de la famille

		foreach($articles_par_familles as $article_par_famille) {

			$article_par_famille->famille = modele('famille', $article_par_famille['famille_id']);
		}

		return $articles_par_familles;
	}

	public function historique_prix_d_achat_de_l_article() {

		// On récupère l'historique des prix d'achats
		$historique_prix_d_achat = modele('facture_achat')
		->join('facture_achat_lignes', 'facture_achat.id', 'facture_achat_lignes.document_id')
		->select('facture_achat_lignes.fournisseur_id_ligne', 'tarif', 'date', 'facture_achat.id')
		->where('facture_achat_lignes.article_id', $this->id_element)
		->orderBy('date', 'desc')
		->take(30)
		->get();

		return $historique_prix_d_achat;
		//
	}

	/**
	 *
	 * Retourne la composition de l'article
	 *
	 */
	public function articles_contenu_pack() {

		$tarifs = modele('article_contenu_pack')->where('article_id', $this->id_element)->get();

		return $tarifs;
	}

	public function modules_disponibles(){
		$modules = parent::modules_disponibles();
		if(isset($modules['commerce_lignes'])){
			$listes_sur_fiches_lignes = Liste_libre::whereIn(
                'id_rapport',
                array_map(fn($type_document) => 'fiche_'.$this->type_element.'_' . $type_document, Variables::documents_gescom_lignes_disponibles())
            )->count();

			if($listes_sur_fiches_lignes == 0)
				unset($modules['commerce_lignes']);
			return $modules;
		}
	}

	/**
	 *
	 * Retourne la liste des déclinaisons
	 *
	 */
	public function articles_declinaisons() {

		$articles = Article_declinaison::where('article_id', $this->id_element)->get();

		return $articles;
	}

	/**
	 *
	 * Retourne les filtres liés à l'article
	 *
	 * Il faut retourner les filtres disponibles en fonction de la catégorie de l'article, et les filtres qui ont été sélectionnés
	 *
	 * @return collection
	 *
	 */
	public function themes_de_filtres() {

		$filtres = management('article', $this->id_element)->filtres_disponibles_pour_themes_de_filtres();

		return collect($filtres);




		$filtres_disponibles = array();

		$article = modele('article', $this->id_element);

		// quels thèmes de filtres pour la catégorie de l'article ?
		$themes_de_filtres = modele('famille')->where('id', $article->famille_id)->first()->theme_de_filtres;


		foreach($themes_de_filtres as $theme_de_filtres) {

			// on va chercher les filtres disponibles
			$filtres_dispo = modele('theme_de_filtres')->find($theme_de_filtres->id)->filtres;

			if(!empty($filtres_dispo))
				$filtres_dispo = $filtres_dispo->pluck('filtre', 'id');

			// on va chercher les filtres sélectionnés
			$filtres_choisis = modele('article')->find($this->id_element)->filtres;

			if(!empty($filtres_choisis))
				$filtres_choisis = $filtres_choisis->pluck('filtre', 'id');

			$filtres_disponibles[] = array(

				'theme_de_filtres' => $theme_de_filtres,
				'filtres_dispo' => $filtres_dispo,
				'filtres_choisis' => $filtres_choisis,
			);
		}
		if($filtres_disponibles === null)
			return collect(array());

		return collect($filtres_disponibles);
	}

	/**
	 *
	 * Retourne la liste des entrepôts
	 *
	 */
	public function entrepots() {
		return modele('entrepot')->get();
	}

	/**
	 *
	 * Retourne les stocks initiaux pour l'article
	 *
	 */
	public function stocks_initiaux() {
		return modele('stock_initial')->where('article_id', $this->id_element)->get();
	}

	/**
	 *
	 * Retourne le seuil de stock minimal
	 *
	 */
	public function stock_seuil_mini() {
		return modele('article', $this->id_element)->stock_seuil_mini;
	}

	/**
	 *
	 * Retourne le seuil de stock d'alerte
	 *
	 */
	public function stock_seuil_alerte() {
		return modele('article', $this->id_element)->stock_seuil_alerte;
	}

	public function recupere_type_element_pour_champ($liste_libre){

		if(in_array($liste_libre->type_element, Variables::$documents_gescom))
            $type_element_pour_champ = $liste_libre->type_element.'_lignes';
        else 
            $type_element_pour_champ = $liste_libre->type_element;
        
        return $type_element_pour_champ;
    }

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        if(in_array('fiche_article_article_categorie_comptable',$this->modules_utilises()))
            $options_fil_ariane[] = [
                'id' => 'copie_article_categorie_comptable',
                'parametres' => [
                    'id_liste' => $donnees['listes_sur_fiche']['fiche_article_article_categorie_comptable']['liste_libre']->id ?? 0,
                    'contexte' => 'fiche'
                ],
                'ordre' => 0
            ];

        return $options_fil_ariane;
    }
}
