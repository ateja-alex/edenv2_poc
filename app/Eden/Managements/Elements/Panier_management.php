<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Panier_detail;
use App\Eden\Models\Element_image;
use App\Eden\Models\Article_declinaison;
use App\Eden\Models\Famille_declinaison;

class Panier_management extends Element_management {

	/**
	 *
	 * Récupère le panier courant pour l'utilisateur connecté
	 *
	 * S'il n'a pas de panier en cours, on en crée un nouveau
	 *
	 */
	public function recupere() {
		
		$client_id = session('utilisateur_eden_ecommerce');
		
		if(empty($client_id)) {
			
			$panier = modele('panier')->where('session_id', session()->getId())->where('statut', 0)->first();
		}
		else {
			
			$panier = modele('panier')->where('client_id', $client_id)->where('statut', 0)->first();
		}

		if(empty($panier)) {
			
			// on doit créer un panier
			$panier = management('panier');
			
			$infos = array();
			
			if(empty($client_id)) {
				
				$infos['client_id'] = 0;
				$infos['session_id'] = session()->getId();
			}
			else {
				
				$infos['client_id'] = $client_id;
				$infos['session_id'] = '';
			}
			
			$infos['statut'] = 0;
			$infos['mode_paiement_id'] = 0;
			$infos['modalite_paiement_id'] = 0;
			$infos['coupon_reduction_id'] = 0;
			
			$panier->enregistre($infos);
		}
		else {
			
			$panier = management('panier', $panier->id);
		}

		
		return $panier;
	}
	
	/**
	 * 
	 * Récupère les informations du panier, comme le montant total, le nombre d'articles...
	 * 
	 */
	public function informations_panier() {

		
		if(empty($this->modele)) {
			
			return traduction('messages.php.panier.recuperer_informations_panier');
		}
		
		$articles_dans_panier = Panier_detail::where('panier_id', $this->modele->id)->get();
		
		$this->total_ht = 0;
		$this->total_ttc = 0;
		$this->total_tva = 0;
		$this->nombre_articles = 0;
		$this->nombre_references = 0;
		
		foreach($articles_dans_panier as $article_dans_panier) {
			
			$article = modele('article', $article_dans_panier->article_id);
			
			$article->images = Element_image::where('type_element', 'article')->where('element_id', $article->id)->get();
			
			$image_principale = Element_image::where('type_element', 'article')->where('element_id', $article->id)->first();
			
			$article->promo = management('article', $article_dans_panier->article_id)->recupere_promo();
			
			if(empty($image_principale))
				$article->image_principale = $image_principale;
			else {
				
				// note : le str_replace, c'est pour corriger un vieux bug du début de l'erp
				$article->image_principale = str_replace('uploads', '', $image_principale->chemin);
			}
			
			if(!empty($article_dans_panier->declinaison_id)) {
				
				$declinaison = Article_declinaison::find($article_dans_panier->declinaison_id);
				
				if($declinaison !== null) {
					
					if(!empty($declinaison->image)) {
						
						$article->image_principale_declinaison = $declinaison->image;
					}
					
					// au lieu de récupérer le nom de la déclinaison, on va récupérer ses attributs
					// $article->designation .= ' ('.$declinaison->designation.')';
					
					$attributs = array();
					
					for($i=1; $i<=5; $i++) {
						
						if(!empty($declinaison->{'famille_declinaison_'.$i}) && !empty($declinaison->{'valeur_declinaison_'.$i})) {
							
							$famille = modele('famille_declinaison', $declinaison->{'famille_declinaison_'.$i});
							
							if(!empty($famille))
								$attributs[] = $famille->nom.' : '.$declinaison->{'valeur_declinaison_'.$i};
						}
					}
					
					$article->designation .= "<br/>".implode('<br/>', $attributs);
					
					// on modifie le tarif
					$article_dans_panier->tarif_ht = round($declinaison->tarif / (100 + $article->taux_de_tva) * 100, 6);
					$article->tarif = $declinaison->tarif;
				}
			}
			
			$article_dans_panier->article = $article;
			
			$this->nombre_references++;
			$this->nombre_articles += $article_dans_panier->quantite;
			
			// dump_eden($article_dans_panier);
			
			$tarif_ht_article = 0;
			
			if(!empty($article_dans_panier->taille)) {
				
				$tarif_ht_article = $article_dans_panier->quantite * $article_dans_panier->tarif_ht * $article_dans_panier->taille;
			}
			else {
				
				$tarif_ht_article = $article_dans_panier->quantite * $article_dans_panier->tarif_ht;
			}
			
			if(!empty($article->promo)) {
				
				$tarif_ht_article *= (100 - $article->promo) / 100;
			}
			
			$this->total_ht += $tarif_ht_article;
			$this->total_tva += $tarif_ht_article * ($article->taux_de_tva / 100);
			$this->total_ttc += $tarif_ht_article * (1 + $article->taux_de_tva / 100);
		}
		
		$this->articles_dans_panier = $articles_dans_panier;

		// si le panier vide, les frais de livraison sont à zéro
		if($this->articles_dans_panier->isEmpty()) {

			$this->frais_de_livraison = 0;
		}
		else {

			$this->frais_de_livraison = management('panier')->calcule_tarif_livraison()->min('tarif');
		}


		$this->total_ttc += $this->frais_de_livraison ;
		
		return true;
		
	}
	
