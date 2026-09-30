<?php

namespace App\Eden\Managements\Services;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Models\Element_piece_jointe;
use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\FoldersApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Client\Auth\OAuth;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientEmailNotification;
use DocuSign\eSign\Api\EnvelopesApi\UpdateRecipientsOptions;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class Docusign_service
{

    private $api_client;
    private $account_id;

    private $enveloppe_api;

    /**
     * Docusign_service constructor.
     */
    public function __construct()
    {
        $config = new Configuration();
        $this->api_client = new ApiClient($config);

        $lien_docusign = strtolower(env('APP_ENV')) == 'prod' ? 'account.docusign.com' : 'account-d.docusign.com';
        $host = strtolower(env('APP_ENV')) == 'prod' ? 'https://eu.docusign.net/restapi' : 'https://demo.docusign.net/restapi';

        $this->api_client->getOAuth()->setOAuthBasePath($lien_docusign);
        $this->api_client->getConfig()->setHost($host);

        $scope = [
            ApiClient::$SCOPE_SIGNATURE,
            ApiClient::$SCOPE_IMPERSONATION
        ];

        $access_token = parametre('docusign_jwt_access_token');

        if($access_token == null || parametre('docusign_jwt_access_token_expiration') < time()){

            $client_id = config('fonctionnalites_integrations.docusign_client_id');
            $rsa_private_key = config('fonctionnalites_integrations.docusign_rsa_private_key');
            $user_id = config('fonctionnalites_integrations.docusign_utilisateur_id');

            $jwt_token = $this->api_client->requestJWTUserToken($client_id,$user_id, $rsa_private_key, $scope)[0];

            $user_info = $this->api_client->getUserInfo($jwt_token['access_token']);

            $this->account_id = $user_info[0]['accounts'][0]['account_id'];
            $access_token = $jwt_token['access_token'];

            parametre('docusign_jwt_access_token',$access_token);
            parametre('docusign_jwt_access_token_expiration',time()+600);
            parametre('docusign_account_id',$this->account_id);

        }

        $this->api_client->getConfig()->setAccessToken($access_token);

        $this->account_id = parametre('docusign_account_id');

    }

    /**
     * @param $parametres
     * @return EnvelopeDefinition
     *
     * Permet de créer une enveloppe
     *
     */
    public function creer_une_enveloppe($parametres){

        $creation_enveloppe = new EnvelopeDefinition([
            'email_subject' => $parametres['message_signature']
        ]);

        $documents = array();

        foreach($parametres['documents'] as $index => $document){

            $document_base_64 = base64_encode(file_get_contents(storage_path($document['chemin'])));

            $extension = pathinfo(storage_path($document['chemin']), PATHINFO_EXTENSION);

            $documents[] = new Document([
                'document_base64' => $document_base_64,
                'name' => $document['nom'],
                'file_extension' => $extension,
                'document_id' => $index
            ]);
        }

        $creation_enveloppe->setDocuments($documents);

        $signataires = array();

        foreach($parametres['signataires'] as $index => $signataire){

            $email_notification = new RecipientEmailNotification([
                'email_body' => "Bonjour,\n Nous venons de vous envoyer un document pour une signature électronique.\n Pour voir et signer le document, merci de cliquer sur le bouton ci dessus.\nCordialement,\n\n".maquette('nom_application'),
                'email_subject' => maquette('nom_application'). " : Demande de signature ",
                'supported_language' => "fr",
            ]);

            $signataire_docusign = new Signer([
                'email' => $signataire['adresse_email'],
                'name' => !empty($signataire['nom']) ? $signataire['nom'] : $signataire['adresse_email'],
                'recipient_id' => !empty($signataire['id']) ? $signataire['id'] : $index,
                'email_notification' => $email_notification,
            ]);

            if(!empty($signataire['ordre']))
                $signataire_docusign['routing_order'] = $signataire['ordre'];

            $signataires[] = $signataire_docusign;

        }

        $copies_cachees = array();

        if(isset($parametres['cc'])) {
            foreach ($parametres['cc'] as $index => $copie_cachee) {

                $copies_cachees[] = new CarbonCopy([
                    'email' => $copie_cachee['adresse_email'],
                    'name' => $copie_cachee['nom'],
                    'recipient_id' => $copie_cachee['id'],
                    'routing_order' => $copie_cachee['ordre']
                ]);
            }
        }

        $placements_signatures = array();

        if(!empty($parametres['placements_signatures'])) {

            foreach ($parametres['placements_signatures'] as $placement_signature) {

                $nom_classe = 'DocuSign\\eSign\\Model\\'.ucfirst(Str::camel(str_replace('_tabs','',$placement_signature['type'])));

                $placements_signatures[$placement_signature['signataire_id']][$placement_signature['type']][] = new $nom_classe([
                    'anchor_string' => $placement_signature['indicateur'],
                ]);
            }

            foreach ($signataires as $signataire) {

                $signataire->setTabs(new Tabs($placements_signatures[$signataire['recipient_id']]));
            }
        }

        $recipients = new Recipients([
            'signers' => $signataires, 'carbon_copies' => $copies_cachees
        ]);

        $creation_enveloppe->setRecipients($recipients);

        $creation_enveloppe->setStatus($parametres['statut']);

        return $creation_enveloppe;
    }

    /**
     * @param $parametres
     * @return bool|ApiException|\Exception
     *
     * Permet d'envoyer une enveloppe
     *
     */
    public function envoyer_enveloppe($parametres)
    {
        $creation_enveloppe = $this->creer_une_enveloppe($parametres);

        $enveloppe_api = new EnvelopesApi($this->api_client);

        try {
            $resultat = $enveloppe_api->createEnvelope($this->account_id,$creation_enveloppe);
        } catch (ApiException $e) {
            return array('retour' => false,'erreur' =>$e);
        }

        return array('retour' => true,'envelope_id' =>$resultat['envelope_id']);
    }

    /**
     * @throws ApiException
     *
     * Permet de récupérer les enveloppes et de les sauvegarder en bdd
     *
     */
    public function recuperer_enveloppes()
    {
        $enveloppe_api = new FoldersApi($this->api_client);
        $resultat = $enveloppe_api->search($this->account_id, 'all');

        $enveloppes_existantes = modele('docusign_enveloppe')
            ->whereNotNull('docusign_enveloppe_id')
            ->get()->keyBy('docusign_enveloppe_id');

        $signataires = modele('docusign_signataire')
            ->get()->keyBy('id');

        $correspondances_statuts = $this->correspondances_statuts();

        foreach($resultat['folder_items'] as $enveloppe){

            $enveloppe_id = $enveloppe['envelope_id'];

            if(!isset($enveloppes_existantes[$enveloppe_id]))
                continue;

            $this->enveloppe_api = new EnvelopesApi($this->api_client);

            $docusign_enveloppe = $enveloppes_existantes[$enveloppe_id];

            if(!isset($correspondances_statuts[$enveloppe['status']]))
                continue;

            $statut = $correspondances_statuts[$enveloppe['status']];

            if($docusign_enveloppe->statut != $statut || $statut == 7){

                $signers = $this->enveloppe_api->listRecipients($this->account_id, $enveloppe_id)->getSigners();

                foreach($signers as $signer){

                    $id = $signer->getRecipientId();

                    if(!isset($signataires[$id]))
                        continue;

                    $docusign_signataire = $signataires[$id];

                    if(!isset($correspondances_statuts[$signer->getStatus()]))
                        continue;

                    $statut_signataire = $correspondances_statuts[$signer->getStatus()];

                    if($docusign_signataire->statut == $statut_signataire)
                        continue;

                    management('docusign_signataire',$docusign_signataire->id,$docusign_signataire)->enregistre(array(
                        'statut' => $statut_signataire
                    ));
                }
            }

            if($docusign_enveloppe->statut == $statut)
                continue;

            $management_enveloppe = management('docusign_enveloppe',$docusign_enveloppe->id,$docusign_enveloppe);

            $modifications = ['statut' => $statut];

            if($enveloppe['status'] == 'completed') {

                $this->documents_signes($management_enveloppe);

                $modifications['date_de_signature'] = date('Y-m-d H:i:s',strtotime($enveloppe['completed_date_time']));

            }

            $management_enveloppe->enregistre($modifications);
        }
    }

    public function relancer_enveloppe($docusign_enveloppe_id){

        $enveloppe_api = new EnvelopesApi($this->api_client);

        $options = new UpdateRecipientsOptions();

        $options->setResendEnvelope('true');

        try {
            $recipients = $enveloppe_api->listRecipients($this->account_id, $docusign_enveloppe_id);
            $retour = $enveloppe_api->updateRecipients($this->account_id, $docusign_enveloppe_id, $recipients, $options);
        } catch (ApiException $e) {
            return $e;
        }

        return true;
    }

    /**
     * @throws ApiException
     *
     * Permet de récupérer lees documents signés d'une enveloppe
     *
     */
    public function documents_signes($management_enveloppe)
    {
        $enveloppe_api = $this->enveloppe_api;

        $docusign_enveloppe = $management_enveloppe->modele;

        $documents = $enveloppe_api->listDocuments($this->account_id, $docusign_enveloppe->docusign_enveloppe_id)['envelope_documents'];

        $enveloppe_id = $docusign_enveloppe->docusign_enveloppe_id;

        $docusign_documents = modele('docusign_document')
            ->get()->keyBy('id');

        if(!is_dir(storage_path("app/docusign")))
            mkdir(storage_path("app/docusign"));

        if(!is_dir(storage_path("app/docusign/certificat_de_signature")))
            mkdir(storage_path("app/docusign/certificat_de_signature"));

        $certificat = $enveloppe_api->getDocument($this->account_id,  'certificate', $enveloppe_id);

        $chemin = storage_path("app//docusign/certificat_de_signature/enveloppe_".$docusign_enveloppe->id.'.pdf');

        file_put_contents($chemin, file_get_contents($certificat->getPathname()));

        foreach($documents as $document){

            $id = $document->getDocumentId();

            if(!isset($docusign_documents[$id]))
                continue;

            $docusign_document = $docusign_documents[$id];

            $fichier = $enveloppe_api->getDocument($this->account_id,  $id, $enveloppe_id);

            $management = management('docusign_document',$docusign_document,$docusign_document->id);

            $management->docusign_enveloppe = $management_enveloppe;

            management('docusign_document',$docusign_document,$docusign_document->id)->enregistrer_fichier_signe($fichier);
        }
    }

    /**
     *
     * Fonction qui contient les correspondances des statuts EDEN et des statuts DOCUSIGN
     *
     */
    public function correspondances_statuts(){

        return array(
            'correct' => 1,
            'completed' => 2,
            'created' => 3,
            'declined' => 4,
            'deleted' => 5,
            'delivered' => 6,
            'sent' => 7,
            'signed' => 8,
            'transfercompleted' => 9,
            'voided' => 10
        );
    }
}