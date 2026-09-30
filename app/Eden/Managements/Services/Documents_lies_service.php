<?php

namespace App\Eden\Managements\Services;

use DB;

use App\Eden\Variables;

class Documents_lies_service {

    /**
	 *
	 * Ajoute un document au tableau des documents liés
	 *
	 */
    public function ajoute_document_au_tableau_des_documents_lies(&$documents_lies, $type_element, $id_document, $lien_direct = false) {

		// on regarde si ce document existe déjà
		foreach($documents_lies as $document) {

			if($document['management']->modele->id == $id_document && $document['type_element'] == $type_element) {

				return;
			}
		}

		// on l'ajoute aux documents liés
		$management_tmp = management($type_element, $id_document);

		// le document n'existe plus
		if($management_tmp->modele === null)
			return;

		// le document n'existe plus
		if($management_tmp->modele->inactif == 1)
			return;

		// le document a été annulé
		if($management_tmp->modele->annule == 1)
			return;

		$table = table_libre($type_element);

		$champs_libres_affichage = ['date','montant_document_ttc'];

		$affichage_champs = array();

		foreach($champs_libres_affichage as $nom_sql){
			$affichage_champs[$nom_sql] = $management_tmp->champ($nom_sql)->affiche();
		}

		// on ajoute le nom du fournisseur pour les commandes fournisseur
		if($type_element == 'commande_achat' && !empty($management_tmp->modele->fournisseur_id))
			$affichage_champs['fournisseur_id'] = management('fournisseur', $management_tmp->modele->fournisseur_id)->affichage_pour_select();

		// on ajoute le nom du client pour les commandes client
		if($type_element == 'commande_vente' && !empty($management_tmp->modele->client_id))
			$affichage_champs['client_id'] = management('client', $management_tmp->modele->client_id)->affichage_pour_select();

		$documents_lies[] = array(
			'type_element' => $table->type_element,
			'type' => $table->element,
			'type_nom' => ucfirst($table->element),
			'modele' => $management_tmp->modele,
			'affichage_lien' => $management_tmp->affiche_lien(),
			'affichage_champs' => $affichage_champs,
			'management' => $management_tmp,
			'badge' => $management_tmp->badge_dans_titre_sur_saisie_document(),
			'lien_direct' => $lien_direct
		);
    }

    /**
	 *
	 * Retourne tous les documents liés à un document (type_element / id_document), en remontant
	 * et en descendant récursivement toute la chaîne des transformations à la ligne
	 * (ex : devis -> commande -> bl -> facture -> avoir, dans les 2 sens)
	 *
	 * Parcours en largeur au niveau document : à chaque palier, une requête indexée par table sur
	 * les seuls documents de la frontière. Le coût dépend de la longueur de la chaîne et non du
	 * volume de la base. S'appuie sur l'index (type_element_source, id_element_source, id_ligne_source).
	 *
	 * Un document déjà retenu peut faire remonter/redescendre vers un autre document sans lien réel
	 * avec celui de départ (ex : une facture qui consolide plusieurs bl, chacun avec son propre bon
	 * de préparation) : c'est voulu, cette méthode retourne large pour l'affichage de la liste des
	 * documents liés. Pour ne retenir que la chaîne d'ascendance/descendance directe, voir
	 * documents_directement_lies()
	 *
	 */
    public function tous_documents_lies_recursif($type_element, $id_document) {

		$types = Variables::$documents_gescom;
		$types_lies = $this->types_lies($types);
		$origine_vente = substr($type_element, -6) === '_vente';

		// le document de départ est toujours retenu, l'appelant l'ignore de lui-même
		$documents_lies = array($type_element.'#'.$id_document => (object) [
			'type_element' => $type_element,
			'id_document' => $id_document
		]);

		$frontiere = array($type_element => array($id_document));

		while(!empty($frontiere)) {

			$suivante = array();

			foreach($frontiere as $type_frontiere => $ids_frontiere) {

				$ids_frontiere = array_values(array_unique($ids_frontiere));

				if(empty($ids_frontiere))
					continue;

				foreach($this->descendants_de($types_lies, $origine_vente, $type_frontiere, $ids_frontiere) as $type_cible => $ids)
					foreach($ids as $id)
						$this->retient_document($documents_lies, $suivante, $type_cible, $id);

				foreach($this->ascendants_de($types, $types_lies, $origine_vente, $type_frontiere, $ids_frontiere) as $type_source => $ids)
					foreach($ids as $id)
						$this->retient_document($documents_lies, $suivante, $type_source, $id);
			}

			$frontiere = $suivante;
		}

		uasort($documents_lies, function($a, $b){

			return strcmp($a->type_element, $b->type_element) ?: $a->id_document <=> $b->id_document;
		});

		return $documents_lies;
    }

