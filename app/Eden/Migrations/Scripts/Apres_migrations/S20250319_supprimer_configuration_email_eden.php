<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Log;

class S20250319_supprimer_configuration_email_eden implements Script {

    public function execute() {

        if(file_exists(storage_path('app/eden_configuration_email.txt')))
            unlink(storage_path('app/eden_configuration_email.txt'));

        return true;
    }
}

