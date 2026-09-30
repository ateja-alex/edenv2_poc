<?php

namespace App\Eden\Managements\Services;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Eden\Managements\Elements\Element_management;

class Microsoft_teams_service {
	
	/*
	 *
	 * Crée une conversation Teams et envoie un message dessus
	 *
	 */
	public function envoyer_message_sur_teams ($id_utilisateur_eden = false, $id_utilisateur_eden_a_notifier = false, $message_a_envoyer = ''){
		
		//S'il y a bien un id_eden en paramètre on récupère l'id microsoft de l'utilisateur
		if(!empty($id_utilisateur_eden)) {
			
			// Récupère l'ID Microsoft de l'utilisateur eden ($id_utilisateur est un id eden) 
			$id_utilisateur_microsoft = modele('utilisateur', $id_utilisateur_eden)->id_microsoft;
		}
		else {
			
			$id_utilisateur_microsoft = false;
		}
		
		if(!empty($id_utilisateur_eden_a_notifier)) {
			
			// Récupère l'ID Microsoft de l'utilisateur eden ($id_utilisateur est un id eden) 
			$id_utilisateur_microsoft_a_notifier = modele('utilisateur', $id_utilisateur_eden_a_notifier)->id_microsoft;
		}
		else {
			
			$id_utilisateur_microsoft_a_notifier = false;
		}
		//S'il n'y a pas d'id microsoft on sort de la fonction
		if(empty($id_utilisateur_microsoft) || empty($id_utilisateur_microsoft_a_notifier))
			return false;
		
		$graph = service('microsoft_authentification')->instancie_graph();
		
		$nouvelle_conversation = $this->prepare_donnees_pour_conversation();
		
		//On fait la requête pour créer la conversation
		$conversation_a_creer = $graph->createRequest('POST', '/chats')
			->attachBody($nouvelle_conversation)
			->setReturnType(Model\Conversation::class)
			->execute();
		
		//On récupère l'id de la conversation créée
		$id_conversation_creee = $conversation_a_creer->getId();

        if(empty($message_a_envoyer)){

            $nouveau_message = [

                'body' => [
                    'content' => 'test envoi de messages avec l\'API Graph via les notifications Eden',
                ]
            ];
        }
        else {

            $nouveau_message = [

                'body' => [
                    'content' => $message_a_envoyer,
                ]
            ];
        }

		//On fait la requête pour poster le message sur la conversation
		$message_a_poster = $graph->createRequest('POST', '/chats/'. $id_conversation_creee . '/messages')
			->attachBody($nouveau_message)
			->setReturnType(Model\ChatMessage::class)
			->execute();

		if(empty($message_a_poster))
			return 'erreur';
		else
			return true;
	}
	
	/*
	 *
	 * Prépare les données nécessaires à la création d'une nouvelle conversations
	 *
	 */
	public function prepare_donnees_pour_conversation(){
		
		$nouvelle_conversation = [
			'chatType' => 'oneOnOne',
			'members' => [
				[
					'@odata.type' => '#microsoft.graph.aadUserConversationMember',
					'roles' => [ 'owner'],
					'user@odata.bind' => "https://graph.microsoft.com/v1.0/users('a584c875-9bd7-46a0-87f9-cab8c64f4427')",
				],
				[
					'@odata.type' => '#microsoft.graph.aadUserConversationMember',
					'roles' => [ 'owner'],
					'user@odata.bind' => "https://graph.microsoft.com/v1.0/users('82467bb2-54e4-421b-be18-5480861054a8')",
				]
			]
		];
		
		return $nouvelle_conversation;
	}
}