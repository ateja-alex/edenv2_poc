<?php

namespace App\Eden\Managements\Elements;

use Hash;

class Utilisateur_extranet_management extends Element_management {

    public function enregistre($modifications = array(), $modele = false) {

        if(isset($modifications['email'])) {
            $verification_email = modele('utilisateur_extranet')->where('email', $modifications['email']);

            if (!empty($this->modele->id))
                $verification_email->where('id', '!=', $this->modele->id);

            $verification_email = $verification_email->count();

            if ($verification_email >= 1)
                return traduction('messages.php.extranet.email_deja_utilise');
        }

        if(isset($modifications['mot_de_passe'])){

            if(!empty($this->modele->old_mot_de_passe))
                $modifications['old_mot_de_passe'] = null;

            if(!empty($this->modele->renouveler_mot_de_passe))
                $modifications['renouveler_mot_de_passe'] = 0;

            $modifications['date_derniere_modification_mot_de_passe'] = date('Y-m-d H:i:s');
        }

        $contacts = false;

        if(array_key_exists('contacts', $modifications)){
            $contacts = $modifications['contacts'];

            if(empty($contacts))
                return traduction('messages.php.extranet.aucun_contact_associe');
            
            unset($modifications['contacts']);
        }

        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true)
            return $retour;

        if($contacts !== false) {

            $contacts_a_dissocier = modele('contact')
                ->whereNotIn('id', $contacts)
                ->where('utilisateur_extranet_id', $this->modele->id)
                ->get();

            $contacts_a_associer = modele('contact')
                ->whereIn('id', $contacts)
                ->get();

            foreach ($contacts_a_associer as $contact) {
                management('contact',$contact->id, $contact)->enregistre_modele(['utilisateur_extranet_id' => $this->modele->id]);
            }

            foreach ($contacts_a_dissocier as $contact) {
                management('contact',$contact->id, $contact)->enregistre_modele(['utilisateur_extranet_id' => null]);
            }
        }

