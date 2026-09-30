<?php

namespace App\Eden\Controllers;

use App\Eden\Champs\Champ;
use App\Eden\Exceptions\Eden_exception;
use App\Eden\Managements\Email_management;
use App\Eden\Variables;
use App\Http\Controllers\Controller;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Utilisateur;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Recurrence;
use Cron\CronExpression;
use DateTime;
use DateInterval;
use DB;
use Route;
use URL;
use Illuminate\Support\Facades\Log;

class Cron_controller extends Controller
{

    public $cron;

    /**
     *
     * Ajout surcharge email construct
     * On vérifie également si la tâche cron n'est pas déjà en cours d'exécution et s'il faut le réinitialiser
     *
     */
    public function __construct($cron = ''){
        if(empty($cron))
            $cron = Route::current()->getActionMethod();

        $this->cron = modele('cron')->where('nom', $cron)->first();

        if(empty($this->cron))
            throw new Eden_exception('La tâche cron n\'est pas repertoriée dans la base de données');

        define('id_cron', $this->cron->id);
        define('methode_appelee', $cron);

        $retour = management('cron')->initialisation_parametres($this->cron);

        $utilisateur_cron = modele('utilisateur')->where('service', 1)->first();

        $utilisateur_cron->acces_toutes_entites = 1;

        if(!empty($utilisateur_cron))
            session()->put('utilisateur_eden', $utilisateur_cron);

        URL::forceRootUrl(env('APP_URL'));

        //Si $retour == false on arrête l'exécution, car le cron est déjà en cours
        if(!$retour){
            
            define('execution_deja_en_cours', true);
            exit();
        }
    }

    /*
     *
     * On met à jour les valeurs des paramètres en_cours et derniere_execution
     *
     */
    public function __destruct(){

        session()->flush();

        if(defined('execution_deja_en_cours') && execution_deja_en_cours === true)
            exit('L\'exécution de cette tâche cron est déjà en cours');

        management('cron', $this->cron->id, $this->cron)->enregistre_modele(['en_cours' => 0, 'derniere_execution' => date('Y-m-d H:i:s')]);
    }

    /**
     *
     * Envoi les emails de la table libre email à envoyer
     *
     */
    public function envoyer_emails()
    {

        // On récupère les emails à envoyer
        $emails_a_envoyer = modele('email_a_envoyer')->get();

        dump($emails_a_envoyer->count() . ' email' . ($emails_a_envoyer->count() > 1 ? 's' : '') . ' à envoyer');

        foreach ($emails_a_envoyer as $email) {

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'expediteur' => [
                    'email' => $email->from_email,
                    'nom' => $email->from_nom,
                ],
                'contenu' => [
                    'contenu' => $email->contenu,
                    'type' => 'text/html'
                ],
                'destinataire' => json_decode($email->to),
                'cc' => json_decode($email->cc),
                'bcc' => json_decode($email->bcc),
                'pieces_jointes' => json_decode($email->pieces_jointes),
                'sujet' => $email->sujet,
            ];

            $retour = $service_email->envoyer([], [], $parametres_email);

            if($retour !== true)
                throw new Eden_exception("L'envoi de mail a échoué pour la tâche cron " . methode_appelee . ". Erreur : " . $retour);

            dump('Envoyé "' . $email->sujet . '" à ' . implode(', ', json_decode($email->to)));

            $email->delete();

        }

