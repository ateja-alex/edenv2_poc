<?php

namespace App\Eden\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {

		// on inclut tous les fichiers .php du répertoire helpers
        foreach (glob(app_path().'/Eden/Helpers/*.php') as $filename) {
			
			require_once($filename);
		}
		
		// on inclut le fichier des helpers pour les managements
		//require_once(storage_path('app/eden_managements.php'));
		
		temps_execution('Après helper provider eden');
    }
}
