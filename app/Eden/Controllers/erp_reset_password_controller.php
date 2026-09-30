<?php

namespace App\Eden\Controllers;

use Illuminate\Auth\Passwords\CanResetPassword;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Hash;

class erp_reset_password_controller extends Controller
{

    use ResetsPasswords;

    protected $champ_mot_de_passe = "pwd";

    protected function resetPassword($user, $password) {

        $user->{$this->champ_mot_de_passe} = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->pwd_derniere_modif = date('Y-m-d H:i:s');

        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    /**
     * Réinitialise le mot de passe
     * 
     * @return view vue Laravel en fonction des données recueillie
     */
    public function reset(Request $requete) {

        $erreurs = [];

        $management = management('utilisateur');

        // On va chercher l'utilisateur correspondant au token reçu si il existe
        $management->modele = modele('utilisateur')->where('reset_password_token', $requete->token)->first();

        // On vérifie que les données sont correctes 
        $erreurs = $this->verifier_donnees($requete->all(), $management->champ('email')->valeur);

        // On vérifie qu'il n'y a pas de problème
        if($erreurs === null) {

            $modifications = array_merge($requete->except(['token', '_token']), ['reset_password_token' => null]);

            $retour = $management->enregistre($modifications);

            $requete->remember = '';

            $login_controller = new LoginController;

            return $login_controller->login($requete);
        }

        return view('auth.passwords.reset', ['token' => $requete->token, 'errors' => collect($erreurs) ]);
    }

    /**
     * Vérifie que les données reçues sont correctes
     * 
     * @param array     $donnees    données reçues depuis le formulaire de réinitialisation
     * 
     * @param string    $email      email de l'utilisateur
     * 
     * @return mixed null si pas d'erreur, un tableau les regroupants dans le cas contraire
     */
    public function verifier_donnees($donnees, $email) {

        $erreurs = [];

        if($donnees['email'] != $email)
            $erreurs['email'] = 'l\'adresse email ne correspond pas au compte pour lequel la réinitialisation a été demandé ou le lien de réinitialisation n\'est plus valide. ';

        if($donnees['password'] != $donnees['password_verification'])
            $erreurs['password_verification'] = 'le mot de passe et la confirmation ne sont pas identiques';

        return count($erreurs) == 0 ? null : $erreurs;
    }
}
