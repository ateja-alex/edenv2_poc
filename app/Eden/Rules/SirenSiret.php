<?php

namespace App\Eden\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class SirenSiret implements ValidationRule {

    protected string $type = ''; // valeur par défaut

    /**
     * Permet de définir le type : 'siren' ou 'siret'
     */
    public function setType(string $type) {

        $this->type = $type;

        return $this;
    }

    /**
     * Vérifie la validité du SIREN ou SIRET
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        
        if(strpos($value, ' ') !== false)
            $value = str_replace(' ', '', $value);

        if($this->type === 'siren')
            $resultat = $this->verification_validite_siren($value);
        else
            $resultat = $this->verification_validite_siret($value);

        if($resultat !== true)
            $fail($resultat);
    }

    public function verification_validite_siren($siren){

        $siren_array = str_split($siren);

        // On vérifie si il y a 9 nombres pour commencer
        if (sizeof($siren_array) != 9)
            return '';

        // La taille est bonne, on vérifie la validité maintenant
        return $this->verification_algo_luhn($siren_array);
    }

    public function verification_validite_siret($siret) {

        $siret_array = str_split($siret);

        // On vérifie si il y a 14 nombres pour commencer
        if (sizeof($siret_array) != 14)
            return '';

        // La taille est bonne, on vérifie la validité maintenant
        return $this->verification_algo_luhn($siret_array);
    }

    public function verification_algo_luhn($nombre_array){

        $nombre_array = array_reverse($nombre_array);
        $somme = 0;
        $pair = false;

        foreach ($nombre_array as $nombre) {

            // On multiplie par 2 car pair
            if ($pair) {

                $pair = false;
                $nombre_multiplie = $nombre * 2;
            }

            // On multiplie par 1 car impair
            else {

                $pair = true;
                $nombre_multiplie = $nombre * 1;
            }

            // On vérifie si résultat sur 2 caractère
            if ($nombre_multiplie >= 10) {

                $nombre_multiplie_array = str_split($nombre_multiplie);
                $somme = $somme + 1 + $nombre_multiplie_array[1];
            }
            else
                $somme = $somme + $nombre_multiplie;
        }

        // On vérifie que $somme est multiple de 10
        if (($somme % 10) != 0)
            return '';

        return true;
    }
}