<?php

namespace App\Console\Commands;

use App\Eden\Controllers\Cron_controller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;

/**
 * Lance une tâche cron EDEN (méthode de Cron_controller) en ligne de commande.
 * Remplace l'appel HTTP des routes /eden/cron/* : un CronJob Kubernetes par tâche.
 *   php artisan eden:cron envoyer_emails
 *   php artisan eden:cron maj_chaines_tags_recherche client,contact
 *
 * Le verrou (table cron : en_cours) est posé par Cron_controller et libéré ici dans tous
 * les cas (fin normale, exception, dd/exit), ce que le destructeur ne fait pas sur exit().
 */
class EdenCron extends Command
{
    protected $signature = 'eden:cron {nom : Nom de la tâche (méthode de Cron_controller)} {parametres?* : Paramètres de la route, dans l\'ordre}';

    protected $description = 'Lance une tâche cron EDEN';

    public function handle(): int
    {
        $nom = $this->argument('nom');

        if(!$this->est_une_tache($nom)) {
            $this->error('Tâche cron inconnue : '.$nom);
            return self::FAILURE;
        }

        // Beaucoup de tâches se terminent par dd()/exit() (exit(1)) : sans erreur fatale,
        // c'est une fin normale → code 0 pour que le CronJob ne la compte pas en échec.
        // Sur exit(), le destructeur de Cron_controller n'est pas appelé : on libère le verrou ici.
        $termine = false;
        register_shutdown_function(function() use (&$termine) {
            if($termine)
                return;

            if(defined('execution_deja_en_cours')) {
                $this->warn('Tâche déjà en cours : rien n\'est lancé.');
                exit(0);
            }

            $this->liberer_verrou();

            $erreur = error_get_last();
            if($erreur && in_array($erreur['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true))
                exit(1);

            exit(0);
        });

        $debut = microtime(true);

        try {
            // Le constructeur pose le verrou ; si la tâche est déjà en cours, il sort (exit)
            $controleur = new Cron_controller($nom);

            $retour = $controleur->$nom(...$this->argument('parametres'));

            if(is_string($retour))
                $this->line($retour);
            elseif(is_object($retour) && method_exists($retour, 'getContent'))
                $this->line((string) $retour->getContent());
        }
        catch(\Throwable $erreur) {
            $termine = true;
            $this->liberer_verrou();
            $this->error($nom.' : '.($erreur->getMessage() ?: class_basename($erreur)).' ('.$erreur->getFile().':'.$erreur->getLine().')');
            report($erreur);
            return self::FAILURE;
        }

        $termine = true;
        $this->liberer_verrou();

        $this->info(sprintf('%s terminée en %.1f s.', $nom, microtime(true) - $debut));

        return self::SUCCESS;
    }

    /**
     * Libère le verrou posé par Cron_controller, seulement s'il a été pris par cette exécution
     * (id_cron défini par le constructeur, et pas de exécution déjà en cours)
     */
    private function liberer_verrou(): void
    {
        if(!defined('id_cron') || defined('execution_deja_en_cours'))
            return;

        DB::table('cron')->where('id', id_cron)->update(['en_cours' => 0, 'derniere_execution' => date('Y-m-d H:i:s')]);
    }

    /**
     * Méthode publique propre à Cron_controller (pas un constructeur ni une méthode héritée)
     */
    private function est_une_tache(string $nom): bool
    {
        if(!method_exists(Cron_controller::class, $nom) || str_starts_with($nom, '__'))
            return false;

        $methode = new ReflectionMethod(Cron_controller::class, $nom);

        return $methode->isPublic() && $methode->getDeclaringClass()->getName() === Cron_controller::class;
    }
}
