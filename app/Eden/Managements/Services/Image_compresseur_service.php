<?php

namespace App\Eden\Managements\Services;

/**
 *
 * Compression des images du stockage via GD natif.
 *
 * Logique mutualisée entre le cron HTTP (Cron_controller::compresser_images)
 * et la commande Artisan (eden:compresser-images) utilisée pour le backfill en shell.
 *
 */
class Image_compresseur_service {

    /**
     *
     * Extensions d'images prises en charge.
     *
     */
    public static function extensions_supportees(){

        return ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    }

    /**
     *
     * Retourne la liste des images du disque public dépassant $max_octets.
     * Chaque entrée : ['chemin' => <chemin relatif au disque public>, 'taille' => <octets>].
     *
     */
    public static function fichiers_candidats($max_octets){

        $extensions = self::extensions_supportees();
        $candidats  = [];

        foreach(\Storage::disk('public')->allFiles() as $fichier){

            if(!in_array(strtolower(pathinfo($fichier, PATHINFO_EXTENSION)), $extensions))
                continue;

            $chemin_absolu = storage_path('app/public/'.$fichier);

            clearstatcache(true, $chemin_absolu);

            if(!is_file($chemin_absolu))
                continue;

            $taille = filesize($chemin_absolu);

            // On ignore les fichiers déjà sous la limite (idempotence : pas de re-compression)
            if($taille <= $max_octets)
                continue;

            $candidats[] = ['chemin' => $fichier, 'taille' => $taille];
        }

        return $candidats;
    }

    /**
     *
     * Parcourt le disque public et compresse les images au-dessus de la cible.
     * $onStart($total)               : appelé une fois avec le nombre de fichiers à traiter.
     * $onFichier($chemin, $reussi, $erreur) : appelé après le traitement de chaque fichier.
     * Retourne ['candidats' => N, 'succes' => [...], 'erreur' => [...]].
     *
     */
    public static function compresser_stockage($max_octets, ?callable $onStart = null, ?callable $onFichier = null){

        $candidats = self::fichiers_candidats($max_octets);

        if($onStart)
            $onStart(count($candidats));

        $recap = ['candidats' => count($candidats), 'succes' => [], 'erreur' => []];

        foreach($candidats as $candidat){

            $chemin    = $candidat['chemin'];
            $extension = strtolower(pathinfo($chemin, PATHINFO_EXTENSION));
            $reussi    = false;
            $erreur    = null;

            try {

                $reussi = self::compresser_fichier(storage_path('app/public/'.$chemin), $extension, $max_octets);

                if($reussi)
                    $recap['succes'][] = $chemin;
                else
                    $recap['erreur'][] = $chemin.' (cible non atteinte)';

            } catch (\Exception $e) {

                $erreur = $e->getMessage();
                $recap['erreur'][] = $chemin.' : '.$erreur;
            }

            if($onFichier)
                $onFichier($chemin, $reussi, $erreur);
        }

        return $recap;
    }

    /**
     *
     * Compresse une image via GD pour la ramener sous $max_octets, en écrasant le fichier d'origine.
     * Baisse d'abord la qualité (JPEG/WebP) puis réduit les dimensions si nécessaire.
     * Conserve le format/extension d'origine (pour ne pas casser les références stockées en base).
     * Retourne true si le fichier final est sous la cible, false sinon.
     *
     */
    public static function compresser_fichier($chemin_absolu, $extension, $max_octets){

        // Création de la ressource GD selon le format
        switch($extension){
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($chemin_absolu);
                break;
            case 'png':
                $image = @imagecreatefrompng($chemin_absolu);
                break;
            case 'gif':
                $image = @imagecreatefromgif($chemin_absolu);
                break;
            case 'webp':
                $image = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($chemin_absolu) : false;
                break;
            default:
                $image = false;
        }

        if($image === false)
            throw new \Exception('Format non supporté ou fichier illisible');

        $transparence = ($extension === 'png' || $extension === 'webp');

        if($transparence){
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $largeur_origine = imagesx($image);
        $hauteur_origine = imagesy($image);

        $reussi        = false;
        $reduction     = 1.0;   // facteur de redimensionnement (1.0 = dimensions d'origine)
        $reduction_min = 0.3;   // borne basse : on ne descend pas sous 30% des dimensions

        do {

            // Redimensionnement (toujours à partir de l'original pour éviter une perte cumulée)
            if($reduction < 1.0){

                $nouvelle_largeur = max(1, (int) round($largeur_origine * $reduction));
                $nouvelle_hauteur = max(1, (int) round($hauteur_origine * $reduction));

                $ressource = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);

                if($transparence){
                    imagealphablending($ressource, false);
                    imagesavealpha($ressource, true);
                    $transparent = imagecolorallocatealpha($ressource, 0, 0, 0, 127);
                    imagefilledrectangle($ressource, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $transparent);
                }

                imagecopyresampled($ressource, $image, 0, 0, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $largeur_origine, $hauteur_origine);
            }
            else {
                $ressource = $image;
            }

            // Encodage en conservant le format/extension d'origine
            if($extension === 'png'){
                imagepng($ressource, $chemin_absolu, 9);
            }
            elseif($extension === 'gif'){
                imagegif($ressource, $chemin_absolu);
            }
            else {
                // JPEG / WebP : on baisse la qualité jusqu'à passer sous la cible
                $qualite = 85;
                do {
                    if($extension === 'webp')
                        imagewebp($ressource, $chemin_absolu, $qualite);
                    else
                        imagejpeg($ressource, $chemin_absolu, $qualite);

                    clearstatcache(true, $chemin_absolu);
                    $qualite -= 5;
                } while(filesize($chemin_absolu) > $max_octets && $qualite >= 50);
            }

            if($ressource !== $image)
                imagedestroy($ressource);

            clearstatcache(true, $chemin_absolu);

            if(filesize($chemin_absolu) <= $max_octets){
                $reussi = true;
                break;
            }

            // Toujours trop lourd : on réduit les dimensions et on recommence
            $reduction -= 0.1;

        } while($reduction >= $reduction_min);

        imagedestroy($image);

        return $reussi;
    }
}
