<?php

namespace App\Eden\Controllers\Addon_messagerie\Addon_messagerie_controller;

use Illuminate\Http\Request;
use App\Eden\Models\Champ_libre;

class Addon_messagerie_controller extends Controller {
	
	public function __construct() {
		// on identifie l'utilisateur
		$authentification_management = new \App\Eden\Managements\Authentification_management;
		
		list($retour, $utilisateur) = $authentification_management->connexion(request()->email, request()->mot_de_passe);
		
		if($retour !== true) {
			
			return response()->json(array($retour));
		}
		
		// ok utilisateur correct, on doit le connecter
		session()->put('utilisateur_eden', $utilisateur);		
	}
	
	//La fonction recuperation_client permet de renvoyer les infos du client s'il existe afin de le récupérer dans l'add-on sinon nous devront le créer depuis l'add-on
	public function recuperation_client(Request $request){
		
		//On vérifie que le client existe par son mail ou son id s'il est fourni
		$mail = $request->post('mail');
		$id = $request->post('client_id');
		if($id !== ""){
			$client = modele('client')->where('id', $id)->first();
		}
		else{
			$client = modele('client')->where('adresse_email', $mail)->first();
		}

		//Si le client existe on vérifie si l'adresse mail correspond à un contact et on l'ajoute au tableau retourné.
		if($client !== null){
			
			$contact = modele('contact')->where('adresse_email', $mail)->first();

			if($contact !== null){
				$tableau_retour = $client;
				$tableau_retour['nom_contact'] = $contact['nom'];
				$tableau_retour['prenom_contact'] = $contact['prenom'];
				$tableau_retour['email_contact'] = $contact['adresse_email'];
				return response()->json($tableau_retour, 200);
			}

			return response()->json($client, 200);
		}
		//Si le client n'existe pas on vérifie si l'adresse mail correspond à un contact si oui on récupère le client associé au contact sinon on ne retourne rien
		else{
			$contact = modele('contact')->where('adresse_email', $mail)->get()->toArray();

			if(!empty($contact)){
				$client = modele('client')->where('id', $contact['client_id'])->first();
				$tableau_retour = $client;
				$tableau_retour['contact'] = $contact;
				return response()->json($tableau_retour, 200);
			}
			else{
				return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_client_associe')], 200);
			}
		}

	}

	public function recuperation_contact(Request $request){

		//On vérifie que le contact existe via son id ou son mail selon ce qui est fournie.
		if($request->post('id') !== null){
			$id = $request->post('id');
			$contact = modele('contact')->where('id', $id)->first();
		}
		else{
			$mail = $request->post('mail');
			$contact = modele('contact')->where('adresse_email', $mail)->first();
		}

		//On retourne toutes les infos du contact s'ul existe
		if($contact !== null){
			return response()->json($contact, 200);
		}
		else{
			return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_correspondance_contact')], 200);
		}

	}

	public function recuperation_champs_obligatoires(){
		$liste_champs_obligatoires = Champ_libre::where('type_element', 'client')->where('obligatoire', '1')->where(function($query){
			$query->where('type', '0')
				  ->orWhere('type','1')
				  ->orWhere('type','20');
		})
		->get()->toArray();
		if(!empty($liste_champs_obligatoires)){
			return response()->json($liste_champs_obligatoires, 200);
		}
		else{			
			return response()->json(['error', traduction('messages.php.addon_messagerie.erreur_champ_obligatoire_formulaire')], 200);
		}
	}

	public function recuperation_valeurs_liste(Request $request){
		$nom_sql = $request->post('nom_sql');

		$valeurs_liste = management('client')->champ($nom_sql)->valeurs_possibles;

		return response()->json($valeurs_liste, 200);
	}

