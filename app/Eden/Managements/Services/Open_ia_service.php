<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Facades\Http;

class Open_ia_service{

    private $openai_api_key;
    private $openai_url;
    private $informations_modeles = [
        1 => [  
            'contexte' => "
                Tu es dans un contexte d'ERP Eden purement professionnelle. 
                Tu es en train de remplir un textarea WYSIWYG. 
                Ta réponse doit obligatoirement être du code HTML bien structuré, 
                sans aucune explication ni texte hors balises. 
                Utilise uniquement les balises sémantiques nécessaires (h1, h2, p, ul, li, etc.), 
                et le code doit être proprement indenté et facile à lire. 
                N'utilise pas de balises <span> ou autres balises inutiles, 
                n'ajoute pas de balises de style ou de formatage superflues.
                Réponds uniquement avec du HTML brut, sans balises de code ni ```html```
            "
        ],
        2 => [

            'contexte' => "
                Tu es dans un contexte d'ERP Eden purement professionnelle. 
                Tu es en train de remplir un formulaire d'enrichissement de fiche. 
                Tu dois retourner les informations publiques disponibles que tu peux trouver pour chaque champ à enrichir. 
                Si tu ne trouves pas d'informations, tu dois retourner une valeur vide pour ce champ. 
            ",

            'format' => [
                "format" => [
                    "type" => "json_schema",
                    "name" => "enrichissement_element",
                    'schema' => [
                        "type" => "object",
                        "properties" => [
                            "resultats" => [
                                "type" => "array",
                                "items" => [
                                    "type" => "object",
                                    "properties" => [
                                        "champ" => [ "type" => "string" ],
                                        "valeur" => [ "type" => ["string", "null"] ]
                                    ],
                                    "required" => ["champ", "valeur"],
                                    "additionalProperties" => false
                                ]
                            ]
                        ],
                        "required" => ["resultats"],
                        "additionalProperties" => false
                    ]
                ]
            ]
        ]
    ];

    public function __construct()
    {
        $this->openai_api_key = fonctionnalite('open_ai_cle');
        $this->openai_url = 'https://api.openai.com/v1/responses';
    }

    public function requete($parametres,$cle_contexte){
        
        $prompt = $parametres['prompt'] ?? null;

        if (!$prompt) {
            return response()->json(['erreur' => 'Prompt manquant'], 400);
        }

        $options= [
            'model' => 'gpt-4.1',
            "tools" => [["type" => "web_search_preview"]],
        ];

        $informations_modele = $this->informations_modeles[$cle_contexte] ?? null;

        if(!empty($informations_modele['contexte'])) {

            $contexte = $informations_modele['contexte'];

            if(!empty($parametres['complement_contexte']))
                $contexte .= ' ' . $parametres['complement_contexte'];

            $options['instructions'] = $contexte;
        }

        $options['input'] = $prompt;

        if(!empty($informations_modele['format'])) {
            $options['text'] = $informations_modele['format'];
        }

        $reponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openai_api_key,
            'Content-Type'  => 'application/json',
        ])->post($this->openai_url, $options);

        if ($reponse->failed()) {
            dd($reponse->body());
            return null;
        }

        $donnees = $reponse->json();

        $message = array_values(array_filter($donnees['output'] ?? [], function($value) {
            return $value['type'] == 'message';
        }))[0] ?? [];

        return $message['content'][0]['text'] ?? null;
    }
}