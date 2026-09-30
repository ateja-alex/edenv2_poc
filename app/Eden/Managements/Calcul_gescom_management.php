<?php

namespace App\Eden\Managements;

use app\Eden\Models\Table_libre;

use DB;

/**
 * Gestion des calculs en gestion commerciale
 */
class Calcul_gescom_management {
	
	public function __construct() {
		
		$this->documents_valides_uniquement = true;
		$this->a_partir_de = false;
		$this->jusqu_a = false;
		$this->groupements = array();
		$this->resultats = null;
		
		$this->debug = false;
		
		$this->panier_moyen_document = false;
		
		// les différents filtres possibles
		$this->where = array();
		$this->whereIn = array();
		$this->whereRaw = array();
		$this->zero_ou_null = array();
		
		// les froms (clé étrangères, comme la table des lignes, ou celle des articles)
		$this->from = array();
		
		$this->select = 'ht';
		
		
		$this->resultat_courant = array(
			
			'total_ht' => 0,
			'total_ttc' => 0,
			'total_solde' => 0,
		);
		
	}
	
	/**
	 * 
	 * Active le mode débugage
	 * 
	 */
	public function debug() {
		
		$this->debug = true;
		
		return $this;
	}
	
	/**
	 * 
	 * Doit on utiliser des documents validés uniquement ?
	 * 
	 * @param $valeur_option BOOL
	 * 
	 * 
	 */
	public function documents_valides_uniquement($valeur_option) {
		
		$this->documents_valides_uniquement = $valeur_option;
		
		return $this;
	}
	
	/**
	 * 
	 * Filtre sur la date de début
	 * 
	 */
	public function a_partir_de($date_debut) {
		
		$this->a_partir_de = formate_date('Y-m-d', $date_debut);
		
		return $this;
	}
	
	/**
	 * 
	 * Filtre sur la date de fin
	 * 
	 */
	public function jusqu_a($date_fin) {
		
		$this->jusqu_a = formate_date('Y-m-d', $date_fin);
		
		return $this;
	}
	
	/**
	 * 
	 * On groupe par date
	 * 
	 */
	public function groupe_par_date() {
		
		$this->groupe_par_date = true;
		
		return $this;
	}
	
	/**
	 * 
	 * Filtre sur les entités (raccourcis pour $this->whereIn('entite_id', $entites))
	 * 
	 */
	public function filtre_entites($entites = array()) {
		
		if(empty($entites))
			return $this;
		
		return $this->whereIn('document.entite_id', $entites);
	}
	
	/**
	 * 
	 * Filtre pour une date mensuelle
	 * 
	 * @param array $dates, $dates = array('date_debut' => ..., 'date_fin' => ...)
	 * 
	 */
	public function filtre_dates_mensuelles($dates) {

        $debut = $dates['debut'] ?? $dates['date_debut'];
        $fin = $dates['fin'] ?? $dates['date_fin'];

		return $this->where('date', '>=', $debut)->where('date', '<=', $fin.' 23:59:59');
	}
	
	/**
	 * 
	 * Filtre pour une date quotidienne
	 * 
	 * @param array $dates, $dates = array('date_debut' => ..., 'date_fin' => ...)
	 * 
	 */
	public function filtre_dates_quotidiennes($dates) {
		
		return $this->where('date', '>=', $dates['date_debut'])->where('date', '<=', $dates['date_fin'].' 23:59:59');
	}
	
	/**
	 *
	 * Ajoute un filtre sur les familles d'articles
	 *
	 */
	public function filtre_familles($familles) {
		
		return $this->whereIn('famille_id', $familles);
	}

	/**
	 * 
	 * Ajoute un filtre whereIn à la requete
	 * 
	 */
	public function whereIn($champ, $valeurs = array()) {
		
		if(empty($valeurs))
			return $this;
		
		$this->whereIn[] = array($champ, $valeurs);
		
		return $this;		
	}
	
	/**
	 * 
	 * Ajoute un filtre whereRaw à la requete
	 * 
	 */
	public function whereRaw($champ) {
		
		$this->whereRaw[] = $champ;
		
		return $this;		
	}
	
	/**
	 * 
	 * Ajoute un filtre where à la requete
	 * 
	 */
	public function where($champ, $operateur, $valeur = false) {
		
		if($valeur === false) {
			
			$valeur = $operateur;
			$operateur = '=';
		}
		
		$this->where[] = array($champ, $operateur, $valeur);
		
		return $this;
	}
	
