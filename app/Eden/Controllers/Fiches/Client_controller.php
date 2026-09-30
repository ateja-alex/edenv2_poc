<?php

namespace App\Eden\Controllers\Fiches;

use App\Eden\Controllers\Fiche_controller;
use App\Eden\Models\Client_centre_d_interet;
use Illuminate\Http\Request;

use App\Eden\Models\Profil_familial_client;

use PDF;

class Client_controller extends Fiche_controller {

	/**
	 *
	 * Enregistre le profil familial a partir de la fiche du client
	 *
	 */
	public function enregistrer_profil_familial($formulaire, $type_element, $id_element) {

		$profil_familial = Profil_familial_client::where('client_id', $id_element)->first();

		if($profil_familial === null) {

			$profil_familial = new Profil_familial_client;
		}

		$profil_familial->client_id = $id_element;

		$profil_familial->statut = $formulaire->statut;
		$profil_familial->prenom_du_conjoint = $formulaire->prenom_du_conjoint;
		$profil_familial->nom_du_conjoint = $formulaire->nom_du_conjoint;
		$profil_familial->date_de_naissance_du_conjoint = formate_date('Y-m-d', $formulaire->date_de_naissance_du_conjoint);
		$profil_familial->nombre_enfants = $formulaire->nombre_enfants;
		$profil_familial->date_de_mariage = formate_date('Y-m-d', $formulaire->date_de_mariage);

		$profil_familial->nom_enfant_1 = $formulaire->nom_enfant_1;
		$profil_familial->prenom_enfant_1 = $formulaire->prenom_enfant_1;
		$profil_familial->date_de_naissance_enfant_1 = formate_date('Y-m-d', $formulaire->date_de_naissance_enfant_1);

		$profil_familial->nom_enfant_2 = $formulaire->nom_enfant_2;
		$profil_familial->prenom_enfant_2 = $formulaire->prenom_enfant_2;
		$profil_familial->date_de_naissance_enfant_2 = formate_date('Y-m-d', $formulaire->date_de_naissance_enfant_2);

		$profil_familial->nom_enfant_3 = $formulaire->nom_enfant_3;
		$profil_familial->prenom_enfant_3 = $formulaire->prenom_enfant_3;
		$profil_familial->date_de_naissance_enfant_3 = formate_date('Y-m-d', $formulaire->date_de_naissance_enfant_3);

		$profil_familial->nom_enfant_4 = $formulaire->nom_enfant_4;
		$profil_familial->prenom_enfant_4 = $formulaire->prenom_enfant_4;
		$profil_familial->date_de_naissance_enfant_4 = formate_date('Y-m-d', $formulaire->date_de_naissance_enfant_4);

		$profil_familial->nom_enfant_5 = $formulaire->nom_enfant_5;
		$profil_familial->prenom_enfant_5 = $formulaire->prenom_enfant_5;
		$profil_familial->date_de_naissance_enfant_5 = formate_date('Y-m-d', $formulaire->date_de_naissance_enfant_5);

		$profil_familial->relation_membre_famille_1 = $formulaire->relation_membre_famille_1;
		$profil_familial->nom_membre_famille_1 = $formulaire->nom_membre_famille_1;
		$profil_familial->prenom_membre_famille_1 = $formulaire->prenom_membre_famille_1;
		$profil_familial->commentaire_membre_famille_1 = $formulaire->commentaire_membre_famille_1;
		$profil_familial->date_naissance_membre_famille_1 = formate_date('Y-m-d', $formulaire->date_naissance_membre_famille_1);

		$profil_familial->relation_membre_famille_2 = $formulaire->relation_membre_famille_2;
		$profil_familial->nom_membre_famille_2 = $formulaire->nom_membre_famille_2;
		$profil_familial->prenom_membre_famille_2 = $formulaire->prenom_membre_famille_2;
		$profil_familial->commentaire_membre_famille_2 = $formulaire->commentaire_membre_famille_2;
		$profil_familial->date_naissance_membre_famille_2 = formate_date('Y-m-d', $formulaire->date_naissance_membre_famille_2);

		$profil_familial->relation_membre_famille_3 = $formulaire->relation_membre_famille_3;
		$profil_familial->nom_membre_famille_3 = $formulaire->nom_membre_famille_3;
		$profil_familial->prenom_membre_famille_3 = $formulaire->prenom_membre_famille_3;
		$profil_familial->commentaire_membre_famille_3 = $formulaire->commentaire_membre_famille_3;
		$profil_familial->date_naissance_membre_famille_3 = formate_date('Y-m-d', $formulaire->date_naissance_membre_famille_3);

		$profil_familial->relation_membre_famille_4 = $formulaire->relation_membre_famille_4;
		$profil_familial->nom_membre_famille_4 = $formulaire->nom_membre_famille_4;
		$profil_familial->prenom_membre_famille_4 = $formulaire->prenom_membre_famille_4;
		$profil_familial->commentaire_membre_famille_4 = $formulaire->commentaire_membre_famille_4;
		$profil_familial->date_naissance_membre_famille_4 = formate_date('Y-m-d', $formulaire->date_naissance_membre_famille_4);

		$profil_familial->relation_membre_famille_5 = $formulaire->relation_membre_famille_5;
		$profil_familial->nom_membre_famille_5 = $formulaire->nom_membre_famille_5;
		$profil_familial->prenom_membre_famille_5 = $formulaire->prenom_membre_famille_5;
		$profil_familial->commentaire_membre_famille_5 = $formulaire->commentaire_membre_famille_5;
		$profil_familial->date_naissance_membre_famille_5 = formate_date('Y-m-d', $formulaire->date_naissance_membre_famille_5);

		$profil_familial->save();

		return response()->json(true);
	}

