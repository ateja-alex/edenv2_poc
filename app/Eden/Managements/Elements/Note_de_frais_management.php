<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Variables;
use Illuminate\Support\Facades\URL;
use DB;

class Note_de_frais_management extends Element_management {

    public $ajout_intranet = false;

	public function valide() {
		
		if($this->modele->accepte == 1)
			return traduction('messages.php.note_de_frais.deja_acceptee');

		if($this->modele->accepte == 2)
			return traduction('messages.php.note_de_frais.deja_refusee');

        $fonctionnalite_blocage = fonctionnalite('mois_bloquant_saisie_note_de_frais');

        if(!empty($this->modele->date) && !empty($fonctionnalite_blocage) && date('Y-m-d',strtotime('-'.$fonctionnalite_blocage.' months')) > $this->modele->date)
            return traduction('messages.php.note_de_frais.date_inferieur_blocage',null,[$fonctionnalite_blocage]);

        $fonctionnalite_comptabilisation = fonctionnalite('comptabiliser_automatiquement');
        $nb_lignes = modele('note_de_frais_lignes')->where('note_de_frais_id', $this->modele->id)->count();

        if($nb_lignes == 0)
            return traduction('messages.php.note_de_frais.pas_de_lignes');

        $retour = $this->changer_statut(1);

        if($retour !== true)
            return $retour;

        $this->log_validation();

        if(isset($fonctionnalite_comptabilisation['note_de_frais']) && $fonctionnalite_comptabilisation['note_de_frais'] && $this->modele->accepte)
            return $this->comptabilise();

        return true;
	}

	public function refuse() {
		
		if($this->modele->accepte == 1)
			return traduction('messages.php.note_de_frais.deja_acceptee');

		if($this->modele->accepte == 2)
			return traduction('messages.php.note_de_frais.deja_refusee');

        $fonctionnalite_blocage = fonctionnalite('mois_bloquant_saisie_note_de_frais');

        if(!empty($this->modele->date) && !empty($fonctionnalite_blocage) && date('Y-m-d',strtotime('-'.$fonctionnalite_blocage.' months')) > $this->modele->date)
            return traduction('messages.php.note_de_frais.date_inferieur_blocage',null,[$fonctionnalite_blocage]);

        $retour = $this->changer_statut(2);

        if($retour !== true)
            return $retour;

		$this->log_validation();

		return true;
	}
    
