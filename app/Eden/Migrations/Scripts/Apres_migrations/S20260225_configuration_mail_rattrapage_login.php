<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20260225_configuration_mail_rattrapage_login implements Script {

    public function execute(){

        if(Schema::hasColumns('configuration_email', ['login', 'adresse_email_par_defaut']))
            DB::update('UPDATE `configuration_email` SET `login` = `adresse_email_par_defaut`');

        return true;
    }
}