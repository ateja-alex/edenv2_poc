<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;

class S20240529_suppression_migrations_article_categorie_comptable implements Script {

    public function execute() {

        if(file_exists(app_path('Migrations/article_categorie_comptable.php')))
            unlink(app_path('Migrations/article_categorie_comptable.php'));

        if(file_exists(app_path('Migrations/Formulaires_libres/article_categorie_comptable.php')))
            unlink(app_path('Migrations/Formulaires_libres/article_categorie_comptable.php'));

        return true;
    }
}