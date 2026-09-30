<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Controllers\Ecommerce\Famille_controller;
use App\Eden\Controllers\Ecommerce\Article_controller;

use App\Eden\Models\Panier_detail;

use App\Eden\Managements\Ecommerce\Auth_management;
use Mail;
use Storage;

class Mon_compte_controller extends Controller {

    /**
     *
     * On affiche la page d'accueil mon compte
     *
     * @return Response
     */
    public function mon_compte() {

		$client_id = session()->get('utilisateur_eden_ecommerce');
    	$client = management('client', $client_id);

		$donnees = $client->donnees_pour_ecommerce_mon_compte();

		return view('eden::ecommerce.mon-compte.dashboard', compact('donnees'));
    }

    /**
     *
     * On affiche la page d'accueil mon compte
     *
     * @return Response
     */
    public function historique_commandes() {

		$client_id = session()->get('utilisateur_eden_ecommerce');
    	$client = management('client', $client_id);

		$donnees = $client->donnees_pour_ecommerce_mon_compte();
		
		return view('eden::ecommerce.mon-compte.historique', compact('donnees'));
    }

    /**
     *
     * On affiche la page des informations personnelles
     *
     * @return Response
     */
    public function informations_personnelles() {

    	$client = modele('client', session('utilisateur_eden_ecommerce'));

		// on affiche le formulaire de connexion
    	return view('eden::ecommerce.mon-compte.informations', array('client' => $client));
    }

    /**
     *
     * On enregistre les informations personnelles
     *
     * @return Response
     */
    public function informations_personnelles_post(Request $formulaire) {

    	$client = management('client', session('utilisateur_eden_ecommerce'));

		$formulaire = $formulaire->all();

		
    	if(!empty($formulaire['mot_de_passe_actuel'])) {

			// le mot de passe actuel est correct
    		if(md5($formulaire['mot_de_passe_actuel']) != $client->modele->mot_de_passe) {

    			return redirect()->route('ecommerce.informations_personnelles')->with('erreur', traduction('messages.php.ecommerce.compte.mdp_incorrect'));
			}
			


			// les mots de passe avec la confirmation sont corrects
    		if($formulaire['nouveau_mdp'] != $formulaire['confirmer_nouveau_mdp']) {

    			return redirect()->route('ecommerce.informations_personnelles')->with('erreur', traduction('messages.php.ecommerce.compte.mdp_confirmation_incorrect'));
			}

			if(strlen($formulaire['nouveau_mdp']) <= 5) {


				return redirect()->route('ecommerce.informations_personnelles')->with('erreur', traduction('messages.php.ecommerce.compte.erreur_mdp_taille'));

			}

    		if(empty($formulaire['nouveau_mdp'])) {

    			return redirect()->route('ecommerce.informations_personnelles')->with('erreur', traduction('messages.php.ecommerce.compte.erreur_mdp_vide'));
    		}

			// ok on met à jour le mot de passe
    		$formulaire['mot_de_passe'] = md5($formulaire['nouveau_mdp']);
    	}

    	$recherche_client = modele('client')->where(['adresse_email' => $formulaire['adresse_email']])->first();

		// On vérifie que l'email n'existe pas déjà dans la base
    	if($recherche_client !== null && $recherche_client->id != $client->modele->id) {

			// On retourne un message
    		return redirect()->route('ecommerce.informations_personnelles')->with('erreur', traduction('messages.php.ecommerce.compte.adresse_email_utilisee'));
    	}

    	unset($formulaire['mot_de_passe_actuel']);
    	unset($formulaire['nouveau_mdp']);
    	unset($formulaire['confirmer_nouveau_mdp']);
    	unset($formulaire['_token']);

		// on met à jour
    	$retour = $client->enregistre($formulaire);

    	if($retour !== true) {

    		return redirect()->route('ecommerce.informations_personnelles')->with('erreur', $retour);
		}


		// on affiche le formulaire de connexion
    	return redirect()->route('ecommerce.informations_personnelles')->with('information', traduction('messages.php.ecommerce.compte.informations_maj'));
    }

