<?php

namespace App\Eden\Managements\Services;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Eden\Managements\Elements\Element_management;
use Log;

class Microsoft_taches_service {
	
	/**
	 *
	 * Crée une tâche dans la liste de tâches d'un autre utilisateur si son id est spécifié sinon il est créé dans la liste de tâches de l'utilisateur actuellement connecté
	 *
	 */
	public function creer_tache($infos, $id_utilisateur = false){

        // Instancie Graph à partir du profil de l'utilisateur
        $graph = service('microsoft_authentification')->instancie_graph($id_utilisateur);

        if($graph === false)
            return false;

		// Crée une tâche à partir du tableau $infos
		$nouvelle_tache = $this->prepare_donnees_pour_tache($infos);

        $id_liste_taches = $this->recupere_liste_taches($id_utilisateur);

        // Envoie la requête pour créer la tâche dans la liste
        try {

            $reponse = $graph->createRequest('POST', '/me/todo/lists/'.$id_liste_taches.'/tasks')
                ->attachBody($nouvelle_tache)
                ->setReturnType(Model\TodoTask::class)
                ->execute();
        } catch(\Exception $exception) {

            log_mis_en_forme('La création de la tâche a échoué sur la liste de tâches ' . $id_liste_taches . ' pour l\'utilisateur microsoft ' . moi()->id_microsoft, $exception);
            return false;
        }


        return $reponse;
	}
	 
	/**
	 *
	 *  Modifie une tâche dans la liste de tâches d'un autre utilisateur si son id est spécifié sinon il est modifié dans la liste de tâches de l'utilisateur actuellement connecté
	 *
	 */
	public function modifier_tache($infos, $id_tache, $id_utilisateur = false){

        // Instancie Graph à partir du profil de l'utilisateur
        $graph = service('microsoft_authentification')->instancie_graph($id_utilisateur);

        if($graph === false)
            return false;

		// Crée une tâche à partir du tableau $infos
		$nouvelle_tache = $this->prepare_donnees_pour_tache($infos);
			
        $id_liste_taches = $this->recupere_liste_taches($id_utilisateur);

        // Envoie la requête pour modifier la tâche dans la liste
        try {

            $reponse = $graph->createRequest('PATCH', '/me/todo/lists/'.$id_liste_taches.'/tasks/'.$id_tache)
                ->attachBody($nouvelle_tache)
                ->setReturnType(Model\TodoTask::class)
                ->execute();
        } catch(\Exception $exception) {

            log_mis_en_forme('La modification de la tâche ' . $id_tache . ' a échoué sur la liste de tâches ' . $id_liste_taches . ' pour l\'utilisateur microsoft ' . moi()->id_microsoft, $exception);
            return false;
        }


        return $reponse;
	}
	 
	/**
	*
	*  Supprime une tâche dans la liste de tâches d'un autre utilisateur si son id est spécifié sinon il est supprimé dans la liste de tâches de l'utilisateur actuellement connecté
	*
	*/
	public function supprimer_tache($id_tache, $id_utilisateur = false){

        // Instancie Graph à partir du profil de l'utilisateur
        $graph = service('microsoft_authentification')->instancie_graph($id_utilisateur);

        if($graph === false)
            return false;

        $id_liste_taches = $this->recupere_liste_taches($id_utilisateur);

        // Envoie la requête pour supprimer la tâche dans la liste
        try {

            $reponse = $graph->createRequest('DELETE', '/me/todo/lists/'.$id_liste_taches.'/tasks/'.$id_tache)
                ->setReturnType(Model\TodoTask::class)
                ->execute();
        } catch(\Exception $exception) {

            log_mis_en_forme('La suppression de la tâche ' . $id_tache . ' a échoué sur la liste de tâches ' . $id_liste_taches . ' pour l\'utilisateur microsoft ' . moi()->id_microsoft, $exception);
            return false;
        }

        return $reponse;
	}
	
	/**
	*
	*  Récupère la liste de tâches d'un autre utilisateur si son id est spécifié sinon on récupère celle de  l'utilisateur actuellement connecté
	*
	*/
	public function recupere_liste_taches($id_utilisateur = false){

        //Instancie Graph à partir de la session utilisateur en cours
        $graph = service('microsoft_authentification')->instancie_graph($id_utilisateur);

        // Envoie la requête pour récupérer les listes de tâches de l'utilisateur
        try {

            $reponse = $graph->createRequest('GET', '/me/todo/lists')
                ->setReturnType(Model\TodoTaskList::class)
                ->execute();
        } catch(\Exception $exception) {

            log_mis_en_forme('La récupération des listes de tâches a échoué pour l\'utilisateur microsoft ' . moi()->id_microsoft, $exception);
            return false;
        }
				
        //Récupère l'id de la première liste de tâches existantes (par défaut il n'y en a qu'une)
        $id_liste_taches = $reponse[0]->getId();

        return $id_liste_taches;
	}

	/**
	 * 
	 * Prépare un tableau formaté pour microsoft pour la création ou la modification d'une tâche
	 * 
	 */
	protected function prepare_donnees_pour_tache($infos) {

		if(!isset($infos['commentaire']))
			$infos['commentaire'] = '';
			
		if(!isset($infos['sujet']))
			$infos['sujet'] = '';
			
		if(!isset($infos['date_debut']))
			$infos['date_debut'] = date('Y-m-d H:i:00');

        $importance = 'normal';
        $statut = "notStarted";

        if(isset($infos['urgent']) && $infos['urgent'] == 1)
            $importance = 'high';

        if(isset($infos['terminee']) && $infos['terminee'] == 1)
            $statut = "completed";
		
		$nouvelle_tache = [
		
			'title' => strip_tags($infos['sujet']),
			'createdDateTime' => formate_date('Y-m-dTH:i:00Z', $infos['date_debut']),
			'dueDateTime' => [

				'dateTime' => formate_date('Y-m-dTH:i:00Z', $infos['date_debut']),
				'timeZone' => 'Romance Standard Time'
			],
			'body' => [
			
				'content' => $infos['commentaire'],
				'contentType' => 'html'
			],
            'importance' => $importance,
            'status' => $statut,
		];

        if(isset($infos['notification'])){
            $nouvelle_tache['isReminderOn'] = true;
            $nouvelle_tache['reminderDateTime'] = [
				'dateTime' => formate_date('Y-m-dTH:i:00', $infos['notification']),
				'timeZone' => "Romance Standard Time"
			];
        }

		return $nouvelle_tache;
	}
}