<?php

namespace App\Eden\Managements\Elements;
use App\Eden\Models\Champ_libre;

class Budget_insight_management extends Element_management {
	
	/**
	 * 
	 * Permet de mettre à jour une connexion
	 * 
	 */
	public function recupere_lien_mise_a_jour_connexion($token) {

		$opts = array('http' =>
				  array(
				    'header'  => 	"Content-Type: application/json\r\n".
				      				"Authorization: Bearer ".$token."\r\n"
				  )
				);
				
		$context = stream_context_create($opts);
		
		$retour = json_decode(file_get_contents('https://easydeveloppement.biapi.pro/2.0/auth/token/code', false, $context));


		/*
        $parametres = array(
		
            'response_type' => 'code',
            'client_id'     => config('services.budget_insight.client_id'),
            'redirect_uri'  => route('eden.budget_insight'),
            'state'         => '',
            'code'          => $retour->code,
        );
		
		return 'https://easydeveloppement.biapi.pro/2.0/auth/webview/fr/manage/?' . http_build_query($parametres, null, '&');
		*/

		// on récupère le connection_id
		$synchro = modele('budget_insight_synchro')->where('token', $token)->first();

        $budget_insight_compte = modele('budget_insight_comptes')->where('synchro_id', $synchro->id)->first();

        $connection_id = $budget_insight_compte->id_connection;

		$client_id = config('services.budget_insight.client_id');

		if(config('fonctionnalites_integrations.budget_insight_client_id') !=null)
            $client_id = config('fonctionnalites_integrations.budget_insight_client_id');

		$parametres = array(
            'domain' => 'easydeveloppement.biapi.pro',
            'response_type' => 'code',
            'reset_credentials' => 'true',
            'client_id'     => $client_id,
            'redirect_uri'  => route('budget_insight.index'),
            'connection_id' => $connection_id,
            'code'          => $retour->code,
        );
		
        return 'https://webview.powens.com/fr/reconnect?' . http_build_query($parametres, null, '&');

    }
	
	/**
	 * 
     * Synchronisation selon les tokens enregistrés
	 * 
     */
	public function synchronisation($jours) {
		
		$synchros = modele('budget_insight_synchro')->get();
		
		// les comptes bancaires
		foreach($synchros as $synchro) {
			
			// permet de vérifier si une authentification est nécessaire (si il y a eu un changement de mot de passe par exemple)
			$infos_connexion = $this->recupere_banques($synchro->token);
			
			$comptes = $this->recupere_comptes($synchro->token);

			$this->enregistre_compte($comptes, $synchro, $infos_connexion);
		}
		
		// les transactions
		foreach($synchros as $synchro) {

			$this->declenche_synchronisation($synchro->token);

			$transactions = $this->recupere_transactions($synchro->token, $jours);
			
			$this->enregistre_transactions($transactions);
		}
		
		
		return true;
	}
	
	/**
	 * 
     * Dédoublonnage des transactions
	 * 
     */
	public function dedoublonnage() {
		
		$synchros = modele('budget_insight_synchro')->get();
		
		// les transactions
		foreach($synchros as $synchro) {
			
			// supprime les transactions disparues de BI
			$this->supprime_transactions_disparues($synchro->token, $synchro->id);
		}
		
		
		return true;
	}

    /**
	 * 
     *	Récupère et retourne un token de connexion
	 * 
     */
    public function get_token($code) {
		
		sleep(2);

        $client_id = config('services.budget_insight.client_id');

        if(config('fonctionnalites_integrations.budget_insight_client_id') != null)
            $client_id = config('fonctionnalites_integrations.budget_insight_client_id');

        $client_secret = config('services.budget_insight.client_secret');

        if(config('fonctionnalites_integrations.budget_insight_client_secret') != null)
            $client_secret = config('fonctionnalites_integrations.budget_insight_client_secret');

		$retour = ($this->file_get_contents_post('https://easydeveloppement.biapi.pro/2.0/auth/token/access',
				array(
					'client_id'=>$client_id,
					'client_secret'=>$client_secret,
					'code'=>$code
				), '' ));
		sleep(2);
		return json_decode($retour);
    }