    /**
     *
     * On enregistre les informations personnelles
     *
     * @return Response
     */
    public function informations_personnelles_post_ajax(Request $formulaire) {

        $client = management('client', session('utilisateur_eden_ecommerce'));

        $formulaire = $formulaire->all();
        
        if(!empty($formulaire['mot_de_passe_actuel'])) {

            // le mot de passe actuel est correct
            if(md5($formulaire['mot_de_passe_actuel']) != $client->modele->mot_de_passe) {

                return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.mdp_actuel_incorrect')]);
            }

            // les mots de passe avec la confirmation sont corrects
            if($formulaire['nouveau_mdp'] != $formulaire['confirmer_nouveau_mdp']) {

                return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.mdp_confirmation_incorrect')]);
            }

            if(empty($formulaire['nouveau_mdp'])) {

                return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.erreur_mdp_vide')]);
            }

            $testPass = $client->verifier_mot_de_passe_valide($formulaire['nouveau_mdp']) ;
            if( $testPass !== true ) {

                return json_encode(['success' => false, 'erreur' => $testPass]);
            }

            // ok on met à jour le mot de passe
            $formulaire['mot_de_passe'] = md5($formulaire['nouveau_mdp']);
        }

        $recherche_client = modele('client')->where(['adresse_email' => $formulaire['adresse_email']])->first();

        // On vérifie que l'email n'existe pas déjà dans la base
        if($recherche_client !== null && $recherche_client->id != $client->modele->id) {

            // On retourne un message
            return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.adresse_email_utilisee')]);
        }

        unset($formulaire['mot_de_passe_actuel']);
        unset($formulaire['nouveau_mdp']);
        unset($formulaire['confirmer_nouveau_mdp']);
        unset($formulaire['_token']);

        // on met à jour
        $retour = $client->enregistre($formulaire);

        if($retour !== true) {

            return json_encode(['success' => false, 'erreur' => $retour]);
        }


        // on affiche le formulaire de connexion
        return json_encode(['success' => true]);
    }

	/**
	 *
	 * On affiche la page de connexion
	 *
	 * @return Response
	 */
    public function connexion() {
		
		// on affiche le formulaire de connexion
		return view('eden::ecommerce.mon-compte.login');
	}

	/**
	 *
	 * On affiche la page de connexion pour commander 
	 *
	 * @return Response
	 */
    public function connexion_panier() {
		
		// on affiche le formulaire de connexion pour poursuivre commande du panier
		return view('eden::ecommerce.mon-compte.login',['connexion_pour_validation' => true]);
	}

    /**
     *
     * Connexion de l'utilisateur
     *
     * @return Response
     */
    public function deconnexion(Request $formulaire, Auth_management $auth_management) {

		session()->forget('utilisateur_eden_ecommerce');
		
        return redirect()->route('ecommerce.connexion');
	}
		
	/**
	 * 
	 * On affiche la page d'inscription
	 * 
	 * @return Response
	 */
	public function inscription() {
		
		
		// on affiche le formulaire de connexion
        return view('eden::ecommerce.mon-compte.inscription');
    }
	
	/**
     *
     * Inscription de l'utilisateur
     *
     * @return Response
     */
    public function inscription_post(Request $formulaire) {
		
		$management_client = management('client');
		
		$donnees = $formulaire->all();

		
		$resultat = $management_client->verifie_informations_pour_inscription_ecommerce($donnees);

		if($resultat !== true) {
			
			return redirect()->back()->with('erreur', $resultat)->withInput();
		}

		// on prépare les données
		$donnees = $management_client->prepare_informations_pour_inscription_ecommerce($donnees);

		// on crée le client
		$resultat = $management_client->creation_compte_ecommerce($donnees['client']);

		if($resultat !== true) {
			
			return redirect()->back()->with('erreur', $resultat)->withInput();
		}

		
		// ok l'inscription a fonctionnée normalement
		$retour_email = $management_client->envoie_mail_apres_inscription_ecommerce();

		if($retour_email !== true) {

			return redirect()->back()->with('erreur', [traduction('messages.php.ecommerce.compte.erreur_envoi_mail')])->withInput();

		}

		// ok l'inscription a fonctionnée, on crée également l'adresse
    	$adresse_facturation = management('adresse');
		
		$infos_creation_adresse = $donnees['adresse'];


		$infos_creation_adresse['client_id'] = $management_client->modele->id;
		$infos_creation_adresse['type_adresse'] = 1;
		
		// on crée l'adresse

    	$adresse_facturation->enregistre($infos_creation_adresse);

    	$adresse_livraison = management('adresse');

    	$infos_creation_adresse['type_adresse'] = 2;

		// on crée l'adresse
    	$adresse_livraison->enregistre($infos_creation_adresse);

		// on met en session le client
		session()->put('utilisateur_eden_ecommerce', $management_client->modele->id);
		
		// on affiche le formulaire de connexion
    	return redirect()->route('ecommerce.mon_compte');
	}
	
	
    /**
     *
     * Connexion de l'utilisateur
     *
     * @return Response
     */
    public function connexion_post(Request $formulaire, Auth_management $auth_management) {

        $resultat = $auth_management->connexion([
            'email' => $formulaire->adresse_email,
            'mot_de_passe' => $formulaire->mot_de_passe
		]);


        if($resultat['succes'] === true) {

            // on met en session le client
            session()->put('utilisateur_eden_ecommerce', $resultat['client']->id);

            // On stoque les infos clients dans la session
            $infos = management('client', $resultat['client']->id)->recupere_infos_post_connexion();
            session()->put('utilisateur_infos_ecommerce', $infos);

			return management('client', $resultat['client']->id)->redirection();
            // on redirige vers la page mon compte
        }

        // on affiche le formulaire de connexion
        return redirect()->route('ecommerce.connexion')->withInput()->with('erreur', $resultat['message']);
    }

