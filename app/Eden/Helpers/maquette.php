<?php

/*
*
* Helper pour aller chercher une maquette ou un element de la maquette
*
* @param $attribut string
*
*/

use App\Eden\Variables;

function maquette($attribut = false) {

	$maquette = null;
    $url_extranet = fonctionnalite('url_extranet');
	$maquette_extranet_a_utiliser = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == $url_extranet;

    if(cache_actif() && (session()->has('cache.maquette_extranet') || session()->has('cache.maquette'))) {

        if($maquette_extranet_a_utiliser && session()->has('cache.maquette_extranet'))
            $maquette = session()->get('cache.maquette_extranet');
        else if(session()->has('cache.maquette'))
            $maquette = session()->get('cache.maquette');

		return !$attribut ? $maquette : ($maquette[$attribut] ?? null);
    }
	
	$moi = moi();
	$maquette_extranet = fonctionnalite('maquette_extranet');
	
	if (!empty($moi->maquette_id))
		$maquette = modele('maquette')->where('id', $moi->maquette_id)->first()->toArray();
	elseif($maquette_extranet_a_utiliser)
		$maquette = modele('maquette')->where('id', $maquette_extranet)->first()->toArray();
	else
		$maquette = modele('maquette')->where('par_defaut',1)->first()->toArray();

	if(isset($maquette)) {

		if(!$maquette['page_accueil'])
            $maquette['page_accueil'] = $maquette_extranet_a_utiliser ? '/extranet/accueil' : '/eden/accueil';

		if(empty($maquette['taille_police']))
			$maquette['taille_police'] = 100;

		if(empty($maquette['poids_police']))
			$maquette['poids_police'] = 0;

		$maquette['blog_nombre_articles_par_page'] = 9;
		$couleurs = modele('maquette_couleurs')->where('maquette', $maquette['id'])->get();
		$maquette['couleurs'] = array();

		foreach($couleurs as $couleur) {

			management('maquette_couleurs', $couleur->id, $couleur)->charge_valeurs_champs_multiselection();
			$couleur->nom_couleur = Variables::$correspondance_couleurs_maquette[$couleur->nom_couleur];

			if(!empty($couleur->valeurs)){

				$couleurs_a_ajouter = array();

				foreach($couleur->valeurs as $index => $valeur){
					
					$couleurs_a_ajouter[] = [
						'nom_couleur' => str_replace('#numero_couleur#', $index, $couleur->nom_couleur),
						'valeur' => $valeur
					];
				}

				$maquette['couleurs'] = array_merge($maquette['couleurs'] ?? [], $couleurs_a_ajouter);
			}
			else
				$maquette['couleurs'][] = $couleur->toArray();
		}

		if(!empty($maquette['langue_par_defaut'])) {
            $maquette['langue_par_defaut'] = $maquette['langue_par_defaut'];
            $maquette['langue_par_defaut_code'] = \DB::table('traduction_langue')->where('id', $maquette['langue_par_defaut'])->value('code');
        }
		else {
            $maquette['langue_par_defaut'] = \DB::table('traduction_langue')->where('code', 'fr')->value('id');
            $maquette['langue_par_defaut_code'] = 'fr';
        }
	}
	else {

		$maquette = [

			'nom_application' => 'Eden',
			'logo_application' => '',
			'utilisation_logo_haut_gauche' => false,
			'devise_application_symbole' => '€',
			'devise_application_iso' => 'EUR',
			'devise_application_nom' => 'euros',
			'poids_police' => 0,
			'taille_police' => 100,
			'favicon' => '/eden/images/logo_eden.svg',
			'logo_application_connexion' => 'eden/images/no_image.jpg',
			'page_accueil' => '',
			'background_connexion' => '',
			'langue_par_defaut' => null,
			'langue_par_defaut_code' => 'fr',
			'blog_nombre_articles_par_page' => 9,
			'couleurs' => [
				[
					'nom_couleur' => 'couleur_police_nom_application',
					'valeur' => '#ffffff',
				],
				[
					'nom_couleur' => 'background_navbar',
					'valeur' => '#69767b',
				],
				[
					'nom_couleur' => 'background_menus',
					'valeur' => '#7ea6b7',
				],
				[
					'nom_couleur' => 'background_menus_hover',
					'valeur' => '#888585',
				],
				[
					'nom_couleur' => 'background_sous_menus',
					'valeur' => '#a5c4d2',
				],
				[
					'nom_couleur' => 'couleur_texte_menus',
					'valeur' => '#ffffff',
				],
				[
					'nom_couleur' => 'couleur_liens',
					'valeur' => '#69767b',
				],
				[
					'nom_couleur' => 'background_tache',
					'valeur' => '#7ea6b7',
				],
				[
					'nom_couleur' => 'police_tache',
					'valeur' => '#ffffff',
				],
			]
		];
	}

    session()->put($maquette_extranet_a_utiliser ? 'cache.maquette_extranet' : 'cache.maquette', $maquette);

    return !$attribut ? $maquette : ($maquette[$attribut] ?? null);
}
