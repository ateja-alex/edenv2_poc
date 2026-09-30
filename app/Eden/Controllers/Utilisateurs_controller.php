<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

use App\Eden\Managements\Cache_management;
use App\Eden\Models\Utilisateur;
use App\Eden\Models\Table_libre;
use App\Eden\Managements\Utilisateurs_management;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Crypt;

use App\Http\Controllers\Controller;

class Utilisateurs_controller extends Controller {

    /**
	 *
     * Affiche la liste des profils
	 *
     */
    public function liste_utilisateurs() {

        $utilisateurs_management = new Utilisateurs_management();

		$utilisateurs = $utilisateurs_management->liste_utilisateurs();

		$liste_utilisateurs_microsoft = array();

        if(fonctionnalite('microsoft_utiliser_connexion') && !empty(moi()->id_microsoft))
            $graph = service('microsoft_authentification')->instancie_graph();

		if(!empty($graph)){

            try {

                // Récupération des infos voulues
                $utilisateurs_microsoft = $graph->createRequest('GET', "/users?\$filter=userType eq 'Member'")
                    ->setReturnType(Model\User::class)
                    ->execute();
            } catch(\Exception $e) {

                $utilisateurs_microsoft = array();
                Log::warning("[Récupération utilisateurs microsoft] Impossible de récupérer les utilisateurs faisant partie de l'AD. Message d'erreur : " . $e->getMessage() . " Stacktrace : " . $e->getTraceAsString());
            }

			foreach($utilisateurs_microsoft as $utilisateur){

                if($utilisateur->getMail() === null)
                    continue;

				// on vérifie si l'utilisateur est déjà existant en base ?
				$utilisateur_en_base = modele('utilisateur')->where('email', $utilisateur->getMail())->first();

				if($utilisateur_en_base !== null)
					continue;

				$utilisateur_microsoft = array();
				$utilisateur_microsoft['mail'] = $utilisateur->getMail();
				$utilisateur_microsoft['prenom'] = $utilisateur->getGivenName();
				$utilisateur_microsoft['nom'] = $utilisateur->getSurname();

                try {
                    $photo = $graph->createRequest('GET', '/users/' . $utilisateur->getId() . '/photo/$value')
                        ->execute();
                    $photo = $photo->getRawBody();

                    $metadonnees = $graph->createRequest('GET', '/users/' . $utilisateur->getId() . '/photo')
                        ->execute();
                    $metadonnees = $metadonnees->getBody();

                    $utilisateur_microsoft['photo']['stream'] = base64_encode($photo);
                    $utilisateur_microsoft['photo']['type'] = $metadonnees["@odata.mediaContentType"];

                }catch(\Exception $e){

                    $utilisateur_microsoft['photo'] = null;
                }

				$liste_utilisateurs_microsoft[] = $utilisateur_microsoft;
			}
		}

        return view('eden::gestion_utilisateurs', [

            'utilisateurs' => $utilisateurs,
			'utilisateurs_microsoft' => $liste_utilisateurs_microsoft,
            'profils' => modele('profil')
                ->where(function($where){
                    $where->whereNull('extranet')
                        ->orWhere('extranet', 0);
                })
                ->get()
        ]);
    }

