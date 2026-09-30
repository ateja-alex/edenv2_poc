<?php

namespace App\Eden\Controllers;

use App\Eden\Models\Parametre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eden\Variables;

use App;

class Treso_controller  extends Controller{

    /*
     *
     * On récupère les mouvements exceptionnels
     *
     */    
    public function recuperer_mouvement_exceptionnel() {

        // Recupération des mois et des dates necessaires aux mouvements exceptionnels 
        
        $mois = array();
        $dates = array();
        
        for($i=-1;$i<12;$i++){

            $date = strtotime(''.$i.' months');

            array_push($dates,date('Y-m', $date).'-01');
            array_push($mois,strtoupper(Variables::mois_de_lannee_format_complet_majuscule(date('m', $date))));
        }

        $entites = $this->recuperer_entite();

        $mouvements_exceptionnels = modele('treso_mouvement_exceptionnel')->where('entite_id',$entites['entite_choisi']->id)->orderBy('ordre')->get();

        $montants_mensuels_actuel =array();

        foreach($mouvements_exceptionnels as $mouvement_exceptionnel){

            // Conversion des tableaux des mois selectionnes au format json de chaque mouvement exceptionnel
            
            $mouvement_exceptionnel->montants_mensuels=json_decode($mouvement_exceptionnel->montants_mensuels);

            for($i=0;$i<13;$i++){
                
                // Recupération des montants mensuels à partir du mois actuel 

               // if(array_key_exists($dates[$i], $mouvement_exceptionnel->montants_mensuels )){
                    if(isset($mouvement_exceptionnel->montants_mensuels->{$dates[$i]})){

                        $montants_mensuels_actuel[$dates[$i]] = $mouvement_exceptionnel->montants_mensuels->{$dates[$i]};

                }

                else{

                    $montants_mensuels_actuel[$dates[$i]] = null;
                }
            }

            $mouvement_exceptionnel->montants_mensuels = $montants_mensuels_actuel;
        } 
        
        return ['dates' => $dates , 'mois' => $mois , 'mouvements_exceptionnels' => $mouvements_exceptionnels,'entites' => $entites['entites_non_choisi'], 'entite_choisi' => $entites['entite_choisi']];
    }

    /*
     *
     * Récupération des créances clients
     *
     */
    public function recuperer_creance_client() {

        // Recupération des mois et des dates necessaires aux creances clients
        $mois = array();
        $dates = array();
        
        for($i=-1;$i<12;$i++){

            $date = strtotime(''.$i.' months');

            array_push($dates,date('Y-m', $date).'-01');
            array_push($mois,strtoupper(Variables::mois_de_lannee_format_complet_majuscule(date('m', $date))));
        }

        $entites = $this->recuperer_entite();

        $creances_clients = modele('treso_creance_client')->where('entite_id',$entites['entite_choisi']->id)->orderBy('ordre')->get();
        
        $montants_mensuels_actuel =array();

        foreach($creances_clients as $creance_client){

            // Conversion des tableaux des mois selectionnes au format json de chaque creance client
            
            $creance_client->montants_mensuels=json_decode($creance_client->montants_mensuels);

            for($i=0;$i<13;$i++){

                // Recupération des montants mensuels à partir du mois actuel 

                if(isset($creance_client->montants_mensuels->{$dates[$i]})){
                    
                    $montants_mensuels_actuel[$dates[$i]] = $creance_client->montants_mensuels->{$dates[$i]};

                }

                else{

                    $montants_mensuels_actuel[$dates[$i]] = 0;
                }
            }

            $creance_client->montants_mensuels = $montants_mensuels_actuel;
        } 

        return ['dates' => $dates , 'mois' => $mois, 'creances_clients' => $creances_clients,'entites' => $entites['entites_non_choisi'], 'entite_choisi' => $entites['entite_choisi']];
    }