	/**
	 * 
	 * Ajotue un filtre where à la requete de type zero ou null
	 * 
	 */
	public function zero_ou_null($champ) {
		
		$this->zero_ou_null[] = $champ;
		
		return $this;
	}
	
	/**
	 * 
	 * Ajoute au calcul courant les valeur pour $type_document
	 * 
	 */
	public function plus($type_document) {
		
		return $this->ajoute_type_document($type_document, 1);
	}
	
	/**
	 * 
	 * Ajoute (en négatif) au calcul courant les valeur pour $type_document
	 * 
	 */
	public function moins($type_document) {
		
		return $this->ajoute_type_document($type_document, -1);
	}
	
	protected function ajoute_type_document($type_document, $sens) {
		
		// on gère les différents cas de la donnée à récupérer
		if($this->select == 'ht') {
			
			if($this->calcul_montant_via_lignes()) {
				
				$infos = array(
					
					'resultat' => "SUM(".$type_document."_lignes.quantite * ".$type_document."_lignes.tarif * ".$type_document."_lignes.remise_globale_ligne * (100 - ".$type_document."_lignes.remise) / 100) * $sens as resultat",
				);
			}
			else {
				
				$infos = array(
					
					'resultat' => "SUM(montant_document_ht) * $sens as resultat",
				);
			}
		}

		if($this->select == 'ttc') {

			
			if(in_array('article_id', $this->groupements) || in_array('famille_id', $this->groupements)) {
				//@odo à voir avec Fred ou placer le calcul de la tva
				$infos = array(
					
					'resultat' => "SUM(".$type_document."_lignes.quantite * ".$type_document."_lignes.tarif * (100 - ".$type_document."_lignes.remise) / 100) * $sens as resultat",
				);
			}
			else {
				
				$infos = array(
					
					'resultat' => "SUM(montant_document_ttc) * $sens as resultat",
				);

			}
		}
		
		if($this->select == 'nombre_documents') {
			
			$infos = array(
					
				'resultat' => 'COUNT('.$type_document.'.id) * '.$sens.' as resultat',
			);
		}
		
		if($this->select == 'quantite_articles') {
			
			$infos = array(
					
				'resultat' => 'SUM('.$type_document.'_lignes.quantite) * '.$sens.' as resultat',
			);
		}
		
		if($this->select == 'panier_moyen') {
			
			if(in_array('article_id', $this->groupements) || in_array('famille_id', $this->groupements)) {
				
				$infos = array(
					
					'resultat' => "AVG(".$type_document."_lignes.quantite * ".$type_document."_lignes.tarif * (100 - ".$type_document."_lignes.remise) / 100) * $sens as resultat",
				);
			}
			else {
				
				$infos = array(
					
					'resultat' => "AVG(montant_document_ht) * $sens as resultat",
				);
			}
		}
		
		// on ajoute les groupements
		foreach($this->groupements as $colonne) {
			
			if($colonne == 'date_mensuelle') {
				
				$infos[] = "CONCAT(YEAR(date),'-',RIGHT(CONCAT('0',MONTH(date)), 2)) as date_mensuelle";
				
				continue;
			}
			
			if($colonne == 'tva') {
				
				$infos[] = "CAST(tva AS CHAR) as tva";
				
				continue;
			}
			
			
			
			if($colonne == 'entite_id') {
				
				$infos[] = $type_document.'.'.$colonne;
			}
			else {
				
				$infos[] = $colonne;
			}
		}
		
		return $this->execute_requete($type_document, $infos);
	}
	
