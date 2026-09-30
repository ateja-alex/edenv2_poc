<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Eden\Models\Champ_libre;

use App\Http\Controllers\Controller;
use App\Eden\Models\Element_piece_jointe;

use File;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With");

class Addon_messagerie_controller extends Controller {
	
	public function identification_utilisateur() {
		
		// on identifie l'utilisateur
		$authentification_management = new \App\Eden\Managements\Authentification_management;
		
		if(empty(request()->email))
			return response()->json(array(traduction('messages.php.champ_obligatoire')." email"));
			
		if(empty(request()->mot_de_passe))
			return response()->json(array(traduction('messages.php.champ_obligatoire')." mot de passe"));
			
		list($retour, $utilisateur) = $authentification_management->connexion(request()->email, request()->mot_de_passe);
		
		if($retour !== true) {
			
			return response()->json(array($utilisateur));
		}
		
		// ok utilisateur correct, on doit le connecter
		session()->put('utilisateur_eden', $utilisateur);	

		return ['retour' => true];			
	}
	
	//La fonction recuperation_client permet de renvoyer les infos du client s'il existe afin de le récupérer dans l'add-on sinon nous devront le créer depuis l'add-on
	public function recuperation_client(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;

		//On vérifie que le client existe par son mail ou son id s'il est fourni
		$mail = $request->post('mail');
		$id = $request->post('client_id');
		if($id !== ""){
			$client = modele('client')->where('id', $id)->first();
		}
		else{
			$client = modele('client')->where('adresse_email', $mail)->first();
		}

		//Si le client existe on vérifie si l'adresse mail correspond à un contact et on l'ajoute au tableau retourné.
		if($client !== null){
			
			$contact = modele('contact')->where('adresse_email', $mail)->first();

			if($contact !== null){
				$tableau_retour = $client;
				$tableau_retour['nom_contact'] = $contact['nom'];
				$tableau_retour['prenom_contact'] = $contact['prenom'];
				$tableau_retour['email_contact'] = $contact['adresse_email'];
				return response()->json($tableau_retour, 200);
			}

			return response()->json($client, 200);
		}
		//Si le client n'existe pas on vérifie si l'adresse mail correspond à un contact si oui on récupère le client associé au contact sinon on ne retourne rien
		else{
			$contact = modele('contact')->where('adresse_email', $mail)->get()->toArray();

			if(!empty($contact)){
				$client = modele('client')->where('id', $contact['client_id'])->first();
				$tableau_retour = $client;
				$tableau_retour['contact'] = $contact;
				return response()->json($tableau_retour, 200);
			}
			else{
				return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_client_associe')], 200);
			}
		}

	}

	//La fonction recuperation_client permet de renvoyer les infos du client s'il existe afin de le récupérer dans l'add-on sinon nous devront le créer depuis l'add-on
	public function recuperation_projet(Request $request){

		$identification = $this->identification_utilisateur();

		if($identification['retour'] !== true)
			return $identification;

		//On vérifie que le client existe par son mail ou son id s'il est fourni
		$mail = $request->post('mail');
		$id = $request->post('projet_id');
		if($id !== ""){
			$client = modele('projet')->where('id', $id)->first();
		}
		else{
			$client = modele('projet')->where('adresse_email', $mail)->first();
		}

		//Si le client existe on vérifie si l'adresse mail correspond à un contact et on l'ajoute au tableau retourné.
		if($client !== null){

			$contact = modele('contact')->where('adresse_email', $mail)->first();

			if($contact !== null){
				$tableau_retour = $client;
				$tableau_retour['nom_contact'] = $contact['nom'];
				$tableau_retour['prenom_contact'] = $contact['prenom'];
				$tableau_retour['email_contact'] = $contact['adresse_email'];
				return response()->json($tableau_retour, 200);
			}

			return response()->json($client, 200);
		}
		//Si le client n'existe pas on vérifie si l'adresse mail correspond à un contact si oui on récupère le client associé au contact sinon on ne retourne rien
		else{
			$contact = modele('contact')->where('adresse_email', $mail)->get()->toArray();

			if(!empty($contact)){
				$client = modele('projet')->where('id', $contact['projet_id'])->first();
				$tableau_retour = $client;
				$tableau_retour['contact'] = $contact;
				return response()->json($tableau_retour, 200);
			}
			else{
				return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_client_associe')], 200);
			}
		}

	}

	public function recuperation_contact(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;

		//On vérifie que le contact existe via son id ou son mail selon ce qui est fournie.
		if($request->post('id') !== null){
			$id = $request->post('id');
			$contact = modele('contact')->where('id', $id)->first();
		}
		else{
			$mail = $request->post('mail');
			$contact = modele('contact')->where('adresse_email', $mail)->first();
		}

		//On retourne toutes les infos du contact s'ul existe
		if($contact !== null){
			return response()->json($contact, 200);
		}
		else{
			return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_correspondance_contact')], 200);
		}

	}