        dd('ok');
    }

    /**
     *
     * Envoie les notifications (de la table libre) par email
     *
     */
    public function notifications_emails () {

    	$utilisateurs = modele('notification')
    						->select('utilisateur.*')
    						->join('utilisateur','utilisateur.id','utilisateur_id')
							->where('zone', 'email')
							->zero_ou_null('vue')
							->where('date', '<=', date('Y-m-d H:i:s'))
							->groupBy('utilisateur.id')
    						->get();

    	foreach ($utilisateurs as $utilisateur) {

    		$notifications = modele('notification')
								->where('utilisateur_id', $utilisateur->id)
								->where('zone', 'email')
								->whereNull('vue')
								->where('date', '<=', date('Y-m-d H:i:s'))
								->get();

            $compte_email = modele('compte_email')->where('utilisateur_id', $utilisateur->id)->where('valide', 1)->first();

            dump('Envoi de '.count($notifications).' notification'.( count($notifications) > 1 ? 's' : '' ).' mail à '.$utilisateur->prenom.' '.$utilisateur->nom.' ( '.$utilisateur->email.' )'/*, $notifications*/);

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 2,
                'utilisateur' => $utilisateur,
                'destinataire' => [$utilisateur->email],
                'sujet' => count($notifications)." notification".( count($notifications) > 1 ? 's' : '' ).' sur Eden',
            ];

            $variables_email = [
                'notifications' => $notifications,
                'compte_email' => $compte_email,
                'langue_destinataire' => $utilisateur->langue
            ];

            $retour = $service_email->envoyer('eden::mails.notification', $variables_email, $parametres_email);

            if($retour !== true)
                throw new Eden_exception("L'envoi de mail a échoué pour la tâche cron " . methode_appelee . ". Erreur : " . $retour);
            
	    	// On marque les notifications comme étant vues
	    	foreach ($notifications as $notification) {
				$management = management('notification', $notification->id);
				$management->enregistre(['vue' => 1]);
			}
    	}
	}

    /**
     *
     * Envoie un email à toutes les personnes ayant le flag demander_info_paiement et n'ayant pas rempli ses infos de paiement
     *
     */
    public function demander_infos_paiement_emails(){

        $utilisateurs = modele('client')
            ->where('demander_info_paiement', '1')
            ->zero_ou_null('master_id_client')
            ->get();

        foreach ($utilisateurs as $utilisateur) {

            // Si l'utilisateur a déjà un portefeuille enregistré, on l'enregistre et on le désactive
            if (!empty($utilisateur->portefeuille_payline) ||
                !empty($utilisateur->portefeuille_stripe) ||
                !empty($utilisateur->portefeuille_payzen_cb) ||
                !empty($utilisateur->portefeuille_payzen_prelevement)
            ) {

                management('client', $utilisateur->id)->enregistre(['demander_info_paiement' => 0]);
                continue;
            }

            $utilisateur = $utilisateur->toArray();
            $utilisateur['type_element'] = 'client';

            $email_management = service('email');
            $email_management->entite_id = $utilisateur['entite_id'];

            if (empty(fonctionnalite('id_mail_relance_moyen_de_paiement')))
                exception("Aucun modèle de mail pour la relance pour infos de paiement n'a été paramétré.");

            $retour = $email_management->envoyer_modele(
                fonctionnalite('id_mail_relance_moyen_de_paiement'),
                $utilisateur['adresse_email'],
                'client',  $utilisateur->id
            );

            if($retour !== true)
                exception($retour);
        }

        dd('ok!');
    }

    /**
     *
     * Applique les changements de tarifs
     *
     */
    public function changements_tarifs(){

        // On charge les changements à apporter
        $changements = modele('changement_tarif')
            ->where('date', '<=', date('Y-m-d H:i:s'))
            ->zero_ou_null('traite')
            ->get();

        foreach ($changements as $changement) {

            $article = management('article', $changement->article_id);

            dump("Changement tarif article #" . $changement->article_id . " (" . $article->modele->tarif . " => " . $changement->tarif . ")");

            // Si l'article modifié appartient à article_enfant_id
            $nomenclatures = modele('article')
                ->join('composition_article', 'article_id', 'article.id')
                ->where(function($where){
                    $where->where('composition_article.inactif',0)
                        ->orWhereNull('composition_article.inactif');
                })
                ->where('article_enfant_id', $changement->article_id)
                ->select('article.id as id_maitre')
                ->get();

            // Ajouter à la table verification_tarifs_nomenclatures
            foreach ($nomenclatures as $nomenclature) {

                // Si l'article appartient à une nomenclature, on l'ajoute dans la table vérifications
                $a_verifier = management('verification_tarifs_nomenclatures');
                $a_verifier->enregistre(
                    [
                        'article_id_nomenclature' => $nomenclature->id_maitre,
                        'article_id_modifie' => $changement->article_id,
                        'ancien_tarif' => $article->modele->tarif,
                        'nouveau_tarif' => $changement->tarif,
                    ]);
            }

            // On met à jour le tarif de l'article
            $article->enregistre(['tarif' => $changement->tarif]);

            // On enregistre le changement comme traité
            $changement_m = management('changement_tarif', $changement->id);
            $changement_m->enregistre(['traite' => 1]);
        }

        dd('ok');
    }

    /**
     *
     * Génère les interventions pour la maintenance
     *
     */
    public function genere_interventions_maintenance()
    {

        $delai_creation_maintenance = fonctionnalite('maintenance_delai_creation_intervention');

        $maintenances = modele('maintenance')
            ->where(function ($r) use ($delai_creation_maintenance) {
                $r->where('prochaine_date_maintenance', '<=', date('Y-m-d', strtotime('now + ' . $delai_creation_maintenance)));
                $r->orWhereNull('prochaine_date_maintenance');
            })
            ->where('actif', 1)
            ->get();

        foreach ($maintenances as $maintenance) {

            $maintenance_management = management('maintenance', $maintenance->id, $maintenance);

            $maintenance_management->genere_intervention();
        }
    }

    /**
     *
     * Transforme un objet en tableau
     *
     */
    private function object_to_array($obj)
    {

        if (is_object($obj) || is_array($obj)) {

            $ret = (array)$obj;
            foreach ($ret as &$item) {

                $item = $this->object_to_array($item);
            }

            return $ret;

        } else {

            return $obj;
        }
    }

    /**
     *
     * Crée les relances factures
     *
     */
    public function envoyer_relances()
    {

        $relances = modele('relance_automatique_recouvrement')->get();
        foreach ($relances as $relance) {

            // La différence de date devrait ressembler à "J-XXX" ou "J+XXX"
            // On remplace les "-" par des "+" et vice-versa
            $difforg = $relance->difference_date;
            $diff = intval($difforg) * -1;
            $date = date('Y-m-d', strtotime('now ' . $diff . ' days'));
            dump('Relances à J' . $difforg . ' : ' . $date);

            // On prend toutes les factures valides, mais non réglées à relancer
            $factures = modele('facture_vente')
                ->where('date_de_reglement', $date)
                ->where('valide', 1)
                ->zero_ou_null('regle')
                ->get();
            dump($factures->count() . ' facture(s) à relancer');

            foreach ($factures as $facture) {

                $client = modele('client', $facture->client_id);

                if (!empty($relance->modele_email_id) && !empty($client->adresse_email)) {

                    dump('Envoi relance pour facture #' . $facture->id);

                    $email_management = service('email');
                    $retour = $email_management->envoyer_modele(
                        $relance->modele_email_id,
                        $client['adresse_email'],
                        'facture_vente',  $facture->id
                    );

                    if($retour !== true)
                        exception($retour);

                    management('relance_recouvrement')
                        ->enregistre(
                            [
                                'date' => date('Y-m-d'),
                                'client_id' => $facture->client_id,
                                'facture_vente_id' => $facture->id,
                                'type_relance' => $relance->type_relance,
                                'a_faire' => 0,
                            ]
                        );

                } else {

                    dump('Création relance pour facture #' . $facture->id);
                    management('relance_recouvrement')
                        ->enregistre(
                            [
                                'date' => date('Y-m-d'),
                                'client_id' => $facture->client_id,
                                'facture_vente_id' => $facture->id,
                                'type_relance' => $relance->type_relance,
                                'a_faire' => 1,
                            ]
                        );
                }
            }
        }
        dd('ok');
    }

    /**
     *
     *
     * Permet d'envoyer des alertes concernant le seuil de stock des articles
     *
     */
    public function seuil_stocks_articles()
    {

        $articles = modele('article')->where('stockable',1)->get();

        $stocks_articles = [
            'seuil_mini' => [],
            'seuil_alerte' => [],
            'produits_prochainement_dispo' => [],
        ];

        $email_destinataire = fonctionnalite('adresse_email_gestion_stocks');
        $email_a_envoyer = false;

        foreach ($articles as $article) {

            $stock_actuel = management('article', $article->id)->stock_actuel();

            $seuil = modele('seuil_article')
				->select(DB::raw('SUM(seuil_mini) as seuil_mini, SUM(seuil_alerte) as seuil_alerte'))
				->where('article_id', $article->id)
				->groupBy('article_id')
				->get()
				->toArray();

            if(empty($seuil))
                continue;

            // le produit n'est pas stockable
            if($stock_actuel == '-')
                continue;

            $article->stock_actuel = $stock_actuel;
            $stock_seuil_mini = $seuil[0]['seuil_mini'];
            $stock_seuil_alerte = $seuil[0]['seuil_alerte'];

            // si le stock actuel est <= au seuil alerte, on ajoute l'article dans le tableau récap
            if ($stock_actuel <= $stock_seuil_alerte) {

                $email_a_envoyer = true;

                $stocks_articles['seuil_alerte'][] = $article;
                continue;
            }

            // si le stock actuel est <= au seuil mini, on ajoute l'article dans le tableau récap
            if ($stock_actuel <= $stock_seuil_mini) {

                $email_a_envoyer = true;

                $stocks_articles['seuil_mini'][] = $article;
            }
        }

        foreach($articles as $article) {

            // On ajoute les produits qui seront prochainement disponibles
            if (!empty($article->date_de_disponibilite) && !empty($article->en_ligne)) {

                $email_a_envoyer = true;

                $stocks_articles['produits_prochainement_dispo'][] = $article;

            }
        }

        // On trie les produits par dates croissantes de disponibilité
        usort($stocks_articles['produits_prochainement_dispo'], function ($a, $b) {
            return ($a['date_de_disponibilite'] < $b['date_de_disponibilite']) ? -1 : 1;
        });

        if ($email_a_envoyer && !empty($email_destinataire)) {

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 1,
                'destinataire' => [$email_destinataire],
                'sujet' => 'Alerte sur les stocks',
            ];

            $variables_email = [
                'stocks_articles' => $stocks_articles,
                'destinataire' => modele('utilisateur')->where('email', $email_destinataire)->first()
            ];

            $retour = $service_email->envoyer('eden::mails.alerte_stocks_articles', $variables_email, $parametres_email);

            if($retour !== true)
                throw new Eden_exception("L'envoi de mail a échoué pour la tâche cron " . methode_appelee . ". Erreur : " . $retour);
        }

        dd('Fin du script');
    }

    public function taux_de_charge_actuel()
    {

        $url = "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml";
        $xml = simplexml_load_file($url);

        $xml = $xml->Cube[0]->Cube[0];

        $date = date('Y-m-d', strtotime($xml->attributes()['time']));

        foreach ($xml->children() as $child) {

            $devise = modele('devise')
                ->where('code_iso', $child->attributes()['currency'])
                ->first();

            if ($devise !== null) {

                $taux_de_change_existant = modele('taux_de_change')
                    ->where('devise', $devise->id)
                    ->where('date', $date)
                    ->first();

                if ($taux_de_change_existant == null) {

                    $taux_de_change_management = management('taux_de_change');

                    $informations = array(
                        'devise' => $devise->id,
                        'taux' => $child->attributes()['rate'],
                        'date' => $date,
                    );

                    $taux_de_change_management->enregistre($informations);
                }
            }
        }
    }

    /**
     *
     * Exécute les requêtes sql créés pour le cron
     *
     */
    public function execute_requete_sql_cron(){

        $requetes = modele('requete_sql_cron')->get();

		$date_heure_actuel = new DateTime();

        foreach ($requetes as $requete) {

			$on_doit_executer = false;

			if(empty($requete->derniere_execution)) {

				$on_doit_executer = true;
			}
			else {

				$date_heure_derniere_execution = new DateTime($requete->derniere_execution);

				// On calcule la date de la prochaine exécution
				if ($requete->unite_interval == "minute") {

					$duree = 'PT';
					$designation_de_periode = "M";
				}
				else if ($requete->unite_interval == "heure") {

					$duree = 'PT';
					$designation_de_periode = "H";
				}
				else if ($requete->unite_interval == "jour") {

					$duree = 'P';
					$designation_de_periode = "D";
				}

				$duree .= $requete->valeur_interval . $designation_de_periode;

				$date_prochaine_execution = $date_heure_derniere_execution->add(new DateInterval($duree));

				if ($date_prochaine_execution <= $date_heure_actuel) {

					$on_doit_executer = true;
				}
			}

			if(!$on_doit_executer)
				continue;

			//Exécute la requête
			try {

                $debut_execution = microtime(true);
                DB::select($requete->requete);

                // On enregistre la dernière éxécution
                management('requete_sql_cron', $requete->id)->enregistre_modele([
                    'derniere_execution' => $date_heure_actuel->format('Y-m-d H:i:00'),
                    'duree_derniere_execution' => microtime(true)-$debut_execution,
                ]);
			}
			catch(\Exception | \Illuminate\Database\QueryException | \Throwable $e) {

				if(date('Y-m-d H:i:s', strtotime('now -1 day')) > $requete->dernier_envoi_mail) {

                    // on prépare les données pour envoyer le mail
                    $service_email = service('email');

                    $parametres_email = [
                        'type_configuration' => 1,
                        'destinataire' => [fonctionnalite('adresse_mail_a_prevenir_robot')],
                        'sujet' => "Erreur dans une requête SQL cron",
                    ];

                    $variables_email = [
                        'requete' => $requete->requete
                    ];

                    $retour = $service_email->envoyer('eden::mails.envoi_mail_erreur_cron_requete_sql', $variables_email, $parametres_email);

                    if($retour !== true)
                        throw new Eden_exception("L'envoi de mail a échoué pour la tâche cron " . methode_appelee . ". Erreur : " . $retour);
                    
					$requete->dernier_envoi_mail = date('Y-m-d H:i:s');
					$requete->save();
				}

				dump($e);
			}
        }
    }


    /**
     *
     * Permet d'executer l'optimisation des tables de la BDD
     *
     * @return void
     */
    public function optimisation_tables(){
        management("cron")->optimisation_tables();
    }

    /**
     *
     * Envoie 50 questionnaires par mail (joué toutes les cinq minutes)
     *
     */
    public function envoi_mails_questionnaires(){

        $questionnaires_a_envoyer = modele('suivi_envoi_questionnaire')->where(function($r){ $r->where('envoye', 0)->orWhereNull('envoye'); })->take(50)->get();

        $service_publipostage = service('publipostage');

        foreach($questionnaires_a_envoyer as $questionnaire_a_envoyer){

            $management_element = management('suivi_envoi_questionnaire', $questionnaire_a_envoyer->id, $questionnaire_a_envoyer);

            //On crée l'url raccourci
            $lien_questionnaire = route('questionnaire.affichage',[$questionnaire_a_envoyer->id_questionnaire,$questionnaire_a_envoyer->repondant_id,md5('questionnaire_eden_'.$questionnaire_a_envoyer->id_questionnaire.$questionnaire_a_envoyer->repondant_id)]);

            $url_raccourcie = modele('url_raccourcie')->where('lien_origine',$lien_questionnaire)->first();

            if(empty($url_raccourcie)) {
                $management_url_raccourcie = management('url_raccourcie');

                $retour = $management_url_raccourcie->enregistre(array('lien_origine' => $lien_questionnaire));

                if($retour !== true)
                    continue;

                $url_raccourcie = $management_url_raccourcie->modele;
            }

            $questionnaire = modele('questionnaire', $questionnaire_a_envoyer->id_questionnaire);

            $corps_mail = $questionnaire->corps_du_mail;

            $valeurs_publiposter['#lien_questionnaire'] = URL::to('/lr/'.$url_raccourcie->chaine_raccourcie);

            $repondant = modele('questionnaire_element_repondant',$questionnaire_a_envoyer->repondant_id);

            if(empty($repondant))
                continue;

            $sujet = $service_publipostage->publipostage_texte($questionnaire->sujet, $repondant->type_element, [$repondant->element_id]);
            $corps_mail = $service_publipostage->publipostage_texte($corps_mail, $repondant->type_element, [$repondant->element_id], $valeurs_publiposter);

            // on prépare les données pour envoyer le mail
            $service_email = service('email');

            $parametres_email = [
                'type_configuration' => 2,
                'id_compte_email' => $questionnaire->expediteur,
                'destinataire' => [$questionnaire_a_envoyer->email_destinataire],
                'sujet' => $sujet,
            ];

            $variables_email = [
                'corps_mail' => $corps_mail
            ];

            $retour = $service_email->envoyer('eden::mails.envoi_questionnaire', $variables_email, $parametres_email);

            if($retour !== true)
                throw new Eden_exception("L'envoi de mail a échoué pour la tâche cron " . methode_appelee . ". Erreur : " . $retour);
            
            dump('Mail envoyé à ' . $questionnaire_a_envoyer->email_destinataire . ' avec le questionnaire n°' . $questionnaire_a_envoyer->id_questionnaire);

            $management_element->enregistre(['envoye' => 1,'url_raccourcie_questionnaire' => $url_raccourcie->id]);
        }
    }

    /*
     *
     * Applique les évolutions de prix aux articles
     *
     */
    public function comparer_prix_ventes_aux_evolutions($id = false){

        $retour = management('article_evolution_prix')->comparer_prix_ventes_aux_evolutions($id);

        return $retour;
    }

    public function login_automatique_pour_cron(){

		if(moi() !== null && moi()->email != 'eden@eden-erp.fr' && moi()->email != 'cron@easy-developpement.fr')
            exception('Ce script ne peut être appelé par un utilisateur');

        $utilisateur = Utilisateur::where('email','eden@eden-erp.fr')
            ->orWhere('email','cron@easy-developpement.fr')
            ->first();

        if($utilisateur !== null)
            session()->put('utilisateur_eden', $utilisateur);
        else
            exception("L'utilisateur Eden Erp n'a pas été trouvé");

        return true;
    }

    public function generation_elements_recurrents(){

        // on regarde s'il y a des récurrences à générer
        $recurrences = Recurrence::where(function($requete) {
            $requete->where('inactif', 0)->orWhereNull('inactif');
        })->where(DB::raw('DATE_SUB(rdi_prochaine_occurence,INTERVAL COALESCE(delai_generation,0) DAY)'), '<=', date('Y-m-d'))->get();

        // rien à faire
        if($recurrences === null)
            return response()->json(true);

        // on doit gérer des récurrences
        foreach($recurrences as $recurrence) {

            // on va chercher l'élément à dupliquer
            $modele = modele($recurrence->type_element)->where('id_recurrence', $recurrence->id);

            // le premier
            if($recurrence->rdi_id_modele == 0)
                $modele = $modele->orderBy('date')->first();
            // le dernier
            elseif($recurrence->rdi_id_modele == 1)
                $modele = $modele->orderBy('date', 'desc')->first();
            else
                continue;

            // l'élément a probablement été supprimé
            if($modele === null || !empty($modele->inactif) || !empty($modele->annule)) {

                continue;
            }

            // on génère la récurrence
            $management = management($recurrence->type_element, $modele->id);
            $management->prochaine_recurrence();
        }

        session()->flush();

        return response()->json(true);
    }

    /**
    *
    * Remplit les valeurs de chaine_tags_ajax_recherche (après un import par exemple ou une mise à jour) de manière plus rapide
    *
    */
    public function maj_chaines_tags_recherche($types_elements = null) {

        $types_elements = explode('-', $types_elements);

        if(empty($types_elements))
            return response()->json(['retour' => true,]);

        foreach($types_elements as $type_element){
            management($type_element)->maj_index_recherche();
        }

        return response()->json(['retour' => true]);
    }

    /**
     *
     * Remplit les valeurs de chaine_tags_ajax_recherche (après un import par exemple ou une mise à jour) de manière plus rapide
     *
     */
    public function maj_chaines_affichage($types_elements = null) {

        $types_elements = explode('-', $types_elements);

        if(empty($types_elements))
            return response()->json(['retour' => true]);

        foreach($types_elements as $type_element){

            $nombre_a_faire = 100000;

            $nombre_elements = modele($type_element)->avec_inactifs()->count();

            $nombre_de_boucle = ceil($nombre_elements / $nombre_a_faire);

            $management_element = management($type_element);

            for($i = 0;$i < $nombre_de_boucle;$i++){

                $nombre_a_eviter = $i * $nombre_a_faire;

                $elements = modele($type_element)->avec_inactifs()->skip($nombre_a_eviter)->take($nombre_a_faire);

                $elements = modele($type_element)->hydrate(select($elements));

                foreach($elements as $element) {

                    $management_element->modele = $element;

                    //On met la chaine affichage à null pour forcer la régénération dans le affiche
                    $management_element->modele->chaine_affichage = null;

                    $retour = $management_element->affiche();
                }
            }
        }

        return response()->json(['retour' => true]);
    }

    /**
     *
     * Remplit les valeurs de chaine_tags_ajax_recherche (après un import par exemple ou une mise à jour) de manière plus rapide
     *
     */
    public function maj_droits_affichage_index($types_elements = null) {

        $this->maj_chaines_tags_recherche($types_elements);
        $this->maj_chaines_affichage($types_elements);

        return response()->json(['retour' => true,]);
    }

    /**
     *
     * Permet de générer les pdfs des documents qui n'ont pas leur pdf généré
     *
     */
    public function generer_pdfs_documents(){

        $types_documents = Variables::$documents_gescom;

        $recapitulatif = array('succes' => array(), 'erreur' => array());

        foreach($types_documents as $type_document){

      			$ids_documents_a_generer = modele($type_document)->whereNull('pdf')->get()->pluck('id')->toArray();

      			foreach ($ids_documents_a_generer as $id_document_a_generer) {

      				try {
      					management($type_document, $id_document_a_generer)->creation_pdf();
      				} catch (\Exception $e) {
                          management($type_document, $id_document_a_generer)->enregistre_modele(array('pdf' => null));
                          $recapitulatif['erreur'][] = 'Document '.$id_document_a_generer.', type :'.$type_document;
      					continue;
      				}

                      $recapitulatif['succes'][] = 'Document '.$id_document_a_generer.', type :'.$type_document;
      			}
        }

        return response()->json('Succes : '.implode(' | ',$recapitulatif['succes']) . ' Erreur : '.implode(' | ',$recapitulatif['erreur']));
    }

    /**
     *
     * Parcourt les images du disque public et compresse celles qui dépassent la taille
     * maximale paramétrée dans les Fonctionnalités (compression_images_taille_maximale_en_mo,
     * 5 Mo par défaut). Les fichiers sont écrasés sur place.
     *
     */
    public function compresser_images(){

        // Taille cible (convention Mo décimale, comme Fiche_management : / 1000000)
        $max_mo = (float) (fonctionnalite('compression_images_taille_maximale_en_mo') ?: 5);

        $recap = \App\Eden\Managements\Services\Image_compresseur_service::compresser_stockage($max_mo * 1000000);

        return response()->json([
            'taille_maximale_mo' => $max_mo,
            'nb_candidats'       => $recap['candidats'],
            'nb_compressees'     => count($recap['succes']),
            'compressees'        => $recap['succes'],
            'erreurs'            => $recap['erreur'],
        ]);
    }

	/**
     *
     * Permet de synchroniser les rendez-vous provenant du calendrier Microsoft de chaque utilisateur ayant un id_microsoft vers Eden
     *
     */
    public function synchroniser_rdv_microsoft(){

        //On définit une constante qui indique qu'il ne faut pas réenregistrer les rendez-vous dans le calendrier Microsoft
        define('synchro_rdv_microsoft_vers_eden', true);

        //On synchronise les tâches provenant de Microsoft avec Eden
        service('microsoft_calendrier')->synchronise_evenements();

        //On synchronise les tâches Eden qui n'ont pas été synchro dans Office.
        // Il faut faire une refonte de cette méthode car elle crée tout sous forme 
        // de tâches unitaires sans prendre en compte le fait que ça peut être des tâches de récurrence
        //service('microsoft_calendrier')->synchronise_evenements_eden_sans_id();

        return response()->json(true);
    }

    /**
     *
     * Tente une nouvelle synchronisation des rdv pour lesquels il y a eu des erreurs lors de la synchronisation Office vers Eden
     *
     */
    public function synchroniser_rdv_microsoft_echoues(){

        define('synchro_rdv_microsoft_vers_eden', true);

        service('microsoft_calendrier')->synchronise_evenements_echoues();

        return response()->json(true);
    }

    /*
     * 
     * Permet de synchroniser les rendez-vous provenant du calendrier Google de chaque utilisateur vers Eden
     *
     */
    public function synchroniser_rdv_google(){

        //On définit une constante qui indique qu'il ne faut pas réenregistrer les rendez-vous dans le calendrier Google
        define('synchro_rdv_google_vers_eden', true);

        //On synchronise les tâches provenant de Google avec Eden
        service('google_calendrier')->synchronise_evenements();

        return response()->json(true);
    }

    /**
     *
     * Tente une nouvelle synchronisation des rdv pour lesquels il y a eu des erreurs lors de la synchronisation Google vers Eden
     *
     */
    public function synchroniser_rdv_google_echoues(){

        define('synchro_rdv_google_vers_eden', true);

        service('google_calendrier')->synchronise_evenements_echoues();

        return response()->json(true);
    }

    /*
     *
     * Récupère les rendez-vous de chaque utilisateur pour vérifier s'ils existent encore,
     * si ce n'est pas le cas, on supprime la tâche Eden.
     *
     */
    public function supprimer_doublons_rdv_microsoft(){

        $debut = date('Y-m-d', strtotime('- ' . config('fonctionnalites_integrations.microsoft_nombre_mois') . ' months')) . ' 00:00:00';
        $fin = date('Y-m-d', strtotime('+ ' . config('fonctionnalites_integrations.microsoft_nombre_mois') . ' months')) . ' 23:59:59';

        service('microsoft_calendrier')->supprimer_doublons($debut, $fin);

        return response()->json(true);
    }

	/**
	 *
	 * Relève les mails pour créer des tickets
	 *
	 */
	public function relever_tickets_support() {

        service('ticket_client')->synchronisation_ticket_client();

        return response()->json(true);
	}

    /**
	 *
	 * Relève les mails pour créer des éléments EDEN
	 *
	 */
	public function relever_integration_email() {

        $integrations_emails = modele('integration_email')->get();

        foreach($integrations_emails as $integration_email){
            management('integration_email',$integration_email->id,$integration_email)->synchronisation_mail();
        }

        return response()->json(true);
	}

    /**
     *
     * Lancement import sur mesure
     *
     */
    public function import_sur_mesure(){

        $import_sur_mesure = modele('import_en_cours')->whereIn('statut',array(1,2))->orderBy('date')->first();

        $retour = true;

        if($import_sur_mesure!= null)
            $retour = management('import_en_cours',$import_sur_mesure->id)->import();

        return response()->json($retour);
    }

    /*
     *
     *
     *
     */
    public function synchronisation_budget_insight($jours = 3) {

        $budget_insight = management('budget_insight');

        try {

            $retour = $budget_insight->synchronisation($jours);

        } catch(\Exception $erreur){

            $retour = $erreur->getMessage();
            \Log::error('Erreur lors de la synchronisation Budget Insight. Message : ' . $erreur->getMessage());
        }

        return response()->json($retour);
    }

    public function dedoublonnage_budget_insight() {

		$budget_insight = management('budget_insight');

    	$retour = $budget_insight->dedoublonnage();

        return response()->json($retour);
    }

    /**
     *
     *
     *
     */
    public function synchroniser_emails() {
        // avant tout, on essaie d'affecter les mails sans clients ou fournisseurs
        $type_email_a_generer = array('client','fournisseur');
        $emails_par_type = ['client' => [], 'fournisseur' => []];

        foreach($type_email_a_generer as $type){

            foreach(['from', 'to'] as $champ) {

                $requete = modele('email_recus')
                    ->sans_profils()
                    ->whereNull('email_recus.'.$type.'_id')
                    ->whereNotNull('email_recus.'.$champ)
                    ->where('email_recus.'.$champ, '!=', '')
                    ->take(2000);

                $requete_contact = clone $requete;

                $elements = $requete
                    ->select('email_recus.id', $type.'.id as '.$type.'_id')
                    ->join($type, $type.'.adresse_email', '=', 'email_recus.'.$champ)
                    ->zero_ou_null($type.'.inactif')
                    ->get()->pluck($type.'_id','id')->toArray();

                $elements_contacts = $requete_contact
                    ->select('email_recus.id', 'contact.'.$type.'_id as '.$type.'_id')
                    ->join('contact', 'contact.adresse_email', '=', 'email_recus.'.$champ)
                    ->join($type, $type.'.id', '=', 'contact.'.$type.'_id')
                    ->zero_ou_null('contact.inactif')
                    ->zero_ou_null($type.'.inactif')
                    ->get()->pluck($type.'_id','id')->toArray();

                $emails_par_type[$type] = $emails_par_type[$type] + $elements + $elements_contacts;
            }
        }

        foreach($emails_par_type as $type_email => $emails) {

            foreach ($emails as $id_email => $element_id) {
                management('email_recus', $id_email)->enregistre_modele([$type_email.'_id' => $element_id]);
            }
        }

        define('synchro_mail',1);

        // On récupère les synchros mail
        $synchro_mail = modele('synchro_mail')->get();

        $configurations_emails = modele('configuration_email')->get()->keyBy('id');

        $les_id_mail_array = modele('email_recus')
            ->avec_inactifs()->sans_profils()
            ->select(DB::raw("IFNULL(internet_message_id,id_mail) as id_email"))
            ->get()->pluck('id_email')->toArray();

        // On boucle sur les mails à synchro
        foreach ($synchro_mail as $le_mail_synchro) {

            Email_management::synchro_mail($le_mail_synchro, $les_id_mail_array, $configurations_emails);

            $management_de_la_synchro = modele('synchro_mail')->where('id', $le_mail_synchro->id)->first();
            $management_de_la_synchro->statut = 'Derniere synchronisation le ' . date('d/m/Y H:i:s');
            $management_de_la_synchro->save();

        }

        return response()->json('Ok !');
    }

    /*
     *
     *
     *
     */
    public function gestion_des_notifications_manuelles($notification_id = null){

        if($notification_id != null)
            $ids_notifications_manuelles = modele('notification_manuelle')->where('id',$notification_id)->get()->pluck('id')->toArray();
        else
            $ids_notifications_manuelles = modele('notification_manuelle')->get()->pluck('id')->toArray();

        if(empty($ids_notifications_manuelles))
            return response()->json(traduction('messages.php.notification_manuelle.notification_introuvable'));

        foreach($ids_notifications_manuelles as $id_notification_manuelle){

            $management_notification = management('notification_manuelle',$id_notification_manuelle);

            $retour = $management_notification->genere_donnees_notification_manuelle_element();

            if($retour == false)
                continue;

            $management_notification->envoie_des_notifications();
        }

        return response()->json(true);
    }

    /**
     *
     * Mise à jour des index pour les éléments recémment crée
     *
     */
    public function mise_a_jour_index_recherche_element(){

        $types_elements = service('variables')->cron_informations_mise_a_jour_index();

        $date_de_derniere_execution = parametre_cron('date_de_derniere_execution_mise_a_jour_index_recherche_element', id_cron);

        foreach($types_elements as $type_element){

            $table_libre = table_libre($type_element);

            $colonnes = schema_table($type_element);

            if($table_libre !== null && in_array('chaine_tags_recherche', $colonnes)) {

                $champs_libres_recherche = $table_libre->champs_libres()->where('recherche', 1)->get();

                if ($champs_libres_recherche !== null) {

                    if($date_de_derniere_execution === null)
                        $elements_ids = modele($type_element)->select('id')->whereNull('chaine_tags_recherche')->get()->pluck('id')->toArray();
                    else
                        $elements_ids = modele($type_element)->select('id')->where('modifie_le','>',$date_de_derniere_execution)->get()->pluck('id')->toArray();

                    management($type_element)->maj_index_recherche($elements_ids);
                }
            }
        }

        parametre_cron('date_de_derniere_execution_mise_a_jour_index_recherche_element', id_cron, date('Y-m-d H:i:s'));

        return response()->json(true);
    }

    /**
     *
     * Crée tous les éléments des types d'éléments contenus dans la table parametrage_mappage_mfiles
     *
     */
    public function synchronisation_mfiles($forcer_modification = false) {

        $types_elements_a_synchro = modele('parametrage_mappage_mfiles')->get();

        foreach($types_elements_a_synchro as $type_element){

            service('mfiles')->creer_elements_en_masse($type_element->table_libre, $forcer_modification);
        }

        return 'Tous les éléments ont été traités';
    }

    /**
     *
     * Suppression des mails trop vieux
     *
     */
    public function suppression_emails_inutiles(){

        $email_management = new Email_management();

        $email_management->suppression_pj_inutiles();

        return response()->json(true);
    }

    /**
     *
     * Récupération des enveloppes docusign avec leur statut
     *
     */
    public function recuperation_enveloppes_docusign(){

        service('docusign')->recuperer_enveloppes();

        return response()->json(true);
    }

    /*
     *
     * Force la synchronisation entre Budget_insight et la banque
     *
     */
    public function forcer_synchro_banque_budget_insight() {

        $synchros = modele('budget_insight_synchro')->whereNotNull('token')->get();

        foreach($synchros as $synchro){

            $opts = array('http' =>
                array(
                    'header'  => 	"Content-Type: application/json\r\n".
                        "Authorization: Bearer ". $synchro->token ."\r\n",
                    'method'  => 'PUT',
                    'content'  => json_encode(array('psu_requested' => false)),
                    'ignore_errors' => true,
                )
            );

            $opts_list = array('http' =>
                array(
                    'header'  => "Content-Type: application/json\r\n".
                        "Authorization: Bearer ". $synchro->token ."\r\n",
                    'ignore_errors' => true,
                )
            );

            $context = stream_context_create($opts);
            $context_list = stream_context_create($opts_list);

            $liste_connexions = json_decode(file_get_contents('https://easydeveloppement.biapi.pro/2.0/users/me/connections', false, $context_list));

            if(empty($liste_connexions->connections))
                continue;

            foreach($liste_connexions->connections as $connexion){

                file_get_contents('https://easydeveloppement.biapi.pro/2.0/users/me/connections/'.$connexion->id, false, $context);
            }
        }

        return response()->json(['succes' => true]);
    }

    /*
     *
     * Permet de créer les occurrences des récurrences qui ont une date de fin trop lointaine ou qui n'en ont pas
     *
     */
    public function creation_occurrences_recurrence() {

        $recurrences = modele('tache_recurrence')->where(function($r){ $r->where('date_de_fin', '>', date('Y-m-d'))->orWhereNull('date_de_fin'); })->get();

        foreach($recurrences as $recurrence){

            management('tache_recurrence',$recurrence,$recurrence->id)->charge_valeurs_champs_multiselection();

            $recurrence = $recurrence->toArray();

            $recurrence['date_de_debut'] = date('Y-m-d');

            //On récupère la dernière tâche créée dans la récurrence et la tâche parente pour savoir quelles données reprendre pour les prochaines tâches
            $derniere_tache_creee = modele('tache')->where('parent_id', $recurrence['tache_parent_id'])->orderBy('date_de_debut', 'desc')->first();
            $tache_parent = modele('tache')->avec_parents()->where('id', $recurrence['tache_parent_id'])->first();

            if(empty($tache_parent))
                continue;

            $modifications['infos_recurrence'] = $recurrence;

            $tache_parent->date_de_debut = $derniere_tache_creee->date_de_debut;
            $tache_parent->date_de_fin = $derniere_tache_creee->date_de_fin;
            
            $modifications = ['infos_recurrence' => $recurrence];

            // On crée les prochaines occurrences
            service('recurrence')->enregistre_recurrence($modifications, $tache_parent, true);
        }

        return response()->json(true);
    }

    /**
     * @param $pays
     * @return \Illuminate\Http\JsonResponse
     *
     * Permet de récupérer les jours fériés via l'API openholidaysapi
     *
     */
    public function synchronisation_jour_indisponibilite($pays = "FR"){

        $date = date('Y-m-d');

        service('jour_indisponibilite')->synchronisation_avec_api(null,null,$pays);

        return response()->json(true);
    }

    /**
     *
     * On supprime les fichiers des exports différés vieux de plus de 30 jours
     *
     * @return void
     */
    public function supprimer_export_differe()
    {
        $fichiers = scandir(storage_path('app/public/'));

        foreach ($fichiers as $fichier) {
            
            if (strpos($fichier, 'export') === false || is_dir($fichier))
                continue;

            $date = explode('_', $fichier)[2] ?? null;

            if(!isset($date))
                continue;

            $date = explode('.', $date)[0] ?? null;

            if(!isset($date))
                continue;

            $date = intval($date);

            if ($date < strtotime('-30 days'))
                unlink(storage_path('app/public/' . $fichier));
        }

        return response()->json(true);
    }

    public function synchronisation_service($synchronisation_service_id) {

        $synchronisation_service = modele('synchronisation_service_element')
            ->where('id',$synchronisation_service_id)
            ->first();

        if(empty($synchronisation_service) || !empty($synchronisation_service->desactive))
            return response()->json(['succes' => false, 'message' => traduction('messages.php.synchronisation_service_element.synchronisation_desactivee')]);

        $management = management('synchronisation_service_element', $synchronisation_service->id, $synchronisation_service);

        if($synchronisation_service->type_synchronisation == 2)
            $retour = $management->synchronisation_cron();
        else if($synchronisation_service->type_synchronisation == 3 && !empty($synchronisation_service->cron_actif))
            $retour = $management->synchronisation_lecture_cron();
        else
            return response()->json(['succes' => false, 'message' => traduction('messages.php.synchronisation_service_element.synchronisation_desactivee')]);

        if($retour === true)
            return response()->json(['succes' => true, 'message' => traduction('messages.php.synchronisation_service_element.synchronisation_effectuee')]);
        else
            return response()->json(['succes' => false, 'message' => $retour]);
    }

    /**
     *
     * Lance la synchronisation de tous les éléments configurés en cron d'un service de synchronisation
     *
     */
    public function synchronisation_service_groupe($synchronisation_service_id) {

        $synchronisation_service = modele('synchronisation_service')
            ->where('id', $synchronisation_service_id)
            ->first();

        if(empty($synchronisation_service) || !empty($synchronisation_service->desactive) || !empty($synchronisation_service->inactif))
            return response()->json(['succes' => false, 'message' => traduction('messages.php.synchronisation_service_element.synchronisation_desactivee')]);

        $elements = modele('synchronisation_service_element')
            ->where('synchronisation_service_id', $synchronisation_service->id)
            ->where(function($requete) {
                $requete->where('type_synchronisation', 2)
                    ->orWhere(function($sous_requete) {
                        $sous_requete->where('type_synchronisation', 3)->where('cron_actif', 1);
                    });
            })
            ->where(function($requete) {
                $requete->whereNull('desactive')->orWhere('desactive', 0);
            })
            ->where(function($requete) {
                $requete->whereNull('inactif')->orWhere('inactif', 0);
            })
            ->get();

        $resultats = [];

        foreach($elements as $element) {

            $management = management('synchronisation_service_element', $element->id, $element);

            try {

                $retour = $element->type_synchronisation == 2
                    ? $management->synchronisation_cron()
                    : $management->synchronisation_lecture_cron();
            }
            catch(\Exception | \Throwable $exception) {

                Log::error('Erreur lors de la synchronisation du service élément '.$element->id.'. Message : '.$exception->getMessage());

                $retour = $exception->getMessage();
            }

            $resultats[$element->id] = $retour;

            unset($management);
        }

        return response()->json([
            'succes' => !collect($resultats)->contains(fn($retour) => $retour !== true),
            'message' => traduction('messages.php.synchronisation_service_element.synchronisation_effectuee'),
            'resultats' => $resultats,
        ]);
    }
}