	/**
	 * 
	 * Execute une requete plus ou moins
	 * 
	 */
	protected function execute_requete($type_document, $infos) {
		
		// $requete = modele($type_document)
					// ->select(DB::raw(implode(',', $infos)))
					// ->join($type_document.'_lignes', $type_document.'_lignes.document_id', $type_document.'.id')
					// ->join('article', $type_document.'_lignes.article_id', 'article.id');
		
		// on ajoute les articles que si nécessaire
		if($this->calcul_montant_via_lignes()) {
			
			$requete = DB::table($type_document."_lignes")
					->select(DB::raw(implode(',', $infos)))
					->where(function($r) use($type_document) {
						
						$r->where($type_document.'.annule', 0);
						$r->orWhereNull($type_document.'.annule');
					})
					->where(function($r) use($type_document) {
						
						$r->where($type_document.'.inactif', 0);
						$r->orWhereNull($type_document.'.inactif');
					});
					
			$requete->join('article', $type_document.'_lignes.article_id', 'article.id');
			$requete->join($type_document, $type_document.'_lignes.document_id', $type_document.'.id');
		}
		else {
			
			$requete = DB::table($type_document)
					->select(DB::raw(implode(',', $infos)))
					->where(function($r) use($type_document) {
						
						$r->where($type_document.'.annule', 0);
						$r->orWhereNull($type_document.'.annule');
					})
					->where(function($r) use($type_document) {
						
						$r->where($type_document.'.inactif', 0);
						$r->orWhereNull($type_document.'.inactif');
					});
		}
		
		// on annule automatiquement les factures annulées de la v1
		$requete = $requete->where(function($r) use ($type_document) { $r->where($type_document.'.annulee_v1', 0)->orWhereNull($type_document.'.annulee_v1'); });
					
		// si on groupe par date, on classe automatiquement par ordre chrono
		if(in_array('date', $this->groupements) || in_array('date_mensuelle', $this->groupements)) {
			
			$requete = $requete->orderBy($type_document.'.date');
		}
		
		// on applique les groupements
		if(count($this->groupements) > 0) {
			
			foreach($this->groupements as $colonne) {
				
				$requete->groupBy($colonne);
			}
		}
		
		// si on veut que les documents validés 		
		if($this->documents_valides_uniquement === true) {
			
			$requete = $requete->where($type_document.'.valide', 1);
		}
		
		// on applique les filtres
		foreach($this->whereIn as $infos) {
			
			$table = str_replace('document.', $type_document.'.', $infos[0]);
			
			$requete = $requete->whereIn($table, $infos[1]);
		}
		
		// on applique les filtres
		foreach($this->whereRaw as $infos) {
			
			$table = str_replace('document.', $type_document.'.', $infos);
			
			$requete = $requete->whereRaw($table);
		}
		
		foreach($this->where as $infos) {
			
			$table = str_replace('document.', $type_document.'.', $infos[0]);
			
			$requete = $requete->where($table, $infos[1], $infos[2]);
		}
		
		foreach($this->zero_ou_null as $nom_sql) {
			
			$nom_sql = str_replace('document.', $type_document.'.', $nom_sql);

			$requete = $requete->where(function($r) use($nom_sql) {
						
						$r->where($nom_sql, 0);
						$r->orWhereNull($nom_sql);
					});
		}
		
		if($this->a_partir_de !== false) {
			
			$requete = $requete->where('date', '>=', $this->a_partir_de);
		}
		
		if($this->jusqu_a !== false) {
			
			$requete = $requete->where('date', '<=', $this->jusqu_a);
		}
		
		if($this->debug === true)
			dd_eden(vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings()));
		
		$resultats = $requete->get();
		
		// dump_eden(array($type_document, $resultats));
		
		$this->agreger_resultats($resultats);
		