    /**
     *
     * Déclenche la synchronisation de budget insight
     *
     */
    private function declenche_synchronisation($token) {
		
		$url = 'https://easydeveloppement.biapi.pro/2.0/users/me/connections';
		 
		$ch = curl_init($url);
		 
		// C'est une requete PUT
		curl_setopt($ch, CURLOPT_PUT, true);

		// On transmet les headers
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				    'header'  => 	"Content-Type: application/json\r\n".
				      				"Authorization: Bearer ".$token."\r\n"
				  ));
		 
		//We want the result / output returned.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		// On peut transmettre des données
		$fields = array();
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		 
		//Execute the request.
		$response = curl_exec($ch);

		return json_decode($response);
    }



    /**
	 * 
     *	Récupère et retourne les transactions associées au Token chez Budget Insight
	 * 
     */
    public function recupere_transactions($token, $jours) {

		$opts = array('http' =>
				  array(
				    'header'  => 	"Content-Type: application/json\r\n".
				      				"Authorization: Bearer ".$token."\r\n"
				  )
				);
				
		$context = stream_context_create($opts);

		$retour = json_decode(file_get_contents('https://easydeveloppement.biapi.pro/2.0/users/me/transactions?last_update='.date('Y-m-d', strtotime('today -'.$jours.' days')).'&limit=1000', false, $context));
        $transactions = $retour->transactions;

        while(isset($retour->_links->next->href)){

            $retour = json_decode(file_get_contents($retour->_links->next->href, false, $context));

            if(!empty($retour->transactions))
                $transactions = array_merge($transactions, $retour->transactions);
        }

        return $transactions;
    }


    /**
	 * 
     *	Récupère et retourne les comptes associés au Token chez Budget Insight
	 * 
     */
    public function recupere_comptes($token) {
		
		$opts = array('http' =>
				  array(
				    'header'  => 	"Content-Type: application/json\r\n".
				      				"Authorization: Bearer ".$token."\r\n"
				  )
				);
				
		$context = stream_context_create($opts);
		$retour = json_decode(file_get_contents('https://easydeveloppement.biapi.pro/2.0/users/me/accounts', false, $context));
		
		return $retour->accounts;
    }
	
	/**
	 * 
	 * Retourne de l'information sur la connexion actuelle
	 * 
	 */
	public function recupere_banques($token) {
		
		$opts = array('http' =>
				  array(
				    'header'  => 	"Content-Type: application/json\r\n".
				      				"Authorization: Bearer ".$token."\r\n"
				  )
				);
				
		$context = stream_context_create($opts);
		$retour = json_decode(file_get_contents('https://easydeveloppement.biapi.pro/2.0/users/me/connections?expand=bank', false, $context));

		return $retour;
	}


    /**
	 * 
     * Enregistre les infos des comptes en BDD 
	 * 
     */
    public function enregistre_compte($comptes, $synchro, $infos_connexion) {

        $id_synchro = $synchro->id;
		
    	$champs = array ('id','coming_balance','loan','webid','number','bookmarked','formatted_balance','id_connection','original_name','last_update','usage','type','deleted','id_parent','bic','iban','id_type','ownership','coming','id_user','name','error','balance','display');

        $comptes_tmp = modele('budget_insight_comptes')->where('synchro_id', $id_synchro)->get();

        $modifications = null;

        if(empty($infos_connexion->connections)){
            $modifications = array(

				'error' => 'Connexion impossible',
				'state' => '1-eden',
			);
        }
        else if(empty($comptes)){
            $modifications = array(
				'error' => $infos_connexion->connections[0]->error,
				'state' => $infos_connexion->connections[0]->state
			);
        }

        if(!empty($modifications)){

            foreach($comptes_tmp as $compte) {

				management('budget_insight_comptes', $compte->id,$compte)->enregistre_modele($modifications);
			}
        }

        if(empty($infos_connexion->connections) || empty($comptes))
            return;
		
    	foreach ($comptes as $modifications_brut) {
			
    		$modifications_brut = (array) $modifications_brut;
			
    		$modifications = array();

    		foreach ($champs as $champ) {

    			if (isset($modifications_brut[$champ]))
    				$modifications[$champ] = $modifications_brut[$champ];

    		}

    		$compte_en_base = modele('budget_insight_comptes')->avec_inactifs()->sans_profils()->where('id', $modifications['id'])->first();
			
			if($compte_en_base !== null && $compte_en_base->inactif == 1)
				continue;
			
			// je commente cette partie, car je ne suis pas sur de comprendre à quoi elle sert ?
			// if($compte_en_base !== null && $compte_en_base->{'utilisateur_'.moi()->id} != 1)
				// continue;

    		$compte = management('budget_insight_comptes', (isset($compte_en_base->id) ? $compte_en_base->id : false) );
			
			// on rajoute l'info de la synchro id
			$modifications['synchro_id'] = $id_synchro;

			// on rajoute l'état
			$modifications['error'] = $infos_connexion->connections[0]->error;
			$modifications['state'] = $infos_connexion->connections[0]->state;
			$modifications['last_update'] = $infos_connexion->connections[0]->last_update;
			$modifications['banque'] = $infos_connexion->connections[0]->bank->name;
			
			$retour = $compte->enregistre($modifications);
			
		}
    }


    /**
	 * 
     * Enregistre les infos des transactions en BDD 
	 * 
     */
    public function enregistre_transactions($transactions) {

    	$entites_comptes = modele('budget_insight_comptes')
    					->get()
    					->pluck('entite_id', 'id')
    					->toArray();

    	$champs = Champ_libre::where('type_element', 'budget_insight_transaction')
    							->where('nom_sql', '!=', 'modifie_le')
    							->where('nom_sql', '!=', 'cree_le')
    							->where('nom_sql', '!=', 'cree_par')
    							->where('nom_sql', '!=', 'modifie_par')
    							->get()
    							->pluck('nom_sql')
    							->toArray();
						
		foreach($transactions as $transaction) {
			
			// $transaction_initiale = $transaction;
			
			$transaction = (array) $transaction ;
			
			if (isset($transaction['counterparty']) && is_object($transaction['counterparty'])) {

				$counterparty = (array) $transaction['counterparty'];

				$transaction['counterparty_label'] = $counterparty['label'] ?? null;
				$transaction['counterparty_type']  = $counterparty['type'] ?? null;

				if (($counterparty['account_scheme_name'] ?? null) === 'iban') {
					$transaction['counterparty_iban'] = $counterparty['account_identification'] ?? null;
				} else {
					$transaction['counterparty_iban'] = null;
				}
			}

			$uid_eden = 'v2-'.$transaction['id'];
			
			$transaction_en_base = modele('budget_insight_transaction')->where('uid_eden', $uid_eden)->first();
			
			if($transaction_en_base !== null) {
				
				$transaction_management = management('budget_insight_transaction', $transaction_en_base->id);
			}
			else {
				
				$transaction_management = management('budget_insight_transaction');
			}
				
			
			$modifications = array();

			foreach ($champs as $champ) {
				
				if(isset($transaction[$champ]) && !is_object($transaction[$champ])){

                    //On gère différemment le champ type car c'est une liste formatée (502)
                    if($champ == 'type'){

                        switch ($transaction[$champ]){
                            case 'order':
                                $modifications[$champ] = 1;
                                break;
                            case 'check':
                                $modifications[$champ] = 2;
                                break;
                            case 'deposit':
                                $modifications[$champ] = 3;
                                break;
                            case 'card':
                                $modifications[$champ] = 4;
                                break;
                            case 'summary_card':
                                $modifications[$champ] = 5;
                                break;
                            case 'withdrawal':
                                $modifications[$champ] = 6;
                                break;
                            case 'transfer':
                                $modifications[$champ] = 7;
                                break;
                            case 'bank':
                                $modifications[$champ] = 8;
                                break;
                            case 'payback':
                                $modifications[$champ] = 9;
                                break;
                            case 'loan_payment':
                                $modifications[$champ] = 10;
                                break;
                            case 'deferred_card':
                                $modifications[$champ] = 11;
                                break;
                            default:
                                $modifications[$champ] = 0;
                        }
                        continue;
                    }

                    $modifications[$champ] = $transaction[$champ];
                }

			}
			
    		if(isset($entites_comptes[$modifications['id_account']]) )
    			$modifications['entite_id'] = $entites_comptes[$modifications['id_account']];
			
			$modifications['uid_eden'] = $uid_eden;
			
			if(empty($transaction_management->modele))
				$modifications['a_rapprocher'] = $transaction['value'];
			
			$modifications['debit'] = 0;
			$modifications['credit'] = 0;
			
			if($transaction['value'] < 0)
				$modifications['debit'] = $transaction['value'];
			else
				$modifications['credit'] = $transaction['value'];
			
			unset($modifications['id']);

            if(!empty($transaction_management->modele->id)) {
                foreach ($modifications as $nom_sql => $modification) {
                    if($transaction_management->modele->{$nom_sql} == $modification)
                        unset($modifications[$nom_sql]);
                }
            }

            if(empty($modifications))
                continue;

    		$retour = $transaction_management->enregistre($modifications);
    	}
    }
	
	/**
	 * 
	 * Supprime les transactions qui n'existent plus chez BI
	 * 
	 */
	public function supprime_transactions_disparues($token, $id_synchro) {
		
		$comptes = modele('budget_insight_comptes')->where('synchro_id', $id_synchro)->get()->pluck('id')->toArray();
		
		if(empty($comptes))
			return;
		
		$transactions = modele('budget_insight_transaction')->where('date', '>=', date('Y-m-d', strtotime('now -2 days')))->whereIn('id_account', $comptes)->get();
		
		foreach($transactions as $transaction) {
			
			$opts = array('http' =>
				  array(
				    'header'  => 	"Content-Type: application/json\r\n".
				      				"Authorization: Bearer ".$token."\r\n"
				  )
				);
				
			$context = stream_context_create($opts);
			
			$transaction_budget_insight = json_decode(@file_get_contents('https://easydeveloppement.biapi.pro/2.0/users/me/transactions/'.str_replace('v2-', '', $transaction->uid_eden), false, $context));
			
			if(empty($transaction_budget_insight)) {
				
				if(empty($transaction->statut_eden)) {
					
					management('budget_insight_transaction', $transaction->id, $transaction)->supprime();
				}
			}
				
		}
	}
	
	/**
	 * 
	 * @todo à commenter
	 * 
	 */
	private function file_get_contents_post($url, $post, $token) {
		
		$postdata = http_build_query($post);

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer ".$token,
		        'content' => $postdata
		    )
		);

		set_error_handler(
		    function ($severity, $message, $file, $line) {
		        return $message;
		    }
		);
		$context  = stream_context_create($opts);
		try {
			$result = file_get_contents($url, false, $context);
		} catch (Exception $e) {
		    return false;
		}
		restore_error_handler();

		return $result ;
	}

}