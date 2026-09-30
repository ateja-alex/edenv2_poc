<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Payzen_management;
use App\Eden\Models\Formulaire;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Lyra;
use App\Eden\Models\Element_piece_jointe;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Variables;
use PDF;


class Client_management extends Element_management {


	/**
	 *
	 * @cf Element_management::retraite_modifications()
	 *
	 */
	protected function retraite_modifications($modifications) {

		// On vérifie si le mot de passe a été saisi
		if(isset($modifications['mot_de_passe'])) {
			// Si le mot de passe n'est pas déjà crypté
			if(strlen($modifications['mot_de_passe']) != 32) {
				// On crypte le mot de passe
				$modifications['mot_de_passe'] = md5($modifications['mot_de_passe']);
			}
		}

		// l'élément existe déjà, rien de spécial à faire
		// if($this->existe())
			// return parent::retraite_modifications($modifications);


		// la fonctionnalité multi entité n'est pas activée, on passe
		// if(fonctionnalite('mode_multi_entites') === false)
			// return parent::retraite_modifications($modifications);

		// il faut vérifier qu'on a une entité de renseignée
		if(!empty($modifications['entite_id']) || !empty($this->modele->entite_id) )
			return parent::retraite_modifications($modifications);

		// est ce qu'on a une seule entité existente ?
		$nombre_entites = modele('entite')->count();

		if($nombre_entites == 1) {

			$entite = modele('entite')->first();

			$modifications['entite_id'] = $entite->id;

			return parent::retraite_modifications($modifications);
		}

		return traduction('messages.php.client.entite_non_renseigne');
	}

	/**
	 *
	 * On gère le cas particulier ou le client n'a pas de documents de gestion commerciale
	 *
	 */
	public function modification_entite_autorisee() {

		$facture = modele('facture_vente')->sans_profils()->where('valide', 1)->where('client_id', $this->modele->id)->first();

		if($facture !== null)
			return false;

		$acompte = modele('acompte_vente')->sans_profils()->where('valide', 1)->where('client_id', $this->modele->id)->first();

		if($acompte !== null)
			return false;

		$avoir = modele('avoir_vente')->sans_profils()->where('valide', 1)->where('client_id', $this->modele->id)->first();

		if($avoir !== null)
			return false;

		$devis = modele('devis_vente')->sans_profils()->where('valide', 1)->where('client_id', $this->modele->id)->first();

		if($devis !== null)
			return false;

		$commande = modele('commande_vente')->sans_profils()->where('valide', 1)->where('client_id', $this->modele->id)->first();

		if($commande !== null)
			return false;

		$bl = modele('bl_vente')->sans_profils()->where('valide', 1)->where('client_id', $this->modele->id)->first();

		if($bl !== null)
			return false;

		return true;
	}

	/**
	 *
	 * Retourne la situation géographique comptable du client
	 *
	 * 1 = france
	 * 2 = UE
	 * 3 = Export
	 *
	 */
	public function situation_geographique_comptable() {

		/*
		@todo
		*/
		return 1;
	}

	/**
	 *
	 * Retourne le compte auxiliaire du client (utilisé en comptabilité)
	 *
	 */
	public function compte_auxiliaire() {

		if(!empty($this->modele->compte_auxiliaire))
			return $this->modele->compte_auxiliaire;

		return $this->cree_compte_auxiliaire_alphanum();
	}

	/**
	 *
	 * Calcul le compte auxiliaire du client sur X caractères
	 *
	 */
	public function cree_compte_auxiliaire_alphanum() {

		$nombre_caracteres = fonctionnalite('compta_compte_auxiliaire_nombre_caracteres');

		if(empty($nombre_caracteres)) {

			return false;
		}

		$chaine_alphanum = substr(retraite_caracteres_speciaux($this->cree_chaine_pour_compte_auxiliaire()), 0, $nombre_caracteres);

		$suffixe = '';

		while(modele('client')->sans_profils()->avec_inactifs()->where('compte_auxiliaire', $chaine_alphanum.$suffixe)->first() !== null) {

			$chaine_alphanum = substr($chaine_alphanum, 0, $nombre_caracteres - 2);

			if(empty($suffixe)) {

				$suffixe = '01';
			}
			else {

				$suffixe++;

				// pour avoir un zero initial
				$suffixe = substr('0'.$suffixe, -2);
			}
		}

		$compte_auxiliaire = $chaine_alphanum.$suffixe;

		$management = management('client', $this->modele->id);

		// on met à jour sur le client
		$management->enregistre(array('compte_auxiliaire' => $compte_auxiliaire));

		return $compte_auxiliaire;

	}

	/**
	 *
	 * Retourne la chaine de caractères à utiliser pour créer le compte auxiliaire en compta
	 *
	 * Cette méthode est prévue pour être surchargée
	 *
	 */
	protected function cree_chaine_pour_compte_auxiliaire() {

		return $this->modele->nom.$this->modele->prenom;
	}


	/**
	 *
	 * Utilisé pour l'inscription via le site ecommerce
	 *
	 */
	public function creation_compte_ecommerce($formulaire) {

		return $this->enregistre($formulaire);
	}

	/**
	 *
	 * Envoie l'email de bienvenue après l'inscription via le ecommerce
	 *
	 */
	public function envoie_mail_apres_inscription_ecommerce() {

		return true;
	}

	/**
     *
	 * Retourne des infos concernant le client.
	 *
	 */
	public function recupere_infos_post_connexion() {

		$infos = array();

		return $infos;
	}

	/**
	 *
	 * Retourne les informations nécessaires pour l'affichage de la page mon compte sur le ecommerce
	 *
	 */
	public function donnees_pour_ecommerce_mon_compte($donnees = array()) {

		// les devis
		$donnees['devis'] = modele('devis_vente')->where('client_id', $this->modele->id)->orderBy('date', 'DESC')->get();
		$donnees['commandes'] = modele('commande_vente')->where('client_id', $this->modele->id)->orderBy('date', 'DESC')->get();
		$donnees['factures'] = modele('facture_vente')->where('client_id', $this->modele->id)->orderBy('date', 'DESC')->get();


		return $donnees;
	}