	/** 
	 * 
	 * Ajoute un produit au panier
	 * 
	 * @return json
	 * 
	 */
	public function ajoute_produit($formulaire, $prix = null, $taille_pour_article = null) {
		
		if(empty($this->modele)) {
			
			return response()->json(['succes' => false, 'message' => traduction('messages.php.panier.ajout_produit_sans_panier')]);
		}

		if($prix == null) {
			
			$prix = $formulaire->tarif_ht;
		}
		
		// déclinaison multiple
		if(is_array($formulaire->declinaison_id)) {

			$where_parametres = [];
			foreach($formulaire->get('declinaison_id') as $index => $declinaison) {
	
				$index = ($index + 1);
	
				$where_parametres['valeur_declinaison_'.$index] =  $declinaison;
			}

			$where_parametres['article_id'] = $formulaire->article_id;
			$declinaison = modele('article_declinaison')->where($where_parametres)->first();

			// on attribue la nouvelle valeur (Risque de doublon avec une déclinaison simple (problème lors de la vérification))
			$formulaire->declinaison_id = $declinaison->id;
		}


		
		// est ce que ce produit est déjà au panier ?
		if(empty($formulaire->declinaison_id)) {
			
			$ligne_panier = Panier_detail::where('article_id', $formulaire->article_id)
								->where('panier_id', $this->modele->id)
								->where(function($query) {
									
									$query->where('declinaison_id', 0);
									$query->orWhereNull('declinaison_id');
								})
								->first();
		}
		else {
			
			$ligne_panier = Panier_detail::where('article_id', $formulaire->article_id)
								->where('panier_id', $this->modele->id)
								->where('declinaison_id', $formulaire->declinaison_id)
								->first();
		}
		
		// si l'article possède une taille mesurable, on crée à chaque fois une nouvelle ligne
		if(empty($ligne_panier) || $taille_pour_article) {
			
			$ligne_panier = new Panier_detail;
			$ligne_panier->article_id = $formulaire->article_id;
			$ligne_panier->declinaison_id = $formulaire->declinaison_id;
			$ligne_panier->panier_id = $this->modele->id;
			$ligne_panier->quantite = $formulaire->quantite;
			$ligne_panier->tarif_ht = $prix;
			if($taille_pour_article === true) 
				$ligne_panier->taille = $formulaire->taille;

			
		}
		else {
			
			$ligne_panier->quantite += $formulaire->quantite;
			
			if($taille_pour_article === true) 
				$ligne_panier->taille += $formulaire->taille;

		}
		
		$ligne_panier->save();
		
		return response()->json(['succes' => true]);
	}
	
