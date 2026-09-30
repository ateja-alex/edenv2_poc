<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Eden\Models\Liste_libre;
use App\Eden\Managements\Cache_management;
use App\Eden\Managements\Parametrage\Liste_libre_management;

class S20240702_rattrapage_configuration_email implements Script {

    public function execute(){

        if(Schema::hasColumn('configuration_email', 'nom_utilisateur_par_defaut')){

            DB::update('UPDATE configuration_email SET login = nom_utilisateur_par_defaut');

            //On doit garder la colonne sur reference pour la rétrocompatibilité, étant donné que la config Eden est récupérée depuis ce projet
            if(url('/') . '/' != env('EDEN_MODEL_API_URL'))
                DB::select('ALTER TABLE configuration_email DROP COLUMN nom_utilisateur_par_defaut');
        }

        $adresse_mail_extranet = fonctionnalite('adresse_mail_extranet');
        $nom_mail_extranet = fonctionnalite('nom_mail_extranet');
        $modele_configuration = [

            'protocole' => 2,
            'adresse_host' => fonctionnalite('email_smtp_host'),
            'port' => fonctionnalite('email_stmp_port'),
            'cryptage_utilise' => fonctionnalite('email_stmp_encryption'),
            'mot_de_passe_par_defaut' => fonctionnalite('email_stmp_mot_de_passe'),
            'login' => fonctionnalite('email_stmp_identifiant'),
        ];

        $types_configurations_email = Cache_management::valeurs_liste_formatee(592);

        //On vérifie si des valeurs sont vides, si c'est le cas, on n'exécute pas le reste du script
        foreach($modele_configuration as $attribut){

            if(empty($attribut))
                return true;
        }

        foreach($types_configurations_email as $cle => $type){

            $verification = modele('configuration_email')->where('type', $cle)->where('valeur_par_defaut', 1)->first();

            if(in_array($type['id_valeur'], [0,1]) || !empty($verification))
                continue;

            $configuration = $modele_configuration;
            $configuration = array_merge($configuration, [
                'nom' => $type['valeur'],
                'type' => $type['id_valeur'],
            ]);

            if($type['id_valeur'] == 4 && !empty($adresse_mail_extranet))
                $configuration['login'] = $adresse_mail_extranet;

            if($type['id_valeur'] == 4 && !empty($nom_mail_extranet))
                $configuration['nom_expediteur_par_defaut'] = $nom_mail_extranet;

            management('configuration_email')->enregistre($configuration);
        }

        return true;
    }
}