	public function ajout_contact(Request $request){
		
		//On récupère toutes les valeurs fournies par l'add-on
		$nom = $request->post('nom');
		$prenom = $request->post('prenom');
		$adresse_email = $request->post('adresse_email');
		$telephone = $request->post('telephone');
		$telephone_portable = $request->post('telephone_portable');
		$poste = $request->post('poste');
		$client_id = $request->post('client_id');
		
		//On les envoies dans la base de donnée afin de créer un nouveau contact
		$management_contact = management('contact');
		$management_contact->enregistre(array('nom' => $nom , 'prenom' => $prenom , 'adresse_email' => $adresse_email , 'telephone' => $telephone , 'telephone_portable' => $telephone_portable , 'poste' => $poste , 'client_id' => $client_id));
		
		return response()->json(['valide' => 'valide'],200);
	}

	public function ajout_client(Request $request){
		
		//On récupère toutes les valeurs fournies par l'add-on
		$tout = $request->all();
		
		//On les envoies dans la base de donnée afin de créer un nouveau contact
		$management_client = management('client');
		$management_client->enregistre($tout);
		
		return response()->json(['valide' => 'valide'],200);
	}

	public function recherche_contact(Request $request){

		//On récupère toutes les valeurs fournies par l'add-on
		$valeur_recherche = $request->post('valeur_recherche');
		$client_id = $request->post('client_id');

		if($client_id !== null){
			//On récupère tous les contacts qui répondent aux critères.
			$liste_contact = modele('contact')->where('chaine_tags_recherche', 'like', '%'.$valeur_recherche.'%')->where('client_id', $client_id)->get()->toArray();			
		}
		else{
			//On récupère tous les contacts qui répondent aux critères.
			$liste_contact = modele('contact')->where('chaine_tags_recherche', 'like', '%'.$valeur_recherche.'%')->get()->toArray();			
		}

		//Si la liste n'est pas vide on renvoi la liste sinon on renvoi un tableau erreur.
		if(!empty($liste_contact)){

			return response()->json($liste_contact, 200);
		}
		else{
			return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_correspondance_contact_recherche')], 200);
		}

	}

	public function recherche_client(Request $request){

		//On récupère toutes les valeurs fournies par l'add-on
		$valeur_recherche = $request->post('valeur_recherche');
		$client_id = $request->post('client_id');
		$client_id = json_decode($client_id);

		//On récupère tous les contacts qui répondent aux critères.
		$liste_client = modele('client')->where('chaine_tags_recherche', 'like', '%'.$valeur_recherche.'%')->orWhere('adresse_email',$valeur_recherche)->get()->toArray();
		if($client_id !== null){
			foreach($client_id as $id){
				if(!empty($liste_client)){
					foreach($liste_client as $tableau_client){
						if($tableau_client['id'] !== $id){
							$client = modele('client')->where('id', $id)->first()->toArray();
							$liste_client[] = $client;
						}
					}
				}
				else{
					$client = modele('client')->where('id', $id)->first()->toArray();
					$liste_client[] = $client;
				}
			}
		}

		//Si la liste n'est pas vide on renvoi la liste sinon on renvoi un tableau erreur.
		if(!empty($liste_client)){

			return response()->json($liste_client, 200);
		}
		else{
			return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_correspondance_client_recherche')], 200);
		}

	}

	public function maj_contact(Request $request){
		//On récupère toutes les valeurs fournies par l'add-on
		$id = $request->post('id');
		$mail = $request->post('adresse_email');
		$nom = $request->post('nom');
		$prenom = $request->post('prenom');
		$poste = $request->post('poste');

		//On récupère le contact correspondant à l'id.
		$management_contact = management('contact',$id);

		//On met à jour ses infos
		$management_contact->enregistre(array('nom' => $nom , 'prenom' => $prenom , 'adresse_email' => $mail , 'poste' => $poste));
	}

	public function stock_mail(Request $request){

		//On récupère toutes les valeurs fournies par l'add-on
		$contact_id = $request->post('contact_id');
		$date = $request->post('date');
		$description = $request->post('description');
		$element_id = $request->post('element_id');

		//On ajoute le mail à la base de donnée.
		$management_contact = management('echange');
		$management_contact->enregistre(array('date' => $date , 'contact_id' => $contact_id , 'description' => $description , 'type_element' => 'client' , 'element_id' => $element_id , 'type' => '3'));
	}

}