<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;
use App\Eden\Variables;

use DB;

/**
 * Gestion des fiches projets
 */
class Fiche_projet_management extends Fiche_management {

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

		// on récupère les modules utilisés pour optimiser
		$modules = $this->modules_utilises();

		$projet_management = management('projet', $this->id_element);

		// les participants du projet
		if(fonctionnalite('projet_participants') === true) {

			$donnees['participants_projet'] = $this->participants_projet();

		}

		// la consommation de stock sur le projet
		$donnees['consommation_de_stock'] = $this->consommation_de_stock();

		$donnees['marge_projet'] = $projet_management->calcul_marge();

		// on va ajouter les abonnements
		if(in_array('indicateurs', $modules)) {

			$donnees['indicateurs'] = $this->indicateurs();
		}

		$donnees['heures_details'] = $projet_management->heures_details();

		$donnees['adresses'] = $this->adresses();

		return $donnees;
	}

	public function indicateurs() {

		// on ajoute le nombre d'heures réalisées sur ce projet
		$heures_realisees = modele('feuille_de_temps')
            ->where('type_element', 'projet')
            ->where('element_id', $this->id_element)->sum('duree');

		return array('heures_realisees' => $heures_realisees);
	}

	/**
	 *
	 * Retourne les documents commerciaux pour ce projet
	 *
	 * @return collection
	 *
	 */
	public function commerce($documents_autorises = null) {

		$documents = array();

		foreach(Variables::$documents_gescom as $type_element) {

			if(in_array($type_element, array('acompte_vente', 'acompte_achat')))
				continue;

			if(fonctionnalite('gescom_'.$type_element) && $documents_autorises == null) {

				$documents[$type_element] = modele($type_element)->where('projet_id', $this->id_element)->get();
			}
			elseif($documents_autorises != null){

				foreach($documents_autorises as $key => $d){

					if($key == $type_element && $d == "true"){

						$documents[$type_element] = modele($type_element)->where('projet_id', $this->id_element)->get();


					}
				}
			}
		}

		return $this->prepare_donnees_commerce($documents);
	}

	/**
	 *
	 * Retourne les achats sur document pour ce projet
	 *
	 * @return collection
	 *
	 */
	public function achats() {

		$achats = modele('achat_sur_document')->where('projet_id', $this->id_element)->get();
		foreach($achats as $achat) {

			$achat->nom_fournisseur = management('achat_sur_document', $achat->id)->champ('fournisseur_id')->affiche();
		}

		return $achats;
	}

	/**
	 *
	 * Retourne la consommation des stocks pour ce projet
	 *
	 * @return collection
	 *
	 */
	public function consommation_de_stock() {
		
		$vendus = modele('mouvement_de_stock')
				->select(DB::raw('SUM(quantite) as quantite, article_id'))
				->from('mouvement_de_stock')
				->where('reserve', 1)
				->whereIn('type_document', array('commande_vente'))
				->where('projet_id', $this->id_element)
				->groupBy('article_id')
				->get()
				->pluck('quantite', 'article_id');
				
		$livres = modele('mouvement_de_stock')
				->select(DB::raw('SUM(quantite) as quantite, article_id'))
				->from('mouvement_de_stock')
				->whereIn('type_document', array('bl_vente', 'facture_vente'))
				->zero_ou_null('reserve')
				->where('projet_id', $this->id_element)
				->groupBy('article_id')
				->get()
				->pluck('quantite', 'article_id');
				
		// on va chercher le stock actuel
		$stocks_actuels = modele('mouvement_de_stock')
				->select(DB::raw('SUM(quantite) as quantite, article_id'))
				->from('mouvement_de_stock')
				->zero_ou_null('reserve')
				->groupBy('article_id')
				->get()
				->pluck('quantite', 'article_id');
				
		$stocks_a_terme = modele('mouvement_de_stock')
				->select(DB::raw('SUM(quantite) as quantite, article_id'))
				->from('mouvement_de_stock')
				->groupBy('article_id')
				->get()
				->pluck('quantite', 'article_id');
				
		$stocks_initiaux = modele('stock_initial')
				->select(DB::raw('SUM(stock_initial) as quantite, article_id'))
				->groupBy('article_id')
				->get()
				->pluck('quantite', 'article_id');
				
		
				
		// on va ajouter les livraisons au vendus pour retrouver le nombre d'origine
		foreach($livres as $article_id => $quantite) {
			
			if(!isset($vendus[$article_id])) {

				$vendus[$article_id] = $quantite;
				continue;
			}
			
			$vendus[$article_id] += $quantite;
		}

		

		$articles = array();

		foreach($vendus as $article_id => $quantite) {

			// initialisation du tableau
			$articles[$article_id] = array(

				'article' => management('article', $article_id)->affiche_lien(),
				'vendus' => $quantite * -1,
				'livres' => 0,
				'reliquat' => 0,
				'stock_actuel' => 0,
				'stock_a_terme' => 0,
			);
			
			if(isset($livres[$article_id])) {
				
				$articles[$article_id]['livres'] = $livres[$article_id] * -1;
				$articles[$article_id]['reliquat'] = $quantite * -1 + $livres[$article_id];
			}
			else {
				
				$articles[$article_id]['reliquat'] = $quantite * -1;
			}
			
			$stock_actuel = 0;
			$stock_a_terme = 0;
			
			if(isset($stocks_initiaux[$article_id])) {
				
				$stock_actuel += $stocks_initiaux[$article_id];
				$stock_a_terme += $stocks_initiaux[$article_id];
			}
			
			if(isset($stocks_actuels[$article_id]))
				$stock_actuel += $stocks_actuels[$article_id];
			
			if(isset($stocks_a_terme[$article_id]))
				$stock_a_terme += $stocks_a_terme[$article_id];
			
			$articles[$article_id]['stock_a_terme'] = $stock_a_terme;
			$articles[$article_id]['stock_actuel'] = $stock_actuel;
			
		}

		return $articles;
	}

	/**
	 *
	 * Retourne les participants liés au projet
	 *
	 * @return collection
	 *
	 */
	public function participants_projet() {

		$projet = modele('projet', $this->id_element);

		if(empty($projet->participants))
			return collect(array());

		$participants = json_decode(base64_decode($projet->participants));

		if(empty($participants))
			return collect(array());

		return collect($participants);
	}

    /**
     * @return Array
     *
     * Permet de récupérer les options des fils arianes
     *
     */
    public function options_fil_ariane($donnees){

        $options_fil_ariane = parent::options_fil_ariane($donnees);

        $options_fil_ariane[] = [
            'id' => 'planifier_intervention',
            'ordre' => -2
        ];

        if(fonctionnalite('gescom_gerer_avancement_via_projet') == true)
            $options_fil_ariane[] = [
                'id' => 'facture_avancement',
                'ordre' => -1
            ];

        return $options_fil_ariane;
    }
}