	public function recuperation_champs_obligatoires(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;

        $type_element = $request->post('element');
			
		$liste_champs_obligatoires = Champ_libre::where('type_element', $type_element)->where('obligatoire', '1')->where(function($query){
			$query->where('type', '0')
				  ->orWhere('type','1')
				  ->orWhere('type','20');
		})
		->get()->toArray();
		if(!empty($liste_champs_obligatoires)){
			return response()->json($liste_champs_obligatoires, 200);
		}
		else{			
			return response()->json(['error', traduction('messages.php.addon_messagerie.erreur_champ_obligatoire_formulaire')], 200);
		}
	}

	public function recuperation_valeurs_liste(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;
			
		$nom_sql = $request->post('nom_sql');

		$valeurs_liste = management('client')->champ($nom_sql)->valeurs_possibles;

		return response()->json($valeurs_liste, 200);
	}

	public function ajout_contact(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;
		
		//On récupère toutes les valeurs fournies par l'add-on
		$nom = $request->post('nom');
		$prenom = $request->post('prenom');
		$adresse_email = $request->post('adresse_email');
		$telephone = $request->post('telephone');
		$telephone_portable = $request->post('telephone_portable');
		$poste = $request->post('poste');
		$client_id = $request->post('client_id');
		
		//On les envoies dans la base de donnée afin de créer un nouveau contact
		$management_contact = management('contact');
		$management_contact->enregistre(array('nom' => $nom , 'prenom' => $prenom , 'adresse_email' => $adresse_email , 'telephone' => $telephone , 'telephone_portable' => $telephone_portable , 'poste' => $poste , 'client_id' => $client_id));
		
		return response()->json(['valide' => 'valide'],200);
	}

	public function ajout_client(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;
		
		//On récupère toutes les valeurs fournies par l'add-on
		$tout = $request->all();

		unset($tout['email']);
		unset($tout['mot_de_passe']);
		
		//On les envoies dans la base de donnée afin de créer un nouveau contact
        if(isset($tout['client_id']) && $tout['client_id'] !== "undefined")
		    $management_client = management('client', $tout['client_id']);
        else
		    $management_client = management('client');
		$management_client->enregistre($tout);
		
		return response()->json(['valide' => 'valide'],200);
	}

	public function recherche_contact(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;

		//On récupère toutes les valeurs fournies par l'add-on
		$valeur_recherche = $request->post('valeur_recherche');
		$client_id = $request->post('client_id');

		if($client_id !== null){
			//On récupère tous les contacts qui répondent aux critères.
			$liste_contact = modele('contact')->where('chaine_tags_recherche', 'like', '%'.$valeur_recherche.'%')->where('client_id', $client_id)->get()->toArray();			
		}
		else{
			//On récupère tous les contacts qui répondent aux critères.
			$liste_contact = modele('contact')->where('chaine_tags_recherche', 'like', '%'.$valeur_recherche.'%')->get()->toArray();			
		}

		//Si la liste n'est pas vide on renvoi la liste sinon on renvoi un tableau erreur.
		if(!empty($liste_contact)){

			return response()->json($liste_contact);
		}
		else{
			return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_correspondance_contact_recherche')], 200);
		}

	}

	public function recherche_client(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;

		//On récupère toutes les valeurs fournies par l'add-on
		$element_recherche = $request->post('element_recherche');

        if(empty($element_recherche))
            $element_recherche = 'client';

		$valeur_recherche = $request->post('valeur_recherche');
		$element_id = $request->post('client_id');
        $element_id = json_decode($element_id);

		//On récupère tous les contacts qui répondent aux critères.
		$liste_client = modele($element_recherche)->where(function($r) use ($valeur_recherche,$element_recherche) {

			$r->where('chaine_tags_recherche', 'like', '%'.$valeur_recherche.'%');
            if($element_recherche == 'client')
                $r->orWhere('adresse_email',$valeur_recherche);
		})->get()->toArray();

		if($element_id !== null){
			$clients = modele($element_recherche)->whereIn('id', $element_id)->get()->toArray();
			$liste_client = array_merge($liste_client,$clients);
		}

		//Si la liste n'est pas vide on renvoi la liste sinon on renvoi un tableau erreur.
		if(!empty($liste_client)){
            $verif_client = [];
            $liste_client_envoie = [];
            foreach ($liste_client as $key => $client){

                if(!in_array($client['id'], $verif_client)) {
                    $verif_client[] = $client['id'];
                    $liste_client_envoie[] = $client;
                }

            }

			return response()->json($liste_client_envoie, 200);
		}
		else{
			return response()->json(['error' => traduction('messages.php.addon_messagerie.erreur_correspondance_contact_recherche')], 200);
		}

	}

