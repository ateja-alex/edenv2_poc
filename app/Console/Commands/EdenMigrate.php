<?php

namespace App\Console\Commands;

use App\Eden\Managements\Maintenance_management;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Met à jour la structure de la base et le paramétrage généré.
 * Lancée à chaque déploiement, sur une base existante (une nouvelle base part d'un dump),
 * une seule fois par un Job Kubernetes (ou le service migrate du compose), jamais par pod.
 */
class EdenMigrate extends Command
{
    protected $signature = 'eden:migrate {--wait=600 : Secondes d\'attente si une autre migration est en cours}';

    protected $description = 'Applique les migrations EDEN (structure, paramétrage, crons, licences)';

    public function handle(): int
    {
        // Verrou MariaDB plutôt que Cache::lock : chaque étape fait un Cache::flush()
        // qui effacerait le verrou. Libéré automatiquement si le processus est tué.
        $verrou = substr('eden_migrate:'.DB::getDatabaseName(), 0, 64);

        if((int) DB::scalar('SELECT GET_LOCK(?, ?)', [$verrou, (int) $this->option('wait')]) !== 1) {
            $this->error('Une autre migration est en cours ('.$verrou.').');
            return self::FAILURE;
        }

        $debut = microtime(true);

        try {
            Maintenance_management::lancer_migrations(function(string $etape, bool $ignoree = false) {
                if($ignoree)
                    $this->warn('→ '.$etape.' ignorée : EDEN_MODEL_API_URL non configurée');
                else
                    $this->info('→ '.$etape);
            });
        }
        catch(\Throwable $erreur) {
            $this->error(($erreur->getMessage() ?: class_basename($erreur)).' ('.$erreur->getFile().':'.$erreur->getLine().')');
            return self::FAILURE;
        }
        finally {
            DB::scalar('SELECT RELEASE_LOCK(?)', [$verrou]);
        }

        $this->info(sprintf('Migrations terminées en %.1f s.', microtime(true) - $debut));

        return self::SUCCESS;
    }
}
