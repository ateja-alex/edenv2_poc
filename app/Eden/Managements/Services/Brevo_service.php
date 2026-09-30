<?php

namespace App\Eden\Managements\Services;

use App\Eden\Models\Parametre;
use Illuminate\Support\Facades\Http;

class Brevo_service extends Synchronisation_service_service {

    protected $tables_externes = [
        [
            "id" => "list",
            "nom" => "Liste",
            "disponibilites" => [
                ["type_synchronisation" => 0, "reference_url" => "https://developers.brevo.com/reference/create-list", "types_evenements" => [1,2,3]],
                ["type_synchronisation" => 2, "reference_url" => "https://developers.brevo.com/reference/get-lists", "limite_par_appel" => 50]
            ],
            "champs" => [
                [
                    "name" => "name",
                    "type" => "string", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "folderId",
                    "type" => "float", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1,"obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "listId",
                    "type" => "float", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 2,"obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 3,"obligatoire" => true, "sens" => 0],
                    ]
                ],
                [
                    "name" => "id",
                    "type" => "float", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 1],
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
            ]
        ],
        [
            "id" => "contact",
            "nom" => "Contact",
            "disponibilites" => [
                ["type_synchronisation" => 0, "reference_url" => "https://developers.brevo.com/reference/create-contact", "types_evenements" => [1,2,3]],
            ],
            "champs" => [
                [
                    "name" => "identifier",
                    "type" => ["float","string"], 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 2,"obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 3,"obligatoire" => true, "sens" => 0],
                    ]
                ],
                [
                    "name" => "email",
                    "type" => "string", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                    ]
                ],
                [
                    "name" => "emailBlacklisted",
                    "enumeration" => [['label' => 'Yes','value' => true],['label' => 'No','value' => false]],
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                    ]
                ],
                [
                    "name" => "attributes",
                    "type" => "array", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                    ]
                ],
                [
                    "name" => "id",
                    "type" => "float", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 1],
                    ]
                ],
            ]
        ],
        [
            "id" => "contact_list",
            "nom" => "Contact - Liste",
            "disponibilites" => [
                ["type_synchronisation" => 0, "reference_url" => "https://developers.brevo.com/reference/add-contact-to-list", "types_evenements" => [1,3]],
            ],
            "champs" => [
                [
                    "name" => "listId",
                    "type" => "float", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 3, "obligatoire" => true, "sens" => 0],
                    ]
                ],
                [
                    "name" => "ids",
                    "type" => "float", 
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 3, "obligatoire" => true, "sens" => 0],
                    ]
                ],
            ]
        ],
        [
            "id" => "marketing_email",
            "nom" => "Marketing email",
            "disponibilites" => [
                ["type_synchronisation" => 1, "reference_url" => "https://developers.brevo.com/docs/transactional-webhooks"],
            ],
            "champs" => [
                [
                    "name" => "event",
                    "enumeration" => [['label' => 'Opened','value' => "opened"],
                        ['label' => 'Soft Bounced','value' => "soft_bounced"],
                        ['label' => 'Hard Bounced','value' => "hard_bounce"],
                        ['label' => 'Click','value' => "click"],
                        ['label' => 'Delivered','value' => "delivered"],
                        ['label' => 'Spam','value' => "spam"],
                        ['label' => 'Unsubscribe','value' => "unsubscribe"]],
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "id",
                    "type" => "integer", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "email",
                    "type" => "string", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "date_sent",
                    "type" => "datetime", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "date_event",
                    "type" => "datetime", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "camp_id",
                    "type" => "integer", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "campaign name",
                    "type" => "string", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
                [
                    "name" => "URL",
                    "type" => "string", 
                    "disponibilites" => [
                        ["type_synchronisation" => 1, "sens" => 1],
                    ]
                ],
            ]
        ],
    ];

    public function attributes_contact(){

        $parametre = Parametre::where('nom', 'attributes_contact_brevo')->first();

        if(!empty($parametre) && date('Y-m-d',strtotime($parametre->date_creation)) == date('Y-m-d'))
            $attributes_contact = json_decode($parametre->valeur, true);
        else{
            $reponse = $this->requete('contacts/attributes', [], 'GET');

            if ($reponse->failed())
                $attributes_contact =  [];
            else{
                $attributes_contact = $reponse->json()['attributes'] ?? [];

                parametre('attributes_contact_brevo',json_encode($attributes_contact));
            }
        }

        $attributes_contact[] = [
            'name' => 'EMAIL', 
            'type' => 'string', 
            'disponibilites' => [
                ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0]
            ]
        ];

        return $attributes_contact;
    }

    private $api_url = "https://api.brevo.com/v3";
    private $api_key;

    public function defini_parametres($parametres){
        $this->api_key = $parametres['cle_api'] ?? null;
    }

    protected function requete($url, $donnees = [], $methode = 'GET'){

        $http = Http::withHeaders([
            'api-key' => $this->api_key,
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ]);

        return $http->{$methode}($this->api_url.'/'.$url, $donnees);
    }

    public function creation_list($modifications){

        $modifications['folderId'] = intval($modifications['folderId']);

        $reponse = $this->requete('contacts/lists', $modifications, 'POST');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function modification_list($modifications){

        $listId = $modifications['listId'] ?? null;
        unset($modifications['listId']);

        $reponse = $this->requete('contacts/lists/'.$listId, $modifications, 'PUT');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function suppression_list($modifications){

        $reponse = $this->requete('contacts/lists/'.($modifications['listId'] ?? null), [], 'DELETE');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function creation_contact($modifications){

        $reponse = $this->requete('contacts', $modifications, 'POST');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        //todo : si erreur de contact déjà existant, récupérer le contact_brevo 

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function modification_contact($modifications){

        $identifier = $modifications['identifier'] ?? null;
        unset($modifications['identifier']);

        $reponse = $this->requete('contacts/'.$identifier, $modifications, 'PUT');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function suppression_contact($modifications){

        $identifier = $modifications['identifier'] ?? null;
        unset($modifications['identifier']);

        $reponse = $this->requete('contacts/'.$identifier, [], 'DELETE');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function creation_contact_list($modifications){

        $list_id = $modifications['listId'] ?? null;
        $ids = $modifications['ids'];

        if(!is_array($ids))
            $ids = array($ids);

        $ids = array_map('intval', $ids);

        $reponse = $this->requete('contacts/lists/'.$list_id.'/contacts/add', ['ids' => $ids], 'POST');

        if ($reponse->failed()){
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];
        }

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function suppression_contact_list($modifications){

        $list_id = $modifications['listId'] ?? null;
        $ids = $modifications['ids'];

        if(!is_array($ids))
            $ids = array($ids);

        $ids = array_map('intval', $ids);

        $reponse = $this->requete('contacts/lists/'.$list_id.'/contacts/remove', ['ids' => $ids], 'POST');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse
        ];
    }

    public function recuperation_list($parametres){

        $reponse = $this->requete('contacts/lists', [
            'limit' => $parametres['limite_par_page'],
            'offset' => $parametres['nombre_elements_recuperes']
        ]);

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees_reponse = $reponse->json();

        return [
            'succes' => true,
            'donnees' => $donnees_reponse['lists'] ?? []
        ];
    }
}