	/** 
	 * 
	 * Ajoute un produit au panier
	 * 
	 * @return json
	 * 
	 */
	public function modifie_produit($formulaire, $prix = NULL) {
		
		if(empty($this->modele)) {
			
			return response()->json(['succes' => false, 'message' => traduction('messages.php.panier.ajout_produit_sans_panier')]);
		}

		if ( $prix == NULL ) {
			$prix = $formulaire->tarif_ht ;
		}
		
		// est ce que ce produit est déjà au panier ?
		$ligne_panier = Panier_detail::where('article_id', $formulaire->article_id)->where('panier_id', $this->modele->id)->first();
		
		if ( intval($formulaire->quantite) <= 0 ) {
			Panier_detail::where('article_id', $formulaire->article_id)->where('panier_id', $this->modele->id)->delete();
		} else {
			if(empty($ligne_panier)) {
				// Ajout du produit au panier
				$ligne_panier = new Panier_detail;
				$ligne_panier->article_id = $formulaire->article_id;
				$ligne_panier->panier_id = $this->modele->id;
				$ligne_panier->quantite = $formulaire->quantite;
				$ligne_panier->tarif_ht = $prix;
			} else {
				// Modification de la quantité
				$ligne_panier->quantite = $formulaire->quantite;
			}
		}
		
		$ligne_panier->save();
		
		return response()->json(['succes' => true]);
	}


    /**
     * 
     * Affiche le panier
     * 
     * @return Response
     */
    public function panier() {
		
		$panier = management('panier')->recupere();
		$panier->informations_panier();
		
		return view('eden::ecommerce.commande.panier', ['panier' => $panier]);
    }


	/**
	 *
	 * Vérifications avant de valider la commande.
	 * @return true ou message d'erreur
	 *
	 */
	public function verification_avant_validation () {
		return true ;
	}

	public function enregistre_adresse($data) {

		$adresse = management('adresse');
		$choix_adresse_livraison = $data['choix_adresse_livraison'];
        $choix_adresse_facturation = $data['choix_adresse_facture'];

        if($choix_adresse_livraison == 0) {
			//on ajoute une nouvelle adresse de livraison
			//@todo à vérifier
			$data_livraison['client_id'] = session()->get('utilisateur_eden_ecommerce');
			$data_livraison['type_adresse'] = 2;
			$data_livraison['nom_adresse'] = $data['nom_adresse_livraison'];
			if(isset($data['societe_adresse_livraison'])) {
				$data_livraison['societe'] = $data['societe_adresse_livraison'];
			}
			$data_livraison['adresse'] = $data['adresse_livraison'];
			$data_livraison['adresse_complement'] = $data['adresse_complement_livraison'];
			$data_livraison['code_postal'] = $data['code_postal_livraison'];
			$data_livraison['ville'] = $data['ville_livraison'];
			$data_livraison['pays_id'] = $data['pays_id_livraison'];
			$data_livraison['telephone_fixe'] = $data['telephone_fixe_livraison'];
			$data_livraison['telephone_portable'] = $data['telephone_portable_livraison'];
			$data_livraison['modifie_le'] = now()->format('Y-m-d');
			$data_livraison['cree_le'] = now()->format('Y-m-d');

			//$adresse_de_livraison = $adresse->enregistre($data_livraison);

			$id_adresse_de_livraison = \DB::table('adresse')->insertGetId($data_livraison);	
		}
		
        else {

            $id_adresse_de_livraison = modele('adresse', $choix_adresse_livraison)->id;
		}
		

        if(isset($data['meme_adresse']) && $data['meme_adresse'] == 'on') {

            $id_adresse_de_facturation = $id_adresse_de_livraison;

        }
        else {

            if($choix_adresse_facturation == 0) { 

				$data_facturation['client_id'] = session()->get('utilisateur_eden_ecommerce');
				$data_facturation['type_adresse'] = 1;
				$data_facturation['nom_adresse'] = $data['nom_adresse_facturation'];
				if(isset($data['societe_adresse_facturation'])) {
					$data_livraison['societe'] = $data['societe_adresse_facturation'];
				}
				$data_facturation['adresse'] = $data['adresse_facturation'];
				$data_facturation['adresse_complement'] = $data['adresse_complement_facturation'];
				$data_facturation['code_postal'] = $data['code_postal_facturation'];
				$data_facturation['ville'] = $data['ville_facturation'];
				$data_facturation['pays_id'] = $data['pays_id_facturation'];
				$data_facturation['telephone_fixe'] = $data['telephone_fixe_facturation'];
				$data_facturation['telephone_portable'] = $data['telephone_portable_facturation'];
				$data_facturation['modifie_le'] = now()->format('Y-m-d');
				$data_facturation['cree_le'] = now()->format('Y-m-d');
				
				$id_adresse_de_facturation = \DB::table('adresse')->insertGetId($data_facturation);

            }
            else {

                $id_adresse_de_facturation = modele('adresse', $choix_adresse_facturation)->id;

            }
        }

		management('panier', $this->modele->id)->enregistre(['adresse_de_facturation' => $id_adresse_de_facturation, 'adresse_de_livraison' => $id_adresse_de_livraison ]);
		
		return true;
	}