        return $retour;
    }

    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        if(empty($modele_avant->id) && !defined('migration_en_cours'))
            $this->envoie_email_creation_compte();

		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}

    public function methodes_post_suppression($modele) {

        parent::methodes_post_suppression($modele);

        $contacts_a_dissocier = modele('contact')
            ->where('utilisateur_extranet_id', $modele->id)
            ->get();

        foreach ($contacts_a_dissocier as $contact) {
            management('contact',$contact->id, $contact)->enregistre_modele(['utilisateur_extranet_id' => null]);
        }
    }

    public function envoie_email_creation_compte(){

        $modele = $this->modele;
        $destinataire = $modele->email;
        $extranet_token = Hash::make($modele->email .'inscription');

        $url = route('extranet.inscription',['id' => $modele->id,'t' => $extranet_token]);
        $url = str_replace($_SERVER['HTTP_HOST'], fonctionnalite('url_extranet'),$url);

        // on prépare les données pour envoyer le mail
        $service_email = service('email');
        $service_publipostage = service('publipostage');
        $modele_email = fonctionnalite('extranet_modele_email_inscription');

        if(!empty($modele_email)){

            $modele_email = modele('modele_email', $modele_email);

            $sujet = $service_publipostage->publipostage_texte($modele_email->sujet_modele, 'utilisateur_extranet', [$this->modele->id]);
            $contenu_mail = $service_publipostage->publipostage_texte($modele_email->modele, 'utilisateur_extranet', [$this->modele->id]);

            if(strpos($contenu_mail, '#url_inscription#') !== false)
                $contenu_mail = str_replace('#url_inscription#', $url, $contenu_mail);

            if(strpos($sujet, '#url_inscription#') !== false)
                $sujet = str_replace('#url_inscription#', $url, $sujet);
        }

        $parametres_email = [
            'type_configuration' => 4,
			'id_compte_email' => fonctionnalite('extranet_compte_email'),
            'destinataire' => [$destinataire],
            'sujet' => $sujet ?? "Un compte sur ".maquette('nom_application')." a été créé pour vous",
        ];

        $variables_email = [
            'compte_createur' => moi(),
            'url' => $url,
            'contenu_mail' => $contenu_mail ?? '',
        ];

        return $service_email->envoyer('eden::extranet.mails.inscription_mail', $variables_email, $parametres_email);
    }

    public function contacts_associes(){

        $contacts = modele('contact')->where('utilisateur_extranet_id', $this->modele->id)->get();

        $clients = modele('client')->whereIn('id', $contacts->pluck('client_id'))->get()->keyBy('id');

        foreach($contacts as $contact){

            if(!empty($clients[$contact->client_id])){
                $client = management('client', $contact->client_id, $clients[$contact->client_id]);
                $client->modele->chaine_affichage = $client->affiche();
                $contact->client = $client->modele;
            }
            else
                $contact->client = null;
        }

        return $contacts;
    }

    public function creation_session($contact_id = null){

        management('log_page_extranet')->enregistre(array('date' => date('Y-m-d H:i:s'), 'utilisateur_extranet_id' => $this->modele->id));

        $contacts_associes = $this->contacts_associes();

        $modele_session = clone $this->modele;
        $modele_session->contacts_associes = $contacts_associes;
        $modele_session->mot_de_passe = null;
        $modele_session->old_mot_de_passe = null;

        if($contact_id !== null && $contacts_associes->where('id', $contact_id)->count() == 1)
            $modele_session->contact_selectionne = $contacts_associes->where('id', $contact_id)->first();
        else if($modele_session->contact_selectionne_id !== null && $contacts_associes->where('id', $modele_session->contact_selectionne_id)->count() == 1)
            $modele_session->contact_selectionne = $contacts_associes->where('id', $modele_session->contact_selectionne_id)->first();
        else{
            $modele_session->contact_selectionne = $contacts_associes->first();
            $this->enregistre_modele(['contact_selectionne_id' => $modele_session->contact_selectionne->id]);
        }
        
        // on met l'utilisateur en session
        session()->put('utilisateur_eden_extranet', $modele_session);

        return true;
    }

    public function comparaison_mdp_connexion($mot_de_passe_saisi){

        if(empty($this->modele->mot_de_passe) && !empty($this->modele->old_mot_de_passe)){
            
            if($this->modele->renouveler_mot_de_passe != 1)
                $this->enregistre_modele(['renouveler_mot_de_passe' => 1]);

            return $this->modele->old_mot_de_passe == md5('extraneteden'.$mot_de_passe_saisi);
        }
        else if(!Hash::check($mot_de_passe_saisi, $this->modele->mot_de_passe))
            return false;

        return true;
    }

    /**
	 *
	 * @todo à décrire
	 *
	 */
	public function enregistrer_modification_utilisateur_connecte($modifications = array(), $modele = false) {

		if(!empty($modifications["mot_de_passe"])){

			$mot_de_passe = $modifications["mot_de_passe"];
			$confirmation_mot_de_passe = $modifications["mot_de_passe_verification"] ?? '';

			if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $mot_de_passe))
				return traduction('messages.php.connexion.mdp_invalide');

			if($mot_de_passe != $confirmation_mot_de_passe)
				return traduction('messages.php.connexion.mdp_differents');

			if(Hash::check($mot_de_passe, $this->modele->mot_de_passe))
				return traduction('messages.php.connexion.mdp_pareils_precedent');

			$modifications["mot_de_passe"] = Hash::make($mot_de_passe);

			if(array_key_exists("mot_de_passe_verification", $modifications))
				unset($modifications["mot_de_passe_verification"]);
		}
		else if(array_key_exists("mot_de_passe", $modifications))
			unset($modifications["mot_de_passe"]);

		return parent::enregistre($modifications);
	}

    public function mot_de_passe_a_renouveler(){

        if($this->modele->renouveler_mot_de_passe == 1)
			return true;
        
		if(fonctionnalite('renouvellement_mot_de_passe_utilisateur_extranet') === false)
			return false;

		$jours = fonctionnalite('duree_validite_mot_de_passe_utilisateur_extranet');

		if($this->modele->date_derniere_modification_mot_de_passe >= date('Y-m-d H:i:s', strtotime('-' . $jours . ' days')))
			return false;

		$this->enregistre_modele(['renouveler_mot_de_passe' => 1]);

		return true;
	}
}