	/**
	 *
	 * Retourne les informations nécessaires pour l'affichage de la page mon compte sur le ecommerce
	 *
	 */

	public function generer_nouveau_mdp() {

		// on génère un nouveau mot de passe aléatoire
		$nouveau_mdp = Str::random(12);

		// on crypte le mot de passe
		// $nouveau_mdp = md5($nouveau_mdp);

		return $nouveau_mdp;
	}

	/**
	 *
	 * Enregistre un nouveau mot de passe pour un client pour le site ecommerce
	 *
	 */
	public function enregistre_nouveau_mot_de_passe_ecommerce($mot_de_passe) {

		return $this->enregistre(['mot_de_passe' => $mot_de_passe]);
	}

	/**
	 *
	 * Retourne les informations nécessaires pour l'affichage de la page validation de panier sur le ecommerce
	 *
	 */
	public function donnees_pour_ecommerce_panier_valide($donnees = array()) {


		return $donnees;
	}

	/**
	 *
	 * Retourne si le mot de passe corresponds aux règles définies (par défaut:true)
	 * Sinon, renvoi une chaine d'erreur.
	 *
	 */
	public function verifier_mot_de_passe_valide($mot_de_passe) {

		return true;
	}

	public function envoie_mail_mot_de_passe_oublie($token) {

		return true;
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'echange';

        return $liste_options;
    }

	/**
	 *
	 * On vérifie si l'adresse email existe déjà
	 *
	 */
	public function verifie_adresse_email_unique($adresse_email) {

		// création d'un client
		if(empty($this->modele)) {

			return modele('client')->sans_profils()
				->where('adresse_email', $adresse_email)
				->first();
		}
		else {

			return modele('client')->sans_profils()
				->where('adresse_email', $adresse_email)
				->where('id', '!=', $this->modele->id)
				->first();
		}
	}

	/**
	 *
	 * On vérifie si l'adresse email existe déjà (pour une entité donnée)
	 *
	 */
	public function verifie_adresse_email_unique_pour_entite($adresse_email, $id_entite = false) {

		if($id_entite === false) {

			if(empty($this->modele)) {

				exception("Nous ne pouvons pas vérifier si l'email est unique pour une entité donnée, le modèle n'est pas défini sur l'élément et l'entité n'a pas été passée comme paramètre");
			}

			$id_entite = $this->modele->entite_id;
		}

		// création d'un client
		if(empty($this->modele)) {

			return modele('client')->sans_profils()
				->where('adresse_email', $adresse_email)
				->where('entite_id', $id_entite)
				->first();
		}
		else {

			return modele('client')->sans_profils()
				->where('adresse_email', $adresse_email)
				->where('id', '!=', $this->modele->id)
				->where('entite_id', $id_entite)
				->first();
		}
	}

	/**
	 *
	 * Vérifie les informations pour la création d'un compte ecommerce
	 *
	 */
	public function verifie_informations_pour_inscription_ecommerce($donnees) {

		// on vérifie les champs obligatoires
		if(empty($donnees['client']['adresse_email']))
			return traduction('messages.php.champ_obligatoire').' '.traduction('champs_libres.client.adresse_email.nom');

		if(empty($donnees['client']['mot_de_passe']))
			return traduction('messages.php.champ_obligatoire').' '.traduction('champs_libres.client.mot_de_passe.nom');

		// on vérifie que l'adresse email est unique
		$client = modele('client')->avec_inactifs()->where('adresse_email', $donnees['client']['adresse_email'])->first();

		if($client !== null) {

			/**
			@todo envoyer un email de mot de passe oublié automatiquement ?
			*/

			return traduction('messages.php.element_deja_utilise', null, [traduction('champs_libres.client.adresse_email.nom')]);
		}

		return true;
	}

	/**
	 *
	 * Prépare les informations pour la création d'un compte ecommerce
	 *
	 */
	public function prepare_informations_pour_inscription_ecommerce($donnees) {

		$donnees['client']['mot_de_passe'] = md5($donnees['client']['mot_de_passe']);

		// on rempli automatiquement certaines infos pour l'adresse
		if(!isset($donnees['adresse']['nom'])) {

			$donnees['adresse']['nom'] = $donnees['client']['nom'];
			$donnees['adresse']['prenom'] = $donnees['client']['prenom'];
		}

		return $donnees;
	}

	/**
	 *
	 * Retourne si le client a le droit de consulter une facture.
	 * Par défaut, si il en est le propriétaire
	 *
	 */
	public function droit_facture($facture_vente) {

		return $facture_vente->modele->client_id == $this->modele->id ;
	}

	/**
	 *
	 * retourne l'id client pour le calcul des crédits
	 *
	 */
	public function client_id_pour_credits() {

		return $this->modele->id;
	}

	public function redirection() {

		return redirect()->route('ecommerce.mon_compte');
	}

	/**
	 *
	 * Retourne l'id_master pour le client
	 *
	 */
	public function master_id_client() {

		$master_id_entite = $this->master_id_entite();

		if($master_id_entite === false) {

			return false;
		}

		if($this->modele->entite_id == $master_id_entite) {

			return $this->modele->id;
		}
		else {

			// on ne peut pas dupliquer (elle a probablement été créée à la main)
			if(empty($this->modele->master_id_client))
				return false;

			return $this->modele->master_id_client;
		}
	}