	/**
	 *
	 * Appel des fonctions de paiement.
	 * return true si paiement automatiquement accepté.
	 * @return true ou message d'erreur
	 *
	 */
	public function effectue_paiement ($panier, $data = null) {
		return true ;		
	}


	/**
	 *
	 * Enregistre la commande dans l'ERP.
	 * Ne devrait pas échouer
	 *
	 */
	public function enregistre_dans_erp () {

		$modifications = $this->prepare_informations_pour_document();


		
		$facture = management('facture_vente');

		$retour = $facture->enregistre($modifications);

		if ( $retour !== true ) return $retour ;

		return $facture ;
	}

	/**
   	 * 
   	 * Prépare le tableau des modifications à envoyer à l'erp pour la création d'un document
   	 * 
	 */
	public function prepare_informations_pour_document() {

		$client = management('client',session('utilisateur_eden_ecommerce'));

		$modifications = array(

			'date' => date('Y-m-d'),
			'client_id' => $client->modele->id,
			'articles' => $this->recupere_articles_depuis_panier(),

		);

		if (!empty($this->modele->adresse_de_facturation) && !empty($this->modele->adresse_de_livraison)) {
			

			$modifications = array(

				'date' => date('Y-m-d'),
				'client_id' => $client->modele->id,
				'articles' => $this->recupere_articles_depuis_panier(),
				'adresse_de_facturation' => $this->modele->adresse_de_facturation,
				'adresse_de_livraison' => $this->modele->adresse_de_livraison,
			);
		}

		return $modifications;
	}

	/**
	 * 
	 * Construit le tableau des articles à envoyer à l'erp à partir des informations du panier
	 * 
	 */
	public function recupere_articles_depuis_panier() {

		$articles = array();

		foreach($this->articles_dans_panier as $article ) {

			$articles[] = array(
				'article_id' => $article->article->id,
				'quantite' => $article->quantite,
				'tva' => $article->article->taux_de_tva,
				'tarif' => $article->article->tarif,
				'type_tarif' => 'ttc',	// Uniquement si TTC
				'remise' => 0,
				'designation' => $article->article->designation,
			);
		}

		return $articles;
	}

	public function supprime_produit($formulaire) {

        
    }

	/**
	 *
	 * Enregistre le paiement dans l'ERP.
	 * Ne devrait pas échouer
	 *
	 */
	public function enregistre_paiement ($facture) {

	}


	/**
	 *
	 * Envoi un mail au client.
	 * Ne devrait pas échouer
	 *
	 */
	public function mail_validation_commande ($facture) {

	}