    /**
     *
     * Connexion de l'utilisateur dans le cas où il veux retourner sur le panier
     *
     * @return Response
     */
    public function connexion_panier_post(Request $formulaire, Auth_management $auth_management) {

        $resultat = $auth_management->connexion([
            'email' => $formulaire->adresse_email,
            'mot_de_passe' => $formulaire->mot_de_passe
		]);


        if($resultat['succes'] === true) {

            // on met en session le client
            session()->put('utilisateur_eden_ecommerce', $resultat['client']->id);

			return redirect()->route('ecommerce.adresse');
            // on redirige vers la page mon compte
        }

        // on affiche le formulaire de connexion
        return redirect()->route('ecommerce.connexion')->with('erreur', $resultat['message']);
    }

    /**
     *
     * Connexion de l'utilisateur
     *
     * @return Response
     */
    public function connexion_post_json(Request $formulaire, Auth_management $auth_management) {

        $resultat = $auth_management->connexion([
		
            'email' => $formulaire->adresse_email,
            'mot_de_passe' => $formulaire->mot_de_passe
        ]);

		if($resultat['succes'] === true) {

            // on met en session le client
            session()->put('utilisateur_eden_ecommerce', $resultat['client']->id);
            
            // On renvoi également les infos clients, via le management
            $infos = management('client', $resultat['client']->id)->recupere_infos_post_connexion() ;
            session()->put('utilisateur_infos_ecommerce', $infos);
			
			// on récupère le panier courant de l'utilisateur
			$panier = modele('panier')->where('session_id', session()->getId())->where('statut', 0)->first();
			
			// il a bien un panier en cours
			if($panier !== null) {
				
				// est ce qu'il est vide ?
				$detail = Panier_detail::where('panier_id', $panier->id)->first();
				
				if($detail !== null) {
					
					// on passe son ancien panier en validé, sinon il en aura 2 en cours
					$ancien_panier = modele('panier')->where('client_id', $resultat['client']->id)->where('statut', 0)->first();
					
					if($ancien_panier !== null) {
						
						$ancien_panier->statut = 1;
						$ancien_panier->save();
					}
					
					// il n'est pas vide, on va donc trasférer ce panier vers l'utilisateur
					$panier->client_id = $resultat['client']->id;
					
					$panier->save();
					
				}
			}
			

            return ['success' => true, 'infos' => $infos];
        }
		

        // on affiche le formulaire de connexion
        return ['success' => false];
    }

    

	public function mot_de_passe_oublie() {

		return view('eden::ecommerce.mon-compte.mot_de_passe_oublie');
	}

	public function mot_de_passe_oublie_envoi_mail(Request $request) {
		
		$client = modele('client')->where('adresse_email', $request->adresse_email)->first();

		// aucun utilisateur trouvé
		if($client === null) {
			
			return redirect()->back()->withErrors([traduction('messages.php.ecommerce.compte.adresse_email_non_trouve')]);
		}
		
		$management_client = management('client', $client->id);
		
		
		// on doit calculer un token via l'utilisateur
		$token = md5($client->email.$client->id.$client->mot_de_passe);

		$lien_reinitialisation_mot_de_passe = route('ecommerce.reinitialisation_mot_de_passe', [$client->id, $token]);
		
		$retour_email = $management_client->envoie_mail_mot_de_passe_oublie($lien_reinitialisation_mot_de_passe);

		
		if($retour_email !== true) {
			
			return redirect()->back()->with('erreur', traduction('messages.php.ecommerce.compte.erreur_envoi_mail'))->withInput();

		}

		return redirect()->back()->with('succes', traduction('messages.php.ecommerce.compte.envoi_mail'));
	}


