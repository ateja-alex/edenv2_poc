<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Table_libre;

use Mail;


class Log_tentative_connexion_management extends Element_management {

    /**
     *
     * Contrôles supplémentaires
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications) {

        $this->verification_alerte_mail_bot($modele);

        // Si la tentative de connexion est un succès, on supprime les tentatives ratées sur cette adresse IP
        if(!empty($modele->succes)) {

            // On fait un modele->delete, pour des raisons de vie privée, le but étant de supprimer les IP de la base
            modele('log_tentative_connexion')
                ->where('adresse_email', $modele->adresse_email)
                ->where('ip', $modele->ip)
                ->where('succes', 0)
                ->delete();
        }

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    /**
     *
     * On lance une alerte mail si une IP essaie de se connecter + de 25 fois à l'ERP sans succès de suite, toutes adresse
     * mails confondus, probablement un robot ou autre attaque. Permet de bloquer l'IP sur AWS manuellement
     *
     */
    public function verification_alerte_mail_bot($modele){

        $adresse_ip = $modele->ip;

        // On va compter le nombre de tentatives de connexions en echec d'affilé de cette adresse I
        $date_derniere_connexion_ok = "2000-01-01 00:00:00";

        $derniere_tentative_connexion_ok = modele('log_tentative_connexion')
            ->where('ip', $adresse_ip)
            ->where('succes', 1)
            ->orderBy('id', 'desc')
            ->first();

        if($derniere_tentative_connexion_ok !== null)
            $date_derniere_connexion_ok = $derniere_tentative_connexion_ok->date;

        $nombre_tentatives_fail = modele('log_tentative_connexion')
            ->where('ip',$adresse_ip)
            ->where('succes',0)
            ->where('date', '>' ,$date_derniere_connexion_ok)
            ->count();

        // On ne lance pas d'alerte si le seuil des 25 tentatives n'a pas été atteint
        if($nombre_tentatives_fail != 25)
            return;

        $lien_projet_actuel = str_replace('index.php','eden/accueil','https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
        $contenu_mail = "Bonjour, l'adresse IP $adresse_ip à tenté de se connecter $nombre_tentatives_fail fois sans succès. 
Cette activitée est suspecte et potentiellement représentative d'un BOT. 
Le projet concerné est le suivant : <a href='$lien_projet_actuel'>$lien_projet_actuel</a>";

        // on prépare les données pour envoyer le mail
        $service_email = service('email');

        $parametres_email = [
            'type_configuration' => 1,
            'destinataire' => [fonctionnalite('adresse_mail_a_prevenir_robot')],
            'sujet' => 'Potentiel robot détecté',
        ];

        $variables_email = [
            'contenu_email' => $contenu_mail,
            'titre' => 'Alerte : un utilisateur a eu de nombreuses tentatives de connexion en échec'
        ];

        $retour = $service_email->envoyer('eden::mails.template_standard', $variables_email, $parametres_email);
    }
}