	/**
	 *
	 * Duplique les modifications d'une fiche client vers ses équivalents sur les autres entités
	 *
	 */
	public function duplique_modifications_sur_autres_entites() {

		// on est déjà en train de dupliquer des modifications
		if(isset($this->duplication_inter_entites) && $this->duplication_inter_entites === true)
			return;

		// on récupère les données de la fiche client actuelle
		$colonnes = \Schema::getColumnListing('client');

		// on doit traiter ou le modèle est null (l'utilisateur n'a pas accès au modèle)
		if($this->modele === null || $this->modele->id === null) {

			throw new \App\Eden\Exceptions\Eden_exception(traduction('messages.php.client.echec_duplication_modele',null,[moi()->id,$this->modele->id]));
		}

		$modifications = array();

		foreach($colonnes as $colonne) {

			$modifications[$colonne] = $this->modele->$colonne;
		}

		$modifications = $this->retouche_modifications_pour_duplication_modifications_sur_autres_entites($modifications);

		$master_id_entite = $this->master_id_entite();

		if($master_id_entite === false) {

			return false;
		}


		// on modifie les clients
		if($this->modele->entite_id == $master_id_entite) {

			if($master_id_entite === null) {

				throw new \App\Eden\Exceptions\Eden_exception(traduction('messages.php.client.echec_duplication_entite',null,[moi()->id,$this->modele->id,2]));
			}

			if($this->modele->id === null) {

				throw new \App\Eden\Exceptions\Eden_exception(traduction('messages.php.client.echec_duplication_entite',null,[moi()->id,$this->modele->id,3]));
			}

			$clients = modele('client')->where('master_id_client', $this->modele->id)->get();
		}
		else {

			// on ne peut pas dupliquer (elle a probablement été créée à la main)
			if(empty($this->modele->master_id_client))
				return;

			if($this->modele->master_id_client === null) {

				throw new \App\Eden\Exceptions\Eden_exception(traduction('messages.php.client.echec_duplication_entite',null,[moi()->id,$this->modele->id,4]));
			}

			$clients = modele('client')->where(function($query) {

					$query->where('master_id_client', $this->modele->master_id_client);
					$query->orWhere('id', $this->modele->master_id_client);
				})->get();
		}

		$modifications = $this->retraite_donnees_pour_duplication_multi_entite($modifications);

		foreach($clients as $client) {

			if($client->id == $this->modele->id)
				continue;

			// on passe par les managements pour les logs
			$management = management('client', $client->id);

			// pour ne pas dupliquer les modifications à l'infini
			$management->duplication_inter_entites = true;

			$management->enregistre_modele($modifications);
		}

		return true;
	}

	/**
	 *
	 * On retraite les données pour une modification multi entité, cf méthode duplique_modifications_sur_autres_entites()
	 *
	 * Cette méthode est faite pour être surchargée
	 *
	 */
	protected function retraite_donnees_pour_duplication_multi_entite($modifications) {

		return $modifications;
	}

	/**
	 *
	 * Lorsqu'on modifie une fiche client, on doit dupliquer sur ses homolugues sur les autres entités
	 * Certaines colonnes ne doivent pas être modifiées, on les liste ici
	 * Cette méthode est pensée pour être surchargée en spécifique si nécessaire
	 * Dans ce cas, attention de bien appeler parrent::retouche_modifications_pour_duplication_modifications_sur_autres_entites()
	 *
	 */
	protected function retouche_modifications_pour_duplication_modifications_sur_autres_entites($modifications) {

		unset($modifications['id']);
		unset($modifications['inactif']);
		unset($modifications['entite_id']);
		unset($modifications['master_id_client']);
		unset($modifications['cree_le']);
		unset($modifications['cree_par']);
		unset($modifications['modifie_le']);
		unset($modifications['modifie_par']);
		unset($modifications['chaine_tags_recherche']);
		unset($modifications['maj_droits']);

		return $modifications;
	}

	/**
	 *
	 * Doit être surchargé en fonction des projets, pour retourner le master id entite dans le cas de la duplication des fiches
	 *
	 */
	protected function master_id_entite() {

		return false;
	}

	/**
	 *
	 * Retourne la liste des clients par entité pour un master id client donné
	 *
	 */
	public function clients_pour_master_id_client() {

		if(fonctionnalite('fiche_client_unique_multi_entite') !== true)
			return array($this->modele->entite_id => $this->modele->id);

		$master_id_entite = $this->master_id_entite();

		if($master_id_entite === false) {

			return array($this->modele->entite_id => $this->modele->id);
		}

		if($this->modele->entite_id == $master_id_entite) {

			// on est sur l'entité principale
			$client_id_par_entite = modele('client')->where(function($query) {

					$query->where('master_id_client', $this->modele->id);
					$query->orWhere('id', $this->modele->id);
				})->get()->pluck('id', 'entite_id')->toArray();
		}
		else {

			// on gère le cas ou le client n'a pas de master id entite
			if(empty($this->modele->master_id_client)) {

				return array($this->modele->entite_id => $this->modele->id);
			}

			// on est sur une autre entité
			$client_id_par_entite = modele('client')->where(function($query) {

					$query->where('master_id_client', $this->modele->master_id_client);
					$query->orWhere('id', $this->modele->master_id_client);
				})->get()->pluck('id', 'entite_id')->toArray();
		}

		return $client_id_par_entite;
	}

	/**
	 *
	 * Duplique un client pour une autre entité
	 *
	 */
	public function duplique_client_pour_entite($entite_id) {

		if($this->modele->entite_id == $this->master_id_entite()) {

			$master_id_client = $this->modele->id;
		}
		else {

			$master_id_client = $this->modele->master_id_client;
		}

		// on a donc notre client modèle avec $personne_morale

		// on vérifie que le client n'existe pas déjà
		$client_existant = modele('client')->where('master_id_client', $master_id_client)->where('entite_id', $entite_id)->first();

		// elle existe, on retourne
		if($client_existant !== null)
			return $client_existant;

		// il n'existe pas
		// on copie purement et simplement les champs
		$nouveau_client = $this->duplique_avec_modifications(array('entite_id' => $entite_id, 'master_id_client' => $master_id_client));

		if(!is_object($nouveau_client)) {

			throw new \App\Eden\Exceptions\Eden_exception(traduction('messages.php.client.echec_duplication')." : ".$nouveau_client);
		}

		return $nouveau_client->modele;
	}

	/**
	 *
	 * Récupère le portefeuille du client
	 *
	 */
	public function recupere_portefeuille() {

		return $this->modele->portefeuille_payline;
	}