    /*
     *
     * Récupération des revenus récurrents
     *
     */
    public function recuperer_revenu_recurrent() {

        // Recupération des mois et des dates necessaires aux revenus recurrents
        $mois = array();
        $dates = array();

        for($i=0;$i<12;$i++){

            $date = strtotime(''.$i.' months');

            array_push($dates,date('Y-m', $date).'-01');
            array_push($mois,strtoupper(Variables::mois_de_lannee_format_complet_majuscule(date('m', $date))));
        }

        $entites = $this->recuperer_entite();

        $revenus_recurrents = modele('treso_revenu_recurrent')->where('entite_id',$entites['entite_choisi']->id)->orderBy('ordre')->get();

        $montants_mensuels_actuel = array();
        
        foreach($revenus_recurrents as $revenu_recurrent){

            // Conversion des tableaux des mois selectionnes au format json de chaque revenu recurrent

            $revenu_recurrent->montants_mensuels=json_decode($revenu_recurrent->montants_mensuels);

            for($i=0;$i<12;$i++){

                // Recupération des montants mensuels à partir du mois actuel 
                if(isset($revenu_recurrent->montants_mensuels->{$dates[$i]}))
                    $montants_mensuels_actuel[$dates[$i]] = $revenu_recurrent->montants_mensuels->{$dates[$i]};
                else
                    $montants_mensuels_actuel[$dates[$i]] = 0;

            }

            $revenu_recurrent->montants_mensuels = $montants_mensuels_actuel;
        } 

        return ['dates' => $dates , 'mois' => $mois, 'revenus_recurrents' => $revenus_recurrents,'entites' => $entites['entites_non_choisi'], 'entite_choisi' => $entites['entite_choisi']];
    }

    public function recuperer_charge_recurrente() {

        $entites = $this->recuperer_entite();

        $charges_recurrentes = modele('treso_charge_recurrente')->where('entite_id',$entites['entite_choisi']->id)->orderBy('ordre')->get();
        
        // Conversion des tableaux des mois selectionnes au format json de chaque charge récurrente

        foreach($charges_recurrentes as $charge_recurrente){
            
            $charge_recurrente->mois_selectionnes=json_decode($charge_recurrente->mois_selectionnes);
        }

        return ['charges_recurrentes' => $charges_recurrentes,'entites' => $entites['entites_non_choisi'], 'entite_choisi' => $entites['entite_choisi']];
    }

    /*
     *
     * On récupère les rapprochements
     *
     */
    public function recuperer_rapprochement() {

        $entites = $this->recuperer_entite();

        $charges_recurrentes = modele('treso_charge_recurrente')->where('entite_id',$entites['entite_choisi']->id)->orderBy('ordre')->get();

        $date = date('Y-m').'-01  00:00:00';

        $mois_actuel = intval(date('m'));

        $charges_recurrentes_actuelles = array();

        $charges_recurrentes_decaissees_actuelles = array();

        foreach($charges_recurrentes as $charge_recurrente){

            // Conversion des tableaux des mois selectionnes au format json de chaque charge récurrente

            $charge_recurrente->mois_selectionnes=json_decode($charge_recurrente->mois_selectionnes);

            // Vérification si la charge est active durant le mois actuel

            if( $charge_recurrente->mois_selectionnes->{$mois_actuel} ){

                // Vérification si la charge a déjà été décaissé ce mois-ci
                
                if(modele('treso_charge_decaissee')->where('date',$date)->where('id_charge_recurrente',$charge_recurrente->id)->get()->isEmpty()){

                    array_push($charges_recurrentes_actuelles, $charge_recurrente);
    
                }
                
                else{
    
                    array_push($charges_recurrentes_decaissees_actuelles, $charge_recurrente);    
                }
                
            }
            
        }
            
        $transactions = modele('budget_insight_transaction')->where('entite_id',$entites['entite_choisi']->id)->where('date','like',date('Y-m').'-%')->orderBy('date')->get();

        $affichage_transaction = parametre_utilisateur('affichage_transaction');

        if(!is_numeric($affichage_transaction)){

            parametre_utilisateur('affichage_transaction',0);
            $affichage_transaction = parametre_utilisateur('affichage_transaction');
        }
        
        return [
            'charges_recurrentes_actuelles' => $charges_recurrentes_actuelles, 
            'charges_recurrentes_decaissees_actuelles' => $charges_recurrentes_decaissees_actuelles,
            'transactions' => $transactions,
            'affichage_transaction' => $affichage_transaction,
            'date' => $date,
            'entites' => $entites['entites_non_choisi'], 'entite_choisi' => $entites['entite_choisi']];
    }
    
    public function affichage($type_element) {

        $type_element_fonction = 'recuperer_'.$type_element;

        if($type_element == 'visualisation_tresorerie'){

            return view('eden::tresorerie.'.$type_element,$this->$type_element_fonction());
        }

        return view('eden::tresorerie.'.$type_element.'_saisie',$this->$type_element_fonction());
    }
    