	public function taches() {

        $taches = modele('tache')->where('client_id', $this->id_element)->get();

        $modele_par_defaut = modele_par_defaut('tache');

		return response()->json(array('taches' => $taches,'modele_par_defaut' => $modele_par_defaut));
    }

	public function projets() {

		return response()->json(modele('projet')->where('client_id', $this->id_element)->get());
	}

	public function credits() {
		
		// on va chercher le management
		$management = fiche($this->type_element, $this->id_element);
		
		return array(
			
			'credits' => $management->credits(),
			'indicateurs' => $management->indicateurs_credits(),
		);
	}

	public function mettre_a_jour_centres_d_interet(Request $formulaire, $type_element, $id_element) {
		$ancien_centres_d_interet_client = Client_centre_d_interet::where('client_id', $id_element)->get();

		$centres_d_interet_client = array_keys($formulaire->all());

		$centres_d_interet = modele('centre_d_interet')->where('parent_id', '!=', 0)->get();

		foreach($centres_d_interet as $centre_d_interet) {

			$ancien_centre_d_interet = $ancien_centres_d_interet_client->where('centre_d_interet_id', $centre_d_interet->id);

			if(in_array($centre_d_interet->id, $centres_d_interet_client) && $ancien_centre_d_interet->count() == 0) {

				$nouveau_centre_d_interet = new Client_centre_d_interet;
				$nouveau_centre_d_interet->client_id = $id_element;
				$nouveau_centre_d_interet->centre_d_interet_id = $centre_d_interet->id;
				$nouveau_centre_d_interet->save();
			} elseif(!in_array($centre_d_interet->id, $centres_d_interet_client) && $ancien_centre_d_interet->count() > 0) {

				$ancien_centre_d_interet->first()->delete();
			}
		}

		return json_encode(array('retour' => true));
	}

	/**
	 * 
	 * Récupère les articles, pour les réductions commerciales
	 * 
	 */
	public function recupere_articles(Request $request) {

		$famille = modele('famille', $request->famille_id);

		if($famille->parent_id != null) {

			$articles = modele('article')->where('famille_id', $famille->id)->get();
		}
		else {

			$famille_enfants = modele('famille')->where('parent_id', $famille->id)->get()->pluck('id')->toArray();

			array_push($famille_enfants, $famille->id);
			$articles = modele('article')->whereIn('famille_id', $famille_enfants)->get();
		}

        return response()->json(['success' => true, 'articles' => $articles]);

	}

	/**
	 * 
	 * Enregistre un paramétrage avancé des documents
	 * 
	 */
	public function parametrage_avance_des_documents_enregistre() {
		
		// on enregistre
		$donnees = request()->all();
		
		if(isset($donnees['champs_obligatoires']) && is_array($donnees['champs_obligatoires'])) {
			
			$donnees['champs_obligatoires'] = json_encode($donnees['champs_obligatoires']);
		}
		else {
			
			$donnees['champs_obligatoires'] = json_encode([]);
		}
		
		// on va modifier le bon élément
		$modele = modele('parametrage_avance_des_documents')->where('client_id', $donnees['client_id'])->where('type_element', $donnees['type_element'])->first();
		
		if($modele === null) {
			
			$management = management('parametrage_avance_des_documents');
		}
		else {
			
			$management = management('parametrage_avance_des_documents', $modele->id);
		}
		
		$management->enregistre($donnees);
		
		$management = fiche($this->type_element, $this->id_element);
		
		return $management->parametrage_avance_des_documents();
	}

	/**
	 * 
	 * 
	 * Retourne les articles facturés selon une période de date
	 * 
	 * 
	 */
	public function filtre_date_articles_factures(Request $formulaire) {

		$id_element = $this->id_element;
		$type_element = $this->type_element;
		$dates = $formulaire->all();

		$articles_factures = fiche($type_element, $id_element)->articles_factures($dates);
		$articles_factures_par_famille = fiche($type_element, $id_element)->articles_factures_par_famille($dates);

		$articles_factures_par_famille_graphique = collect();

		foreach($articles_factures_par_famille as $id_famille => $montant) {

			$nom = 'Non précisé';
			if(!empty($id_famille))
				$nom = modele('famille')->find($id_famille)->nom;

			$articles_factures_par_famille_graphique->push(['name' => $nom, 'y' => $montant]);
		}

		return response()->json(['retour' => true, 'articles_factures' => $articles_factures, 'articles_factures_par_famille_graphique' => $articles_factures_par_famille_graphique]);
	}

    public function valider_tache($formulaire, $type_element, $id_element){

        $retour = management('tache',$formulaire->tache_id)->enregistre(['terminee' => 1]);

        return response()->json(['retour' => $retour]);

    }
	
	/**
	 *
	 * Récupère les coupons de réduction
	 *
	 */
	public function recupere_coupons_reduction($type_element, $id_element) {

		$client = fiche('client', $this->id_element);

		return response()->json($client->coupons_reductions());
	}

    /**
     *
     * Change le statut d'un client via clique sur fiche ( badge )
     *
     */
    public function change_statut($type_element, $element_id, $id_statut) {

        $client = modele('client', $element_id);

        $client->categorie_de_client = $id_statut;
        $client->save();

        return redirect()->back();
    }

    public function generer_fiche_pdf($formulaire, $type_element, $id_element) {

        // On déplace la méthode dans un management pour pouvoir surcharger en spé
        $pdf = management('client', $id_element)->genere_fiche_pdf($formulaire, $type_element, $id_element);

        return $pdf->stream('document.pdf');
    }

    public function recuperer_nombre_elements_a_fusionner($formulaire, $type_element, $id_element) {

        return management('client', $id_element)->recuperer_nombre_elements_a_fusionner($formulaire, $type_element, $id_element);
    }
}