	/**
	 *
	 * Récupère les infos du client pour la gestion commerciale
	 *
	 * Cette méthode est appelée lorsqu'on crée ou modifie un document
	 *
	 */
	public function infos_client_pour_gestion_commerciale() {

		if(empty($this->modele) || empty($this->modele->id)) {

			return array(

				'encours' => null,
				'entite_id' => null,
				'modele' => modele('client'),
				'contacts' => [],
				'exoneration_tva' => false,
				'adresse' => null,
				'adresses_facturation' => [],
				'adresses_livraison' => [],
				'projets' => [],
			);
		}

		// l'encours
		$encours = modele('facture_vente')
			->where('client_id', $this->modele->id)
			->where('valide', 1)
			->where(function($r) {

				$r->where('annulee_par_avoir', 0)->orWhereNull('annulee_par_avoir');
			})
			->sum('solde_document_ttc');

		// on récupère les contacts du client
		$contacts = modele('contact')
			->where('client_id', $this->modele->id)
			->get();

		//on récupére les paiements du client non rattahce à un document
        $paiements_non_rattaches = modele('paiement')
                ->whereNull('type_element')
                ->zero_ou_null('id_document')
                ->zero_ou_null('neutralise')
                ->where('client_id', $this->modele->id)
                // ->zero_ou_null('rapproche')
                ->join('mode_paiement','mode_paiement_id','mode_paiement.id')
                ->select('paiement.*','mode_paiement.nom as mode_paiement_nom')
                ->get();

		$adresse = modele('adresse')->where('client_id', $this->modele->id)->first();

		// on va chercher la liste des adresses
		$adresses_facturation = modele('adresse')->where('client_id', $this->modele->id)->where(function($r) { $r->whereIn('type_adresse', array(0,1,3))->orWhereNull('type_adresse'); })->get();
		$adresses_livraison = modele('adresse')->where('client_id', $this->modele->id)->where(function($r) { $r->whereIn('type_adresse', array(0,2,3))->orWhereNull('type_adresse'); })->get();
		$projets = modele('projet')->where('client_id', $this->modele->id)->get();

		$this->modele->affiche_lien = $this->affiche_lien();

        foreach (champs_libres_management(champs_libres('client')) as $champ_libre){

            if($champ_libre->modele->type == 10){

                $this->modele->{$champ_libre->modele->nom_sql} = $champ_libre->recupere_valeurs_du_modele($this->modele->id);

            }

        }

		return array(

			'encours' => $encours,
			'entite_id' => $this->modele->entite_id,
			'modele' => $this->modele,
			'contacts' => $contacts,
			'paiements_non_rattache' => $paiements_non_rattaches,
			'exoneration_tva' => $this->exoneration_tva(),
			'adresse' => $adresse,
			'adresses_facturation' => $adresses_facturation,
			'adresses_livraison' => $adresses_livraison,
			'projets' => $projets,
			'eco_contribution' => $this->modele->eco_contribution
		);
	}

	/**
	 *
	 *
	 * Affiche le dernier échange d'un client dans une liste libre
	 *
	 */
	public function dernier_echange($modele) {

		$dernier_echange = modele('echange')->where('client_id', $modele->id)->orderBy('date', 'desc')->first();

		if($dernier_echange != null) {

			return "<b>". formate_date('d/m/Y', $dernier_echange->date) ." : ". Str::limit($dernier_echange->description, 100) ."</b>";
		}

		return 'Aucun échange';
	}


	/**
	 *
	 * Retourne la première adresse de facturation
	 *
	 */
	public function premiere_adresse_facturation() {

		$adresse_de_facturation = modele('adresse')
										->where('client_id', $this->modele->id)
							            ->where(function ($query) {
							                $query->where('type_adresse', 2)
							                      ->orWhere('type_adresse', 3);
							            })
										->first();

		if($adresse_de_facturation == null)
			return traduction('messages.Pas d\'adresse de facturation', 'Pas d\'adresse de facturation');

		return $adresse_de_facturation->adresse.' '.$adresse_de_facturation->code_postal.' '.$adresse_de_facturation->ville;
	}

	/**
	 *
	 * Retourne la première adresse de facturation (sous forme d'objet)
	 *
	 */
	public function premiere_adresse_facturation_objet() {

		$adresse_de_facturation = modele('adresse')
										->where('client_id', $this->modele->id)
							            ->where(function ($query) {
							                $query->where('type_adresse', 2)
							                	  ->orWhere('type_adresse', 1)
							                      ->orWhere('type_adresse', 3)
							                      ->orWhere('type_adresse', 0)
							                      ->orWhere('type_adresse', 4)
							                      ->orWhereNull('type_adresse');
							            })
										->first();

		if($adresse_de_facturation == null)
			return false;

		return $adresse_de_facturation;
	}

	/**
	 *
	 *
	 * Retourne la première adresse de livraison
	 *
	 */
	public function premiere_adresse_livraison($modele) {

		$adresse_de_livraison = modele('adresse')
										->where('client_id', $modele->id)
							            ->where(function ($query) {
							                $query->where('type_adresse', 1)
							                      ->orWhere('type_adresse', 3);
							            })
										->first();

		if($adresse_de_livraison == null)
			return traduction('messages.php.client.adresse_livraison_introuvable');

		return $adresse_de_livraison->adresse.' '.$adresse_de_livraison->code_postal.' '.$adresse_de_livraison->ville;
	}

	/**
	*
	* On ajoute le lien de MAJ CB stripe
	*
	*/
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		// on ajoute le lien de maj de CB stripe s'il n'existe pas
		if(empty($modele->lien_mise_a_jour_cb_stripe)) {

			$lien_mise_a_jour_cb_stripe = route('maj_carte', ['stripe', $modele->id, md5('eden'.$modele->id)]);

			$this->enregistre_modele(array('lien_mise_a_jour_cb_stripe' => $lien_mise_a_jour_cb_stripe));
		}


