<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\Storage;

class S20221206_suppression_composants_storage implements Script {

    public function execute() {

        Storage::deleteDirectory('public/composants');

        return true;
    }
}