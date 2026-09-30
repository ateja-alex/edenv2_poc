<?php

namespace App\Eden\Managements\Services;

use App\Eden\Models\Table_libre;

/**
 * 
 * Ce service a pour but de permettre de personnaliser les listes standards de l'ERP
 * 
 * !!!!!! Attention !!!!!!
 * Quand vous surchargez une liste avec des valeurs en dur (1,2,3, etc)
 * Si vous jaoutez une nouvelle valeur, merci de prendre de la marge, par exemple si la liste standard va de 1 à 12
 * comme les types d'échanges ci dessous, ajouter un type d'échange avec un ID > 100 par exemple dans la surcharge
 * pour éviter d'avoir des comflits avec les nouvelles valeurs dans Eden s'il venait à y en avoir
 * 
 * @todo permettre de faire cela via l'interface serait un +, notamment toutes les listes "en dur", comme celle ci dessous
 * 
 */
class Listes_formatees_service {

	
	/**
	 * 
	 * Liste #33 : les différents types d'échanges
	 * 
	 */
	public function types_echange() {
		
		return array(
			
			1 => 'Appel entrant',
			2 => 'Appel sortant',
			3 => 'Email entrant',
			4 => 'Email sortant',
			5 => 'RDV physique',
			7 => 'Prospection physique',
			8 => 'Courrier entrant',
			9 => 'Courrier sortant',
			6 => 'WhatsApp',
			10 => 'SMS entrant',
			11 => 'Sms sortant',
			12 => 'Notes internes',
            13 => 'Suivi',
		);
	}

    /**
     *
     * Liste #503 : les différents types de tache todo
     *
     */
    public function types_tache_todo() {

        return array(

            1 => 'Email',
            2 => 'Appel',
            19 => 'Divers tâche 1',
            20 => 'Divers tâche 2',
            21 => 'Divers tâche 3',
            22 => 'Divers tâche 4',
            23 => 'Divers tâche 5',
            24 => 'Divers tâche 6',
            25 => 'Divers tâche 7 ',
            26 => 'Divers tâche 8',
            27 => 'Divers tâche 9',
            28 => 'Divers tâche 10',
        );
    }

    /**
     *
     * Liste #504 : les différents types de tache rdv
     *
     */
    public function types_tache_rdv() {

        return array(

            21 => 'RDV commercial',
            22 => 'Suivi de projet',
            23 => 'Réunion interne',
            24 => 'Intervention',
            25 => 'Formation',
            26 => 'Congés',
            39 => 'Divers RDV',
            40 => 'Divers RDV 2',
            41 => 'Divers RDV 3',
            42 => 'Divers RDV 4',
            43 => 'Divers RDV 5',
            44 => 'Divers RDV 6',
            45 => 'Divers RDV 7',
            46 => 'Divers RDV 8',
            47 => 'Divers RDV 9',
            48 => 'Divers RDV 10',
        );
    }

	public function recupere_modeles_de_document_par_type_element($type_element){
        $liste_par_defaut[0] = 'Par défaut';

        $liste = $liste_par_defaut + modele('modele_de_document')
			->select('modele_de_document.*')
			->join('modele_de_document_type_element', 'modele_de_document.id', '=', 'modele_de_document_type_element.cle_locale')
			->where('valeur', Table_libre::select('id')->where('nom_table_sql', $type_element))
			->get()->pluck('nom', 'id')->toArray();

        return $liste;
    }
	
	/**
	 * 
	 * Liste #100 : Status des devis vente
	 * 
	 */
	public function statuts_devis_vente() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			10 => 'En attente',
			20 => 'Accepté',
			30 => 'Refusé',
			40 => 'Annulé',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #101 : Status des commandes vente
	 * 
	 */
	public function statuts_commandes_vente() {
		
		$statuts = array(
			
			0 => 'Pro forma',
            3 => 'AR à envoyer',
            5 => 'AR envoyé',
			10 => 'Attente réception frs',
			15 => 'Articles disponibles',
			20 => 'En attente acompte',
			25 => 'En attente de mise en prod',
			30 => 'En production',
			40 => 'BL à émettre',
            43 => 'Partiellement expédiée',
			45 => 'Expédiée',
			49 => 'Partiellement facturée',
			50 => 'Facturée',
		);
		
		if(fonctionnalite('gescom_document')['bl_vente']) {
			
			$statuts[49] = 'Partiellement expédiée';
			$statuts[50] = 'Expédiée';
		}
		
		if(fonctionnalite('gescom_document')['bon_preparation_vente']) {
			
			$statuts[49] = 'Partiellement préparée';
			$statuts[50] = 'Préparée';
		}
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #102 : Status des bl vente
	 * 
	 */
	public function statuts_bl_vente() {
		
		$statuts = array(
		
			0 => 'Pro forma',
			10 => 'A facturer',
			13 => 'À préparer',
			15 => 'Partiellement préparé',
			20 => 'Préparé',
			30 => 'Expédié',
			35 => 'Posé sur chantier',
			38 => 'Matériels rendus',
			40 => 'Partiellement facturé',
			50 => 'Facturé',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #540 : Status des bons de préparation vente
	 * 
	 */
	public function statuts_bon_preparation_vente() {
		
		$statuts = array(
		
			0 => 'Pro forma',
			10 => 'À préparer',
			20 => 'Partiellement préparé',
			30 => 'Expédié',
		);
		
		return $statuts;
	}
	
	
	
	/**
	 * 
	 * Liste #103 : Status des acomptes vente
	 * 
	 */
	public function statuts_acomptes_vente() {
		
		$statuts = array(
	
			0 => 'Sans statut',
			10 => 'Non réglé',
			20 => 'Réglé',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #104 : Status des factures vente
	 * 
	 */
	public function statuts_factures_vente() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			5 => 'A envoyer',
			10 => 'Non réglée',
			20 => 'Réglée',
		);

		return $statuts;
	}
	
	/**
	 * 
	 * Liste #107 : Status des avoirs vente
	 * 
	 */
	public function statuts_avoirs_vente() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			10 => 'Attente accord direction',
			20 => 'Non réglé',
			30 => 'Réglé',
		);
		
		return $statuts;
	}


	/**
	 * 
	 * Liste #124 : Status des devis achat
	 * 
	 */
	public function statuts_devis_achat() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			10 => 'En attente',
			20 => 'Accepté',
			30 => 'Refusé',
			40 => 'Annulé',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #125 : Status des commandes achat
	 * 
	 */
	public function statuts_commande_achat() {
		
		$statuts = array(
			
			0 => 'Sans statut',
            3 => 'AR à recevoir',
            5 => 'AR reçu',
			10 => 'Attente réception frs',
			30 => 'Partiellement reçue',
			50 => 'Reçue',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #126 : Status des bl achat
	 * 
	 */
	public function statuts_bl_achat() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			10 => 'Réceptionné',
			50 => 'Facturé',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #127 : Status des acomptes achat
	 * 
	 */
	public function statuts_acompte_achat() {
		
		$statuts = array(
	
			0 => 'Sans statut',
			10 => 'Non réglé',
			20 => 'Réglé',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #128 : Status des factures achat
	 * 
	 */
	public function statuts_facture_achat() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			10 => 'Non réglée',
			20 => 'Réglée',
		);
		
		return $statuts;
	}
	
	/**
	 * 
	 * Liste #129 : Status des avoirs achat
	 * 
	 */
	public function statuts_avoir_achat() {
		
		$statuts = array(
		
			0 => 'Sans statut',
			10 => 'Non réglé',
			// 20 => 'Non réglé',
			30 => 'Réglé',
		);
		
		return $statuts;
	}


}

