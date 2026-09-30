<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Managements\Parametrage\Rapport_management;
use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Colonne;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_autresvues;
use App\Eden\Models\Liste_libre_calcul;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Liste_libre_filtre;
use App\Eden\Models\Rapport_libre;

class S20240129_suppression_liste_entreprise implements Script
{

    public function execute()
    {
        try {
            Script_management::supprime_fichier_spe('Migrations/entreprise.php');

            $liste_entreprise = Liste_libre::where('type_element', 'entreprise')->get();

            Script_management::supprime_fichier_spe('Migrations/Listes_libres/entreprise.php');

            foreach ($liste_entreprise as $liste) {

                $rapport = null;

                if (!empty($liste->id_rapport)) {
                    $rapport = Rapport_libre::where('id_rapport', $liste->id_rapport)->first();

                    if (!empty($rapport))
                        Rapport_management::supprimer($rapport, $liste);

                }

                if (empty($liste->id_rapport) || empty($rapport)) {
                    Colonne::where('liste_libre_id', $liste->id)->delete();
                    Liste_libre_filtre::where('liste_libre_id', $liste->id)->delete();
                    Liste_libre_calcul::where('liste_libre_id', $liste->id)->delete();
                    Liste_libre_couleur::where('liste_libre_id', $liste->id)->delete();
                    Liste_libre_autresvues::where('liste_libre_id_1', $liste->id)->delete();

                    $liste->delete();
                }

            }

        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la migration de suppression de la liste entreprise : {$e}");
        }

        return true;
    }
}