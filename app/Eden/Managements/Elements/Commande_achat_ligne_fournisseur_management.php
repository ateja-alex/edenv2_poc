<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Liste_libre;

use DB;

class Commande_achat_ligne_fournisseur_management extends Element_management {
	
	
	/**
	
	@note frédéric: j'ai mis ce code ici car il n'avait plus lieu d'être pour le moment
	Si on crée un ticket qui permet d'annuler une réception (suite à une erreur de saisie par exemple)
	Le code ci dessous est à reprendre, c'est le principe :
	on supprime le BL achat lié à la réception + on remet la ligne en reçue = non
	
	management('bl_achat',$this->modele->bl_achat_id)->supprime();

	$this->enregistre([
		'recu' => 0,
		'bl_achat_id' => null,
	]);
			
	*/
	
	/**
	 *
	 * Impossible de supprimer une ligne commande achat
	 * 
	 */
	public function supprime($modele = false) {
		
		return traduction('messages.php.element.suppression_impossible');
	}

	/**
	 * 
	 * Enregistre la réception totale d'une ligne
	 * 
	 */
	public function enregistre_reception_totale() {
		
		$retour = true;
		
		$management_commande_achat = management('commande_achat',$this->modele->commande_achat_id);
		$commande_achat = $management_commande_achat->modele;
		
		// on va chercher les lignes articles sur la commande achat
		// en fonction du conditionnement
		if(!empty($this->modele->conditionnement_id)) {
			
			$articles =  $management_commande_achat->modele_lignes()
							->where('article_id', $this->modele->article_id)
							->where('document_id', $management_commande_achat->modele->id)
							->where('conditionnement', $this->modele->conditionnement_id)
							->orderBy('ligne')
							->get();
		}
		else {

			$articles =  $management_commande_achat->modele_lignes()
							->where('article_id', $this->modele->article_id)
							->where('document_id', $management_commande_achat->modele->id)
                            ->where(function($requete) {
                                $requete->where('conditionnement', 0)
                                      ->orWhereNull('conditionnement');
                            })
							->orderBy('ligne')
							->get();
		}
		
		// on retouche les quantités
		foreach($articles as $article) {
			
			$article->quantite = $this->modele->quantite;
		}
		
		$informations = array(
			'date' => date('Y-m-d'),
			'fournisseur_id' => $this->modele->fournisseur_id,
			'projet_id' => $commande_achat->projet_id,
			'livre' => 1,
			'articles' => $articles,
			'adresse_de_livraison' => $commande_achat->adresse_de_livraison,
			'commande_achat_source' => $this->modele->commande_achat_id,
		);

		$management_bl_achat = management('bl_achat');

		$retour = $management_bl_achat->enregistre($informations);
		
		// ok tout a fonctionné, on valide et on enregistre le # de BL créé sur la ligne réception fournisseur
		if($retour !== true) {
			
			return traduction('messages.php.element.erreur_creation')." ( ".table_libre('bl_achat')->element." ) : ".$retour;
		}
		
		
		$management_bl_achat->valide();

		$this->enregistre([
			'bl_achat_id' => $management_bl_achat->modele->id,
			'recu' => 1,
		]);

		// quoi qu'il en soit on met à jour les stocks sur la commande achat
		$commande_achat_management = management('commande_achat', $this->modele->commande_achat_id);
		
		$commande_achat_management->gere_statut_et_stocks_automatiques_achats();
		
		return $retour;
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        if(in_array('dupliquer',$liste_options))
            unset($liste_options[array_search('dupliquer',$liste_options)]);

        $liste_options[] = 'reception_fractionnee';
        $liste_options[] = 'recu';

        return $liste_options;
    }

	/**
	 * 
	 * On ajoute la colonne des adresses de livraison pour les listes
	 * 
	 */
	public function ajout_colonne_adresse_livraison($ligne){

		$commande_achat_id = $ligne['commande_achat_id'];

		$modele_commande_achat = modele('commande_achat')->where('id',$commande_achat_id)->first();

		if($modele_commande_achat == null)
			return;

		$a_livrer_chez_client = $modele_commande_achat->a_livrer_chez_client;

		$adresse_livraison_id = $modele_commande_achat->adresse_de_livraison;

		if($a_livrer_chez_client == 0)
			$type_element = 'adresse_interne';

		else if($a_livrer_chez_client == 1)
			$type_element = 'adresse';

		else
			return;

		if($adresse_livraison_id == null)
			return;
		
		$adresse_livraison = modele($type_element)
							 ->where('id',$adresse_livraison_id)
							 ->select(DB::raw("CONCAT(societe,', ',adresse,', ',code_postal,' ',ville) as nom"))
							 ->first()->nom;

		return $adresse_livraison;

	}
}