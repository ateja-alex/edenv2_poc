<?php

namespace App\Eden\Controllers\Extranet;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Crypt;
use App\Eden\Controllers\Fiche_controller;

class Utilisateur_extranet_controller extends Controller {

    public function envoi_mail_inscription(){

        $id = request()->id;

        $modele = modele('utilisateur_extranet')->find($id);

        if(empty($modele))
            return response()->json(['retour' => traduction('messages.php.extranet.utilisateur_extranet_introuvable')]);

        $management = management('utilisateur_extranet', $id, $modele);

        $retour = $management->envoie_email_creation_compte();

        return response()->json(['retour' => $retour]);
    }

    /**
	 *
	 * Retourne la vue pour modifier l'utilisateur connecté
	 *
	 */
	public function profil_utilisateur_connecte(){

		$utilisateur = moi_extranet();

		return view('eden::extranet.gestion_utilisateur_connecte',[
			'utilisateur' => $utilisateur
		]);
	}

	/**
	 *
	 * Enregistre les modifications que l'utilisateur peut effectuer
	 *
	 */
	public function enregistrer_modification_utilisateur_connecte(Request $formulaire) {
        
        $infos_utilisateur = $formulaire->all();

        $utilisateur = management('utilisateur_extranet',moi_extranet()->id);

        // on enregistre l'utilisateur
        $retour = $utilisateur->enregistrer_modification_utilisateur_connecte($infos_utilisateur);

        foreach($utilisateur->modele->toArray() as $cle => $valeur){
            if($cle != 'mot_de_passe')
                moi_extranet()->$cle = $valeur; 
        }

        return response()->json(array('retour' => $retour,'utilisateur' => moi_extranet()));

	}

    public function changer_double_facteur_application(){

        $utilisateur = modele('utilisateur_extranet')->find(request()->input('utilisateur_id'));

        if($utilisateur === null)
            return response()->json(['erreur' => traduction('messages.php.extranet.utilisateur_extranet_introuvable')]);
    
        $google2fa = new Google2FA();

        $secretKey = $google2fa->generateSecretKey();
        $url_codeqr = $google2fa->getQRCodeInline(
            maquette('nom_application'),
            $utilisateur->email,
            $secretKey
        );

        $management = management('utilisateur_extranet',$utilisateur->id,$utilisateur);

        $retour = $management->enregistre_modele([
            'google2fa_secret' => Crypt::encryptString($secretKey),
            'double_facteur_authentification' => 2
        ]);

        $utilisateur_extranet = session()->get('utilisateur_eden_extranet');

        $utilisateur_extranet->google2fa_secret = $management->modele->google2fa_secret;
        $utilisateur_extranet->double_facteur_authentification = 2;

        return response()->json(['qrcode' => $url_codeqr,'code_secret' => $secretKey]);
    }

    /**
	 * 
	 * Redirige vers la fiche client de l'utilisateur connecté
	 * 
	 */
	public function mes_informations() {

        define('acces_autorise',true);

        $fiche_controlleur = new Fiche_controller();

        return $fiche_controlleur->execute('client',moi_extranet()->contact_selectionne->client_id);
	}

    public function ajout_contact_en_masse(){

        $ids = request()->input('ids_element');

        $contacts_par_mail = modele('contact')->whereIn('id',$ids)->get()->groupBy('adresse_email');

        $management_utilisateur_extranet = management('utilisateur_extranet');

        $erreurs = array();

        foreach($contacts_par_mail as $adresse_email => $contacts){

            $contact = $contacts->first();
            $ids_contact = $contacts->pluck('id')->toArray();

            $retour = management('utilisateur_extranet')->test_enregistre([
                'email' => $adresse_email,
                'nom' => $contact->nom,
                'prenom' => $contact->prenom,
                'contacts' => $ids_contact,
            ]);

            if($retour !== 'test_ok'){

                if(!isset($erreurs[$retour]))
                    $erreurs[$retour] = [];

                $erreurs[$retour][] = $adresse_email .'(#'.implode(', #',$ids_contact).')';
            }
        }

        if(!empty($erreurs)){

            $texte_erreur = traduction('messages.php.extranet.compte_creation_masse_erreur');

            foreach($erreurs as $message_erreur => $liste_email){

                $texte_erreur .= '<br>'.$message_erreur." : ".implode(', ',$liste_email);
            }
            return response()->json(['retour' => $texte_erreur]);
        }

        foreach($contacts_par_mail as $adresse_email => $contacts){

            $contact = $contacts->first();
            $ids_contact = $contacts->pluck('id')->toArray();

            $retour = management('utilisateur_extranet')->enregistre([
                'email' => $adresse_email,
                'nom' => $contact->nom,
                'prenom' => $contact->prenom,
                'contacts' => $ids_contact,
            ]);
        }

        return response()->json(['retour' => true]);
    }
}