    private function changer_statut($statut){

        $employe = modele('utilisateur', $this->modele->utilisateur_id);
        $management_employe = management('utilisateur', $employe->id, $employe);
        $management_employe->charge_valeurs_champs_multiselection();

        $fonctionnalite_email = fonctionnalite('emails_actifs_intranet')['note_de_frais'] ?? false;
        $utilisateur_connecte = moi();
        $donnees = array();

        if((in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_1) && !empty($this->modele->valide_n1))
            || (in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_2) && !empty($this->modele->valide_n2)))
            return traduction('messages.php.note_de_frais.deja_traite_par_validateur');

        if(in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_1)) {

            $donnees = ['valide_n1' => $statut];

            // l'employé ne possède pas de n+2, ou si le n+1 refuse, on met à jour le statut
            if(empty($employe->validation_ndf_n_plus_2) || $statut == 2)
                $donnees['accepte'] = $statut;
        }
        else if(in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_2))
            $donnees = ['valide_n2' => $statut, 'accepte' => $statut];
        else if($utilisateur_connecte->type_utilisateur == 2)
            $donnees['accepte'] = $statut;

        $retour = $this->enregistre($donnees);

        if($retour !== true)
            return $retour;

        // on envoie un email si la ndf a été acceptée par le n+1 et qu'il possède un n+2
        if($statut == 1 && in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_1) && !empty($employe->validation_ndf_n_plus_2) && $fonctionnalite_email)
            $this->email_validation_note();

        //Si on a un statut = acceptée ou refusée, on prévient l'employé
        if(!empty($this->modele->accepte) && $fonctionnalite_email)
            $this->email_ndf_traitee();

        return true;
    }

    private function email_ndf_traitee(){

        $employe = modele('utilisateur', $this->modele->utilisateur_id);

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [$employe->email],
            'sujet' => traduction('messages.php.mail_ndf_traitee.sujet'),
        ];

        $variables_email = [
            'note_de_frais' => $this->modele,
            'employe' => $employe
        ];

        return $service_email->envoyer('eden::mails.confirmation_note_de_frais', $variables_email, $parametres_email);
    }

	/**
	 *
	 * On vérifie que la note de frais n'est pas validée ou comptabilisée
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {

        $articles = [];

        $date = !empty($modifications['date']) ? $modifications['date'] : $this->modele->date ?? null;

        $fonctionnalite_blocage = fonctionnalite('mois_bloquant_saisie_note_de_frais');

        if(!empty($date) && !empty($fonctionnalite_blocage) && date('Y-m-d',strtotime('-'.$fonctionnalite_blocage.' months')) > $date)
            return traduction('messages.php.note_de_frais.date_inferieur_blocage',null,[$fonctionnalite_blocage]);

        if(isset($modifications['articles'])) {
            $articles = $modifications['articles'];
            unset($modifications['articles']);
        }
        
        $taux_de_change_avant = $this->modele->taux_de_change ?? null;

        if($this->existe()){

            $champs_modifiables_post_validation = Champ_libre::where('type_element', 'note_de_frais')
                ->where('modification_post_validation', 1)->get()->pluck('nom_sql')->toArray();

            $champs_non_modifiable = false;

            foreach($modifications as $cle => $valeur){

                if($valeur == $this->modele->{$cle})
                    continue;

                if(!in_array($cle,$champs_modifiables_post_validation))
                    $champs_non_modifiable[] = traduction('champs_libres.note_de_frais.'.$cle.'.nom');
            }

            if(!empty($champs_non_modifiable)) {

                $id_moi = moi()->id;

                $employe = modele('utilisateur', $this->modele->utilisateur_id);
                $management_employe = management('utilisateur', $employe->id, $employe);
                $management_employe->charge_valeurs_champs_multiselection();

                if($this->modele->accepte == 2 && $id_moi === $this->modele->utilisateur_id)
                    $modifications = array_merge($modifications, [
                        'accepte' => 0,
                        'valide_n1' => 0,
                        'valide_n2' => 0,
                    ]);
                else if ($this->modele->accepte >= 1 && ((!in_array($id_moi,$employe->validation_ndf_n_plus_1) && !in_array($id_moi,$employe->validation_ndf_n_plus_2)) || (isset($this->modele->valide_n2) && in_array($id_moi,$employe->validation_ndf_n_plus_1))))
                    return traduction('messages.php.note_de_frais.modification_champs_impossible_validee').implode(', ', $champs_non_modifiable);

                if ($this->modele->comptabilisee == 1)
                    return traduction('messages.php.note_de_frais.modification_champ_impossible_comptabilisee').implode(', ', $champs_non_modifiable);

            }
        }

        if(!empty($articles)) {

            foreach ($articles as $index => $article) {

                $management_article = management('note_de_frais_lignes');

                $retour = $management_article->test_enregistre($article);

                if($retour !== 'test_ok')
                    return $retour;
            }
        }

		$retour = parent::enregistre($modifications, $modele);

        if($retour !== true)
            return $retour;

        $articles_note_de_frais = modele('note_de_frais_lignes')
            ->where('note_de_frais_id',$this->modele->id)
            ->get();

        if(!empty($articles)) {

            $retours = '';

            $articles_note_de_frais = $articles_note_de_frais->pluck('id')->toArray();

            foreach ($articles as $index => $article) {

                $article['note_de_frais_id'] = $this->modele->id;

                if (isset($article['id'])) {
                    $management_article = management('note_de_frais_lignes', $article['id']);

                    $index_tableau = array_search($article['id'],$articles_note_de_frais);
                    unset($articles_note_de_frais[$index_tableau]);

                } else {
                    $management_article = management('note_de_frais_lignes');
                }

                $retour = $management_article->enregistre($article);

                if($retour !== true)
                    $retours .= ucfirst(table_libre('article')->element).' ' . ($index + 1) . ' : ' . $retour;

            }

            foreach($articles_note_de_frais as $article_a_supprimer){
                management('note_de_frais_lignes', $article_a_supprimer)->supprime();
            }

            if($retours !== '')
                return $retours;
        }
        else if(!empty($modifications['taux_de_change']) && !empty($taux_de_change_avant)){

            foreach ($articles_note_de_frais as $article) {

                $management_article = management('note_de_frais_lignes', $article->id, $article);

                $management_article->enregistre([
                    'montant_ht' => $article->montant_ht * $modifications['taux_de_change'] / $taux_de_change_avant,
                    'montant_ttc' => $article->montant_ttc * $modifications['taux_de_change'] / $taux_de_change_avant,
                ]);
            }
        }

        if (!empty(array_intersect_key($this->changement_enregistrement, array_flip(['date', 'montant_ttc', 'utilisateur_id', 'accepte'])))) {

            $requete_de_base = modele('note_de_frais')
                ->where(function ($q) {
                    $q->whereIn('accepte', [0, 1])->orWhereNull('accepte');
                });

            $modeles = [
                ['modele' => $this->modele_avant, 'doublon' => 1, 'nouvelle_valeur' => 0, 'having' => true],
                ['modele' => $this->modele, 'doublon' => 0, 'nouvelle_valeur' => 1, 'having' => false],
            ];

            $requetes = [];
            foreach ($modeles as $config) {
                $requete = (clone $requete_de_base)
                    ->where('utilisateur_id', $config['modele']->utilisateur_id)
                    ->where('date', $config['modele']->date)
                    ->where('montant_ttc', $config['modele']->montant_ttc)
                    ->where('id', '!=', $config['modele']->id)
                    ->selectRaw("*, {$config['nouvelle_valeur']} as nouvelle_valeur_doublon_potentiel");

                if ($config['having']) {
                    $requete->where('doublon_potentiel', $config['doublon'])->groupBy('utilisateur_id', 'date', 'montant_ttc')->havingRaw('COUNT(id) = 1');
                }

                $requetes[] = $requete;
            }

            $doublons_note_de_frais = $requetes[0]->union($requetes[1])->get()
                ->groupBy('id')
                ->map(fn($group) => $group->sortBy('nouvelle_valeur_doublon_potentiel')->first())
                ->values();

            if ($doublons_note_de_frais->isNotEmpty()) {
                foreach ($doublons_note_de_frais as $doublon) {
                    management('note_de_frais', $doublon->id, modele('note_de_frais', $doublon->id))
                        ->enregistre(['doublon_potentiel' => $doublon->nouvelle_valeur_doublon_potentiel]);
                }

                $doublon_apres_enregistrement = $doublons_note_de_frais->contains('nouvelle_valeur_doublon_potentiel', 1) ? 1 : 0;

                $doublon_potentiel_modele = ($this->modele->accepte == 2) ? 0 : $doublon_apres_enregistrement;

                if ($this->modele->doublon_potentiel != $doublon_potentiel_modele) {
                    $this->enregistre_modele(['doublon_potentiel' => $doublon_potentiel_modele]);
                }
            } else {
                $this->enregistre_modele(['doublon_potentiel' => 0]);
            }
        }

        if(empty($articles) && array_key_exists('ecart_gestion_ttc', $modifications) && $modifications['ecart_gestion_ttc'] != $this->modele_avant->ecart_gestion_ttc)
            $this->maj_montants();

        return true;
	}
	
	/**
	 *
	 * Log la validation de la note de frais
	 *
	 */
	protected function log_validation() {

		return $this->enregistrer_log(Variables::$types_logs['validation']);
	}

	/**
     *
     * Retourne les actions sur les listes
     * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
     *
     */
    public function actions_a_afficher($id_liste) {

        // on recupère les actions principales
        $actions = parent::actions_a_afficher($id_liste);

        $actions['valider_les_notes_note_de_frais'] = '<a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_valider_note_de_frais"><i class="fa fa-fw fa-check"></i> <span v-html="$root.traduction(\'interface.listes.valider_notes_de_frais\')"></span></a>';

        $actions['refuser_les_notes_note_de_frais'] = '<a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_refuser_note_de_frais"><i class="fa fa-fw fa-times"></i> <span v-html="$root.traduction(\'interface.listes.refuser_notes_de_frais\')"></span></a>';
        
        if(profil($this->_type_element, $this->modele->entite_id ?? null, 'comptabilisation'))
		    $actions['comptabiliser_notes_de_frais'] = '<span class="dropdown-item" @click="modale_comptabiliser_notes_de_frais = true"><i class="fa fa-fw fa-check"></i> <span v-html="$root.traduction(\'interface.listes.comptabiliser\')"></span></span>';

        $actions['pdf'] = '<a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal_pdf_note_de_frais"><i class="fa fa-fw fa-file-pdf"></i><span v-html="$root.traduction(\'interface.listes.exporter_en_pdf\')"></span></a>';

        return $actions;
    }

    public function actions_listes($liste_libre){

        $actions = parent::actions_listes($liste_libre);

        $utilisateurs_ids = modele('utilisateur')
            ->join('utilisateur_validation_ndf_n_plus_1','utilisateur.id','cle_locale')
            ->get()->pluck('valeur')->toArray();

        $utilisateurs_ids = array_merge($utilisateurs_ids,modele('utilisateur')
            ->join('utilisateur_validation_ndf_n_plus_2','utilisateur.id','cle_locale')
            ->get()->pluck('valeur')->toArray());

        $utilisateurs_ids = array_unique($utilisateurs_ids);

        if(empty(moi()) || (!editeur() && !in_array(moi()->id,$utilisateurs_ids))){

            if(isset($actions['valider_les_notes_note_de_frais']))
                unset($actions['valider_les_notes_note_de_frais']);

            if(isset($actions['refuser_les_notes_note_de_frais']))
                unset($actions['refuser_les_notes_note_de_frais']);
        }

        return $actions;
    }

    /**
     *
     * Calcule le total de la tva
     *
     */
    public function total_tva($modele) {

        $total = round(round($modele->montant_ttc,2) - round($modele->montant_ht,2),2);

        return $total;
    }

	/**
	 * 
	 * 
	 * Fonction qui met à jour le statut de la demande de congé
	 * 
	 */
	public function validation_note_de_frais($reponse) { 

		$note_de_frais = $this->modele;

		$employe = modele('utilisateur', $note_de_frais->utilisateur_id);
        $management_employe = management('utilisateur', $employe->id, $employe);
        $management_employe->charge_valeurs_champs_multiselection();
		$utilisateur_connecte = moi();

		// Action qu'on effecute si le N+1 valide la demande de congé
		if(in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_1) || in_array($utilisateur_connecte->id,$employe->validation_ndf_n_plus_2)) {

			$donnees['accepte'] = $reponse;			
			$this->enregistre($donnees);
		}
		
	}

	/**
	 * 
	 * 
	 * On envoie un email pour valider la note de frais
	 * 
	 */
	public function email_validation_note() {

        $utilisateur = modele('utilisateur')
            ->where('id', $this->modele->utilisateur_id)
            ->first();

		$note_de_frais = [
		
			'modele' => $this->modele->toArray(),
			'employe' => $utilisateur
		];

        $destinataires = [];
		
		// Si le n+1 existe, et qu'il n'a pas encore validé la demande de congé
        if(empty($this->modele->valide_n1)) {

            $validateurs_n_plus_1 = modele('utilisateur')
                ->select('utilisateur.*')
                ->join('utilisateur_validation_ndf_n_plus_1','valeur','utilisateur.id')
                ->where('cle_locale',$this->modele->utilisateur_id)
                ->get();

            foreach($validateurs_n_plus_1 as $validateur){

                $note_de_frais['sujet'] = traduction('mails.validation_demande.merci_de_valider_demande_conges', $utilisateur->n_plus_1_langue,
                    array(
                        $validateur->prenom,
                        $validateur->nom,
                        $utilisateur->prenom,
                        $utilisateur->nom
                    )
                );

                $note_de_frais['destinataire'] = [
                    'email' => $validateur->email,
                    'prenom' => $validateur->prenom,
                    'nom' => $validateur->nom,
                    'langue' => $validateur->langue,
                ];

                $destinataires[] = $note_de_frais;
            }
		}
		// Sinon, on envoie un email au n+2 
        elseif(empty($this->modele->valide_n2)) {

            $validateurs_n_plus_2 = modele('utilisateur')
                ->select('utilisateur.*')
                ->join('utilisateur_validation_ndf_n_plus_2','valeur','utilisateur.id')
                ->where('cle_locale',$this->modele->utilisateur_id)
                ->get();

            foreach($validateurs_n_plus_2 as $validateur){

                $note_de_frais['sujet'] = traduction('mails.validation_demande.merci_de_valider_demande_conges', $utilisateur->n_plus_1_langue,
                    array(
                        $validateur->prenom,
                        $validateur->nom,
                        $utilisateur->prenom,
                        $utilisateur->nom
                    )
                );

                $note_de_frais['destinataire'] = [
                    'email' => $validateur->email,
                    'prenom' => $validateur->prenom,
                    'nom' => $validateur->nom,
                    'langue' => $validateur->langue,
                ];

                $destinataires[] = $note_de_frais;
            }
		}

        if(empty($destinataires))
            return;

        foreach($destinataires as $note_de_frais){

            if(empty($note_de_frais['destinataire']['langue']))
                $note_de_frais['destinataire']['langue'] = 'fr';

            if(empty($note_de_frais['modele']['montant_ttc']))
                $note_de_frais['modele']['montant_ttc'] = 0;

            if(empty($note_de_frais['modele']['data']))
                $note_de_frais['modele']['data'] = date('Y-m-d');

            $note_de_frais['route_justificatif'] = URL::to('storage/'.$note_de_frais['modele']['scan']);
            $note_de_frais['route_validation'] = route('note_de_frais.validation_note', ['id_demande' => $note_de_frais['modele']['id'], 'reponse' => 1]);
            $note_de_frais['route_refus'] = route('note_de_frais.validation_note', ['id_demande' => $note_de_frais['modele']['id'], 'reponse' => 2]);

            try {

                // on prépare les données pour envoyer le mail
                $service_email = service('email');

                $parametres_email = [
                    'type_configuration' => 1,
                    'destinataire' => [$note_de_frais['destinataire']['email']],
                    'sujet' => $note_de_frais['sujet'],
                ];

                $variables_email = [
                    'note_de_frais' => $note_de_frais
                ];

                $retour = $service_email->envoyer('eden::mails.validation_note_de_frais', $variables_email, $parametres_email);
            }
            catch(\Exception $e){
                log_eden('Erreur envoi note de frais '.$note_de_frais['modele']['id'].' mail de validation');
            }
        }
	}

    /**
     *
     *
     * On change le modele par défaut pour rajouter les articles
     *
     */
    public function modele_par_defaut() {

        $retour = parent::modele_par_defaut();

        $retour['articles'] = [];

        return $retour;
    }


    /**
     *
     * On envoie un mail de validation à la création
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications){
        
        if((!isset($modele_avant->id) || $modele_avant->accepte == 2 && empty($modele->accepte))  && fonctionnalite('emails_actifs_intranet')['note_de_frais'])
            $this->email_validation_note();

        parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }
    
    /**
     *
     *
     * On supprime les lignes associés
     *
     */
    protected function methodes_post_suppression($modele) {

        parent::methodes_post_suppression($modele);

        $lignes = modele('note_de_frais_lignes')->where('note_de_frais_id',$modele->id)->get();

        foreach($lignes as $ligne){

            management('note_de_frais_lignes',$ligne->id)->supprime();
        }
    }

    /**
     *
     *
     * On vérifie les états des notes de frais
     *
     */
    public function supprime($modele = false) {

        if($this->modele->statut > 0 || $this->modele->accepte > 0)
            return traduction('messages.php.note_de_frais.suppression_impossible');

        return parent::supprime($modele);
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);

        $liste_options[] = 'zoom';

        array_unshift($liste_options, 'valider_refuser');

        return $liste_options;
    }

    /**
     *
     * Recalcule le montant remboursé et le retourne
     *
     */
    public function recalcule_montant_rembourse(){

        return modele('note_de_frais_lignes')
            ->select(DB::raw('SUM(note_de_frais_lignes.montant_rembourse) + COALESCE(ecart_gestion_ttc,0) AS montant_rembourse'))
            ->join('note_de_frais','note_de_frais.id','note_de_frais_lignes.note_de_frais_id')
            ->where('note_de_frais_id',$this->modele->id)
            ->groupBy('note_de_frais.id')
            ->first()
            ->toArray();
    }

    /**
     *
     * Recalcule les montants HT et TTC et les retourne
     *
     */
    public function recalcule_montants(){

        return modele('note_de_frais_lignes')
            ->select(DB::raw('SUM(note_de_frais_lignes.montant_ht) AS montant_ht'), DB::raw('SUM(note_de_frais_lignes.montant_ttc) + COALESCE(ecart_gestion_ttc,0) AS montant_ttc'))
            ->join('note_de_frais','note_de_frais.id','note_de_frais_lignes.note_de_frais_id')
            ->where('note_de_frais_id',$this->modele->id)
            ->groupBy('note_de_frais.id')
            ->first()
            ->toArray();
    }
	
	/**
	 *
	 * Affiche une liste de tags pour les listes
	 *
	 */
	public function tags_pour_liste($modele) {

		$tags = array();

		if($modele->accepte == 1) {

			$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.acceptee').'</span>';
		}
		elseif($modele->accepte == 2) {

			$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.refusee').'</span>';
		}
		else {

			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.en_attente_de_validation').'</span>';
		}
		
		
		if($modele->comptabilisee == 1) {
			
			$tags[] = '<span class="badge badge-warning" style="background: #249e8e">'.traduction('interface.listes.tags_pour_liste.comptabilisee').'</span>';
		}
		
		if($modele->rembourse == 1) {
			
			$tags[] = '<span class="badge badge-warning" style="background: #b45cbf">'.traduction('interface.listes.tags_pour_liste.remboursee').'</span>';
		}
		
		if($modele->montant_ttc > $modele->montant_rembourse) {
			
			$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.hors_plafond').'</span>';
		}

        if($modele->doublon_potentiel)
            $tags[] = '<span class="badge badge-warning">'.traduction('champs_libres.note_de_frais.doublon_potentiel.nom').'</span>';

		return implode(' ', $tags);
	}

    /**
     *
     * Indique ou non une alerte de plafond
     *
     */
    public function alerte_plafond($modele){

        $lignes = modele('note_de_frais_lignes')->where('note_de_frais_id',$modele->id)->get();

        foreach($lignes as $ligne){
            if($ligne->montant_ttc > $ligne->plafond)
                return '<div style="text-align: center;color:#ff6666"><i class="fas fa-exclamation-triangle"></i></div>';
        }
        
        return '';
    }
	
	/**
	 *
	 * Comptabilisation
	 *
	 */
	public function comptabilise() {

		// ce document est déjà comptabilisé
		if($this->modele->comptabilisee == 1) {

			return traduction('messages.php.note_de_frais.deja_comptabilisee');
		}

		// ce document n'est pas accepté
		if($this->modele->accepte != 1) {

			return traduction('messages.php.note_de_frais.non_acceptee');
		}

        $fonctionnalite_blocage = fonctionnalite('mois_bloquant_saisie_note_de_frais');

        if(!empty($this->modele->date) && !empty($fonctionnalite_blocage) && date('Y-m-d',strtotime('-'.$fonctionnalite_blocage.' months')) > $this->modele->date)
            return traduction('messages.php.note_de_frais.date_inferieur_blocage',null,[$fonctionnalite_blocage]);
		
		// on vérifie le compte général salariés
		$compte_general_salaries = fonctionnalite('compta_compte_general_salaries');
		
		if(empty($compte_general_salaries)) {
			
			return traduction('messages.php.note_de_frais.compte_general_non_defini');
		}
		
		// on vérifie le matricule de l'utilisateur
		if(empty($this->modele->utilisateur_id))
			return traduction('messages.php.note_de_frais.non_affectee_utilisateur');
		
		$utilisateur = modele('utilisateur', $this->modele->utilisateur_id);
		
		$matricule = $utilisateur->matricule;
		
		if(empty($matricule))
			return traduction('messages.php.note_de_frais.matricule_non_defini');

		// on va chercher les lignes de la note de frais
		$lignes = modele('note_de_frais_lignes')->where('note_de_frais_id', $this->modele->id)->get();
		
		$comptes_charge = array();
        $comptes_produit = array();
		$comptes_tva = array();
		$montant_total = 0;
		
		foreach($lignes as $ligne) {
			
            $article = modele('article_note_de_frais', $ligne->article_id);
			$compte_charge = $article->compte_comptable;
			
			if(empty($compte_charge))
				return traduction('messages.php.note_de_frais.compte_comptable_article_non_defini',null,[$article->nom]);

            if($ligne->montant_ttc == 0)
				return traduction('messages.php.note_de_frais.erreur_ligne_ttc_a_0',null,[$article->nom]);
			
			if(!isset($comptes_charge[$compte_charge]))
				$comptes_charge[$compte_charge] = 0;
			
			// on doit mettre le montant HT
			$montant_ht = $ligne->montant_ht * $ligne->montant_rembourse / $ligne->montant_ttc;
			$montant_tva = 0;
			
			// on va chercher le compte de TVA
			if(!empty($ligne->taux_tva) && $ligne->taux_tva != -1) {
				
				$code_tva = modele('code_tva', $ligne->taux_tva);
				
				if(empty($code_tva->compte_comptable))
					return traduction('messages.php.note_de_frais.compte_comptable_code_tva_non_defini',null,[$code_tva->code]);
				
				if(!isset($comptes_tva[$code_tva->compte_comptable]))
					$comptes_tva[$code_tva->compte_comptable] = 0;
				
				$montant_tva = ($ligne->montant_ttc - $ligne->montant_ht) * $ligne->montant_rembourse / $ligne->montant_ttc;
				
				$comptes_tva[$code_tva->compte_comptable] += $montant_tva;
			}
				
			$comptes_charge[$compte_charge] += $montant_ht;
			
			$montant_total += $montant_ht + $montant_tva;
		}
		
		// ok normalement on a tous les comptes... on traite.
		$ecriture_id = modele('ecriture_comptable')->orderBy('ecriture_id', 'DESC')->take(1)->first();

		if($ecriture_id === null)
			$ecriture_id = 1 + management('ecriture_comptable')->increment_initial_ecriture_comptable();
		else
			$ecriture_id = $ecriture_id->ecriture_id + 1;

        if(!empty($this->modele->ecart_gestion_ttc)) {

            $ecart_gestion_ttc = $this->modele->ecart_gestion_ttc;

            $compte_comptable = $ecart_gestion_ttc < 0 ? fonctionnalite('ecart_gestion_compte_produit') : fonctionnalite('ecart_gestion_compte_charge');
				
			if(empty($compte_comptable))
				return traduction('messages.php.document.compte_produit_parametrage_categorie_comptable');

            if($ecart_gestion_ttc < 0){
                if(!isset($comptes_produit[$compte_comptable]))
				    $comptes_produit[$compte_comptable] = 0;

				$comptes_produit[$compte_comptable] += $ecart_gestion_ttc * -1;
            }
            else{
                if(!isset($comptes_charge[$compte_comptable]))
                    $comptes_charge[$compte_comptable] = 0;

                $comptes_charge[$compte_comptable] += $ecart_gestion_ttc;
            }

            $montant_total += $ecart_gestion_ttc;
		}
		
		// création de l'écriture

		//on vérifie si les totaux collent bien
		$difference = round(array_sum($comptes_charge) - array_sum($comptes_produit) + array_sum($comptes_tva), 2) - round($montant_total, 2);
		
		if(abs($difference) > 0.01) {
			
			return traduction('messages.php.document.comptabilisation_impossible',null,[round(array_sum($comptes_tva) - array_sum($comptes_produit) + array_sum($comptes_charge), 2),round($montant_total, 2)]);
		}
		
		$journal = fonctionnalite('compta_journal_note_de_frais');
		
		if(empty($journal))
			return traduction('messages.php.document.determiner_journal_impossible').' '.table_libre('note_de_frais')->element;
		
		$libelle = $this->compta_genere_libelle();

        $ecritures_a_creer = [];

		// les comptes de produit ou charge
		foreach($comptes_charge as $compte_id => $montant) {
			
			if(empty($montant))
				continue;

			$ecritures_a_creer[] = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				'auxiliaire' => '',
				'debit' => round($montant, 2),
				'credit' => 0,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'libelle' => $libelle,
			);
		}

        // les comptes de produit ou charge
		foreach($comptes_produit as $compte_id => $montant) {
			
			if(empty($montant))
				continue;

			$ecritures_a_creer[] = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				'auxiliaire' => '',
				'debit' => 0,
				'credit' => round($montant, 2),
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'libelle' => $libelle,
			);
		}

		// les comptes de TVA
		foreach($comptes_tva as $compte_id => $montant) {
			
			if(empty($montant))
				continue;

			$ecritures_a_creer[] = array(

				'ecriture_id' => $ecriture_id,
				'date' => $this->modele->date,
				'journal_id' => $journal,
				'compte_comptable_id' => $compte_id,
				'auxiliaire' => '',
				'debit' => round($montant, 2),
				'credit' => 0,
				'type_element' => $this->_type_element,
				'element_id' => $this->modele->id,
				'entite_id' => $this->modele->entite_id,
				'libelle' => $libelle,
			);
		}

		// le compte de tiers
		$ecritures_a_creer[] = array(

			'ecriture_id' => $ecriture_id,
			'date' => $this->modele->date,
			'journal_id' => $journal,
			'compte_comptable_id' => $compte_general_salaries,
			'auxiliaire' => $matricule,
			'debit' => 0,
			'credit' => round($montant_total, 2),
			'type_element' => $this->_type_element,
			'element_id' => $this->modele->id,
			'entite_id' => $this->modele->entite_id,
			'libelle' => $libelle,
		);

        foreach($ecritures_a_creer as $ecriture) {

            $management = management('ecriture_comptable');

            $retour = $management->test_enregistre($ecriture);

            if($retour !== 'test_ok')
                return $retour;
        }

        foreach($ecritures_a_creer as $ecriture) {

            $management = management('ecriture_comptable');

            $management->enregistre($ecriture);
        }

		// on enregistre le document comme comptabilisé
		$this->enregistre_modele(array('comptabilisee' => 1));

		// on logue la comptabilisation du document
        $this->log_comptabilisation();

		return true;
	}

    /**
	 *
	 * Retourne le libellé a utiliser pour l'écriture comptable
	 *
	 */
	protected function compta_genere_libelle() {

		$libelle = '';

        $libelle_parametrable = fonctionnalite('compta_libelle_note_de_frais');

        if(!empty($libelle_parametrable))
            $libelle = service('publipostage')->publipostage_texte($libelle_parametrable, 'note_de_frais', [$this->modele->id]);

		return $libelle;
	}
	
	/**
	 *
	 * On logue la comptabilisation d'un document
	 *
	 * @return void
	 *
	 */
    public function log_comptabilisation() {

        return $this->enregistrer_log(Variables::$types_logs['comptabilisation']);
	}

    /**
     *
     * Retraite les notes de frais pour l'export pdf
     *
     */
    public function traitement_notes_de_frais_pour_export_pdf($ids_note_de_frais, &$numero_page, &$totaux, &$annexes, &$categories){

        foreach($ids_note_de_frais as $id){

            $note_de_frais = modele('note_de_frais',$id);

            $note_de_frais->annexe = "Pas d'annexe";

            $extension = null;

            if($note_de_frais->scan !=null){

                $extension = strtolower(pathinfo($note_de_frais->scan, PATHINFO_EXTENSION));

                if($extension != 'pdf') {

                    $numero_page++;

                    $note_de_frais->annexe = "Page n° " . $numero_page;

                }

            }

            $totaux['tva']+= $note_de_frais->montant_ttc - $note_de_frais->montant_ht;

            $totaux['ttc']+= $note_de_frais->montant_ttc;

            $annexes[$note_de_frais->id]['page'] = $note_de_frais->annexe;
            $annexes[$note_de_frais->id]['image'] = $note_de_frais->scan;
            $annexes[$note_de_frais->id]['extension'] = $extension;

        }

    }

    /**
     *
     * On ajoute les articles au modéle
     *
     */
    public function retraite_modele_recuperation(){

        if(!empty($this->modele->id)) {
            $this->modele->articles = modele('note_de_frais_lignes')
                ->select('note_de_frais_lignes.*', DB::raw('ROUND(note_de_frais_lignes.montant_ht / taux_de_change,2) as montant_devise'))
                ->join('note_de_frais', 'note_de_frais_lignes.note_de_frais_id', 'note_de_frais.id')
                ->where('note_de_frais_id', $this->modele->id)
                ->get();
        }
    }

    /*
     *
     * On récupére les paiements pour savoir combien a été remboursé
     *
     */
    public function maj_remboursement(){

        $paiements = modele('paiement')->where('type_element','note_de_frais')
            ->where('id_document', $this->modele->id)
            ->get();

        $montant_rembourse = 0;
        $date = null;

        foreach($paiements as $paiement){

            $montant_rembourse+= $paiement->montant_saisi;

            if($date == null || $date < $paiement->date)
                $date = $paiement->date;
        }

        if($montant_rembourse == $this->modele->montant_rembourse)
            $this->enregistre(array(
                'rembourse' => 1,
                'date_reelle_de_remboursement' => $date
            ));
    }

    /*
     *
     * Calcule la catégorie de dépenses pour laquelle le total est le plus élevé et la retourne
     *
     */
    public function recalcule_categorie_depense() {

        return [
            'article_id' => modele('note_de_frais_lignes')
                ->select(DB::raw('SUM(montant_ttc) AS montant_ttc'), 'article_id')
                ->where('note_de_frais_id',$this->modele->id)->groupBy('article_id')
                ->orderBy('montant_ttc', 'DESC')
                ->value('article_id')
        ];
    }

    /*
     *
     * Met à jour les montants (dont le montant remboursé) et la catégorie de dépenses principale de la note de frais
     *
     */
    public function maj_montants() {

        $montants = $this->recalcule_montants();
        $montant_rembourse = $this->recalcule_montant_rembourse();
        $categorie_depense = $this->recalcule_categorie_depense();

        $modifications = array_merge($montants, $montant_rembourse, $categorie_depense);

        $this->enregistre($modifications);
    }

    /**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        $liste_libre = Liste_libre::where('type_element','note_de_frais_lignes')->where('id_rapport','detail_ligne_note_de_frais')->first();

        if(empty($liste_libre))
            exception(traduction('messages.php.rapport_detail_ligne_inexistant'));

		// On va chercher les infos qui nous interessent
		$management = management($type_element, $id_element);

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_document";

        $liste_management = liste('note_de_frais_lignes','detail_ligne_note_de_frais');

        $nombres_de_lignes = modele('note_de_frais_lignes')->where('note_de_frais_id',$id_element)->count();

        $filtres_pour_fiche = array(
            'note_de_frais_id' => $id_element,
        );

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id,[
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => $nombres_de_lignes,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

		$vue_render = view($vue, array(
			'management' => $management,
			'id_liste' => $liste_libre->id,
			'type_element' => 'note_de_frais_lignes',
			'id_element' => $id_element,
			'liste_libre' => $liste_libre,
			'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'id_liste_parent' => $id_liste_parent,
		))->render();

		return array('composant' => $vue_render);
	}
}

