<?php

namespace App\Eden\Console\Commands;

use App\Eden\Managements\Services\Image_compresseur_service;
use Illuminate\Console\Command;

/**
 *
 * Compresse en shell (sans timeout HTTP) les images du disque public dépassant la
 * taille maximale paramétrée. Réutilise la même logique que le cron HTTP
 * (Cron_controller::compresser_images) via Image_compresseur_service.
 *
 * Idempotent : les fichiers déjà sous la limite sont ignorés, l'exécution est relançable.
 *
 */
class Compresser_images_command extends Command
{
    protected $signature = 'eden:compresser-images
                            {--max= : Taille maximale cible en Mo (défaut : fonctionnalité compression_images_taille_maximale_en_mo, ou 5)}
                            {--dry-run : Lister les images dépassant la limite sans rien modifier}';

    protected $description = 'Compresse les images du disque public dépassant la taille maximale paramétrée (backfill shell, sans timeout HTTP).';

    public function handle()
    {
        $max_mo = $this->option('max');

        if($max_mo === null || $max_mo === '')
            $max_mo = config('fonctionnalites.compression_images_taille_maximale_en_mo', 5);

        $max_mo = (float) $max_mo;

        if($max_mo <= 0){
            $this->error('La taille maximale doit être supérieure à 0 Mo.');
            return self::FAILURE;
        }

        $max_octets = $max_mo * 1000000;

        $this->info('Taille maximale cible : '.rtrim(rtrim(number_format($max_mo, 2, '.', ''), '0'), '.').' Mo');

        // Mode simulation : on liste sans rien modifier (utile pour estimer le volume avant un gros backfill)
        if($this->option('dry-run')){

            $candidats    = Image_compresseur_service::fichiers_candidats($max_octets);
            $total_octets = array_sum(array_column($candidats, 'taille'));

            $this->info(count($candidats).' image(s) dépassent la limite ('.$this->format_octets($total_octets).' au total).');

            foreach(array_slice($candidats, 0, 20) as $candidat)
                $this->line('  - '.$candidat['chemin'].' ('.$this->format_octets($candidat['taille']).')');

            if(count($candidats) > 20)
                $this->line('  ... et '.(count($candidats) - 20).' autre(s).');

            return self::SUCCESS;
        }

        $barre = null;

        $recap = Image_compresseur_service::compresser_stockage(
            $max_octets,
            function($total) use (&$barre){
                $this->info($total.' image(s) à compresser.');
                $barre = $this->output->createProgressBar($total);
                $barre->start();
            },
            function($chemin, $reussi, $erreur) use (&$barre){
                if($barre)
                    $barre->advance();
            }
        );

        if($barre){
            $barre->finish();
            $this->newLine(2);
        }

        $this->info('Terminé : '.count($recap['succes']).' compressée(s), '
            .count($recap['erreur']).' en erreur / cible non atteinte sur '
            .$recap['candidats'].' candidate(s).');

        foreach($recap['erreur'] as $erreur)
            $this->warn('  '.$erreur);

        return self::SUCCESS;
    }

    /**
     *
     * Formate une taille en octets (o / Ko / Mo) — convention décimale, comme Fiche_management.
     *
     */
    private function format_octets($octets){

        if($octets >= 1000000)
            return round($octets / 1000000, 2).' Mo';

        if($octets >= 1000)
            return round($octets / 1000).' Ko';

        return $octets.' o';
    }
}
