<?php

namespace App\Eden\Managements\Services;

class Pdf_service {
    
    public function pdf_utilisable($fichier) {
        $pdf_version = fopen($fichier,"r");

        if($pdf_version) {
            $premiere_ligne = fgets($pdf_version);
            fclose($pdf_version);
        }
        else{
            throw new \App\Eden\Exceptions\Eden_exception("Impossible d'ouvrir le pdf " . $fichier);
        }

        preg_match_all('!\d+!', $premiere_ligne, $matches);
        $pdf_version = floatval(implode('.', $matches[0]));

        if ($pdf_version > 1.4) {
            $ancien_fichier = $fichier;
            $fichier = str_replace('.pdf', '_nouvelle_version.pdf', $fichier);
            
            shell_exec('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile="'.$fichier.'" "'.$ancien_fichier.'"');
        }
        
        return $fichier;
    }

    /**
     *
     * Si le chemin fourni pointe vers une image, génère un pdf temporaire à partir de celle-ci
     * afin de pouvoir l'ajouter au merge ou au zip. Retourne le chemin (relatif à storage/app) à utiliser.
     *
     */
    public function convertit_image_en_pdf_temporaire($chemin_fichier, &$fichiers_temporaires_a_supprimer) {

        if(empty($chemin_fichier))
            return $chemin_fichier;

        $chemin_absolu = storage_path('app/' . $chemin_fichier);

        $extensions_image_vers_mime = [
            'jpg' => 'jpeg',
            'jpeg' => 'jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'bmp' => 'bmp',
            'webp' => 'webp',
        ];

        $extension = strtolower(pathinfo($chemin_absolu, PATHINFO_EXTENSION));

        if(!isset($extensions_image_vers_mime[$extension]) || !is_file($chemin_absolu))
            return $chemin_fichier;

        $donnees_image = base64_encode(file_get_contents($chemin_absolu));

        $pdf = \PDF::loadHTML('<html><body style="margin:0"><img src="data:image/'.$extensions_image_vers_mime[$extension].';base64,'.$donnees_image.'" style="width:100%"></body></html>');

        $chemin_pdf_temporaire = 'tmp/image_vers_pdf_'.uniqid().'.pdf';

        \Storage::put($chemin_pdf_temporaire, $pdf->output());

        $fichiers_temporaires_a_supprimer[] = storage_path('app/' . $chemin_pdf_temporaire);

        return $chemin_pdf_temporaire;
    }
    
}
