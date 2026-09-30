<?php

namespace App\Eden\Controllers\Api;

use App\Eden\Champs\Champ;
use App\Eden\Managements\Maintenance_management;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Eden\Managements\Parametres_erp_management;

use App\Eden\Variables;

use Log;
use Illuminate\Support\Facades\DB;

use Validator;

class Api_controller extends BaseController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	private $erp_login_controller;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

		if(!defined('id_utilisateur'))
			define('id_utilisateur', 0);
    }

	/**
	*
	* Envoi un retour API
	*
	* Toutes les toutes de l'API doivent se terminer par cette méthode
	*
	*/
	private function retour($statut, $donnees, $code_erreur = false) {

		return response()->json(array('statut' => $statut, 'donnees' => $donnees, 'code_erreur' => $code_erreur));
	}

	/**
	*
	* Retourne un retour sans erreur
	*
	*/
	protected function ok($donnees) {

		return $this->retour(true, $donnees);
	}


	/**
	*
	* Retourne un retour avec erreur
	*
	*/
	protected function ko($donnees) {

		return $this->retour(false, $donnees);
	}

	/**
	*
	* Permet de lister les éléments
	*
	*/
	public function list(Request $parametres, $type_element) {

		$elements = modele($type_element);

		if($parametres->get('deleted') == 1) {

			$elements = $elements->avec_inactifs();
		}

		if($parametres->get('where') !== null) {

			$wheres = $parametres->get('where');

			foreach($wheres as $where) {

                if(is_array($where[0])){

                    $elements = $elements->where(function($sous_requete) use ($where){

                        foreach($where as $sous_where){

                            $condition = 'where';

                            if(isset($sous_where['condition']))
                                $condition = $sous_where['condition'];

                            if(isset($sous_where[2]))
                                $sous_requete = $sous_requete->{$condition}($sous_where[0], $sous_where[1], $sous_where[2]);

                            elseif(isset($sous_where[1]))
                                $sous_requete = $sous_requete->{$condition}($sous_where[0], $sous_where[1]);

                            else
                                $sous_requete = $sous_requete->{$condition}($sous_where[0]);
                        }
                    });
                }
                else {

                    $condition = 'where';

                    if(isset($where['condition']))
                        $condition = $where['condition'];

                    if(isset($where[2]))
                        $elements = $elements->{$condition}($where[0], $where[1], $where[2]);

                    elseif(isset($where[1]))
                        $elements = $elements->{$condition}($where[0], $where[1]);

                    else
                        $elements = $elements->{$condition}($where[0]);
                }
			}
		}

        if($parametres->get('joins') !== null) {

			$joins = $parametres->get('joins');

			foreach($joins as $join) {

				$elements = $elements->join($join[0], $join[1], $join[2]);
			}
		}

		if($parametres->get('take') !== null) {

			$elements = $elements->take($parametres->get('take'));
		}

		if($parametres->get('select') !== null) {

			$elements = $elements->select($parametres->get('select'));
		}


		if($parametres->get('orderBy') !== null) {

			if(is_array($parametres->get('orderBy'))) {

				foreach($parametres->get('orderBy') as $colonne) {

					if(is_array($colonne)) {

						$elements = $elements->orderBy($colonne[0], $colonne[1]);
					}
					else {

						$elements = $elements->orderBy($colonne);
					}
				}
			}
			else {

				$elements = $elements->orderBy($parametres->get('orderBy'));
			}
		}

		if($parametres->get('groupBy') !== null) {

			$elements = $elements->orderBy($parametres->get('groupBy'));
		}

		$retour = $elements->get();
		$champs_libres = table_libre($type_element)->champs_libres()->get();

		if($retour !== null) {

			foreach($retour as $modele) {

				$relations = array();

				if(isset($parametres->relations) && is_array($parametres->relations)) {

					foreach($parametres->relations as $infos_relations) {

						if(!empty($modele->{$infos_relations[1]}))
							$relations[$infos_relations[1]] = management($infos_relations[0], $modele->{$infos_relations[1]})->modele;
					}
				}

				$modele->relations_modele = $relations;

				if(isset($parametres->avec_valeurs_txt)) {

					$management = management($type_element, $modele->id);

					foreach($champs_libres as $champ) {

						$modele->{$champ->nom_sql.'_txt'} = $management->champ($champ->nom_sql)->affiche();
					}
				}
			}
		}

		return $this->ok($retour);
	}

	/**
	*
	* Permet de sauvegarder un élément (création ou modification)
	*
	*/
	public function save(Request $parametres, $type_element, $id_element = false) {

		

		$management = management($type_element, $id_element);

		$retour = $management->enregistre($parametres->all());

		if(in_array($type_element, Variables::$documents_gescom)) {

			$management->modele->articles = $management->articles();
		}

		// $this->ko(get_class($management));

		if($retour === true)
			return $this->ok($management->modele);
		else
			return $this->ko($retour);
	}

	/**
	*
	* Permet de supprimer un élément
	*
	*/
	public function delete(Request $parametres, $type_element, $id_element) {

		

		$management = management($type_element, $id_element);

		$retour = $management->supprime();

		if($retour === true)
			return $this->ok($management->modele);
		else
			return $this->ko($retour);
	}

	/**
	*
	* Permet de récupérer un élément
	*
	*/
	public function get(Request $parametres, $type_element, $id_element) {

		

		$management = management($type_element, $id_element);

		$relations = array();

		if(isset($parametres->relations) && is_array($parametres->relations)) {

			foreach($parametres->relations as $infos_relations) {

				$relations[$infos_relations[1]] = management($infos_relations[0], $management->modele->{$infos_relations[1]})->modele;
			}
		}

		if(in_array($type_element, Variables::$documents_gescom)) {

			$management->modele->articles = $management->articles();
		}

		$management->modele->relations_modele = $relations;

		return $this->ok($management->retourne_pour_api());
	}

	/**
	*
	* Valider un élément (de gestion commerciale)
	*
	*/
	public function valid(Request $parametres, $type_element, $id_element) {

		

		$management = management($type_element, $id_element);

		$retour = $management->valide();

		// $this->ko(get_class($management));

		if(in_array($type_element, Variables::$documents_gescom)) {

			$management->modele->articles = $management->articles();
		}

		if($retour === true)
			return $this->ok($management->modele);
		else
			return $this->ko($retour);
	}

    /**
     * 
     * Transformer un document
     * 
     */
    public function transform_document($type_element, $id_element, $type_transformation){

        $document_origine = management($type_element, $id_element);

        $transformations_possibles_par_type = $document_origine->transformations_possibles();

        $transformations_possibles = [];

        foreach($transformations_possibles_par_type as $type => $documents){
            $transformations_possibles = array_merge($transformations_possibles,array_keys($documents));
        }

        if(!in_array($type_transformation,$transformations_possibles))
            return $this->ko('Ce document ne peut être transformer en '.$type_transformation);

        list($retour,$nouveau_document) = $document_origine->transformer_document($type_transformation, request()->modifications ?? []);

        if($retour === true)
			return $this->ok($nouveau_document->modele);
		else
			return $this->ko($retour);
    }

    /**
     * 
     * Mettre à jour le prix d'un document
     * 
     */
    public function update_price($type_element, $id_element){

        $document = management($type_element, $id_element);

        if(empty($document->modele->id))
            return $this->ko('Le document n\'existe pas');

        $tarif_uniquement = request()->tarif_uniquement ?? false;
        
        $articles = $document->articles()->toArray();

        $parametres = [
            'date_document' =>  $document->modele->date,
            'catalogue_groupement_id' =>  $document->modele->catalogue_groupement_id,
        ];

        if($document->est_une_vente())
            $parametres['client_id'] = $document->modele->client_id;
        else
            $parametres['fournisseur_id'] = $document->modele->fournisseur_id;

        $articles = $document->mise_a_jour_tarif($articles,$parametres,$tarif_uniquement,true);

        $retour = $document->enregistre(array('articles' => $articles));

        if($retour === true)
		    return $this->ok(true);
        else
            return $this->ko($retour);
    }

	/**
	*
	* Envoyer un email via un modele eden
	*
	*/
	public function email_modele_eden(Request $parametres) {

		

		$retour = service('email')->envoyer_modele($parametres->modele_email_id, $parametres->adresse_email, $parametres->type_element, $parametres->element_id);

        if($retour === true)
		    return $this->ok(true);
        else
            return $this->ko($retour);
	}

	/**
	 *
	 *  Récupérer une liste de paramètres
	 *
	 */
	public function settings(Request $parametres) {

		

		$liste = Parametres_erp_management::liste($parametres->settings);

		return $this->ok(array('settings' => $liste));
	}

	public function recupere_contacts_avec_telephone($telephone, Request $requete){

        Log::info('headers : ');
        Log::info($requete->header());
        Log::info('body : ');
        Log::info($requete->all());
        Log::info('url : ');
        Log::info($requete->fullUrl());

        //On ne prend que les 9 derniers chiffres pour pouvoir prendre en compte les numéros qui commencent par +33
        $telephone = '%' . substr($telephone, -9);

        $contacts = modele('contact')
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '.', ''), '/', '') LIKE ?" , $telephone)
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telephone_portable, ' ', ''), '-', ''), '.', ''), '/', '') LIKE ?" , $telephone)
            ->get();
        $fournisseurs = modele('fournisseur')
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '.', ''), '/', '') LIKE ?" , $telephone)
            ->get();
        $leads = modele('lead')
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '.', ''), '/', '') LIKE ?" , $telephone)
            ->get();
        $clients = modele('client')
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '.', ''), '/', '') LIKE ?" , $telephone)
            ->get();

        $contacts_a_retourner = array();

        foreach($contacts as $contact){

            $donnees = [
                'contact' => [
                    'id' => $contact->id,
                    'prenom' => $contact->prenom,
                    'nom' => $contact->nom,
                    'email' => $contact->adresse_email,
                    'telephone' => $contact->telephone,
                    'portable' => $contact->telephone_portable,
                ]];

            $client = modele('client')->where('id', $contact->client_id)->first();

            if(!empty($client)){

                $donnees['contact']['client'] = $client->nom . ' ' . $client->prenom;
                $donnees['contact']['url'] = url('eden/fiche/client/' . $contact->client_id);
            }
            else {

                $donnees['contact']['client'] = '';
                $donnees['contact']['url'] = url('eden/fiche/contact/' . $contact->id);
            }

            $contacts_a_retourner[] = $donnees;
        }

        foreach($fournisseurs as $fournisseur){

            $donnees = [
                'fournisseur' => [
                    'id' => $fournisseur->id,
                    'prenom' => '',
                    'nom' => $fournisseur->nom,
                    'client' => '',
                    'email' => $fournisseur->adresse_email,
                    'telephone' => $fournisseur->telephone,
                    'portable' => '',
                    'url' => url('eden/fiche/fournisseur/' . $fournisseur->id),
                ]];

            $contacts_a_retourner[] = $donnees;
        }

        foreach($leads as $lead){

            $client = modele('client', $lead->client_id);

            $contacts_a_retourner[] = [
                'lead' => [
                    'id' => $lead->id,
                    'prenom' => $lead->prenom,
                    'nom' => $lead->nom,
                    'client' => '',
                    'email' => $lead->adresse_email,
                    'telephone' => $lead->telephone,
                    'portable' => '',
                    'url' => url('eden/fiche/lead/' . $lead->id),
                ]];
        }

        foreach($clients as $client){

            $contacts_a_retourner[] = [
                'client' => [
                    'id' => $client->id,
                    'prenom' => $client->prenom,
                    'nom' => $client->nom,
                    'client' => $client->nom . ' ' . $client->prenom,
                    'email' => $client->adresse_email,
                    'telephone' => $client->telephone,
                    'portable' => '',
                    'url' => url('eden/fiche/client/' . $client->id),
                ]];
        }

        return json_encode($contacts_a_retourner,JSON_UNESCAPED_SLASHES);
    }

    public function recupere_contacts_avec_email($email, Request $requete){

        Log::info('headers : ');
        Log::info($requete->header());
        Log::info('body : ');
        Log::info($requete->all());
        Log::info('url : ');
        Log::info($requete->fullUrl());

        

        $contacts = modele('contact')->where('adresse_email', $email)->get();
        $fournisseurs = modele('fournisseur')->where('adresse_email', $email)->get();
        $leads = modele('lead')->where('adresse_email', $email)->get();
        $clients = modele('client')->where('adresse_email', $email)->get();

        $contacts_a_retourner = array();

        foreach($contacts as $contact){

            $donnees = [
                'contact' => [
                    'id' => $contact->id,
                    'prenom' => $contact->prenom,
                    'nom' => $contact->nom,
                    'email' => $contact->adresse_email,
                    'telephone' => $contact->telephone,
                    'portable' => $contact->telephone_portable,
                ]];

            $client = modele('client')->where('id', $contact->client_id)->first();

            if(!empty($client)){

                $donnees['contact']['client'] = $client->nom . ' ' . $client->prenom;
                $donnees['contact']['url'] = url('eden/fiche/client/' . $contact->client_id);
            }
            else {

                $donnees['contact']['client'] = '';
                $donnees['contact']['url'] = url('eden/fiche/contact/' . $contact->id);
            }

            $contacts_a_retourner[] = $donnees;
        }

        foreach($fournisseurs as $fournisseur){

            $donnees = [
                'fournisseur' => [
                    'id' => $fournisseur->id,
                    'prenom' => '',
                    'nom' => $fournisseur->nom,
                    'client' => '',
                    'email' => $fournisseur->adresse_email,
                    'telephone' => $fournisseur->telephone,
                    'portable' => '',
                    'url' => url('eden/fiche/fournisseur/' . $fournisseur->id),
                ]];

            $contacts_a_retourner[] = $donnees;
        }

        foreach($leads as $lead){

            $client = modele('client', $lead->client_id);

            $contacts_a_retourner[] = [
                'lead' => [
                    'id' => $lead->id,
                    'prenom' => $lead->prenom,
                    'nom' => $lead->nom,
                    'client' => '',
                    'email' => $lead->adresse_email,
                    'telephone' => $lead->telephone,
                    'portable' => '',
                    'url' => url('eden/fiche/lead/' . $lead->id),
                ]];
        }

        foreach($clients as $client){

            $contacts_a_retourner[] = [
                'client' => [
                    'id' => $client->id,
                    'prenom' => $client->prenom,
                    'nom' => $client->nom,
                    'client' => $client->nom . ' ' . $client->prenom,
                    'email' => $client->adresse_email,
                    'telephone' => $client->telephone,
                    'portable' => '',
                    'url' => url('eden/fiche/client/' . $client->id),
                ]];
        }

        return json_encode($contacts_a_retourner,JSON_UNESCAPED_SLASHES);
    }

    public function recupere_utilisateurs_par_type(){

        

        $utilisateurs = modele('utilisateur')->get();

        $nombre_pages_vues = modele('log_page')->where('date', 'like', date('Y-m'). '-%')->count();
        $utilisateurs_par_type = array();

        foreach($utilisateurs as $utilisateur){

            $utilisateurs_par_type[$utilisateur->type_utilisateur][$utilisateur->id] = $utilisateur;
        }

        $utilisateurs_par_type['tous_les_utilisateurs'] = modele('utilisateur')->whereNotIn('type_utilisateur', array(3,2))->where('autorise_a_se_connecter',1)->get();

        return json_encode(['utilisateurs' => $utilisateurs_par_type, 'nombre_pages_vues' =>  $nombre_pages_vues]);
    }

    /**
	*
	* Permet de créer plusieurs éléments
	*
	*/
	public function ajout_traductions(Request $parametres_par_elements) {

		

        $traductions_existantes = modele('traduction_index')->get()->pluck('index')->toArray();
        $categories = Champ::recuperer_valeur_listes_preenregistrees(590)['liste'];
        $langues = modele('traduction_langue')->get()->pluck('code')->toArray();

        $parametres_par_elements = $parametres_par_elements->all();

        $index_existants = array();
        $index_vides = array();
        $valeurs_vides = array();
        $categories_inexistantes = array();
        $langues_inexistantes = array();

        foreach($parametres_par_elements as $ligne => $parametres) {

            if(!isset($parametres['index']) || empty($parametres['index']))
                $index_vides[] = $ligne;

            else if(in_array($parametres['index'],$traductions_existantes))
                $index_existants[] = $parametres['index'];

            if(!isset($parametres['valeur']) || empty($parametres['valeur']))
                $valeurs_vides[] = $ligne;

            if(!isset($categories[$parametres['categorie']]))
                $categories_inexistantes[] = $parametres['categorie'];

            if(!in_array($parametres['langue'],$langues))
                $langues_inexistantes[] = $parametres['langue'];

        }

        if(!empty($index_existants) || !empty($index_vides) || !empty($valeurs_vides) || !empty($categories_inexistantes) || !empty($langues_inexistantes)) {

            $message= '';

            if(!empty($index_existants))
                $message .= traduction('messages.php.api_traduction.erreur_index_deja_existant')." : ".implode(',',$index_existants)." \n";

            if(!empty($index_vides))
                $message .= traduction('messages.php.api_traduction.erreur_index_vide')." : ".implode(',',$index_vides)." \n";

            if(!empty($valeurs_vides))
                $message .= traduction('messages.php.api_traduction.erreur_valeur_vide')." : ".implode(',',$valeurs_vides)." \n";

            if(!empty($categories_inexistantes))
                $message .= traduction('messages.php.api_traduction.erreur_categorie')." : ".implode(',',$categories_inexistantes)." \n";

            if(!empty($langues_inexistantes))
                $message .= traduction('messages.php.api_traduction.erreur_langue')." : ".implode(',',$langues_inexistantes);

            if($message != '')
                return response()->json(array('retour' => false, 'message' => $message));
        }

        foreach($parametres_par_elements as $parametres) {

            management('traduction_index')->enregistre(
                array(
                    'index' => $parametres['index'],
                    'categorie' => $parametres['categorie'],
                )
            );

            management('traduction_valeur')->enregistre(
                array(
                    'index' => $parametres['index'],
                    'langue' => $parametres['langue'],
                    'traduction_standard' => $parametres['valeur'],
                )
            );

        }

        return response()->json(array('retour' => true));
	}

    /**
	*
	* Permet de synchroniser les nouvelles valeurs de traduction lors de la synchronisation des environnements
	*
	*/
	public function ajout_traductions_synchronisation(Request $parametres) {

        

        $parametres = $parametres->all();

        $retour = service('traduction')->ajout_traductions_synchronisation($parametres);

        $retour['statut'] = true;

        return response()->json($retour);
    }

    /**
     *
     * Permet de mettre à jour les utilisateurs d'un projet
     *
     */
    public function maj_utilisateurs_projet(Request $requete){

        

        $utilisateurs = $requete->get('utilisateurs');

        $retour = Maintenance_management::maj_utilisateurs_easydev($utilisateurs);

        if(!empty($retour))
            return response()->json(array('retour' => false, 'erreur' => $retour));

        return response()->json(array('retour' => true));
    }

    /**
     *
     * Permet de mettre à jour les licences d'un projet
     *
     */
    public function maj_licences_projet(Request $requete){

        $elements_par_type = $requete->get('elements_par_type');

        $retour = service('licence')->synchronisation($elements_par_type);

        if(!empty($retour))
            return response()->json(array('retour' => false, 'erreur' => $retour));

        return response()->json(array('retour' => true));
    }

    /**
     *
     * Permet de synchroniser les jours d'indisponibilites
     *
     */
    public function maj_jour_indisponibilites(){

        $parametres = request()->all();

        service('jour_indisponibilite')->verification_synchronisation_indisponibilite($parametres['date_debut'],$parametres['date_fin']);

        $jour_indisponibilites = modele('jour_indisponibilite')->get();

        return response($jour_indisponibilites);
    }

    /**
     *
     *  Permet d'envoyer un mail avec la configuration par défaut du projet
     *
     */
    public function envoyer_mail(Request $request){

        $parametres = $request->all();

        $service_email = service('email');

        $retour = $service_email->envoyer_avec_contenu($parametres['contenu'], $parametres['variables_mail'], $parametres['parametres']);

        if($retour !== true)
            return $this->ko($retour);

        return $this->ok(true);
    }

    public function synchronisation_service($id_element){

        $synchronisation_service_element = modele('synchronisation_service_element')->find($id_element);

        if(empty($synchronisation_service_element) || $synchronisation_service_element->type_synchronisation != 1 || !empty($synchronisation_service_element->desactive))
            return $this->ko(traduction('messages.php.synchronisation_service_element.synchronisation_desactivee'));

        queue('webhook_synchronisation_externe')::dispatch(management('synchronisation_service_element', $id_element, $synchronisation_service_element),request()->all());

        return $this->ok(true);
    }
}