    public function recuperer_visualisation_tresorerie() {
		
		// on va chercher le solde actuel en banque
		$solde_actuel_en_banque = 0;
		$soldes_par_compte = array();
		
		$synchros = modele('budget_insight_synchro')->get();
		
		foreach($synchros as $synchro) {
			
			$comptes = management('budget_insight')->recupere_comptes($synchro->token);
			
			if(!empty($comptes)) {
				
				foreach($comptes as $compte) {
					$compte_en_base = modele('budget_insight_comptes')->where('id', $compte->id)->first();
					
					if($compte_en_base === null)
						continue;
					
					$solde_actuel_en_banque += $compte->balance;
					$soldes_par_compte[] = array($compte->name, $compte->balance, $compte_en_base['entite_id']);
				}
			}
		}
		
        $dates = array();
        $mois = array();

        $date_actuelle = date('Y-m').'-01  00:00:00';

        $dates_mois_dernier = date('Y-m', strtotime('-1 months')).'-01';

        for($i=0;$i<12;$i++){
            
            $date = strtotime(''.$i.' months');

            array_push($dates,date('Y-m', $date).'-01');
            array_push($mois,strtoupper(Variables::mois_de_lannee_format_complet_majuscule(date('m', $date))));
        }
        

        $nombre_de_mois_enregistrer = parametre_utilisateur('treso_visualisation_tresorerie');

        if(!is_numeric($nombre_de_mois_enregistrer)){

            parametre_utilisateur('treso_visualisation_tresorerie',12);
            $nombre_de_mois_enregistrer = parametre_utilisateur('treso_visualisation_tresorerie');
        }

        // Création des différents tableaux nécessaires à l'affichage

        $total_charges_recurrentes = array();
        $total_creances_clients = array();
        $total_revenus_recurrents = array();
        $total_mouvements_exceptionnels = array();

        $total_mensuel = array();

        $solde_global = array();

        $solde_actuel = $solde_actuel_en_banque;

        for($i=0;$i<12;$i++){

            $total_charges_recurrentes[$dates[$i]]= 0;
            $total_creances_clients[$dates[$i]] = 0;
            $total_revenus_recurrents[$dates[$i]] = 0;
            $total_mouvements_exceptionnels[$dates[$i]] = 0;

        }

        // Récupération de toutes les saisies de l'utilisateur

        /**
         * 
         * REVENU RECURRENT
         * 
         */

        $donnees_revenus_recurrents = $this->recuperer_revenu_recurrent();

        $revenus_recurrents = $donnees_revenus_recurrents['revenus_recurrents'];

        //Conversion du tableau des montants et récupération des montants en fonction des mois utiles
        
        foreach($revenus_recurrents as $revenu_recurrent){
        
            // Calcul du total pour chaque mois

            for($i=0;$i<12;$i++){


                $total_revenus_recurrents[$dates[$i]] = $total_revenus_recurrents[$dates[$i]] + str_replace(' ','',$revenu_recurrent->montants_mensuels[$dates[$i]]);


                }
        }

        /**
         * 
         * CREANCE CLIENT
         * 
         */

        $donnees_creances_clients = $this->recuperer_creance_client();

        $creances_clients = $donnees_creances_clients['creances_clients'];
        
        foreach($creances_clients as $creance_client){

            $montants_mensuels = $creance_client['montants_mensuels'];
            unset($montants_mensuels[$dates_mois_dernier]);

            $creance_client['montants_mensuels'] = $montants_mensuels ;
            //Calcul du total pour chaque mois

            for($i=0;$i<12;$i++){

                 $total_creances_clients[$dates[$i]] = $total_creances_clients[$dates[$i]] + $creance_client->montants_mensuels[$dates[$i]];
            }
        } 


        /**
         * 
         * CHARGE RECURRENTE
         * 
         */


        $donnees_charges_recurrentes = $this->recuperer_charge_recurrente();

        $charges_recurrentes = $donnees_charges_recurrentes['charges_recurrentes'];

        $charges_recurrentes_decaissees_actuelles = array();

        $charges_recurrentes_actuelles = array();
       
        foreach($charges_recurrentes as $charge_recurrente){
            
            $charge_recurrente_mois = array();

            $mois_actuel = intval(date('m'));
            
            // Classement des données en fonction des dates actuelles
            
            for($i=0;$i<12;$i++){
                
                $charge_recurrente_mois[$dates[$i]] = $charge_recurrente->mois_selectionnes->{$mois_actuel};

                if($mois_actuel == 12){

                    $mois_actuel = 0;
                }

                $mois_actuel= $mois_actuel+1;
            }

            $charge_recurrente->mois_selectionnes = $charge_recurrente_mois;
            
            // Vérification si la charge est active durant le mois actuel
            
            if($charge_recurrente->mois_selectionnes[$dates[0]]){

                // Vérification si la charge a déjà été décaissé ce mois-ci
                    
                if(modele('treso_charge_decaissee')->where('date',$date_actuelle)->where('id_charge_recurrente',$charge_recurrente->id)->get()->isEmpty()){

                    for($i=0;$i<12;$i++){

                        if($charge_recurrente->mois_selectionnes[$dates[$i]]){

                            $total_charges_recurrentes[$dates[$i]] = $total_charges_recurrentes[$dates[$i]] - $charge_recurrente->montant;
                        
                        }

                    }

                    array_push($charges_recurrentes_actuelles, $charge_recurrente);  
                }
                
                else{ 

                    for($i=1;$i<12;$i++){

                        if($charge_recurrente->mois_selectionnes[$dates[$i]]){

                            $total_charges_recurrentes[$dates[$i]] = $total_charges_recurrentes[$dates[$i]] - $charge_recurrente->montant;
                        
                        }

                    }

                    array_push($charges_recurrentes_decaissees_actuelles, $charge_recurrente);
                    
                }

            }

            else{
                
                for($i=0;$i<12;$i++){

                    if($charge_recurrente->mois_selectionnes[$dates[$i]]){

                        $total_charges_recurrentes[$dates[$i]] = $total_charges_recurrentes[$dates[$i]] - $charge_recurrente->montant;
                    
                    }

                }

                array_push($charges_recurrentes_actuelles, $charge_recurrente);
            }
            
        }


        /**
         * 
         * MOUVEMENT EXCEPTIONNEL
         * 
         */

        $donnees_mouvements_exceptionnels = $this->recuperer_mouvement_exceptionnel();

        $mouvements_exceptionnels = $donnees_mouvements_exceptionnels['mouvements_exceptionnels'];
        
        foreach($mouvements_exceptionnels as $mouvement_exceptionnel){
            
            $montants_mensuels = $mouvement_exceptionnel['montants_mensuels'];
            unset($montants_mensuels[$dates_mois_dernier]);

            $mouvement_exceptionnel['montants_mensuels'] = $montants_mensuels ;

            // Calcul du total pour chaque mois

            for($i=0;$i<12;$i++){
                
                $total_mouvements_exceptionnels[$dates[$i]] = intval($total_mouvements_exceptionnels[$dates[$i]]) + intval($mouvement_exceptionnel->montants_mensuels[$dates[$i]]);
            }
        } 

        //Calcul du total mensuel

        for($i=0;$i<12;$i++){
                
            $total_mensuel[$dates[$i]] = $total_mouvements_exceptionnels[$dates[$i]] + $total_creances_clients[$dates[$i]] + $total_revenus_recurrents[$dates[$i]] + $total_charges_recurrentes[$dates[$i]];
        }

        // Calcul du solde global

        $solde_global[$dates[0]] = $solde_actuel+ $total_mensuel[$dates[0]];

        for($i=1;$i<12;$i++){

            $solde_global[$dates[$i]] = round($solde_global[$dates[$i-1]] + $total_mensuel[$dates[$i]]);

        }
		
		$solde_global_pour_tableau = array();
		
		foreach($solde_global as $date => $solde) {
			
			$solde_global_pour_tableau[$date] = montant($solde);
		}
		
		foreach($total_mensuel as $date => $solde) {
			
			$total_mensuel[$date] = montant($solde);
		}
		
		foreach($total_revenus_recurrents as $date => $solde) {
			
			$total_revenus_recurrents[$date] = montant($solde);
		}
		
		foreach($total_creances_clients as $date => $solde) {
			
			$total_creances_clients[$date] = montant($solde);
		}
		
		foreach($total_mouvements_exceptionnels as $date => $solde) {
			
			$total_mouvements_exceptionnels[$date] = montant($solde);
		}
		
		foreach($total_charges_recurrentes as $date => $solde) {
			
			$total_charges_recurrentes[$date] = montant($solde);
		}

        // Stockage de toutes les données nécessaires pour les prévisions
       
        $entites = $this->recuperer_entite();

        $tableau_de_donnees = array('revenus_recurrents' => $revenus_recurrents,
			'creances_clients' => $creances_clients,
			'charges_recurrentes' => $charges_recurrentes_actuelles,
			'charges_recurrentes_decaissees' => $charges_recurrentes_decaissees_actuelles,
			'mouvements_exceptionnels' => $mouvements_exceptionnels,
			'total_revenus_recurrents' => $total_revenus_recurrents,
			'total_creances_clients' => $total_creances_clients,
			'total_charges_recurrentes' => $total_charges_recurrentes,
			'total_mouvements_exceptionnels' => $total_mouvements_exceptionnels,
			'total_mensuel' => $total_mensuel,
			'solde_global' => $solde_global,
			'solde_global_pour_tableau' => $solde_global_pour_tableau,
		);

        return array(
			'mois' => $mois , 
			'nombre_de_mois_enregistrer' => $nombre_de_mois_enregistrer,
			'tableau_de_donnees' => $tableau_de_donnees,
			'dates' => json_encode($dates),
			'entites' => $entites['entites_non_choisi'], 
			'entite_choisi' => $entites['entite_choisi'],
			'solde_actuel_en_banque' => $solde_actuel_en_banque,
			'soldes_par_compte' => $soldes_par_compte,
		);
    }
    
