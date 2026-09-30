<?php

function parametre($nom, $nouvelle_valeur = false) {
	
	// @todo ajouter un système de cache
	
	$valeur = App\Eden\Models\Parametre::where('nom', $nom)->first();
	
	// on doit juste le retourner
	if($nouvelle_valeur === false) {
		
		if($valeur === null)
			return null;
		
		return $valeur->valeur;
	}
	
	// on doit l'enregistrer
	
	// on supprime l'ancien
	App\Eden\Models\Parametre::where('nom', $nom)->delete();

	// on crée le nouveau
	$parametre = new App\Eden\Models\Parametre;
	
	$parametre->nom = $nom;
	$parametre->valeur = $nouvelle_valeur;
	$parametre->id_entite = 0;
    $parametre->date_creation = date('Y-m-d H:i:s');
	
	$parametre->save();
	
	// je ne retourne rien volontairement, je ne sais pas encore s'il est mieux de retourner true ou alors de retourner la valeur
	
}

function parametre_supprimer($nom) {
    App\Eden\Models\Parametre::where('nom', $nom)->delete();
}

function parametre_entite($id_entite, $nom, $nouvelle_valeur = false) {
	
	// @todo ajouter un système de cache
	
	if(empty($id_entite)) {
		
		$id_entite = 0;
	}
	
	$valeur = App\Eden\Models\Parametre::where('nom', $nom)->where('id_entite', $id_entite)->first();
	
	// on doit juste le retourner
	if($nouvelle_valeur === false) {
		
		if($valeur === null)
			return null;
		
		return $valeur->valeur;
	}
	
	// on supprime l'ancien
	App\Eden\Models\Parametre::where('nom', $nom)->where('id_entite', $id_entite)->delete();
	
	// on crée le nouveau
	$parametre = new App\Eden\Models\Parametre;
	
	$parametre->nom = $nom;
	$parametre->valeur = $nouvelle_valeur;
	$parametre->id_entite = $id_entite;
    $parametre->date_creation = date('Y-m-d H:i:s');
	
	$parametre->save();
	
	// je ne retourne rien volontairement, je ne sais pas encore s'il est mieux de retourner true ou alors de retourner la valeur
	
}

function parametre_utilisateur($nom, $nouvelle_valeur = false, $id_utilisateur = false) {
	
	// @todo ajouter un système de cache
	
	if($id_utilisateur === false){

		$utilisateur = session()->get('utilisateur_eden');

        if(isset($utilisateur['id']))
		    $id_utilisateur = $utilisateur['id'];
	
	}
	
	$valeur = App\Eden\Models\Parametre::where('nom', $nom)->where('id_utilisateur', $id_utilisateur)->first();
	
	// on doit juste le retourner
	if($nouvelle_valeur === false) {
		
		if($valeur === null)
			return null;
		
		return $valeur->valeur;
	}
	
	// on supprime l'ancien
	App\Eden\Models\Parametre::where('nom', $nom)->where('id_utilisateur', $id_utilisateur)->delete();
	
	// on crée le nouveau
	$parametre = new App\Eden\Models\Parametre;
	
	$parametre->nom = $nom;
	$parametre->valeur = $nouvelle_valeur;
	$parametre->id_utilisateur = $id_utilisateur;
    $parametre->date_creation = date('Y-m-d H:i:s');
	
	$parametre->save();
	
	// je ne retourne rien volontairement, je ne sais pas encore s'il est mieux de retourner true ou alors de retourner la valeur
	
}

function parametre_cron($nom, $id_cron, $nouvelle_valeur = false, $id_utilisateur = false, $id_entite = false){

    $valeur = modele('cron_parametres')->where('nom', $nom)->where('id_cron', $id_cron);

    if($id_utilisateur !== false)
        $valeur = $valeur->where('id_utilisateur', $id_utilisateur);

    if($id_entite !== false)
        $valeur = $valeur->where('id_entite', $id_entite);

    $valeur = $valeur->first();

    // on doit juste le retourner
    if($nouvelle_valeur === false) {

        if($valeur === null)
            return null;

        return $valeur->valeur;
    }

    $donnees = [

        'id_cron' => $id_cron,
        'nom' => $nom,
        'valeur' => $nouvelle_valeur,
        'date_creation' => date('Y-m-d H:i:s'),
    ];

    if(!empty($id_utilisateur))
        $donnees['id_utilisateur'] = $id_utilisateur;

    if(!empty($id_entite))
        $donnees['id_entite'] = $id_entite;

    if(!empty($valeur))
        management('cron_parametres', $valeur->id, $valeur)->enregistre($donnees);
    else
        management('cron_parametres')->enregistre($donnees);
}