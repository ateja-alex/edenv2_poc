<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Rapport_libre;

class S20241028_categorie_rapport implements Script
{
    public function execute()
    {
        $categories = [
            'favoris' => 'Rapports favoris',
            'gestion_commerciale' => 'Gestion commerciale',
            'gestion_projet' => 'Gestion de projet',
            'crm' => 'CRM',
            'rh' => 'RH',
            'activite_operationnelle' => 'Activité opérationnelle',
            'animation_reseau' => 'Animation réseau',
            'process_achat' => 'Process achat',
            'production_livraison' => 'Production / Livraison',
            'data' => 'Analyse data',
            'gestion' => 'Gestion',
            'compta' => 'Comptabilité',
            'administration' => 'Administration',
            'extranet' => 'Extranet',
        ];

        foreach ($categories as $index => $categorie) {
            management('categorie_rapport')->enregistre(['nom' => $categorie, 'index' => $index]);
        }

        return true;
    }
}