	public function maj_contact(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;
			
			
		//On récupère toutes les valeurs fournies par l'add-on
		$id = $request->post('id');
		$mail = $request->post('adresse_email');
		$nom = $request->post('nom');
		$prenom = $request->post('prenom');
		$poste = $request->post('poste');
        if($request->has('telephone'))
		    $telephone = $request->post('telephone');
        if($request->has('telephone_portable'))
		    $telephone_portable = $request->post('telephone_portable');

		//On récupère le contact correspondant à l'id.
		$management_contact = management('contact',$id);

		//On met à jour ses infos
		return response()->json($management_contact->enregistre(array('nom' => $nom , 'prenom' => $prenom , 'adresse_email' => $mail , 'poste' => $poste, 'telephone' => $telephone, 'telephone_portable' => $telephone_portable)));
	}

	public function stock_mail(Request $request){
		
		$identification = $this->identification_utilisateur();
		
		if($identification['retour'] !== true)
			return $identification;

		//On récupère toutes les valeurs fournies par l'add-on
		$contact_id = $request->post('contact_id');
		$date = $request->post('date');
		$description = $request->post('description');
        if($request->post('element') != null)
            $element = $request->post('element');
        else
            $element = "client";
        if($request->post('element_id') != null)
		    $element_id = $request->post('element_id');
        else{
            $contact = modele('contact')->where('id', $contact_id)->first();

            if($element == 'client')
                $modele_element = modele('client')->where('id', $contact['client_id'])->first();
            else
                $modele_element = modele('projet')->where('id', $contact['projet_id'])->first();

            $element_id = $modele_element['id'];
        }

        if($request->post('destinataires_email'))
            $destinataires_email = $request->post('destinataires_email');
        else
            $destinataires_email = null;

        $client_id = null;

        if($contact_id == null && $element != 'contact' && $destinataires_email !== null){


            if($element == 'projet'){

                $contact = modele('contact')->where('adresse_email', $destinataires_email)->whereNotNull('client_id')->first();

                $client_id = $contact->client_id;

            }
            if($element == 'client'){

                $contact = modele('contact')->where('adresse_email', $destinataires_email)->where('client_id',$element_id)->first();

            }

            $contact_id = $contact->id;

        }

        if($request->post('emetteur_email'))
		    $emetteur_email = $request->post('emetteur_email');
        else
            $emetteur_email = null;

        if(!empty($request->noms_fichiers)) {
            $description = $this->enregistre_piece_jointes($request, $description);
        }

		//On ajoute le mail à la base de donnée.
		$management_contact = management('echange');
		$management_contact->enregistre(array('date' => $date , 'contact_id' => $contact_id , 'description' => $description , 'type_element' => $element , 'element_id' => $element_id, 'client_id' => $client_id , 'type' => '3', 'destinataires_email' => $destinataires_email, 'emetteur_email' => $emetteur_email));
	}



    public function recupere_clients(Request $request){

        $identification = $this->identification_utilisateur();

        if($identification['retour'] !== true)
            return $identification;

        $clients = modele('client')->select('id', 'chaine_affichage')->get()->keyBy('id');

        return response()->json($clients, 200);

    }

    public function enregistre_piece_jointes($request, $description){

        $identification = $this->identification_utilisateur();

        if($identification['retour'] !== true)
            return $identification;

        foreach ($request->noms_fichiers as $nom_fichier){

            if(empty($request->file($nom_fichier)))
                continue;

            if($request->post('element') != null)
                $type_element = $request->post('element');
            else
                $type_element = "client";

            $chemin_enregistrement = str_replace('public/', '', $request->file($nom_fichier)->store('public/' . $type_element));

            if(config("eden.enregistrement_documents_sur_aws") !== false) {

                $chemin_enregistrement_s3 = $request->file($nom_fichier)->store('/', 's3');
            }

            // on enregistre sur le modele
            $image = new Element_piece_jointe;
            $image->type_element = $type_element;
            $image->element_id = $request->post('element_id');
            $image->nom = $request->file($nom_fichier)->getClientOriginalName();
            $image->chemin = $chemin_enregistrement;
            $image->titre = $image->nom;

            $image->save();

            $description .= "<br><strong>PJ :</strong><a href='storage/" . $chemin_enregistrement . "'>" . $request->file($nom_fichier)->getClientOriginalName() . "</a>";

        }

        return $description;

    }

