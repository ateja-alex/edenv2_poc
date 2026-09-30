<?php

namespace App\Eden\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Numero_securite_sociale implements ValidationRule {

    /**
     * 
     * Vérifie la validité du numéro de sécurité sociale
     * 
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {

        if(strlen($value) !== 15)
            $fail('');

        $nombre = substr($value, 0, 13);
        $cle_ss = (int) substr($value, 13, 2);
        
        // Cas particulier : pour la Corse, le code département (6ème et 7ème chiffre) peut être
        // 2A (Corse-du-Sud) ou 2B (Haute-Corse), il faut les remplacer respectivement par 19 et 18
        $nombre = (int) str_replace(['2A', '2B', ' '], ['19', '18', ''], $nombre);

        $reste = $nombre % 97;
        $cle_calculee = 97 - $reste;

        if ($cle_calculee !== $cle_ss)
            $fail('');
    }
}