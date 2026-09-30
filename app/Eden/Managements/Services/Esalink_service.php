<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class Esalink_service extends Synchronisation_service_service {

    protected $tables_externes = [
        [
            "id" => "routing_code",
            "nom" => "Code de routage",
            "disponibilites" => [
                ["type_synchronisation" => 3],
            ],
            "champs" => [
                [
                    "name" => "siret",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "obligatoire" => true, "sens" => 0],
                    ]
                ],
                [
                    "name" => "routingIdentifierType",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "routingCodeName",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "routingIdentifier",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "managesLegalCommitmentCode",
                    "type" => "boolean",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "administrativeStatus",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "address",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ]
            ]
        ],
        [
            "id" => "directory_line",
            "nom" => "Annuaire de facturation",
            "disponibilites" => [
                ["type_synchronisation" => 3],
            ],
            "champs" => [
                [
                    "name" => "siren",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 0],
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "siret",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 0],
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "addressingIdentifier",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "addressingSuffix",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "businessName",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "address",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ],
                [
                    "name" => "directoryLineStatus",
                    "enumeration" => [['label' => 'Enabled','value' => 'Enabled'],['label' => 'Disabled','value' => 'Disabled'],['label' => 'Upcoming','value' => 'Upcoming']],
                    "disponibilites" => [
                        ["type_synchronisation" => 3, "sens" => 1],
                    ]
                ]
            ]
        ],
        [
            "id" => "flow",
            "nom" => "Flow (envoi facture)",
            "disponibilites" => [
                ["type_synchronisation" => 0, "reference_url" => "#api_url#/flows", "types_evenements" => [1, 2]],
                ["type_synchronisation" => 2, "reference_url" => "#api_url#/flows/search", "limite_par_appel" => 100, "types_evenements" => [2]],
            ],
            "champs" => [
                [
                    "name" => "trackingId",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "name",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                    ]
                ],
                [
                    "name" => "flowSyntax",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                    ]
                ],
                [
                    "name" => "processingRule",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                    ]
                ],
                [
                    "name" => "file",
                    "type" => "file",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "obligatoire" => true, "sens" => 0],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "obligatoire" => true, "sens" => 0],
                    ]
                ],
                [
                    "name" => "dateRetry",
                    "type" => "date",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 0],
                    ]
                ],
                [
                    "name" => "flowId",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 0, "type_evenement" => 1, "sens" => 1],
                        ["type_synchronisation" => 0, "type_evenement" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "statut",
                    "enumeration" => [
                        ["label" => "Déposée", "value" => "200"],
                        ["label" => "Émise par la plateforme", "value" => "201"],
                        ["label" => "Reçue par la plateforme", "value" => "202"],
                        ["label" => "Mise à disposition", "value" => "203"],
                        ["label" => "Prise en charge", "value" => "204"],
                        ["label" => "Approuvée", "value" => "205"],
                        ["label" => "Approuvée partiellement", "value" => "206"],
                        ["label" => "En litige", "value" => "207"],
                        ["label" => "Suspendue", "value" => "208"],
                        ["label" => "Complétée", "value" => "209"],
                        ["label" => "Refusée", "value" => "210"],
                        ["label" => "Paiement transmis", "value" => "211"],
                        ["label" => "Encaissée", "value" => "212"],
                        ["label" => "Rejetée", "value" => "213"],
                    ],
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "motif",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "motif_code",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "submittedAt",
                    "type" => "date",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
            ]
        ],
        [
            "id" => "flow_erreur_technique",
            "nom" => "Flow (détection erreur technique)",
            "disponibilites" => [
                ["type_synchronisation" => 2, "reference_url" => "#api_url#/flows/{flowId}", "limite_par_appel" => 100, "types_evenements" => [2]],
            ],
            "champs" => [
                [
                    "name" => "flowId",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 0],
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "motif",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
            ]
        ],
        [
            "id" => "flow_achat",
            "nom" => "Flow achat (réception facture)",
            "disponibilites" => [
                ["type_synchronisation" => 2, "reference_url" => "#api_url#/flows/search", "limite_par_appel" => 100, "types_evenements" => [1, 2]],
            ],
            "champs" => [
                [
                    "name" => "flowId",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "trackingId",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "flowType",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "sirenEmetteur",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "nomEmetteur",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "sirenDestinataire",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "referenceDocument",
                    "type" => "string",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "dateDocument",
                    "type" => "date",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "typeCodeFacturx",
                    "enumeration" => [
                        ["label" => "380 - Facture commerciale", "value" => "380"],
                        ["label" => "381 - Avoir", "value" => "381"],
                        ["label" => "384 - Facture rectificative", "value" => "384"],
                        ["label" => "386 - Facture d'acompte", "value" => "386"],
                        ["label" => "389 - Facture auto-facturée", "value" => "389"],
                        ["label" => "261 - Avoir auto-facturé", "value" => "261"],
                        ["label" => "262 - Avoir pour remise globale", "value" => "262"],
                        ["label" => "393 - Facture affacturée", "value" => "393"],
                        ["label" => "396 - Avoir affacturé", "value" => "396"],
                    ],
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "montantHt",
                    "type" => "float",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "montantTva",
                    "type" => "float",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "montantTtc",
                    "type" => "float",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "pdf",
                    "type" => "file",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "submittedAt",
                    "type" => "date",
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
                [
                    "name" => "statut",
                    "enumeration" => [
                        ["label" => "Déposée", "value" => "200"],
                        ["label" => "Émise par la plateforme", "value" => "201"],
                        ["label" => "Reçue par la plateforme", "value" => "202"],
                        ["label" => "Mise à disposition", "value" => "203"],
                        ["label" => "Prise en charge", "value" => "204"],
                        ["label" => "Approuvée", "value" => "205"],
                        ["label" => "Approuvée partiellement", "value" => "206"],
                        ["label" => "En litige", "value" => "207"],
                        ["label" => "Suspendue", "value" => "208"],
                        ["label" => "Complétée", "value" => "209"],
                        ["label" => "Refusée", "value" => "210"],
                        ["label" => "Paiement transmis", "value" => "211"],
                        ["label" => "Encaissée", "value" => "212"],
                        ["label" => "Rejetée", "value" => "213"],
                    ],
                    "disponibilites" => [
                        ["type_synchronisation" => 2, "sens" => 1],
                    ]
                ],
            ]
        ],
    ];

    private $api_url;
    private $api_url_prod = "https://hubtimize.fr/api/orchestrator/v1";
    private $api_url_preprod = "https://ppd.hubtimize.fr/api/orchestrator/v1";
    private $parametres = [];

    public function __construct($nom = null){

        $this->api_url = strtolower(env('APP_ENV')) == 'prod' ? $this->api_url_prod : $this->api_url_preprod;
    }

    public function defini_parametres($parametres){
        $this->parametres = $parametres;
    }

    public function tables_externes(){

        $tables_externes = $this->tables_externes;

        foreach($tables_externes as $index_table => $table_externe) {

            foreach($table_externe['disponibilites'] as $index_disponibilite => $disponibilite) {

                if(empty($disponibilite['reference_url']))
                    continue;

                $tables_externes[$index_table]['disponibilites'][$index_disponibilite]['reference_url'] = str_replace('#api_url#', $this->api_url, $disponibilite['reference_url']);
            }
        }

        return $tables_externes;
    }

    private function cle_api(){

        return $this->parametres['cle_api'] ?? '';
    }

    public function recuperation_token(){

        $http = Http::asForm()->withHeaders([
            'hubtimize-api-key' => $this->cle_api(),
        ]);

        $token = parametre('access_token_esalink') ?? null;
        $date_expiration = parametre('date_expiration_token_esalink') ?? null;

        if(!empty($token) && $date_expiration > date('Y-m-d H:i:s'))
            return $token;

        $refresh_token = parametre('refresh_token_esalink') ?? null;

        $parametres = [
            'client_id' => $this->parametres['client_id'],
            'client_secret' => $this->parametres['client_secret'],
        ];

        $route = $this->api_url.'/oauth2/token';

        if(!empty($refresh_token)){
            $parametres_refresh = $parametres;
            $parametres_refresh['grant_type'] = 'refresh_token';
            $parametres_refresh['refresh_token'] = $refresh_token;

            $retour = $http->POST($route,$parametres_refresh);

            if(isset($retour['access_token'])){
                parametre('access_token_esalink', $retour['access_token']);
                parametre('refresh_token_esalink', $retour['refresh_token']);
                $date_expiration = date('Y-m-d H:i:s', time() + $retour['expires_in']);
                parametre('date_expiration_token_esalink', $date_expiration);
                return $retour['access_token'];
            }
        }


        $parametres['grant_type'] = 'client_credentials';

        try{
            $retour = $http->POST($route,$parametres);
        }catch(\Exception|\Throwable $e){
            throw new \Exception("Erreur lors de la récupération du token : ".$e->getMessage());
        }

        if($retour->failed())
            throw new \Exception("Erreur lors de la récupération du token : ".$retour->body());

        $retour = $retour->json();

        parametre('access_token_esalink', $retour['access_token']);
        parametre('refresh_token_esalink', $retour['refresh_token']);
        $date_expiration = date('Y-m-d H:i:s', time() + $retour['expires_in']);
        parametre('date_expiration_token_esalink', $date_expiration);

        return $retour['access_token'];
    }

    protected function requete($url, $donnees = [], $methode = 'GET'){

        $http = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->recuperation_token(),
            'Accept' => 'application/json',
            'hubtimize-api-key' => $this->cle_api(),
        ]);

        return $http->{$methode}($this->api_url.'/'.$url, $donnees);
    }

    public function lecture_routing_code($modifications){

        $siret = $modifications['siret'] ?? null;

        $filters = [];

        $body = [
            'filters' => [
                "siret" => [
                    "op" => "strict",
                    "value" => $siret
                ]
            ],
            'sorting' => [
                ['field' => 'siret', 'order' => 'ascending'],
                ['field' => 'routingIdentifier', 'order' => 'ascending'],
            ],
            'fields' => [
                'siret',
                'routingIdentifierType',
                'routingCodeName',
                'routingIdentifier',
                'managesLegalCommitmentCode',
                'administrativeStatus',
                'address',
            ]
        ];

        $reponse = $this->requete('routing-code/search', $body, 'POST');

        if ($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['code'] ?? null,
                'message' => $reponse->json()['message'] ?? null,
                'retry' => empty($reponse->json()['code'])
            ];

        $donnees = array_map(function($item){

            $adresse = $item['address'] ?? [];

            $item['address'] = implode(', ', array_filter([
                $adresse['addressLine1'] ?? null,
                $adresse['addressLine2'] ?? null,
                $adresse['addressLine3'] ?? null,
                $adresse['postalCode'] ?? null,
                $adresse['locality'] ?? null,
                $adresse['countryCode'] ?? null,
            ]));

            return $item;

        }, $reponse->json()['results']);

        return [
            'succes' => true,
            'donnees' => $donnees
        ];
    }

    public function lecture_directory_line($modifications){

        $siret = $modifications['siret'] ?? null;
        $siren = $modifications['siren'] ?? null;

        $filters = [];

        if(!empty($siret))
            $filters['siret'] = ['op' => 'strict', 'value' => $siret];

        if(!empty($siren))
            $filters['siren'] = ['op' => 'strict', 'value' => $siren];

        $body = [
            'filters' => $filters,
            'sorting' => [
                ['field' => 'siret', 'order' => 'ascending'],
                ['field' => 'addressingIdentifier', 'order' => 'ascending'],
            ],
            'fields' => [
                "addressingIdentifier",
                "siren",
                "siret",
                "addressingSuffix"
            ],
            "include" => [
                "siren",
                "siret"
            ],
            'limit' => 100
        ];

        $donnees = [];

        do {

            $body['ignore'] = is_array($donnees) ? sizeof($donnees) : 0;

            $reponse = $this->requete('directory-line/search', $body, 'POST');

            if ($reponse->failed())
                return [
                    'succes' => false,
                    'code' => $reponse->json()['code'] ?? null,
                    'message' => $reponse->json()['message'] ?? null,
                    'retry' => empty($reponse->json()['code'])
                ];

            $donnees_boucle = array_map(function($item){

                $adresse = $item['facility']['address'] ?? [];

                $item['address'] = implode(', ', array_filter([
                    $adresse['addressLine1'] ?? null,
                    $adresse['addressLine2'] ?? null,
                    $adresse['addressLine3'] ?? null,
                    $adresse['postalCode'] ?? null,
                    $adresse['locality'] ?? null,
                    $adresse['countryCode'] ?? null,
                ]));

                $item['businessName'] = $item['legalUnit']['businessName'] ?? null;

                return $item;

            }, $reponse->json()['results']);

            $donnees = array_merge($donnees,$donnees_boucle);
            
        } while(sizeof($donnees_boucle) == 100);

        return [
            'succes' => true,
            'donnees' => $donnees
        ];
    }

    public function creation_flow($modifications){

        $chemin_fichier = $modifications['file'] ?? null;

        unset($modifications['file']);
        unset($modifications['dateRetry']);

        $http = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->recuperation_token(),
            'Accept' => 'application/json',
            'hubtimize-api-key' => $this->cle_api(),
        ]);

        if(!empty($chemin_fichier))
            $http = $http->attach('file', file_get_contents($chemin_fichier), basename($chemin_fichier));

        $reponse = $http->post($this->api_url.'/flows', ['flowInfo' => json_encode($modifications)]);

        if($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['errorCode'] ?? null,
                'message' => $reponse->json()['errorMessage'] ?? null,
                'retry' => $reponse->status() >= 500
            ];

        return [
            'succes' => true,
            'donnees' => $reponse->json()
        ];
    }

    public function modification_flow($modifications){

        return $this->creation_flow($modifications);
    }

    public function recuperation_flow($parametres){

        $donnees = [];

        $reponse = $this->requete('flows/search', [
            'limit' => $parametres['limite_par_page'],
            'where' => [
                'updatedAfter' => Carbon::parse($parametres['derniere_synchronisation'])->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'),
                'flowType' => ['CustomerInvoiceLC', 'SupplierInvoiceLC', 'StateCustomerInvoiceLC', 'StateSupplierInvoiceLC'],
            ]
        ], 'POST');

        if($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['errorCode'] ?? null,
                'message' => $reponse->json()['errorMessage'] ?? null,
                'retry' => $reponse->status() >= 500
            ];

        $resultats = $reponse->json()['results'] ?? [];

        foreach($resultats as $flow_lifecycle){

            $telechargement = $this->requete('flows/'.$flow_lifecycle['flowId'], ['docType' => 'Original'], 'GET');
            if($telechargement->failed())
                return [
                    'succes' => false,
                    'code' => $telechargement->json()['errorCode'] ?? null,
                    'message' => $telechargement->json()['errorMessage'] ?? null,
                    'retry' => $telechargement->status() >= 500
                ];

            $dom = new \DOMDocument();
            $dom->loadXML($telechargement->body());
            $xpath = new \DOMXPath($dom);

            $noeud_statut = $xpath->query("//*[local-name()='ReferenceReferencedDocument']/*[local-name()='ProcessConditionCode']")->item(0);
            $noeud_motif = $xpath->query("//*[local-name()='SpecifiedDocumentStatus']/*[local-name()='Reason']")->item(0);
            $noeud_motif_code = $xpath->query("//*[local-name()='SpecifiedDocumentStatus']/*[local-name()='ReasonCode']")->item(0);

            // la date de soumission/mise à jour du flow (submittedAt/updatedAt) est celle de l'enregistrement
            // de la notification chez Esalink, pas celle de l'événement de cycle de vie qu'elle décrit : les
            // notifications peuvent être relayées dans le désordre. La vraie chronologie métier est portée par
            // l'IssueDateTime du document CDAR lui-même, c'est elle qu'on utilise pour trier.
            $noeud_date_evenement = $xpath->query("//*[local-name()='ExchangedDocument']/*[local-name()='IssueDateTime']//*[local-name()='DateTimeString']")->item(0);

            $flow_lifecycle['date_evenement'] = $noeud_date_evenement
                ? Carbon::createFromFormat('YmdHis', $noeud_date_evenement->textContent)->format('Y-m-d H:i:s')
                : Carbon::parse(rtrim($flow_lifecycle['submittedAt'], 'Z'))->format('Y-m-d H:i:s');

            // le trackingId d'un flow CDAR est le flowId de la facture d'origine (cf changelog Esalink v1.2.4)
            $flow_lifecycle['statut'] = $noeud_statut ? $noeud_statut->textContent : null;
            $flow_lifecycle['motif'] = $noeud_motif ? $noeud_motif->textContent : null;
            $flow_lifecycle['motif_code'] = $noeud_motif_code ? $noeud_motif_code->textContent : null;
            $flow_lifecycle['submittedAt'] = Carbon::parse(rtrim($flow_lifecycle['submittedAt'], 'Z'))->format('Y-m-d H:i:s');

            $donnees[] = $flow_lifecycle;
        }

        usort($donnees, function($a, $b){
            return ($a['statut'] < $b['statut']) ? -1 : 1;
        });

        $retour = [
            'succes' => true,
            'donnees' => $donnees
        ];

        if(count($resultats) >= $parametres['limite_par_page'])
            $retour['derniere_synchronisation'] = Carbon::parse(rtrim(collect($resultats)->max('updatedAt'), 'Z'))->format('Y-m-d H:i:s.u');

        return $retour;
    }

    /**
     *
     * Récupère les factures d'achat électroniques réellement reçues (le document lui-même, pas
     * un statut de cycle de vie) : flowType SANS le suffixe "LC" (confirmé en réel sur CustomerInvoice/
     * CustomerInvoiceLC pour l'émission ; SupplierInvoice par symétrie côté réception, à vérifier
     * dès la première facture fournisseur réelle reçue).
     *
     */
    public function recuperation_flow_achat($parametres){

        $reponse = $this->requete('flows/search', [
            'limit' => $parametres['limite_par_page'],
            'where' => [
                'updatedAfter' => Carbon::parse($parametres['derniere_synchronisation'])->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'),
                'flowType' => ['SupplierInvoice'],
            ]
        ], 'POST');

        if($reponse->failed())
            return [
                'succes' => false,
                'code' => $reponse->json()['errorCode'] ?? null,
                'message' => $reponse->json()['errorMessage'] ?? null,
                'retry' => $reponse->status() >= 500
            ];

        $resultats = $reponse->json()['results'] ?? [];

        $donnees = [];

        foreach($resultats as $flow){

            $telechargement = $this->requete('flows/'.$flow['flowId'], ['docType' => 'Original'], 'GET');

            if($telechargement->failed())
                return [
                    'succes' => false,
                    'code' => $telechargement->json()['errorCode'] ?? null,
                    'message' => $telechargement->json()['errorMessage'] ?? null,
                    'retry' => $telechargement->status() >= 500
                ];

            $flow = array_merge($flow, $this->donnees_document($telechargement->body()));
            $flow['pdf'] = $telechargement->body();

            $flow['submittedAt'] = Carbon::parse(rtrim($flow['submittedAt'], 'Z'))->format('Y-m-d H:i:s');

            $flow['statut'] = null;

            $reponse_statuts = $this->requete('flows/search', [
                'limit' => 100,
                'where' => [
                    'updatedAfter' => Carbon::parse($flow['submittedAt'])->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z'),
                    'trackingId' => $flow['flowId'],
                    'flowType' => ['SupplierInvoiceLC', 'StateSupplierInvoiceLC'],
                ]
            ], 'POST');

            $flows_lifecycle = $reponse_statuts->successful() ? ($reponse_statuts->json()['results'] ?? []) : [];

            $dernier_flow_lifecycle = collect($flows_lifecycle)->sortByDesc('updatedAt')->first();

            if(!empty($dernier_flow_lifecycle)){

                $telechargement_statut = $this->requete('flows/'.$dernier_flow_lifecycle['flowId'], ['docType' => 'Original'], 'GET');

                if($telechargement_statut->successful()){

                    $dom_statut = new \DOMDocument();
                    $dom_statut->loadXML($telechargement_statut->body());
                    $xpath_statut = new \DOMXPath($dom_statut);

                    $noeud_statut = $xpath_statut->query("//*[local-name()='ReferenceReferencedDocument']/*[local-name()='ProcessConditionCode']")->item(0);

                    $flow['statut'] = $noeud_statut ? $noeud_statut->textContent : null;
                }
            }

            $donnees[] = $flow;
        }

        usort($donnees, function($a, $b){
            return strcmp($a['submittedAt'], $b['submittedAt']);
        });

        $retour = [
            'succes' => true,
            'donnees' => $donnees
        ];

        if(count($resultats) >= $parametres['limite_par_page'])
            $retour['derniere_synchronisation'] = Carbon::parse(rtrim(collect($resultats)->max('updatedAt'), 'Z'))->format('Y-m-d H:i:s.u');

        return $retour;
    }

    public function recuperation_flow_erreur_technique($parametres){

        $flowsId = $parametres['flowId'] ?? [];

        $page = array_slice($flowsId, $parametres['nombre_elements_recuperes'], $parametres['limite_par_page']);

        $donnees = [];

        foreach($page as $flowId){

            $verification = $this->requete('flows/'.$flowId, [], 'GET');

            $en_erreur = $verification->failed() || ($verification->json()['acknowledgement']['status'] ?? null) == 'Error';

            if(!$en_erreur)
                continue;

            $motif = collect($verification->json()['acknowledgement']['details'] ?? [])
                ->pluck('reasonMessage')
                ->filter()
                ->implode(' / ');

            if(empty($motif))
                $motif = $verification->json()['errorMessage'] ?? null;

            $donnees[] = [
                'flowId' => $flowId,
                'motif' => $motif,
            ];
        }

        return [
            'succes' => true,
            'donnees' => $donnees,
            'nombre_traites' => count($page)
        ];
    }

    private function donnees_document($contenu){

        $donnees = [
            'sirenEmetteur' => null,
            'nomEmetteur' => null,
            'sirenDestinataire' => null,
            'referenceDocument' => null,
            'dateDocument' => null,
            'typeCodeFacturx' => null,
            'montantHt' => null,
            'montantTva' => null,
            'montantTtc' => null,
        ];

        if(str_starts_with($contenu, '%PDF')){
            try {
                $contenu = (new \Atgp\FacturX\Reader())->extractXML($contenu, false);
            }
            catch(\Atgp\FacturX\Exceptions\ExceptionInterface $e){
                return $donnees;
            }
        }

        $dom = new \DOMDocument();

        if(empty($contenu) || !@$dom->loadXML($contenu))
            return $donnees;

        $xpath = new \DOMXPath($dom);

        $valeur = fn($requete) => $xpath->query($requete)->item(0)?->textContent;

        $racine = $dom->documentElement->localName;

        if($racine == 'StandardBusinessDocument'){

            $interne = $xpath->query("/*/*[local-name()='Invoice' or local-name()='CreditNote' or local-name()='CrossIndustryInvoice']")->item(0);

            if(empty($interne))
                return $donnees;

            $extrait = new \DOMDocument();
            $extrait->appendChild($extrait->importNode($interne, true));

            return $this->donnees_document($extrait->saveXML());
        }

        if(in_array($racine, ['Invoice', 'CreditNote'])){

            $date = $valeur("/*/*[local-name()='IssueDate']");
            $partie = fn($role, $champ) => $valeur("/*/*[local-name()='$role']/*[local-name()='Party']/*[local-name()='PartyLegalEntity']/*[local-name()='$champ']");

            return [
                'sirenEmetteur' => $partie('AccountingSupplierParty', 'CompanyID'),
                'nomEmetteur' => $partie('AccountingSupplierParty', 'RegistrationName'),
                'sirenDestinataire' => $partie('AccountingCustomerParty', 'CompanyID'),
                'referenceDocument' => $valeur("/*/*[local-name()='ID']"),
                'dateDocument' => $date ? Carbon::parse($date)->format('Y-m-d') : null,
                'typeCodeFacturx' => $valeur("/*/*[local-name()='".($racine == 'CreditNote' ? 'CreditNoteTypeCode' : 'InvoiceTypeCode')."']"),
                'montantHt' => $valeur("/*/*[local-name()='LegalMonetaryTotal']/*[local-name()='TaxExclusiveAmount']"),
                'montantTva' => $valeur("/*/*[local-name()='TaxTotal']/*[local-name()='TaxAmount']"),
                'montantTtc' => $valeur("/*/*[local-name()='LegalMonetaryTotal']/*[local-name()='TaxInclusiveAmount']"),
            ];
        }

        $date = $valeur("//*[local-name()='ExchangedDocument']/*[local-name()='IssueDateTime']//*[local-name()='DateTimeString']");

        return [
            'sirenEmetteur' => $valeur("//*[local-name()='SellerTradeParty']/*[local-name()='SpecifiedLegalOrganization']/*[local-name()='ID']"),
            'nomEmetteur' => $valeur("//*[local-name()='SellerTradeParty']/*[local-name()='Name']"),
            'sirenDestinataire' => $valeur("//*[local-name()='BuyerTradeParty']/*[local-name()='SpecifiedLegalOrganization']/*[local-name()='ID']"),
            'referenceDocument' => $valeur("//*[local-name()='ExchangedDocument']/*[local-name()='ID']"),
            'dateDocument' => $date ? Carbon::createFromFormat('Ymd', $date)->format('Y-m-d') : null,
            'typeCodeFacturx' => $valeur("//*[local-name()='ExchangedDocument']/*[local-name()='TypeCode']"),
            'montantHt' => $valeur("//*[local-name()='SpecifiedTradeSettlementHeaderMonetarySummation']/*[local-name()='TaxBasisTotalAmount']"),
            'montantTva' => $valeur("//*[local-name()='SpecifiedTradeSettlementHeaderMonetarySummation']/*[local-name()='TaxTotalAmount']"),
            'montantTtc' => $valeur("//*[local-name()='SpecifiedTradeSettlementHeaderMonetarySummation']/*[local-name()='GrandTotalAmount']"),
        ];
    }
}
