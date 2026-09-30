<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Models\Champ_libre;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Formulaire;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\Schema;

class S20250404_suppression_ticket_hotline implements Script
{

    public function execute()
    {
        try {

            if(file_exists(app_path()."/Migrations/ticket_hotline.php"))
                unlink(app_path()."/Migrations/ticket_hotline.php");
            
            $champs_libre = Champ_libre::where('type_element', 'ticket_hotline')->get();
            
            foreach ($champs_libre as $champ) {
                $champ->delete();
            };

            if(file_exists(app_path()."/Migrations/Listes_libres/ticket_hotline.php"))
                unlink(app_path()."/Migrations/Listes_libres/ticket_hotline.php");
            
            $listes = Liste_libre::where('type_element', 'ticket_hotline')->get();
            
            foreach ($listes as $liste) {
                
                if(!empty($liste->id_rapport) && file_exists(app_path()."/Migrations/Listes_libres/{$liste->id_rapport}.php"))
                    unlink(app_path()."/Migrations/Listes_libres/{$liste->id_rapport}.php");
                
                $liste->delete();
            };
            
            $formulaires = Formulaire::where('type_element', 'ticket_hotline')->get();
            
            foreach ($formulaires as $formulaire) {

                if(file_exists(app_path()."/Migrations/Formulaires_libres/{$formulaire->nom_formulaire}.php"))
                    unlink(app_path()."/Migrations/Formulaires_libres/{$formulaire->nom_formulaire}.php");
                
                $formulaire->delete();
            };
            
            $rapports = Rapport_libre::where('type_element', 'ticket_hotline')->get();
            
            foreach ($rapports as $rapport) {

                if(!empty($rapport->id_rapport) && file_exists(app_path()."/Migrations/Rapports/{$rapport->id_rapport}.php"))
                    unlink(app_path()."/Migrations/Rapports/{$rapport->id_rapport}.php");
                
                $rapport->delete();
            };
            
            if(Schema::hasTable('ticket_hotline')) {
                $ticket_hotline = modele('ticket_hotline')->get();
                if($ticket_hotline->isEmpty()) {
                    Schema::drop('ticket_hotline');

                    $table = Table_libre::where('type_element', 'ticket_hotline')->first();

                    $table->delete();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la suppression de la table ticket_hotline : {$e}");
        }

        return true;
    }
}