	public function mot_de_passe_oublie_envoi_mail_ajax(Request $request) {

		$client = modele('client')->where('adresse_email', $request->adresse_email)->first();
		$management_client = management('client', $client->id);

		// aucun utilisateur trouvé
		if($client === null) {
			
			return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.adresse_email_non_trouve')]) ;
		}
		
		$token = md5($client->email.$client->id.$client->mot_de_passe);

		$lien_reinitialisation_mot_de_passe = route('ecommerce.reinitialisation_mot_de_passe', [$client->id, $token]);


		$management_client->envoie_mail_mot_de_passe_oublie($lien_reinitialisation_mot_de_passe);

		return json_encode(['success' => true]) ;
	}


	public function reinitialisation_mot_de_passe($id, $token) {
		
		$client = modele('client')->find($id);

		if($client === null) {
			
			return redirect()->route('ecommerce.mot_de_passe_oublie')->withErrors([traduction('messages.php.ecommerce.compte.erreur_survenue')])->with('erreur', traduction('messages.php.ecommerce.compte.erreur_survenue'));
		}

		$token_base_de_donnees = md5($client->email.$client->id.$client->mot_de_passe);
		
		if($token_base_de_donnees != $token) {
			
			return redirect()->route('ecommerce.mot_de_passe_oublie')->withErrors([traduction('messages.php.ecommerce.compte.erreur_survenue')." (Error #2)"])->with('erreur', traduction('messages.php.ecommerce.compte.erreur_survenue')." (Error #2)");
		}

		return view('eden::ecommerce.mon-compte.reinitialisation_mot_de_passe', [
			
			'id_client' => $client->id,
			'token' => $token_base_de_donnees,
		]);

	}

	public function reinitialisation_mot_de_passe_post(Request $request) {
		
		// on va chercher l'utilisateur
		$client = modele('client')->find($request->id_client);

		// aucun utilisateur trouvé
		if($client === null) {
			
			return redirect()->back()->withErrors(traduction('messages.php.ecommerce.compte.erreur_survenue')." (Error #1)")->with('erreur', traduction('messages.php.ecommerce.compte.erreur_survenue')." (Error #1)");
		}
		
		// on doit calculer un token via l'utilisateur
		$token_base_de_donnees = md5($client->email.$client->id.$client->mot_de_passe);
		
		if($token_base_de_donnees != $request->token) {
			
			return redirect()->back()->withErrors(traduction('messages.php.ecommerce.compte.erreur_survenue')." (Error #2)")->with('erreur', traduction('messages.php.ecommerce.compte.erreur_survenue')." (Error #2)");
		}
		
		if($request->mot_de_passe != $request->mot_de_passe_confirmation) {
			
			return redirect()->back()->withErrors(traduction('messages.php.ecommerce.compte.mdp_non_identiques'))->with('erreur', traduction('messages.php.ecommerce.compte.mdp_non_identiques'));
		}


		// on modifie l'utilisateur en base
		$client->mot_de_passe = md5($request->mot_de_passe);
		$client->save();
		
		// on met l'utilisateur en session
		session()->put('utilisateur_eden_ecommerce', $client->id);
		
		// il y a un remember token
	/* 	if($formulaire->get('remember') == 'on') {
			
			// on crée un remember token
			$remember_token = md5($utilisateur->id . date('dmyHis') . $formulaire->password);
			
			$utilisateur->remember_token = $remember_token;
			$utilisateur->save();
			
			Cookie::queue(Cookie::make('remember_token', $remember_token, 60 * 24 * 365));
		} */
		
		// on redirige vers l'accueil
		return redirect()->route('ecommerce.mon_compte');
	}