		// on ajoute le lien de maj de CB payzen s'il n'existe pas
		if(empty($modele->lien_mise_a_jour_payzen)) {

			$lien_mise_a_jour_payzen = route('maj_carte', ['payzen', $modele->id, md5('eden'.$modele->id)]);

			$this->enregistre_modele(array('lien_mise_a_jour_payzen' => $lien_mise_a_jour_payzen));
		}

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

	}

	/**
	 *
	 *
	 * Retourne si le client est exonéré de la TVA
	 *
	 */
	public function exoneration_tva() {

		return false;
	}

	/**
	 *
	 * On vérifie si le client a des documents avant de le supprimer
	 *
	 */
	public function supprime($modele = false) {

		if(!fonctionnalite('gescom_bloquer_suppression_client_si_presence_documents_commerciaux'))
			return parent::supprime($modele);

		// on doit vérifier s'il a un document de gestion commerciale
		foreach(Variables::$documents_vente_gescom as $type_element) {

			// $first = modele($type_element)->sans_profils()->avec_inactifs()->where('client_id', $this->modele->id)->first();
			$first = modele($type_element)->sans_profils()->where('client_id', $this->modele->id)->first();

			if($first !== null)
				return traduction('messages.php.client.suppression_impossible_document');
		}

		return parent::supprime($modele);
	}

	/*
	 * On récupère les dernières adresses utilisée par le client
	 */
	public function derniere_adresse_de_livraison() {

		$derniere_adresse_de_livraison = modele('adresse')
						->select('adresse.*')
						->join('facture_vente', 'facture_vente.client_id', 'adresse.client_id')
						->where('adresse.client_id', $this->modele->id)
			            ->where(function ($query) {
			                $query->where('type_adresse', 2)
			                      ->orWhere('type_adresse', 3);
			            })
						->orderByDesc('date')
						->first() ;

		if (!empty($derniere_adresse_de_livraison)) return $derniere_adresse_de_livraison ;

		return modele('adresse')
						->where('client_id', $this->modele->id)
			            ->where(function ($query) {
			                $query->where('type_adresse', 2)
			                      ->orWhere('type_adresse', 3);
			            })
						->first() ;
	}

	public function derniere_adresse_de_facturation() {

		$derniere_adresse_de_facturation = modele('adresse')
						->select('adresse.*')
						->join('facture_vente', 'facture_vente.client_id', 'adresse.client_id')
						->where('adresse.client_id', $this->modele->id)
			            ->where(function ($query) {
			                $query->where('type_adresse', 1)
			                      ->orWhere('type_adresse', 3);
			            })
						->orderByDesc('date')
						->first() ;

		if (!empty($derniere_adresse_de_facturation)) return $derniere_adresse_de_facturation ;

		return modele('adresse')
						->where('client_id', $this->modele->id)
			            ->where(function ($query) {
			                $query->where('type_adresse', 1)
			                      ->orWhere('type_adresse', 3);
			            })
						->first() ;
	}

	/**
	 *
	 * Renvoi le montant dû par le client
	 * Passer le modele client en parametre, sinon le modele du management sera utilisé
	 *
	 */
	public function solde_du($modele = null, $date = null) {

		if(empty($date))
			$date = date('Y-m-d');

		if(empty($modele))
			$modele = $this->modele;

		if(empty($modele))
			exception('Erreur, modele vide (calcul du solde dû)');

		$champ_date = fonctionnalite('date_a_utiliser_pour_considerer_une_facture_comme_due');

		$management = management('client', $modele->id, $modele);

		$factures = modele('facture_vente')
						->whereIn('client_id', $management->clients_pour_master_id_client())
						->where('valide', 1)
						->zero_ou_null('regle')
						->where(function($r) use($champ_date, $date) {

							$r->where($champ_date, '<=', $date)
								->orWhereNull($champ_date)
								->orWhere($champ_date, '0000-00-00');
						})
						->get();
						// ->sum('solde_document_ttc');

		$montant = 0;

		if($factures !== null) {

			foreach($factures as $facture) {

				// on regarde si y'a des échéances
				$echeances = modele('echeance')->where('element_id', $facture->id)->where('type_element', 'facture_vente')->get();

				if($echeances !== null) {

					// il y a des échéances, on va retoucher à la volée
					// on regarde les échéances à venir
					$echeances_a_venir = modele('echeance')->where('type_element', 'facture_vente')->where('element_id', $facture->id)->where('date', '>', date('Y-m-d'))->sum('montant');

					// on les retranche du solde
					$facture->solde_document_ttc -= $echeances_a_venir;
				}

				// si on a un solde négatif, on passe
				if($facture->solde_document_ttc <= 0)
					continue;

				$montant += $facture->solde_document_ttc;
			}
		}


		return $montant;
	}

	/**
	 *
	 * Renvoi le montant dû par le client. Utilisé pour le prélèvement.
	 * Si besoin de prélever en avancer, passer la date en 2ème paramètre.
	 * Passer le modele client en parametre, sinon le modele du management sera utilisé
	 *
	 */
	public function solde_du_pour_prelevement($modele = null) {

		return $this->solde_du($modele = null);
	}

	/**
	 *
	 * Renvoi les factures dues par le client
	 * Passer le modele client en parametre, sinon le modele du management sera utilisé
	 *
	 */
	public function factures_dues($modele = null, $date = null) {

		if(empty($date)) $date = date('Y-m-d');

		if(empty($modele)) $modele = $this->modele;

		if(empty($modele)) exception('Erreur, modele vide (factures dues)');

		$champ_date = fonctionnalite('date_a_utiliser_pour_considerer_une_facture_comme_due');

		$management = management('client', $modele->id, $modele);

		$factures = modele('facture_vente')
						->whereIn('client_id', $management->clients_pour_master_id_client())
						->where('valide', 1)
						->where($champ_date, '<=', $date)
						->zero_ou_null('regle')
						->get();

		return $factures;
	}

	/**
	 *
	 *
	 * Retourne les informations d'un article selon les règles du client
	 *
	 */
	public function regles_pour_article($article_du_document) {

		return $article_du_document;
	}


	/**
	 *
	 * Renvoi les factures dues par le client. Utilisé pour le prélèvement.
	 * Si besoin de prélever en avancer, passer la date en 2ème paramètre.
	 * Passer le modele client en parametre, sinon le modele du management sera utilisé
	 *
	 */
	public function factures_dues_pour_prelevement($modele = null, $date = null) {

		return $this->factures_dues($modele = null);
	}

	/**
	 *
	 * Affiche l'adresse de facturation par défaut pour les listes
	 *
	 */
	public function colonne_adresse_de_facturation_par_defaut($modele) {

		$adresse = modele('adresse')->where('client_id', $modele->id)->where('type_adresse', 1)->where('adresse_par_defaut', 1)->first();

		if($adresse !== null) {

			return $adresse->adresse.',<br/>'.$adresse->code_postal.' '.$adresse->ville;
		}

		$adresse = modele('adresse')->where('client_id', $modele->id)->where('type_adresse', 3)->where('adresse_par_defaut', 1)->first();

		if($adresse !== null) {

			return $adresse->adresse.',<br/>'.$adresse->code_postal.' '.$adresse->ville;
		}

		return '';
	}

	/**
	 *
	 * Affiche l'adresse de facturation par défaut pour les listes
	 *
	 */
	public function colonne_adresse_de_livraison_par_defaut($modele) {

		$adresse = modele('adresse')->where('client_id', $modele->id)->where('type_adresse', 2)->where('adresse_par_defaut', 1)->first();

		if($adresse !== null) {

			return $adresse->adresse.',<br/>'.$adresse->code_postal.' '.$adresse->ville;
		}

		$adresse = modele('adresse')->where('client_id', $modele->id)->where('type_adresse', 3)->where('adresse_par_defaut', 1)->first();

		if($adresse !== null) {

			return $adresse->adresse.',<br/>'.$adresse->code_postal.' '.$adresse->ville;
		}

		return '';
	}

	/**
	 *
	 * Retourne le montant du solde dû par le client
	 *
	 */
	public function colonne_solde_du($modele) {

		return montant($this->solde_du($modele)).' ' . maquette('devise_application_symbole');
	}

	/**
	 *
	 * Affiche le bouton "prélever via payzen" du solde dû par le client
	 *
	 */
	public function colonne_payzen_prelever($modele) {

		$chaine = '<iframe style="width:400px;height:100px;" src="'.route('paiement_solde_payzen', [$modele->id, md5('eden'.$modele->id)]).'"/>' ;

		return $chaine ;
	}

	/**
	 *
	 *
	 * Retourne les actions sur les listes
	 * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
	 *
	 */
	public function actions_a_afficher($id_liste) {

		// on recupère les actions principales
		$actions = parent::actions_a_afficher($id_liste);

		if(fonctionnalite('utiliser_campagne_prospection') === true)
			$actions['clients_campagne_prospection'] = '<span class="dropdown-item" @click="modale_campagne_de_prospection = true""><i class="fa fa-fw fa-phone"></i> <span v-html="$root.traduction(\'interface.listes.campagne_prospection\')"></span></span>';

		$actions['clients_campagne_questionnaire'] = '<span class="dropdown-item" @click="modale_envoi_questionnaire = true"><i class="fa fa-fw fa-question"></i> <span v-html="$root.traduction(\'interface.listes.envoyer_questionnaire_de_satisfaction\')"></span></span>';

		// dd($actions);
		return $actions;
	}

    /**
     *
     * On vérifie que le client source n'a pas de facture, d'acompte, d'avoir ou de paiement sinon il n'est pas fusionnable
     *
     */
	public function verification_fusionnable(){

        $types_a_verifier = ['facture_vente','acompte_vente','avoir_vente','paiement'];

        foreach($types_a_verifier as $type){

            $modele = modele($type)->where('client_id',$this->modele->id)->first();

            if($modele != null){

                return traduction('messages.php.fiche.client_non_fusionnable_element_comptable');
            }
        }

        return true;

    }

    /**
     *
     * Permet de fusionner ce client avec un autre client
     *
     */


    public function fusion_elements_a_deplacer(){

        $champs_a_deplacer = Champ_libre::where(
            function($r) {
                $r->where('nom_sql', 'client_id')
                    ->orWhere('nom_sql', 'element_id')
                    ->orWhere(
                        function($r) {
                            $r->where('type', 21)
                                ->where('contenu', 'like', '%"type_element":"client"%');

                        })
                    ->orWhere(
                        function($r) {
                            $r->where('type', 42)
                                ->where('type_element_ajax', 'client');
                        });
            })
            ->where('eden_champslibres.type_element','!=','ticket')
            ->join('eden_tableslibres','eden_tableslibres.type_element','eden_champslibres.type_element')
            ->where(function($sous_requete){
                $sous_requete->whereNull('vue_sql')->orWhere('vue_sql',0);
            })
            ->select('eden_champslibres.*')
            ->get();

        // On va chercher le champ "ID Element Dynamique" associé
        $champs_id_element = Champ_libre::where('type', 22)
            ->select(\DB::raw("CONCAT(contenu, '.',type_element) as champ, nom_sql"))
            ->get()
            ->pluck('nom_sql', 'champ');

        $elements = [];
        foreach($champs_a_deplacer as $table){

            // Si on a des colonnes type_element et element_id
            if(Schema::hasColumn($table->type_element, 'type_element') && Schema::hasColumn($table->type_element, 'element_id')) {

                $elements_a_fusionner = modele($table->type_element)
                                ->where('element_id', $this->modele->id)
                                ->where('type_element', '=', 'client')
                                ->get();

                if($elements_a_fusionner->count() > 0)
                    $elements[$table->type_element] = $elements_a_fusionner;
            }

            // Si on a une colonne client_id
            if(Schema::hasColumn($table->type_element, 'client_id')) {

                $elements_a_fusionner = modele($table->type_element)
                                ->where('client_id', $this->modele->id)
                                ->get();

                if($elements_a_fusionner->count() > 0)
                    $elements[$table->type_element] = $elements_a_fusionner;
            }

            // Si on a un type element ajax
            if($table->type == 42) {

                $elements_a_fusionner = modele($table->type_element)
                                ->where($table->nom_sql, $this->modele->id)
                                ->get();

                if($elements_a_fusionner->count() > 0)
                    $elements[$table->type_element] = $elements_a_fusionner;
            }

            // Si on a un champ de type "ID Element Dynamique".
            if($table->type == 21) {

                // Si un élément existe, on va chercher
                if(isset($champs_id_element[$table->nom_sql.'.'.$table->type_element])) {

                    $elements_a_fusionner = modele($table->type_element)
                                    ->where($table->nom_sql, 'client')
                                    ->where($champs_id_element[$table->nom_sql.'.'.$table->type_element], $this->modele->id)
                                    ->get();

                    if($elements_a_fusionner->count() > 0)
                        $elements[$table->type_element] = $elements_a_fusionner;
                }
            }
        }

        return [$elements, $champs_a_deplacer];
    }


	public function fusionner_element($id_client_destinataire,$tables_a_fusionner){

        list($elements_a_fusionner, $champs) = $this->fusion_elements_a_deplacer();

        // On va chercher le champ "ID Element Dynamique" associé
        $champs_id_element = Champ_libre::where('type', 22)
            ->select(\DB::raw("CONCAT(contenu, '.',type_element) as champ, nom_sql"))
            ->get()
            ->pluck('nom_sql', 'champ');

        foreach($champs as $champ) {

            if(!isset($elements_a_fusionner[$champ->type_element]))
                continue;

            foreach ($elements_a_fusionner[$champ->type_element] as $element) {

                if (in_array($champ->type_element, $tables_a_fusionner)) {

                    if (Schema::hasColumn($champ->type_element, 'type_element') && Schema::hasColumn($champ->type_element, 'element_id')) {
                        management($champ->type_element, $element->id)->enregistre([
                            'element_id' => $id_client_destinataire,
                        ]);
                    }

                    // Certains éléments ont une colonne client_id ET des relations type_element/element_id, il faut faire les deux vérifications
                    if (Schema::hasColumn($champ->type_element, 'client_id')) {
                        management($champ->type_element, $element->id)->enregistre([
                            'client_id' => $id_client_destinataire,
                        ]);
                    }

                    // Pour les éléments issus de champs 21/22
                    if ($champ->type == 42) {
                        management($champ->type_element, $element->id)->enregistre([
                            $champ->nom_sql => $id_client_destinataire,
                        ]);
                    }

                    // Pour les éléments issus de champs 21/22
                    if ($champ->type == 21 && isset($champs_id_element[$champ->nom_sql . '.' . $champ->type_element])) {
                        management($champ->type_element, $element->id)->enregistre([
                            $champs_id_element[$champ->nom_sql . '.' . $champ->type_element] => $id_client_destinataire,
                        ]);
                    }
                }
            }
        }

        management('client',$this->modele->id)->supprime();
    }

    public function recuperer_nombre_elements_a_fusionner(){

        list($elements_a_fusionner, $osef) = $this->fusion_elements_a_deplacer();

        $nombre_elements = [];
        foreach($elements_a_fusionner as $type_element => $elements){

            if($elements->count() > 0)
                $nombre_elements[$type_element] = ['nom' => table_libre($type_element)->nom_table, 'nombre' => $elements->count(), 'transferer' => true];
        }

        return $nombre_elements;
    }

    /**
     *
     * On surcharge la fonction recupere_informations_pour_index_recherche(), pour ajouter des tags de recherche aux clients
     *
     */
    public function recupere_informations_pour_index_recherche(&$sql_set, &$sql_join, &$table_join_count) {

        parent::recupere_informations_pour_index_recherche($sql_set, $sql_join, $table_join_count);

        if(fonctionnalite('tags_recherche_contact_pour_client')) {

            $table_join_count++;
            $sql_join .= " LEFT JOIN (
                SELECT 
                    t$table_join_count.id,
                    t$table_join_count.client_id,
                    group_concat(
                        CONCAT(`t$table_join_count`.`nom`, '###', `t$table_join_count`.`prenom`)
                        SEPARATOR '###'
                    ) as chaine_tags_recherche 
                FROM `contact` t$table_join_count 
                GROUP BY t$table_join_count.client_id
            ) t$table_join_count on `t$table_join_count`.client_id = `$this->_type_element`.id";
            $sql_set .= "IFNULL(`t$table_join_count`.`chaine_tags_recherche`, '') , '###', ";
		}

        if(fonctionnalite('tags_recherche_adresse_pour_client')) {
            $table_join_count++;
            $sql_join .= " LEFT JOIN (
                SELECT 
                    t$table_join_count.id,
                    t$table_join_count.client_id,
                    group_concat(
                        `t$table_join_count`.`ville`
                        SEPARATOR '###'
                    ) as chaine_tags_recherche 
                FROM `adresse` t$table_join_count 
                GROUP BY t$table_join_count.client_id
            ) t$table_join_count on `t$table_join_count`.client_id = `$this->_type_element`.id";
            $sql_set .= "IFNULL(`t$table_join_count`.`chaine_tags_recherche`, '') , '###', ";
        }

    }

    public function retourne_sous_formulaire(){

        $retour = parent::retourne_sous_formulaire();

        $retour[] = [
            'type_element_enfant' => 'contact',
            'champ_liaison' => 'client_id',
            'optionnel' => 1,
            'unique' => 1,
        ];
        $retour[] = [
            'type_element_enfant' => 'adresse',
            'champ_liaison' => 'client_id',
            'data_vue' => json_encode(['type_adresse' => 2]),
            'remplacement_supplementaire' => [
                [
                    "#formulaire.adresse.adresse#",
                    "#formulaire.adresse.adresse_de_livraison#"
                ]
            ],
            'optionnel' => 1,
            'unique' => 1,
        ];
        $retour[] = [
            'type_element_enfant' => 'adresse',
            'champ_liaison' => 'client_id',
            'data_vue' => json_encode(['type_adresse' => 1]),
            'remplacement_supplementaire' => [
                [
                    "#formulaire.adresse.adresse#",
                    "#formulaire.adresse.adresse_de_facturation#"
                ]
            ],
            'optionnel' => 1,
            'unique' => 1,
        ];

        return $retour;

    }

    public function genere_fiche_pdf($formulaire, $type_element, $id_element) {

        $blocs_a_afficher = [];

        $fiche = modele($type_element, $id_element);

        foreach($formulaire->all() as $type => $bloc) {

            $type_element_tmp = str_replace('bloc_','', $type);

            $vue = "eden::pdf.fiches.$type_element.$type_element_tmp";

            // on vérifie si la vue existe
            if(view()->exists($vue)) {

                if($type_element_tmp == $type_element) {
                    $donnees = modele($type_element)->find($id_element);

                    $date_n_moins_deux = date('Y', strtotime('-2 year'));
                    $date_n_moins_deux = date('Y-m-d', strtotime($date_n_moins_deux . '-01-01'));
                    $factures_liees = modele('facture_vente')->where('client_id', $id_element)->where('date',">=", $date_n_moins_deux)->get();
                    $avoirs_lies = modele('avoir_vente')->where('client_id', $id_element)->where('date',">=", $date_n_moins_deux)->get();

                    $ca = [
                        'ca' => 0,
                        'ca_n_moins_un' => 0,
                        'ca_n_moins_deux' => 0
                    ];

                    foreach ($factures_liees as $facture_liee){

                        if(date('Y', strtotime($facture_liee['date'])) == date('Y', strtotime('-2 year'))){
                            $ca['ca_n_moins_deux'] += $facture_liee['montant_document_ht'];
                        }
                        else if(date('Y', strtotime($facture_liee['date'])) == date('Y', strtotime('-1 year'))){
                            $ca['ca_n_moins_un'] += $facture_liee['montant_document_ht'];
                        }
                        else if(date('Y', strtotime($facture_liee['date'])) == date('Y')){
                            $ca['ca'] += $facture_liee['montant_document_ht'];
                        }

                    }

                    foreach ($avoirs_lies as $avoir_lie){

                        if(date('Y', strtotime($avoir_lie['date'])) == date('Y', strtotime('-2 year'))){
                            $ca['ca_n_moins_deux'] -= $avoir_lie['montant_document_ht'];
                        }
                        else if(date('Y', strtotime($avoir_lie['date'])) == date('Y', strtotime('-1 year'))){
                            $ca['ca_n_moins_un'] -= $avoir_lie['montant_document_ht'];
                        }
                        else if(date('Y', strtotime($avoir_lie['date'])) == date('Y', strtotime(''))){
                            $ca['ca'] -= $avoir_lie['montant_document_ht'];
                        }

                    }

                    $donnees['ca'] = $ca;

                }
                else {

                    if($type_element_tmp !== "taches_rdv"){

                        $methode = 'charge_donnees_pour_pdf_pour_fiche_'.$type_element;
                        $management = management($type_element_tmp);
                    }

                    else{

                        $methode = 'charge_donnees_rdv_pour_pdf_pour_fiche_'.$type_element;
                        $management = management('tache');
                    }

                    $nombre_a_prendre_variable = 'nombre_elements_bloc_'.$type_element_tmp;

                    $nombre_a_prendre_variable = $formulaire->$nombre_a_prendre_variable;

                    // on vérifie que la méthode existe
                    if (method_exists($management, $methode)) {

                        $donnees = $management->$methode($id_element);

                    } else {

                        $donnees = modele($type_element_tmp)->where($type_element . '_id', $id_element);

                        if($nombre_a_prendre_variable === '5')
                            $donnees = $donnees->take(5)->orderBy('id', 'DESC')->get();
                        elseif ($nombre_a_prendre_variable === '10')
                            $donnees = $donnees->take(10)->orderBy('id', 'DESC')->get();
                        elseif ($nombre_a_prendre_variable === '12_mois')
                            $donnees = $donnees->whereBetween('cree_le',[Carbon::now()->subMonth(12), Carbon::now()])->orderBy('id', 'DESC')->get();
                        elseif ($nombre_a_prendre_variable === '24_mois')
                            $donnees = $donnees->whereBetween('cree_le',[Carbon::now()->subMonth(24), Carbon::now()])->orderBy('id', 'DESC')->get();
                        else
                            $donnees = $donnees->orderBy('id', 'DESC')->get();
                    }


                    // Cas particulier des échanges sur client
                    if($type_element_tmp == "echange" && $type_element == "client"){

                        $contacts_client = modele('contact')
                            ->where('client_id',$id_element)
                            ->get()
                            ->pluck('id')
                            ->toArray();

                        $donnees = modele('echange')
                            ->where('type_element', 'client')
                            ->where(function ($query) use ($contacts_client, $id_element){
                                $query->whereIn('contact_id',$contacts_client)
                                    ->orWhere('element_id', $id_element);
                            });

                        if($nombre_a_prendre_variable === '5')
                            $donnees = $donnees->take(5)->orderBy('id', 'DESC')->get();
                        elseif ($nombre_a_prendre_variable === '10')
                            $donnees = $donnees->take(10)->orderBy('id', 'DESC')->get();
                        elseif ($nombre_a_prendre_variable === '12_mois')
                            $donnees = $donnees->whereBetween('cree_le',[Carbon::now()->subMonth(12), Carbon::now()])->orderBy('id', 'DESC')->get();
                        elseif ($nombre_a_prendre_variable === '24_mois')
                            $donnees = $donnees->whereBetween('cree_le',[Carbon::now()->subMonth(24), Carbon::now()])->orderBy('id', 'DESC')->get();
                        else
                            $donnees = $donnees->orderBy('id', 'DESC')->get();

                    }
                }

                $blocs_a_afficher[$type] = array('vue' => $vue, 'donnees' => $donnees);

            }
        }

        $vue_fiche = 'eden::pdf.fiches.fiche_pdf_standard';

        if(view()->exists('eden::pdf.fiches.fiche_pdf_'.$type_element))
            $vue_fiche = 'eden::pdf.fiches.fiche_pdf_'.$type_element;

        if(!is_dir(storage_path('fonts')))
            mkdir(storage_path('fonts'));

        $pdf = PDF::loadView($vue_fiche, array('fiche' => $fiche,'blocs_a_afficher' => $blocs_a_afficher));

        if($type_element == 'fournisseur')
            $pdf = $pdf->setPaper('a4', 'landscape');

        return $pdf;
    }

    function affichage_pour_image($champ_libre){
        return (!empty($this->modele->raison_sociale) ? $this->modele->raison_sociale : $this->modele->nom.' '.$this->modele->prenom).'_'.parent::affichage_pour_image($champ_libre);
    }
}