	/*
	*
	* Désactiver le panier
	*
	*/

	public function desactive_panier() {

		management('panier', $this->modele->id)->enregistre(['inactif' => 1]);

	}

	public function redirection_en_cas_d_erreur($retour) {

		return redirect()->route('ecommerce.panier')->with('erreur', $retour);
	}


	/*
	 * Renvoi le poids du panier. A surcharger selon les besoins
	*/
	public function calcule_poids_panier() {
		return 0 ;
	}
	
	/**
	 * 
	 * Affiche le contenu du panier pour la liste sur eden
	 * 
	 */
	public function liste_articles($modele) {
		
		$panier = unserialize(base64_decode($modele->articles));

		$articles = array();
		
		foreach($panier as $article) {
			
			$articles[] = management('article', $article['id'])->affiche_lien().' (x'.$article['quantite'].')';
		}
		
		return implode('<br/>', $articles);
	}

	/*
	 * Renvoi le poids du panier. A surcharger selon les besoins
	*/
	public function calcule_tarif_livraison() {
		// On récupère le pays actuellement sélectionné, ou on prends le premier pays dans la base
		$id_pays = ( session('pays_actif') ? session('pays_actif') : NULL ) ;
		$pays = modele('pays') ;
		if ( $id_pays != NULL ) $pays = $pays->where('id', $id_pays) ;
		$pays = $pays->first() ;

		// On récupère le code postal actuellement choisi, ou 
		$code_postal = ( session('code_postal_actif') ? substr(session('code_postal_actif'), 0, 2) : '' ) ;

		// On récupère le poids du panier
		$poids = $this->calcule_poids_panier();

		
		// On récupère les zones concernées par le pays 
		$zonesNat = modele('zone_transporteur')
						->join('zone_transporteur_cp', 'zone_transporteur_cp.zone_id', 'zone_transporteur.id')
						->join('transporteur_tarif_livraison', 'transporteur_tarif_livraison.zone_id', 'zone_transporteur_cp.zone_id')
						->join('transporteur', 'transporteur.id', 'transporteur_tarif_livraison.transporteur_id')

						->where('zone_transporteur_cp.pays_id', $pays->id)
						//->where('zone_transporteur.id', '=', 'transporteur_tarif_livraison.zone_id')
						->whereNull('zone_transporteur_cp.departement')
						->where('transporteur_tarif_livraison.poids_min', '<=', $poids)
						->where('transporteur_tarif_livraison.poids_max', '>', $poids)

						->select('transporteur.nom', 'transporteur_tarif_livraison.tarif', 'transporteur_tarif_livraison.zone_id')
						->get()
						//->toSql()
						->keyBy('nom')
						;

		// On récupère les zones concernées par le pays et le CP
		$zonesCP = modele('zone_transporteur')
						->join('zone_transporteur_cp', 'zone_transporteur_cp.zone_id', 'zone_transporteur.id')
						->join('transporteur_tarif_livraison', 'transporteur_tarif_livraison.zone_id', 'zone_transporteur_cp.zone_id')
						->join('transporteur', 'transporteur.id', 'transporteur_tarif_livraison.transporteur_id')

						->where('zone_transporteur_cp.pays_id', $pays->id)
						//->where('zone_transporteur.id', 'transporteur_tarif_livraison.zone_id')
						->where('zone_transporteur_cp.departement', $code_postal)
						->where('transporteur_tarif_livraison.poids_min', '<=', $poids)
						->where('transporteur_tarif_livraison.poids_max', '>', $poids)

						->select('transporteur.nom', 'transporteur_tarif_livraison.tarif', 'transporteur_tarif_livraison.zone_id')
						->get()
						->keyBy('nom')
						;
		


		// On merge ZonesNat avec ZonesCP
		// Par ce biais, la collection ZoneCP est prioritaire sur la collection ZonesNat
		$tarifs = $zonesNat->merge($zonesCP) ;

		return $tarifs ;
	}

}