	public function reinitialisation_mot_de_passe_post_ajax(Request $request) {

		// on va chercher l'utilisateur
		$client = modele('client')->find($request->id_client);

		// aucun utilisateur trouvé
		if($client === null) {
			
			return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.erreur_reinitialisation_compte_client')]) ;
		}
		
		// on doit calculer un token via l'utilisateur
		$token_base_de_donnees = md5($client->email.$client->id.$client->mot_de_passe);
		
		if($token_base_de_donnees != $request->token) {
			
			return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.erreur_reinitialisation_token')]) ;
		}
		
		
		if($request->mot_de_passe != $request->mot_de_passe_confirmation) {
			
			return json_encode(['success' => false, 'erreur' => traduction('messages.php.ecommerce.compte.mdp_non_identiques')]);
		}
		
		$ret_valide = management('client')->verifier_mot_de_passe_valide($request->mot_de_passe) ;
		if( $ret_valide !== true ) {
			
			return json_encode(['success' => false, 'erreur' => $ret_valide]);
		}





		// on modifie l'utilisateur en base
		$client->mot_de_passe = md5($request->mot_de_passe);
		$client->save();
		
		// on met l'utilisateur en session
		session()->put('utilisateur_eden_ecommerce', $client->id);
		
		return json_encode(['success' => true]) ;

	}

	/**
     *
     * Affiche la liste des adresses du client et permet de les modifier
     *
     */
	public function adresses() {

		$adresses = modele('adresse')->where('client_id', session('utilisateur_eden_ecommerce'))->get();

		// on affiche le formulaire de connexion
		return view('eden::ecommerce.mon-compte.adresses', array('adresses' => $adresses));
	}

	/**
     *
     * On met à jour une adresse
     *
     */
	public function adresses_post(Request $formulaire) {

		$modification = false;
		$informations_adresse = $formulaire->all();

		foreach($informations_adresse as $key => $information_adresse) {

			if(substr($key, -6) == "_modal") {

				$informations_adresse[str_replace('_modal', '', $key)] = $information_adresse;
				unset($informations_adresse[$key]);
			}
		}

		$adresse = management('adresse');

		if(isset($informations_adresse['id'])) {

			$modification = true;
			$adresse = management('adresse', $informations_adresse['id']);
		}
		

		$informations_adresse['client_id'] = session('utilisateur_eden_ecommerce');

		// on vérifie que l'adresse est bien celle du client
		if(!empty($informations_adresse['id']) && $adresse->modele->client_id != session('utilisateur_eden_ecommerce')) {

			return redirect()->route('ecommerce.adresses')->with('erreur', traduction('messages.php.ecommerce.compte.erreur_survenue'));
		}

		unset($informations_adresse['id']);

		// on met à jour
		$retour = $adresse->enregistre($informations_adresse);

		if($retour !== true) {

			return redirect()->route('ecommerce.adresses')->with('erreur', $retour);
		}

		
		if(!$modification)
			return redirect()->route('ecommerce.adresses')->with('information', traduction('messages.php.ecommerce.compte.adresse_creee'));
		else
			return redirect()->route('ecommerce.adresses')->with('information', traduction('messages.php.ecommerce.compte.adresse_modifiee'));
	}

	/**
     *
     * Supprimer une adresse pour le client
     *
     */
	public function supprimer_adresse($id) {

		$adresse = management('adresse', $id);

		// on vérifie que l'adresse est bien celle du client
		if($adresse->modele->client_id != session('utilisateur_eden_ecommerce')) {

			return redirect()->route('ecommerce.adresses')->with('erreur', traduction('messages.php.ecommerce.compte.erreur_survenue'));
		}

		$retour = $adresse->supprime();

		if($retour !== true) {

			return redirect()->route('ecommerce.adresses')->with('erreur', $retour);
		}


		return redirect()->route('ecommerce.adresses')->with('information', traduction('messages.php.ecommerce.compte.adresse_supprimee'));
	}


	public function afficher_commande ($id) {

		$facture_vente = management('facture_vente', $id) ;
		$client = management('client', session('utilisateur_eden_ecommerce'));
		if ( $client->droit_facture($facture_vente) ) {
			return view('eden::ecommerce.mon-compte.commande', array('facture_vente' => $facture_vente));
		}
		return view('eden::ecommerce.mon-compte.commande', array('erreur' => traduction('messages.php.ecommerce.compte.acces_refuse')));

	}


	public function telecharger_facture ($id) {

		$facture_vente = management('facture_vente', $id) ;
		$client = management('client', session('utilisateur_eden_ecommerce'));
		if ( $facture_vente->modele->client_id != $client->modele->id ) {
			return view('eden::ecommerce.mon-compte.commande', array('erreur' => traduction('messages.php.ecommerce.compte.acces_refuse')));
		}
		
		$nom_du_pdf = $facture_vente->modele->pdf ;
		header("Content-type:application/pdf");
		header("Content-Disposition:inline;filename='".$facture_vente->modele->pdf."'");
		echo Storage::get($nom_du_pdf) ;
		exit ;
	}
}