		return $this;
	}
	
	/**
	 * 
	 * Agrege les résultats quand on enchaine les méthodes plus() et moins()
	 * 
	 */
	public function agreger_resultats($resultats) {
		
		if(!isset($this->resultats)) {
			
			$this->resultats = $resultats;
			
			return true;
		}

		if(is_bool($this->resultats))
			exception("Le résultat retourné par Calcul_gescom_management est un booléen");
		
		
		// on avait déjà des résultats
		$this->resultats = $this->resultats->merge($resultats);
		
		
		return true;
	}
	
	/**
	 * 
	 * Défini si on peut faire les calculs avec les colonnes de la table entête ou avec la table des lignes.
	 * 
	 * Si on utilise la table des entêtes, dans un SUM(montant_document_ht) et join avec la table lignes,
	 * le montant total du document risque d'être multiplié par le nombre de lignes contenues dans le document.
	 * Il faut alors passer par le calcul via la ligne
	 * 
	 */
	protected function calcul_montant_via_lignes() {
		
		$groupby_qui_necessitent_un_calcul_a_la_ligne = array('article_id', 'famille_id', 'tva');
		
		foreach($groupby_qui_necessitent_un_calcul_a_la_ligne as $champ) {
			
			if(in_array($champ, $this->groupements))
				return true;
		}
		
		$filtres_qui_necessitent_un_calcul_a_la_ligne = array('article_id', 'famille_id', 'tva');
		
		foreach($filtres_qui_necessitent_un_calcul_a_la_ligne as $champ) {
		
			foreach($this->where as $where) {
				
				if($champ == $where[0])
					return true;
			}
			
			foreach($this->whereIn as $where) {
				
				if($champ == $where[0])
					return true;
			}
		}
		
		return false;
	}

	/**
	 * 
	 * Groupe les résultat par la valeur d'une colonne
	 * 
	 */
	public function groupe_par($champ) {
		
		// raccourcis, pour faciliter la vie des dévs
		if($champ == 'article')
			$champ = 'article_id';
		
		if($champ == 'famille')
			$champ = 'famille_id';
		
		if($champ == 'entite')
			$champ = 'entite_id';
		
		if($champ == 'mois')
			$champ = 'date_mensuelle';
		
		if(!in_array($champ, $this->groupements))
			$this->groupements[] = $champ;
		
		return $this;
	}
	
	/**
	 * 
	 * Raccourcis
	 * 
	 */
	public function groupe_par_mois() {
		
		return $this->groupe_par('date_mensuelle');
	}
	
	/**
	 * 
	 * Raccourcis
	 * 
	 */
	public function groupe_par_famille() {
		
		return $this->groupe_par('famille');
	}
	
	/**
	 * 
	 * Raccourcis
	 * 
	 */
	public function groupe_par_entite() {
		
		return $this->groupe_par('entite_id');
	}
	
	/**
	 * 
	 * Raccourcis
	 * 
	 */
	public function groupe_par_client() {
		
		return $this->groupe_par('client_id');
	}
	
	/**
	 * 
	 * Permet de demander le nombre de documents au lieu d'un montant
	 * 
	 */
	public function nombre_documents() {
		
		$this->select = 'nombre_documents';
		
		return $this;
	}
	
	/**
	 * 
	 * Permet de demander le nombre d'articles (la somme des quantités) au lieu d'un montant
	 * 
	 */
	public function quantite_articles() {
		
		$this->select = 'quantite_articles';
		
		return $this;
	}
	
	/**
	 * 
	 * Permet de demander le panier moyen (des documents) au lieu d'un montant
	 * 
	 */
	public function panier_moyen_document() {
		
		// $this->panier_moyen_document = true;
		
		$this->select = 'panier_moyen';
		
		return $this;
	}

	/**
	 * 
	 * Permet de calculer la somme avec la tva (en TTC)
	 * 
	 */
	public function avec_tva() {

		$this->select = 'ttc';

		return $this;
	}
	
    /**
     * 
     * Retourne le résultat du calcul
     * 
     * @return true
     * 
     */
    public function resultat() {
		// on calcule...
		if(!empty($this->groupements)) 
			$this->resultats = $this->resultats->groupBy($this->groupements);
		
		
		$resultats = $this->resultats;
		
		$a_retourner = 0;
		$a_retourner_tableau = array();
		
		foreach($resultats as $cle => $valeurs) {
			
			if(!is_array($valeurs) && property_exists($valeurs, 'resultat')) {
				
				$a_retourner += $valeurs->resultat;
			}
			// il y avait un group by
			else {
				
				foreach($valeurs as $cle_2 => $valeurs_2) {
					
					if(!is_array($valeurs_2) && property_exists($valeurs_2, 'resultat')) {
						
						if(!isset($a_retourner_tableau[$cle]))
							$a_retourner_tableau[$cle] = 0;
						
						$a_retourner_tableau[$cle] += $valeurs_2->resultat;
					}
					// il y avait un 2eme group by...
					else {
						
						// c'est le cas ou il n'y a pas de données
						foreach($valeurs_2 as $cle_3 => $valeurs_3) {

							if(!is_array($valeurs_3) && property_exists($valeurs_3, 'resultat')) {

								if(!isset($a_retourner_tableau[$cle]))
								$a_retourner_tableau[$cle] = array();
							
								if(!isset($a_retourner_tableau[$cle][$cle_2]))
									$a_retourner_tableau[$cle][$cle_2] = 0;
								
									$a_retourner_tableau[$cle][$cle_2] += $valeurs_3->resultat;
							} else {
								
								// il y avait un 3eme group by...
								foreach($valeurs_3 as $cle_4 => $valeurs_4) {
									

									if(!isset($a_retourner_tableau[$cle]))
										$a_retourner_tableau[$cle] = array();
								
									if(!isset($a_retourner_tableau[$cle][$cle_2]))
										$a_retourner_tableau[$cle][$cle_2] = array();

									if(!isset($a_retourner_tableau[$cle][$cle_2][$cle_3]))
										$a_retourner_tableau[$cle][$cle_2][$cle_3] = 0;
									
									$a_retourner_tableau[$cle][$cle_2][$cle_3] += $valeurs_4->resultat;
								}
							}
							
							
						}
					}
					
				}
			}
			
		}
		
		$this->resultats = false;
		
		if(empty($this->groupements)) {
			
			return $a_retourner;
		}
		else {
			
			$this->groupements = array();
			return $a_retourner_tableau;
		}
    }

    
}