	/**
	 *
	 * Retourne la vue pour modifier l'utilisateur connecté
	 *
	 */
	public function profil_utilisateur_connecte(){

		$utilisateur = moi();

		return view('eden::gestion_utilisateur_connecte',[

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

			// à enlever par la suite
			session()->put('locale', $infos_utilisateur['langue']);

			$utilisateur = management('utilisateur',moi()->id);

			// on enregistre l'utilisateur
			$retour = $utilisateur->enregistrer_modification_utilisateur_connecte($infos_utilisateur);

			$utilisateur = Utilisateur::where('id',moi()->id)->first();
			session()->put('utilisateur_eden',$utilisateur);

			return response()->json(array('retour' => $retour));

	}

    /**
	 *
     * On supprime un utilisateur
     *
     */
    public function supprimer(Utilisateurs_management $utilisateurs_management, $id) {

		// on vérifie que l'utilisateur n'est pas lui même
		if(moi()->id == $id) {

			return response()->json(array('retour' => traduction('messages.php.utilisateur.suppresion_propre_compte')));
		}

		// on supprime l'utilisateur
		management('utilisateur', $id)->supprime();

        $utilisateurs = $utilisateurs_management->liste_utilisateurs();

		return response()->json(array('retour' => true, 'utilisateurs' => $utilisateurs));
    }

    /**
	 *
     * On modifie le mode de paramètrage de l'utilisateur
     *
     */
    public function changer_mode_parametrage(Utilisateurs_management $utilisateurs_management, $mode_parametrage) {

		// on change le thème de l'utilisateur
		management('utilisateur', moi()->id)->changer_mode_parametrage($mode_parametrage);

		return response()->json(array('retour' => traduction('messages.php.utilisateur.mode_parametrage_modifie')));
    }

    /**
	 *
     * On modifie le mode de paramètrage de l'utilisateur
     *
     */
    public function changer_mode_vuejs(Utilisateurs_management $utilisateurs_management, $mode_parametrage) {

		session()->put('vuejs_mode_dev', intval($mode_parametrage));

		return response()->json(array('succes' => 1));
    }

    /**
     *
     *  On recupère ou enlève les droits du compte initial
     *
     */
    public function recuperer_droits_compte_initial($action){

        if($action == 1)
            session()->put('activer_recuperer_droits_compte_initial',true);
        elseif(session()->has('activer_recuperer_droits_compte_initial'))
            session()->forget('activer_recuperer_droits_compte_initial');
    }

    /**
     *
     * Permet de récupérer les utilisateurs triés par équipe
     *
     */
    public function utilisateurs_par_equipe(){

        $utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles();

        foreach($utilisateurs as $utilisateur) {

			if(!empty($utilisateur->equipe)) {

				$equipe = modele('equipe', $utilisateur->equipe);
				$utilisateur->nom_equipe = '<span class="badge" style="background: '.$equipe->couleur_fond.'; color: '.$equipe->couleur_police.';">'.$equipe->nom.'</span>';
			}
			else {

				$utilisateur->nom_equipe = '<span class="badge badge-default">Sans équipe</span>';
			}
		}

        return  response()->json(service('planning')->recupere_equipes_d_utilisateurs($utilisateurs));

    }

    /**
     *
     * Page initialisation mot de passe
     *
     */
    public function initialisation_mot_de_passe($token){

        return view('eden::authentification.initialisation_mot_de_passe',[

            'token' => $token,
        ]);
    }

    /**
     *
     * On initialise le mdp
     *
     */
    public function initialisation_mot_de_passe_post(Request $formulaire){

        // On regarde si l'association adresse mail / token existe
        $utilisateur = modele('utilisateur')->where('email',$formulaire->email)->where('token_initialisation_mot_de_passe',$formulaire->token)->first();

        if($utilisateur === null)
            return \Redirect::back()->withErrors(['mail_token' => traduction('messages.php.connexion.token_non_lie_mail')]);

        // On vérifie que le mdp et la vérif correspondent
        if($formulaire->mot_de_passe != $formulaire->confirmation_mot_de_passe)
            return \Redirect::back()->withErrors(['confirmation_mdp' => traduction('messages.php.connexion.mdp_differents')]);

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $formulaire->mot_de_passe))
			return redirect()->back()->withErrors(['confirmation_mdp' => traduction('messages.php.connexion.mdp_invalide')]);

        $management_utilisateur = management('utilisateur',$utilisateur->id);

        // On remplit le mdp en base
        $retour = $management_utilisateur->enregistre_modele(
            array(
                'mot_de_passe' => $management_utilisateur->ed_crypt($formulaire->mot_de_passe),
                'token_initialisation_mot_de_passe' => '',
                'renouveler_mot_de_passe' => 0,
			    'date_derniere_modification_mot_de_passe' => date('Y-m-d H:i:s'),
            )
        );

        if($retour !== null)
            return \Redirect::back()->withErrors(['enregistrement_erreur' => $retour]);

        // Redirect vers login
        return redirect('/eden/login');
    }

    public function changer_double_facteur_application(){

        $utilisateur = modele('utilisateur')->find(request()->input('utilisateur_id'));

        if($utilisateur === null)
            return response()->json(['erreur' => traduction('messages.php.utilisateur.inexistant')]);
    
        $google2fa = new Google2FA();

        $secretKey = $google2fa->generateSecretKey();
        $url_codeqr = $google2fa->getQRCodeInline(
            'EDEN',
            $utilisateur->email,
            $secretKey
        );

        $retour = management('utilisateur',$utilisateur->id,$utilisateur)->enregistre_modele([
            'google2fa_secret' => Crypt::encryptString($secretKey),
            'double_facteur_authentification' => 2
        ]);

        $utilisateur = Utilisateur::where('id',moi()->id)->first();
        session()->put('utilisateur_eden',$utilisateur);

        return response()->json(['qrcode' => $url_codeqr,'code_secret' => $secretKey]);
    }
}