    /**
    *
    * Enregistre les données d'un élément 
    *
    */
    public function enregistrer(Request $formulaire, $type_element) {
        
        // on va chercher l'élément
        $management = management('treso_'.$type_element, $formulaire->id);
        
        $donnees_reçues = $formulaire->all();

        unset($donnees_reçues["id"]);

        $retour = $management->enregistre($donnees_reçues);
        
        $id = $management->modele->id;

        return response()->json(array(
            
            'retour' => $retour,
            'id_'.$type_element => $id,
        ));
        
    }

    /**
    *
    * Supprime un élément
    *
    */

    public function supprimer(Request $request,$type_element){
        
        $management = management('treso_'.$type_element , $request->id);
        
        $retour =  $management->supprime();
        
        return response()->json($retour);
    }

    /**
    *
    * Enregistre l'ordre des elements
    *
    */
    
    public function maj_ordre(Request $formulaire, $type_element) {
        
        $donnees_reçues = $formulaire->all();
        
        foreach($donnees_reçues['ordre'] as $ordre => $id){

            // on va chercher l'élément

            $management = management('treso_'.$type_element, $id);
            
            $retour = $management->enregistre(array('ordre' => $ordre));

            if($retour != true){
                return response()->json($retour);
            }
            
        }
     
    }
    
