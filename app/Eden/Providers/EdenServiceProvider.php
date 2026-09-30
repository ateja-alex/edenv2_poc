<?php

namespace App\Eden\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\Events\JobProcessing;

use Symfony\Component\Finder\Finder;
use App\Eden\Models\Table_libre;
use App\Eden\Managements\Cache_management;

use App\Providers\RouteServiceProvider as ServiceProvider;

class EdenServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
		parent::boot();

		temps_execution('debut provider eden');

		Event::listen(JobProcessing::class, function() {

			Cache_management::partage_oublie_memo();

			Cache_management::verifie_si_cache_obsolete();
		});

        $this->ajouter_commandes();
		
		Blade::directive('champ', function($variables) {

			// On récupère les variables
			eval("\$les_variables = [$variables];");
			
			// les valeurs par défaut pour la taille du libellé et du champ
			if(count($les_variables) == 2) {
				
				$les_variables[] = 2; 
				$les_variables[] = 4; 
			}
			
			list($type_element, $nom_champ, $taille_libelle, $taille_champ) = $les_variables;

   			return '<div class="col-sm-'.$taille_libelle.'">
		{!! management(\''.$type_element.'\')->champ(\''.$nom_champ.'\')->nom_vue() !!}
		</div>
		<div class="col-sm-'.$taille_champ.'">{!! management(\''.$type_element.'\')->champ(\''.$nom_champ.'\')->cree() !!}</div>';
		});

        Blade::directive('traduction', function($variables) {

            // On récupère les variables
            eval("\$les_variables = [$variables];");

			// les valeurs par défaut pour la taille du libellé et du champ
			if(count($les_variables) == 1 ) {
				$les_variables[] = null;
				$les_variables[] = false;
				$les_variables[] = [];
			}
            else if(count($les_variables) == 2) {
				$les_variables[] = false;
                $les_variables[] = [];
			}
            else if(count($les_variables) == 3) {
                $les_variables[] = [];
			}

			list($index_traduction, $champ, $index_vue_js, $parametres) = $les_variables;

            $parametres_composant = '';
            $vhtml = '$root.traduction(';

            if($index_vue_js === true) {
                $parametres_composant .= ':index_traduction="' . $index_traduction . '" ';
                $vhtml .= $index_traduction;
            }
            else {
                $parametres_composant .= 'index_traduction="' . $index_traduction . '" ';
                $vhtml .= '\''.$index_traduction.'\'';
            }

            if($champ !== null) {
                $parametres_composant .= 'champ="' . $champ . '" ';
                $vhtml .= ',\''.$champ.'\'';
            }
            else{
                $vhtml .= ',null';
            }

            if(!empty($parametres)) {
                $parametres_composant .= ':parametres="' . str_replace('"','\`',json_encode($parametres)).'"';
                $vhtml .= ','.str_replace('"','`',json_encode($parametres)).'';
            }

            $vhtml .= ')';

            return' <span v-if="$root.mode_traduction" >
                    <traduction-element '.$parametres_composant.'></traduction-element>
                </span>
                <span v-else v-html="'.$vhtml.'" ></span>';
		});

		temps_execution('après création directive blade provider eden');
	
    }

	 /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {

        parent::register();

		// la config eden
		foreach(Finder::create()->in(__DIR__.'/../Config')->depth('== 0')->name('*.php') as $file) {

			$this->mergeConfigFrom($file->getRealPath(), basename($file->getRealPath(), '.php'));
		}

		// on ajoute les vues eden
		$this->loadViewsFrom(__DIR__.'/../Views', 'eden');
		$this->loadViewsFrom(__DIR__.'/../Views', 'eden_std');
	}

    public function ajouter_commandes() {


        // Commandes Artisan du core Eden
		$this->commands([
			\App\Eden\Console\Commands\Compresser_images_command::class,
		]);
    }



    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map() {

        $this->mapApiRoutes();

        $this->mapExtranetRoutes();

        $this->mapParametrageRoutes();

        $this->mapEcommerceRoutes();

        $this->mapCronRoutes();

        $this->mapMaintenanceRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes() {
		
        Route::middleware('web')->namespace($this->namespace)->group(base_path('app/Eden/routes/web.php'));

        parent::mapWebRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes() {
		
		Route::namespace($this->namespace)->group(base_path('app/Eden/routes/api.php'));
		
		parent::mapApiRoutes();
    }

    /**
     * On définit les routes "extranet"
     *
     * @return void
     */
    protected function mapExtranetRoutes() {

		Route::middleware('web')->namespace($this->namespace)->group(base_path('app/Eden/routes/extranet.php'));
    }

    /**
     * On définit les routes "paramétrage"
     *
     * @return void
     */
    protected function mapParametrageRoutes() {

		Route::middleware('web')->namespace($this->namespace)->group(base_path('app/Eden/routes/parametrage.php'));
    }

    /**
     * On définit les routes "ecommerce"
     *
     * @return void
     */
    protected function mapEcommerceRoutes() {

		Route::middleware('web')->namespace($this->namespace)->group(base_path('app/Eden/routes/ecommerce.php'));
    }

    /**
     * On définit les routes "cron"
     *
     * @return void
     */
    protected function mapCronRoutes() {

		Route::middleware('web')->namespace($this->namespace)->group(base_path('app/Eden/routes/cron.php'));
    }

    /**
     * On définit les routes "maintenance"
     *
     * @return void
     */
    protected function mapMaintenanceRoutes() {

		Route::middleware('web')->namespace($this->namespace)->group(base_path('app/Eden/routes/maintenance.php'));
    }

}