    /**
	 *
	 * Retourne l'ensemble des documents de la chaîne d'ascendance/descendance directe d'un document
	 * (type_element / id_document) : contrairement à tous_documents_lies_recursif(), le sens du
	 * parcours ne change jamais en cours de route. Un document atteint en descendant (généré à
	 * partir de la frontière) ne remonte jamais vers ses propres sources, et un document atteint en
	 * ascendant ne redescend jamais vers ses propres cibles - ce qui exclut les documents "frères"
	 * (ex : une facture qui consolide plusieurs bl, chacun avec son propre bon de préparation)
	 *
	 * Retourne un tableau associatif indexé par clé "type_element#id_document" (le document de
	 * départ y figure aussi), à utiliser comme simple ensemble d'appartenance
	 *
	 * @param $descendre : si false, on ne remonte que la chaîne des ascendants
	 *
	 */
    public function documents_directement_lies($type_element, $id_document, $descendre = true) {

		$types = Variables::$documents_gescom;
		$types_lies = $this->types_lies($types);
		$origine_vente = substr($type_element, -6) === '_vente';

		$vus = array($type_element.'#'.$id_document => true);

		$frontiere = array(
			'descendant' => $descendre ? array($type_element => array($id_document)) : array(),
			'ascendant'  => array($type_element => array($id_document)),
		);

		while(!empty($frontiere['descendant']) || !empty($frontiere['ascendant'])) {

			$suivante = array('descendant' => array(), 'ascendant' => array());

			foreach($frontiere['descendant'] as $type_frontiere => $ids_frontiere) {

				$ids_frontiere = array_values(array_unique($ids_frontiere));

				if(empty($ids_frontiere))
					continue;

				foreach($this->descendants_de($types_lies, $origine_vente, $type_frontiere, $ids_frontiere) as $type_cible => $ids)
					foreach($ids as $id)
						$this->retient_cle($vus, $suivante['descendant'], $type_cible, $id);
			}

			foreach($frontiere['ascendant'] as $type_frontiere => $ids_frontiere) {

				$ids_frontiere = array_values(array_unique($ids_frontiere));

				if(empty($ids_frontiere))
					continue;

				foreach($this->ascendants_de($types, $types_lies, $origine_vente, $type_frontiere, $ids_frontiere) as $type_source => $ids)
					foreach($ids as $id)
						$this->retient_cle($vus, $suivante['ascendant'], $type_source, $id);
			}

			$frontiere = $suivante;
		}

		return $vus;
    }

    /**
	 *
	 * Les types qui portent un lien vers leur source (les devis n'en ont pas)
	 *
	 */
    private function types_lies($types) {

		return array_values(array_diff($types, array('devis_vente', 'devis_achat')));
    }