    /**
    *
    * Enregistre le nombre de mois pour la visualisation
    *
    */
    public function enregistrer_nombre_de_mois(Request $formulaire){

        parametre_utilisateur('treso_visualisation_tresorerie',$formulaire->nombre_de_mois_enregistrer);
        $nombre_de_mois_enregistrer = parametre_utilisateur('treso_visualisation_tresorerie');

        return $nombre_de_mois_enregistrer;
    }

    public function enregistrer_affichage_transaction(Request $formulaire){

        parametre_utilisateur('affichage_transaction',$formulaire->affichage_transaction);
        $affichage_transaction = parametre_utilisateur('affichage_transaction');

        return $affichage_transaction;
    }

    public function changement_entite(Request $formulaire,$type_element){

        parametre_utilisateur('treso_tresorerie_entite',$formulaire->entite_id);

        $type_element_fonction = 'recuperer_'.$type_element;
        
        return response()->json($this->$type_element_fonction()) ;
    }

    public function recuperer_entite(){

        $entite_choisie = parametre_utilisateur('treso_tresorerie_entite');
		
		if($entite_choisie === null){

            parametre_utilisateur('treso_tresorerie_entite', modele('entite')->first()->id);
            $entite_choisie = parametre_utilisateur('treso_tresorerie_entite');

        }

        $entites_non_choisi = modele('entite')->where('id','!=',$entite_choisie)->get();

        $entite_choisie = modele('entite')->where('id', $entite_choisie)->first();

        return array("entites_non_choisi" => $entites_non_choisi,"entite_choisi" => $entite_choisie);
    }

    public function enregistrer_etat_transaction(Request $formulaire){

        $management = management('budget_insight_transaction', $formulaire->id_transaction);
        
        $donnees_reçues = $formulaire->all();

        unset($donnees_reçues["id_transaction"]);
        
        $retour = $management->enregistre($donnees_reçues);

        return response()->json($retour);
    }
}