    public function genere_manifest_outlook(){

        $contenu = '
            <OfficeApp xmlns="http://schemas.microsoft.com/office/appforoffice/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:bt="http://schemas.microsoft.com/office/officeappbasictypes/1.0" xmlns:mailappor="http://schemas.microsoft.com/office/mailappversionoverrides/1.0" xsi:type="MailApp">
                <Id>9752553b-9c21-4289-8aa0-2d5968ea02b2</Id>
                <Version>1.0.0.0</Version>
                <ProviderName>Eden</ProviderName>
                <DefaultLocale>fr-FR</DefaultLocale>
                <DisplayName DefaultValue="Eden"/>
                <Description DefaultValue="Votre ERP Eden directement depuis votre boîte mail !"/>
                <IconUrl DefaultValue="'.env('EDEN_CONSOLE_API_URL').'storage/logo_e_easydev.png"/>
                <HighResolutionIconUrl DefaultValue="https://addon-outlook.easydev.run/assets/icon-128.png"/>
                <SupportUrl DefaultValue="https://addon-outlook.easydev.run/"/>
                <AppDomains>
                    <AppDomain>https://addon-outlook.easydev.run/</AppDomain>
                    <AppDomain>https://' . $_SERVER['HTTP_HOST'] . '</AppDomain>               
                </AppDomains>
                <Hosts>
                    <Host Name="Mailbox"/>
                </Hosts>
                <Requirements>
                    <Sets>
                        <Set Name="Mailbox" MinVersion="1.1"/>
                    </Sets>
                </Requirements>
                <FormSettings>
                    <Form xsi:type="ItemRead">
                        <DesktopSettings>
                            <SourceLocation DefaultValue="https://addon-outlook.easydev.run/taskpane.html"/>
                            <RequestedHeight>250</RequestedHeight>
                        </DesktopSettings>
                    </Form>
                </FormSettings>
                <Permissions>ReadWriteItem</Permissions>
                <Rule xsi:type="RuleCollection" Mode="Or">
                    <Rule xsi:type="ItemIs" ItemType="Message" FormType="Read"/>
                </Rule>
                <DisableEntityHighlighting>false</DisableEntityHighlighting>
                <VersionOverrides xmlns="http://schemas.microsoft.com/office/mailappversionoverrides" xsi:type="VersionOverridesV1_0">
                    <Requirements>
                        <bt:Sets DefaultMinVersion="1.3">
                            <bt:Set Name="Mailbox"/>
                        </bt:Sets>
                    </Requirements>
                    <Hosts>
                        <Host xsi:type="MailHost">
                            <DesktopFormFactor>
                                <ExtensionPoint xsi:type="MessageReadCommandSurface">
                                    <OfficeTab id="TabDefault">
                                        <Group id="msgComposeCmdGroup">
                                            <Label resid="GroupLabel"/>
                                            <Control xsi:type="Button" id="msgReadOpenPaneButton">
                                                <Label resid="TaskpaneButton.Label"/>
                                                <Supertip>
                                                    <Title resid="TaskpaneButton.Label"/>
                                                    <Description resid="TaskpaneButton.Tooltip"/>
                                                </Supertip>
                                                <Icon>
                                                    <bt:Image size="16" resid="Icon.16x16"/>
                                                    <bt:Image size="32" resid="Icon.32x32"/>
                                                    <bt:Image size="80" resid="Icon.80x80"/>
                                                </Icon>
                                                <Action xsi:type="ShowTaskpane">
                                                    <SourceLocation resid="Taskpane.Url"/>
                                                </Action>
                                            </Control>
                                        </Group>
                                    </OfficeTab>
                                </ExtensionPoint>
                            </DesktopFormFactor>
                        </Host>
                    </Hosts>
                    <Resources>
                        <bt:Images>
                            <bt:Image id="Icon.16x16" DefaultValue="'.env('EDEN_CONSOLE_API_URL').'logo_easydev.png"/>
                            <bt:Image id="Icon.32x32" DefaultValue="'.env('EDEN_CONSOLE_API_URL').'logo_easydev.png"/>
                            <bt:Image id="Icon.80x80" DefaultValue="'.env('EDEN_CONSOLE_API_URL').'logo_easydev.png"/>
                        </bt:Images>
                        <bt:Urls>
                            <bt:Url id="Taskpane.Url" DefaultValue="https://addon-outlook.easydev.run/taskpane.html"/>
                        </bt:Urls>
                        <bt:ShortStrings>
                            <bt:String id="GroupLabel" DefaultValue="Eden Add-in"/>
                            <bt:String id="TaskpaneButton.Label" DefaultValue="Eden"/>
                        </bt:ShortStrings>
                        <bt:LongStrings>
                            <bt:String id="TaskpaneButton.Tooltip" DefaultValue="Opens a pane displaying all available properties."/>
                        </bt:LongStrings>
                    </Resources>
                </VersionOverrides>
            </OfficeApp>
        ';

        File::put(storage_path('app/public/manifest.prod.xml'),$contenu);
        return response()->download(storage_path('app/public/manifest.prod.xml'));
    }

}