    /**
	 *
	 * Descendants d'une frontière : les documents des types "types_lies" dont une ligne pointe vers
	 * une ligne d'un document de la frontière. Retourne un tableau [type_cible => [id_document, ...]]
	 *
	 */
    private function descendants_de($types_lies, $origine_vente, $type_frontiere, array $ids_frontiere) {

		$resultat = array();

		foreach($types_lies as $type_cible) {

			// une chaîne partie d'une vente ne doit pas rebondir d'un achat vers une vente
			if($this->rebond_achat_vers_vente_interdit($origine_vente, $type_frontiere, $type_cible))
				continue;

			foreach(array_chunk($ids_frontiere, 1000) as $lot) {

				$documents = DB::select("
					SELECT DISTINCT l.document_id
					FROM {$type_cible}_lignes l
					JOIN {$type_frontiere}_lignes s
					  ON s.document_id = l.id_element_source
					 AND s.id          = l.id_ligne_source
					WHERE l.type_element_source = ?
					  AND l.id_element_source IN (".implode(',', $lot).")
				", array($type_frontiere));

				foreach($documents as $document)
					$resultat[$type_cible][] = (int) $document->document_id;
			}
		}

		return $resultat;
    }

    /**
	 *
	 * Ascendants d'une frontière : les documents sources des lignes des documents de la frontière.
	 * Retourne un tableau [type_source => [id_document, ...]]
	 *
	 */
    private function ascendants_de($types, $types_lies, $origine_vente, $type_frontiere, array $ids_frontiere) {

		$resultat = array();

		if(!in_array($type_frontiere, $types_lies, true))
			return $resultat;

		$couples_par_type = array();

		foreach(array_chunk($ids_frontiere, 1000) as $lot) {

			$sources = DB::select("
				SELECT DISTINCT type_element_source, id_element_source, id_ligne_source
				FROM {$type_frontiere}_lignes
				WHERE document_id IN (".implode(',', $lot).")
				  AND type_element_source IS NOT NULL
				  AND type_element_source <> ''
				  AND id_element_source > 0
				  AND id_ligne_source > 0
			");

			foreach($sources as $source) {

				// type_element_source est un champ libre : il peut contenir n'importe quoi
				if(!in_array($source->type_element_source, $types, true))
					continue;

				if($this->rebond_achat_vers_vente_interdit($origine_vente, $type_frontiere, $source->type_element_source))
					continue;

				$couples_par_type[$source->type_element_source][] = '('.(int) $source->id_element_source.','.(int) $source->id_ligne_source.')';
			}
		}

		// on ne retient l'arête que si la ligne source existe réellement
		foreach($couples_par_type as $type_source => $couples) {

			foreach(array_chunk(array_unique($couples), 1000) as $lot) {

				$documents = DB::select("
					SELECT DISTINCT document_id
					FROM {$type_source}_lignes
					WHERE (document_id, id) IN (".implode(',', $lot).")
				");

				foreach($documents as $document)
					$resultat[$type_source][] = (int) $document->document_id;
			}
		}

		return $resultat;
    }

    /**
	 *
	 * Une chaîne partie d'un document de vente ne doit pas rebondir d'un achat vers une vente
	 * (c'est le rebond commande_achat -> commande_vente qui se produisait avant)
	 *
	 */
    private function rebond_achat_vers_vente_interdit($origine_vente, $type_depuis, $type_vers) {

		return $origine_vente
			&& substr($type_depuis, -6) === '_achat'
			&& substr($type_vers, -6) === '_vente';
    }

    /**
	 *
	 * Retient un document s'il n'a pas déjà été vu, et le place dans la frontière du palier suivant
	 *
	 */
    private function retient_document(&$documents_lies, &$suivante, $type_element, $id_document) {

		$id_document = (int) $id_document;

		if($id_document <= 0)
			return;

		$cle = $type_element.'#'.$id_document;

		if(isset($documents_lies[$cle]))
			return;

		$documents_lies[$cle] = (object) [
			'type_element' => $type_element,
			'id_document' => $id_document
		];

		$suivante[$type_element][] = $id_document;
    }

    /**
	 *
	 * Équivalent de retient_document() pour un simple ensemble de clés "type_element#id_document"
	 *
	 */
    private function retient_cle(&$vus, &$suivante, $type_element, $id_document) {

		$id_document = (int) $id_document;

		if($id_document <= 0)
			return;

		$cle = $type_element.'#'.$id_document;

		if(isset($vus[$cle]))
			return;

		$vus[$cle] = true;

		$suivante[$type_element][] = $id_document;
    }
}
