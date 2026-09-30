<?php

namespace App\Eden\Champs;

use App\Eden\Variables;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\Blade;
use Session;
use DB;
use App\Eden\Models\Champs_liste_formatee;
use App\Eden\Models\Champ_libre_liste;

class Champ {

	/**
	 *
	 * Les attributs standards des champs HTML
	 *
	 */
	public $valeur = '';
	public $placeholder = '';
	public $title = '';
	public $classes_css = '';
	public $attributs = array();
	public $valeur_filtre = array();
	public $sans_vmodel = false;
	public $valeurs_initiales = array();
	public $champ_formulaire = null;
    public string $type_filtre = '';
    public string $nom_composant = '';

	/**
	 *
	 * Pour initialiser certaines valeurs ?
	 *
	 */
	public function __construct($champ_libre, $valeur = false) {

		$this->valeur = $valeur;
		$this->modele = $champ_libre;

        if(isset($champ_libre->nom))
            $this->title = $champ_libre->nom;

		$this->vmodel();

        $this->name();

        $this->attr('nom_sql', $this->modele->nom_sql);
        $this->attr('key', $this->modele->id_cl);

		$this->lecture_seule();

        $this->classes_css_js();
	}

	public function value($valeur) {

		$this->valeur = $valeur;

		return $this;
	}

	public function placeholder($valeur) {

		$this->placeholder = $valeur;

		return $this;
	}

	public function name($valeur = null) {

        if($valeur == null){
            $this->attr('name', $this->modele->nom_sql);
            return;
        } else {
            $this->attr('name', $valeur);
        }
		return $this;
	}

	public function disabled($valeur) {

		$this->attributs['disabled'] = $valeur;

		return $this;
	}

    public function classes_css_js(){
        $classes_css = '';
        if (isset($this->modele->classes_css))
            $classes_css = $this->modele->classes_css;

        $classes_js = '';
        if (isset($this->modele->classes_js))
            $classes_js = $this->modele->classes_js;

        $classes_css_js = $classes_css . ' ' . $classes_js;
        if(empty($classes_css_js)){
            return;
        } else {
            $this->attr('class_css_js', $classes_css_js);
        }
    }

	public function est_disabled() {

		if(isset($this->attributs['disabled']) && ($this->attributs['disabled'] === 'true' || $this->attributs['disabled'] === true))
			return true;

		return false;
	}

	public function classes_css($classes, $ajoute = true) {

		if($ajoute === true)
			$this->classes_css .= ' '.$classes;
		else
			$this->classes_css = $classes;

		return $this;
	}
    
    public function affiche_liste($element, $colonne){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        return [
            'type' => 'contenu',
            'contenu' => $this->affiche($element->{$colonne_valeur}),
        ];
    }

    public function affiche_export($element){

        $colonne_valeur = $this->modele->alias_champ ?? $this->modele->nom_sql;

        return $this->affiche($element->{$colonne_valeur});
    }

	/**
	 *
	 * Récupère la valeur des filtres qui ont été enregistrés pour les réafficher
	 *
	 */
	public function recupere_valeur_filtre($options_liste) {

		if(!isset($options_liste['filtres']))
			return $this;

		if(!isset($options_liste['filtres'][$this->modele->nom_sql]))
			return $this;

		$this->valeur_filtre = $options_liste['filtres'][$this->modele->nom_sql];

		return $this;
	}

	/**
	 *
	 * Attribut divers en html (par exemple checked='checked', ou pour du JS comme id_element="123")
	 *
	 *  @param $attribut le nom de l'attribut
	 *  @param $valeur la valeur de l'attribut
	 *
	 */
	public function attr($attribut, $valeur,$dynamique = null) {

		$this->attributs[$attribut] = [
            'nom' => $attribut,
            'valeur' => $valeur,
            'dynamique' => $dynamique
        ];

		return $this;
	}

    /**
     *
     * Retourne le texte de "sans valeur" (pour gérer des cas spécifiques)
     *
     */
    protected function nom_sans_valeur_dans_filtre() {

        return "Sans valeur";
    }

	/**
	 *
	 * Raccourci pour le v-model
	 *
	 *  @param $parametres bool
	 *
	 */
	public function vmodel($vmodel_document = true,$type_element = null,$nom_sql = null) {

        if(empty($type_element))
            $type_element = in_array($this->modele->type_element, Variables::$documents_gescom) && $vmodel_document ? 'document' : $this->modele->type_element;

        if(empty($nom_sql))
            $nom_sql = $this->modele->nom_sql;

		$this->attr('v-model',$type_element.'.'.$this->modele->nom_sql);
        $this->attr('type-element-v-model', $type_element);
        $this->attr('modele', $type_element, 1);

		return $this;
	}

	/**
	 *
	 * Permet de ne pas mettre le vmodel
	 *
	 */
	public function sans_vmodel() {

		$this->sans_vmodel = true;

		if(isset($this->attributs['v-model']))
			unset($this->attributs['v-model']);

		return $this;
	}

	/**
	 *
	 * @return html
	 *
	 */
	public function attributs() {

		if(empty($this->attributs))
			return;

		$html = array();

		foreach($this->attributs as $attribut) {

            $dynamique = (!empty($attribut['dynamique']) && $attribut['nom'] !== 'v-model') ? ':' : '';

            if(is_array($attribut['valeur'])){
                $valeur = "'".json_encode($attribut['valeur'])."'";
                $dynamique = ':';
            } else if(in_array($attribut['nom'], array('champ_libre', 'composant_enfant_props'))){
                $valeur = "'".$attribut['valeur']."'";
            
            } else {
                $valeur = '"'. str_replace('"','&quot;',$attribut['valeur']).'"';
            }

			$html[] = $dynamique.$attribut['nom'].'='.$valeur;
		}

		return implode(' ', $html);
	}

	/**
	 *
	 * Affiche proprement la valeur d'un champ
	 *
	 */
	public function affiche($valeur = false) {

		if($valeur === false)
			return $this->valeur;
		else
			return $valeur;
	}

	/**
	 *
	 * Affiche proprement le nom d'un champ
	 *
	 */
	public function nom() {

		$index_traduction = $this->modele->index_traduction;

		return traduction($index_traduction.'.nom');
	}

    /**
	 *
	 * Affiche proprement le nom d'un champ
	 *
	 */
	public function nom_vue() {

		$index_traduction = $this->modele->index_traduction;

        return Blade::compileString("@traduction('".$index_traduction.".nom')");
    }

    public function cree(){
        return $this->cree_champ();
    }
    
	public function cree_champ() {

        $input = '<'.$this->nom_composant.' ';

        $input .= $this->attributs();

        $input .= '>';

        $input .= '</'.$this->nom_composant.'>';

        return str_replace('[eden_champ]',$input,$this->paterne_champ());
	}


	public function retourne_valeurs() {

		return $this->valeurs_possibles;
	}

	/**
	 *
	 * Crée le champ pour la création des workflows
	 *
	 */
	public function cree_pour_workflow($nom = 'champ') {

		$this->attr('name', 'parametrage['.$nom.'_'.$this->modele->nom_sql.']');
		$this->vmodel(true,'workflow.parametrage',$nom.'_'.$this->modele->nom_sql);
        $this->attr('modele', 'workflow.parametrage', 1);

		return $this->cree();
	}

	/**
	 *
	 * Applique les filtres sur les listes (les listes d'éléments génériques)
	 *
	 */
	public function applique_filtre_sur_requete($filtre, $requete) {

		// cas des rapports paramétrables par exemple
		if(!is_array($filtre))
			$filtre = array('texte' => $filtre);

        $alias_champ = $this->alias_champ_requete();

		// pas de variable, on traite le cas classique
		if(!empty($filtre['texte'])) {

			// C'est un tableau si c'est un filtre de la colonne de gauche (possibilité de choisir plusieurs textes)
			if(is_array($filtre['texte'])) {

				$requete->where(function($query) use($filtre, $alias_champ){

					$compteur = 0;
					foreach($filtre['texte'] as $texte) {

					    if ($compteur == 0)
					        $query->where($alias_champ, 'LIKE', '%' . $texte . '%');
					    else
					        $query->orWhere($alias_champ, 'LIKE', '%' . $texte . '%');

						$compteur++;
					}
				});
			}

			else {

			    $requete = $requete->where($alias_champ, 'LIKE', '%' . $filtre['texte'] . '%');

			}

		}

		return $requete;
	}

    /**
     *
     * Retourne le champ à filtrer notamment utile en cas de liaison pour gérer les alias des champs
     *
     */
    public function alias_champ_requete(){

        if(!empty($this->modele->alias_champ))
            return $this->modele->alias_champ;

        return $this->modele->type_element .'.'.$this->modele->nom_sql;
    }

    /**
     *
     * Retourne la table à filtrer notamment utile en cas de liaison pour gérer les alias des champs
     *
     */
    public function alias_table_requete(){

        if(!empty($this->modele->alias_table))
            return $this->modele->alias_table;

        return $this->modele->type_element;
    }

	/**
	 *
	 * On retouche éventuellement une valeur pour l'import de données en masse
	 *
	 */
	public function prepare_pour_import($valeur) {

		return $valeur;
	}

	/**
	 *
	 * On vérifie si le champ vient d'un document
	 *
	 */
	public function verification_type_element_document() {

		$retour = false;

		if ($this->modele->type_element == "acompte_vente" || $this->modele->type_element == "acompte_achat" || $this->modele->type_element == "avoir_vente" || $this->modele->type_element == "avoir_achat" || $this->modele->type_element == "bl_vente" || $this->modele->type_element == "bl_achat" || $this->modele->type_element == "commande_vente" || $this->modele->type_element == "commande_achat" || $this->modele->type_element == "devis_achat" || $this->modele->type_element == "devis_vente" || $this->modele->type_element == "facture_achat" || $this->modele->type_element == "facture_vente" )
			$retour = true;

		return $retour;
	}


	/**
	 *
	 * Retourne true si ce champ est modifiable post validation (documents de gestion commerciale notamment), false sinon
	 *
	 * @param $management le management de l'élément en question
	 *
	 */
	public function modifiable_post_validation($management) {

		// élément non existant
		if($management->existe() === false)
			return true;

		// élément non validé
		if(empty($management->modele->valide))
			return true;

		// champ modifiable quoi qu'il en soit
		if($this->modele->modification_post_validation == 1)
			return true;

		// suivant les fonctionnalités, ce document est modifiable même s'il est validé
		if($management->verifie_si_document_modifiable_meme_si_valide())
			return true;

		return false;
	}

	protected function liste_libres() {

		if($this->modele->liste_choix == 0) {

			$id_cl = $this->modele->id_cl;
		}
		else {

			$id_cl = $this->modele->liste_choix;
		}

		$liste = cache_eden('listes_libres.'.$id_cl, function() use ($id_cl) {

			return Champ_libre_liste::where('id_cl', $id_cl)->orderBy('ordre')->get()->pluck('index_traduction', 'id_valeur')->toArray();
		});

		if($this->modele->cacher_sans_valeur == '1')
			$liste_finale = array();
		else
			$liste_finale = array('Sans valeur');

		foreach($liste as $id => $index_traduction) {

            $liste_finale[$id] = traduction($index_traduction.'.nom');
		}

		$this->valeurs_possibles = $liste_finale;
	}

	/**
	 *
	 * @note frédéric 23/03/2022 Est-ce que cette méthode est vraiment appelée ????
	 *
	 */
	protected function liste_libres_ordre() {

		if($this->modele->liste_choix == 0) {

			$id_cl = $this->modele->id_cl;
		}
		else {

			$id_cl = $this->modele->liste_choix;
		}

		$liste_ordre = Champ_libre_liste::where('id_cl', $id_cl)->orderBy('ordre')->get()->pluck('id_valeur', 'ordre')->toArray();

		$this->valeurs_possibles_ordre = $liste_ordre;
	}

	/**
	 *
	 * Retourne la bonne liste de valeurs pour les champs type liste_preenregistree et les multi sélection en liste pré enregistrées
	 *
	 *  Liste à tenir à jour SVP
	 *
     *  1 : Types de liens de menus
	 *  3 : oui (1) / non (2) / sans valeur (0)
	 *  4 : comptes bancaires
	 *  6 : conditions de paiement
	 *  7 : modes de paiement
	 *  8 : entrepots
     *  9 : couleurs d'événements Google
     *  10 : Types de fréquences pour les récurrences
     *  11 : Badges jours pour les récurrences
     *  12: Jours
     *  13: Nombres ordinaux
	 *  14 : oui (1) / non (0)
	 *  15 : catégories du blog
	 *  16 : Types de réduction pour les coupon réduction
	 *  17 : Liste des employés
	 *  18 : Liste des catégories d'activité
     *  19 : Types de comptes email
     *  20 : Restrictions ERP
     *  21 : Type de blocage blacklist email ticket_client
     *  22 : Listes des synchros possibles pour les rdv
     *  23 : Statut des participants sur les rdv
	 *  25 : Devises
	 *  26 : Langues
	 *  27 : CGV
	 *  28 : Pays
	 *  29 : comptes comptables
	 *  31 : Types d'adresse
	 *  32 : Canaux de vente
	 *  33 : Types d'échanges
	 *  34 : Statuts des paniers en ecommerce
	 *  35 : Statuts des rappels
	 *  36 : Catégories de modèles d'email
	 *  38 : Statuts des clients (client, prospect, lead)
	 *  39 : Civilités
	 *  40 : Groupes de recouvrement
	 *  41 : Origine des erreurs
	 *  42 : Origine des crédits
	 *  43 : Étape des leads
	 *  44 : Statut des leads
	 *  45 : Chaleur des leads / prospects / projets (prospection)
	 *  46 : Type de question dans les questionnaires
	 *  50 : Entrepots
	 *  51 : Statut des activités sur les projets
	 *  52 : Statut des projets
	 *  53 : Périodes de debut de demande de congés
	 *  54 : Raisons de demande de congés
	 *  55 : Types de coupons réduction
	 *  56 : Statut des transactions budget insight
	 *  57 : Type de relance
	 *  58 : Type de déclinaisons sur les articles
	 *  59 : Familles de déclinaisons
	 *  60 : Types de récurrence
	 *  61 : Modèle à utiliser pour la récurrence
	 * 	62 : Types d'article
	 * 	63 : Statut demande de prix
	 * 	65 : Statuts des bugs pour les recette clients / easy dev
	 * 	66 : Membres équipe Easy Dév
	 * 	67 : Criticité des bugs
	 * 	68 : Comptes bancaires budget insight
	 *  69 : Synchro compte mail
	 *  70 : Types d'action pour les workflow
	 *  71 : Liste des types elements
	 *  72 : Liste des triggers possibles pour les workflows
	 *  73 : Transports de notification
	 *  75 : Type de facture (avancement)
	 *  76 : Liste des fournisseurs
	 *  78 : Type de d'élément dans le tableau de bord
	 *  80 : Statut des interventions de maintenance
	 *  81 : Unités de vente pour les articles
	 *  82 : Liste des documents de gestion commerciale (Vente et achat)
	 *  83 : Liste de tous les modèles pour tous les devis_vente
	 *  84 : Liste de tous les modèles pour tous les acompte_vente
	 *  85 : Liste de tous les modèles pour tous les avoir_vente
	 *  86 : Liste de tous les modèles pour tous les bl_vente
	 *  87 : Liste de tous les modèles pour tous les facture_vente
	 *  88 : Liste de tous les modèles pour tous les avoir_achat
	 *  89 : Liste de tous les modèles pour tous les bl_achat
	 *  90 : Liste de tous les modèles pour tous les commande_achat
	 *  91 : Liste de tous les modèles pour tous les devis_achat
     *  190 : Liste de tous les modèles pour tous les acompte_achat
	 *  92 : Liste de tous les modèles pour tous les facture_achat
	 *  93 : Liste de tous les modèles pour tous les commande_vente
	 *  191 : Liste de tous les modèles pour tous les bon_preparation_vente
	 *  192 : Liste de tous les modèles pour tous les bon_retour_vente
     *  193 : Liste de tous les modèles pour tous les bon_retour_achat
	 *  94 : Thèmes de filtres
	 *  95 : Transporteurs
	 *  96 : Statuts des propositions commerciales (statut interne)
	 *  97 : Statuts des propositions commerciales (réponse)
	 *  98 : Type de contenu pour les lignes de rapports paramétrables
	 *  99 : Probabilité sur les projets
     *  100 : Statuts des devis vente
     *  101 : Statuts des commandes vente
     *  102 : Statuts des bl vente
     *  103 : Statuts des acomptes vente
     *  104 : Statuts des factures vente
     *  107 : Statuts des avoirs vente
     *  108 : Statuts des commandes achat
	 * 	109 : Type de mouvement de stock
	 *  110 : Statuts pour les lignes de BL VENTE
	 *  111 : Liste des adresses internes
	 *  112 : Type de TVA
	 *  113 : Sens de TVA
	 *  114 : Catégories comptables
     *  118 => Liste des documents de vente,
	 *  120 : Liste des types de bugs
	 *  121 : Liste des types de destinataire pour les e-mails
	 *  124 : Statuts des devis achat
	 *  125 : Statuts des commande achat
	 *  126 : Statuts des BL achat
	 *  127 : Statuts des acompte achat
	 *  128 : Statuts des facture achat
	 *  129 : Statuts des avoirs achat
	 *  130 : Années civiles
	 *  131 : Liste des types d'actions pour les approbations
     *  140 : Disponibilité des articles pour leur saisie
     *  141 : Statut bordereau
     *  143 : Liste des types de numéros de série des articles
     *  144 : Nouveaux statuts des devis, avec le "annulé"
     *  145 : Criticité des bugs pour suivi_recette_easydev



     *  149 : Status pour les approbations
	 *  150 : Status pour les tickets hotline (les clients des clients)
	 *
	 *  160 : Types d'utilisateurs

	 *  200 : Type de rubrique pour le budget

	 *  300 : Type de vue pour les vues sql
	 *  301 : Statut pour les notes de frais
	 *  302 : Statut des lignes sur les documents
	 *  303 : Statut des lignes sur les documents (commande_vente => commande_achat) (le fait de commander chez le frs)
	 *  304 : Statut des lignes sur les documents (commande_vente => commande_achat) (le fait d'attendre une livraison)

	 *  310 : Natures des articles
	 *  311 : Modèles de natures des articles
	 *  312 : Types de tableaux de bord

	 *  315 : Types de statuts docusign pour les devis

	 *  501 : comptes comptables
	 *
     *  502 : Modes de paiement insight
     *
     *
	 *  520 : Types de fichiers pour les exports comptables
     *  521 : Formats de date pour les exports comptables
	 *  523 : Aligment dans le cas du format positionné
     *
     *  530 : Types de paiements encaissements / décaissements
     *  540 : Statuts des bon de préparation vente
     *
     *  539 : Conditions des notifications manuelles
     *  541 : Type de notification manuelle
     *
     *  560 : Encryptage mail synchro
     *  561 : Type de connexion
     *
     *  570 : Statut import en cours
     *  580 : Type de message des échanges de ticket client
     *  581 : Statut de contact
     *
     *  590 : Catégorie de traductions
     *  591 : Liste stockages externes
     *  592 : Types de configuration email
     *  600 : Statuts d'annulation
     *  601 : Protocoles email
     *  602 : Types synchronisation email
     *
     *  605 : Autorisation Intranet
     *
     *  610 : Statuts campagne de prospection
     *  615 : Catégories dépenses MINDEE
     *  620 : Type destinataire email
     *  621 : Type d'élément pour les licences
     *  626 : Type de jour d'insponibilité
     *
     *  630 : Type de document : Doc Achat/Vente / Autres documents
     *  631 : Type de saisie des temps
     *  635 : Type d'application d'éco-contribution
     *  640 : Type de contrat utilisateur
     *  650 : Opérateur recherche avancée
     *
     *  700 : Type de valeur pour les chronomètres
     *  701 : Statut du suivi des jours travaillés
     *  710 : Niveau de détail modele de facturation des temps
     *  715 : Type de requête trigger eden
     *  716 : Type de champs pour l'enrichissement des données
     *  720 : Possibilité double facteur d'authentification
     *  725 : Type de service pour la synchronisation
     *  726 : Sens des données des champs pour la synchronisation
     *  727 : Type de synchronisation service externe
     *  728 : Evenements de synchronisation pour les logs
	 */
	protected function listes_preenregistrees() {

		if(Session::has('cache.listes_preenregistrees.'.$this->modele->liste_choix) && cache_actif()) {

			$this->valeurs_possibles = Session::get('cache.listes_preenregistrees.'.$this->modele->liste_choix);


			if(Session::has('cache.listes_preenregistrees_valeurs_initiales.'.$this->modele->liste_choix) && cache_actif()) {

				$this->valeurs_initiales = Session::get('cache.listes_preenregistrees_valeurs_initiales.'.$this->modele->liste_choix);
			}

			// les couleurs
			if(Session::has('cache.listes_preenregistrees_couleurs.'.$this->modele->liste_choix) && cache_actif())
				$this->couleurs = Session::get('cache.listes_preenregistrees_couleurs.'.$this->modele->liste_choix);


            if(Session::has('cache.listes_preenregistrees_couleurs.'.$this->modele->liste_choix) && cache_actif())
				$this->couleurs_polices = Session::get('cache.listes_preenregistrees_couleurs_polices.'.$this->modele->liste_choix);

			return;
		}


		$recuperation_donnees = $this->recuperer_valeur_listes_preenregistrees($this->modele->liste_choix,$this->modele);

		$this->valeurs_possibles = $recuperation_donnees['liste'];

        $liste_couleurs = $recuperation_donnees['liste_couleurs'];

        $liste_couleurs_polices = $recuperation_donnees['liste_couleurs_polices'];

		$this->valeurs_initiales = $this->valeurs_possibles;

		// on vérifie s'il y a une surcharge sur les valeurs
		$valeurs_a_formatees = Champs_liste_formatee::where(['id_liste_choix' => $this->modele->liste_choix])->orderBy('ordre')->get();

		if($valeurs_a_formatees->isNotEmpty()) {

			$liste = [];

			foreach($valeurs_a_formatees as $cl_formatee) {

				if(!empty($cl_formatee->desactivee))
					continue;

				$valeur_a_afficher = $cl_formatee->valeur;

				// Si le champ est désactivé ou si aucune valeur de surcharge est affecté, on affiche la valeur par défaut
                if(isset($this->valeurs_possibles[$cl_formatee->id_valeur])){

                    $valeur_a_afficher = $this->valeurs_possibles[$cl_formatee->id_valeur];

                    if(!empty($cl_formatee->couleur_fond))
                        $liste_couleurs[$cl_formatee->id_valeur] = $cl_formatee->couleur_fond;

                    if(!empty($cl_formatee->couleur_police))
                        $liste_couleurs_polices[$cl_formatee->id_valeur] = $cl_formatee->couleur_police;
                }

				$liste[$cl_formatee->id_valeur] = $valeur_a_afficher;
			}

			$this->valeurs_possibles = $liste;
		}

		if($liste_couleurs !== false)
			Session::put('cache.listes_preenregistrees_couleurs.'.$this->modele->liste_choix, $liste_couleurs);


		if($liste_couleurs_polices !== false)
			Session::put('cache.listes_preenregistrees_couleurs_polices.'.$this->modele->liste_choix, $liste_couleurs_polices);

        $this->couleurs = $liste_couleurs;

        $this->couleurs_polices = $liste_couleurs_polices;


		// On ne met en cache les listes oui/non, sinon ça crée des bugs lors de la surcharge des valeurs
		if($this->modele->liste_choix != 14 && $this->modele->liste_choix != 3) {

			Session::put('cache.listes_preenregistrees.'.$this->modele->liste_choix, $this->valeurs_possibles);

			Session::put('cache.listes_preenregistrees_valeurs_initiales.'.$this->modele->liste_choix, $this->valeurs_initiales);
		}
	}

	public static function recuperer_valeur_listes_preenregistrees($id_liste_choix,$modele = null,$uniquement_standard = false,$traductions = array()){

	    $liste = array();
        $liste_couleurs = false;
        $liste_couleurs_polices = false;

        /*
         *
         * ATTENTION : Utiliser uniquement cette variable pour faire appel au service listes formatées, c'est important pour récupérer les traductions standard
         *
         */
        $service_liste_formatee = service('listes_formatees',$uniquement_standard);

        // 1 : Types de liens de menus
        if($id_liste_choix == 1) {

            $liste = [
                1 => 'Liste',
                2 => 'Rapport',
                3 => 'Lien interne',
                4 => 'Lien externe',
                5 => 'Autre'
            ];
        }
        
        // oui / non / sans valeur
        if($id_liste_choix == 3) {

            if(!empty($traductions)) {

                $valeurs_listes_formatees_3_valeur_0 = 'valeurs_listes_formatees.3.valeur_0';

                if (isset($traductions['valeurs_listes_formatees.3.valeur_0']))
                    $valeurs_listes_formatees_3_valeur_0 = $traductions['valeurs_listes_formatees.3.valeur_0'];

                $valeurs_listes_formatees_3_valeur_1 = 'valeurs_listes_formatees.3.valeur_1';

                if (isset($traductions['valeurs_listes_formatees.3.valeur_1']))
                    $valeurs_listes_formatees_3_valeur_1 = $traductions['valeurs_listes_formatees.3.valeur_1'];

                $valeurs_listes_formatees_3_valeur_2 = 'valeurs_listes_formatees.3.valeur_2';

                if (isset($traductions['valeurs_listes_formatees.3.valeur_2']))
                    $valeurs_listes_formatees_3_valeur_2 = $traductions['valeurs_listes_formatees.3.valeur_2'];

            }
            else{
                $valeurs_listes_formatees_3_valeur_0 = traduction('valeurs_listes_formatees.3.valeur_0');
                $valeurs_listes_formatees_3_valeur_1 = traduction('valeurs_listes_formatees.3.valeur_1');
                $valeurs_listes_formatees_3_valeur_2 = traduction('valeurs_listes_formatees.3.valeur_2');
            }

            // Si on a une liste de valeurs définies
            if(!empty($modele->contenu) && is_array(json_decode($modele->contenu))) {

                $liste = json_decode($modele->contenu);

                if($liste[0] == 'icone')
                    $liste = array(0 => $valeurs_listes_formatees_3_valeur_0, 1 => $valeurs_listes_formatees_3_valeur_1, 2 => $valeurs_listes_formatees_3_valeur_2);

                else {

                    // On va chercher les traductions
                    foreach ($liste as $clef => $valeur) {

                        $index_traduction = 'valeurs_listes_formatees.3.'.$modele->type_element . '.' . $modele->nom_sql . '.valeur_' . $clef;

                        if(!empty($traductions)) {

                            $traduction = $index_traduction;

                            if(isset($traductions[$index_traduction]))
                                $traduction = $traductions[$index_traduction];
                        }
                        else
                            $traduction = traduction($index_traduction);

                        if((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction){

                            service('traduction')->calcul_index_traduction(
                                9,
                                array(
                                    'valeurs_listes_formatees',
                                    $id_liste_choix,
                                    $modele->type_element,
                                    $modele->nom_sql
                                ),
                                array(
                                    'valeur_'.$clef => $valeur,
                                )
                            );
                        }
                        else
                            $liste[$clef] = $traduction;
                    }
                }

                // Sinon, on renvoie les valeurs normales
            } else {

                $liste = array(0 => $valeurs_listes_formatees_3_valeur_0, 1 => $valeurs_listes_formatees_3_valeur_1, 2 => $valeurs_listes_formatees_3_valeur_2);
            }

        }

        // Noms des couleurs dans la maquette
        if($id_liste_choix == 2) {

            $liste = [

                1 => "Nom de l'application",
                2 => "Background navbar",
                3 => "Background menus",
                4 => "Background menus extranet",
                5 => "Background menus au survol",
                6 => "Background sous-menus",
                7 => "Textes du menu",
                8 => "Liens",
                9 => "Background par défaut des tâches",
                10 => "Police par défaut des tâches",
                11 => "Highcharts",
            ];
        }

        // compte bancaire
        if($id_liste_choix == 4) {

            $liste = modele('compte_bancaire')->get()->pluck('nom', 'id')->toArray();
        }

        // conditions de paiement
        if($id_liste_choix == 6) {

            $liste = modele('modalite_paiement')->orderBy('ordre')->get()->pluck('nom', 'id')->toArray();
        }

        // mode de paiement
        if($id_liste_choix == 7) {

            $liste = modele('mode_paiement')->get()->pluck('nom', 'id')->toArray();
        }

        // entrepôt
        if($id_liste_choix == 8) {

            $liste = modele('entrepot')->get()->pluck('nom', 'id')->toArray();
        }

        // couleurs d'événements Google
        if($id_liste_choix == 9) {

             $liste = [
                 1 => '#7986CB',
                 2 => '#33B679',
                 3 => '#8E24AA',
                 4 => '#E67C73',
                 5 => '#F6BF26',
                 6 => '#F4511E',
                 7 => '#039BE5',
                 8 => '#616161',
                 9 => '#3F51B5',
                 10 => '#0B8043',
                 11 => '#D50000',
             ];
        }

        // Fréquences de récurrence
        if($id_liste_choix == 10) {

            $liste = [
                1 => 'Jour(s)',
                2 => 'Semaine(s)',
                3 => 'Mois',
                4 => 'An(s)',
            ];
        }

        // Valeurs badges jours récurrence
        if($id_liste_choix == 11) {

            $liste = [
                1 => 'L',
                2 => 'Ma',
                3 => 'Me',
                4 => 'J',
                5 => 'V',
                6 => 'S',
                7 => 'D',
            ];
        }

        // Jours
        if($id_liste_choix == 12) {

            $liste = [
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
                7 => 'Dimanche',
            ];
        }

        // Nombres ordinaux
        if($id_liste_choix == 13) {

            $liste = [
                1 => 'Premier',
                2 => 'Deuxième',
                3 => 'Troisième',
                4 => 'Quatrième',
                5 => 'Dernier',
            ];
        }

        // Catégories du blog
        if($id_liste_choix == 15) {

            $liste = modele('blog_categorie')->get()->pluck('nom', 'id')->toArray();
        }

        // Types de réduction pour les coupon réduction
        if($id_liste_choix == 16) {

            $liste = array(0 => '', 1 => 'En montant', 2 => 'En %');
        }

        // Liste des employés
        if($id_liste_choix == 17) {

//            $liste = modele('employe')->select(DB::raw("CONCAT(prenom,' ',nom) as nom_employe, id"))->get()->pluck('nom_employe', 'id')->toArray();

            $liste = [];
        }

        // Liste des catégories d'activité
        if($id_liste_choix == 18) {

            $liste = modele('categorie_activite')->get()->pluck('nom', 'id')->toArray();
        }

        // Liste des types de compte email
        if($id_liste_choix == 19) {

            $liste = [
                0 => 'Standard',
                1 => 'Office 365'
            ];
        }

        // Restrictions ERP : endroits où l'utilisateur ne doit pas être affiché
        if($id_liste_choix == 20) {

            $liste = [
                1 => 'Calendrier',
                2 => 'Planning'
            ];
        }

        //Type de blocage blacklist email ticket_client
        if($id_liste_choix == 21) {

            $liste = [
                1 => 'Adresse email',
                2 => 'Nom de domaine'
            ];
        }

        //Listes des synchros possibles pour les rdv
        if($id_liste_choix == 22) {

            $liste = [
                1 => 'Microsoft',
                2 => 'Google',
            ];
        }

        //Statut des participants sur les rdv
        if($id_liste_choix == 23) {

            $liste = [
                0 => 'En attente',
                1 => 'Accepté',
                2 => 'Refusé',
            ];
        }

        // compte comptable (doublon liste #29)
        if($id_liste_choix == 501) {

            $liste = modele('compte_comptable')->select(DB::raw("CONCAT(numero_de_compte,' ',libelle) as nom, id"))->get()->pluck('nom', 'id')->toArray();
        }

        // Devises
        if($id_liste_choix == 25) {

            $liste = modele('devise')->select(DB::raw("CONCAT(nom,' (',code,')') as chaine_devise, id"))->where('disponible',1)->get()->pluck('chaine_devise', 'id')->toArray();

        }

        // Langues
        if($id_liste_choix == 26) {

            $liste = array(

                1 => 'Français',
                2 => 'Anglais',
            );

        }

        // CGV
        if($id_liste_choix == 27) {

            $liste = modele('cgv')->get()->pluck('titre', 'id')->toArray();

        }

        // Pays
        if($id_liste_choix == 28) {

            $liste = modele('pays')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();

        }

        // Comptes comptables
        if($id_liste_choix == 29) {

            $liste = modele('compte_comptable')->select(DB::raw("CONCAT(numero_de_compte,' ',libelle) as nom_compte, id"))->get()->pluck('nom_compte', 'id')->toArray();

        }

        // Types d'adresse
        if($id_liste_choix == 31) {

            $liste = array(

                0 => 'Indéfini',
                1 => 'Facturation',
                2 => 'Livraison',
                3 => 'Livraison et Facturation',
                4 => 'Siège',
            );

        }

        // Canaux de vente
        if($id_liste_choix == 32) {

            $liste = modele('canal_de_vente')->get()->pluck('nom', 'id')->toArray();

        }



        // Types d'échanges
        if($id_liste_choix == 33) {

            $liste = $service_liste_formatee->types_echange();
        }

        // Statuts des paniers en ecommerce
        if($id_liste_choix == 34) {

            $liste = array(

                0 => 'En cours',
                1 => 'Validé',
            );

        }

        // Statuts des rappels
        if($id_liste_choix == 35) {

            $liste = array(

                0 => 'Enregistré',
                1 => 'En cours',
                2 => 'Terminé',
            );

        }

        // Catégories de modèles d'emails
        if($id_liste_choix == 36) {

            $liste = array(

                1 => 'Prospection',
                2 => 'Administratif',
                3 => 'Recouvrement',
                4 => 'Transactionnel',
                5 => 'Divers',
            );
        }

        // Statuts des clients (client, prospect, lead)
        if($id_liste_choix == 38) {

            $liste = array(

                1 => 'Client',
                2 => 'Prospect',
                3 => 'Lead',
            );
        }

        // Civilités
        if($id_liste_choix == 39) {

            $liste = array(

                1 => 'M.',
                2 => 'Mme',
                3 => 'Melle',
                4 => 'Dr.',
                5 => 'Me',
            );
        }

        // Groupes de recouvrement
        if($id_liste_choix == 40) {

            $liste = modele('groupe_recouvrement')->get()->pluck('nom', 'id')->toArray();

            $liste[0] = "Aucun groupe";

        }

        // Origine des erreurs
        if($id_liste_choix == 41) {

            $liste[0] = "Non spécifié";
            $liste[1] = "ERP";
            $liste[2] = "Ecommerce 1";
            $liste[3] = "Ecommerce 2";
            $liste[4] = "Blog 1";
            $liste[5] = "Blog 2";

        }



        // Origine des crédits
        if($id_liste_choix == 42) {

            $liste[0] = "Non spécifiée";
            $liste[1] = "Parrainage";
            $liste[2] = "Crédits automatiques";
            $liste[3] = "Geste commercial";
            $liste[4] = "Saisie manuelle";

        }

        // Etape des leads
        if($id_liste_choix == 43) {

            $liste[0] = "Non spécifié";
            $liste[1] = "Prospect";
            $liste[2] = "Négociation";
            $liste[3] = "Accord oral";
            $liste[4] = "Transformé en client";

            // on va chercher les couleurs
            $liste_couleurs = array(
                0 => '#ade5f0',
                1 => '#75d3e6',
                2 => '#47abb3',
                3 => '#51b6bd',
                4 => '#5abf93',
            );
        }

        // Statut des leads
        if($id_liste_choix == 44) {

            $liste[0] = "En cours";
            $liste[1] = "Process terminé";
            $liste[2] = "Statut 3";
            $liste[3] = "Statut 4";
            $liste[4] = "Statut 5";
            $liste[5] = "Statut 6";
            $liste[6] = "Statut 7";
            $liste[7] = "Statut 8";
            $liste[8] = "Statut 9";
            $liste[9] = "Statut 10";
            $liste[10] = "Statut 11";
            $liste[11] = "Statut 12";
            $liste[12] = "Statut 13";
            $liste[13] = "Statut 14";
            $liste[14] = "Statut 15";

            // on va chercher les couleurs
            $liste_couleurs = array(
                0 => '#8cc760',
                1 => '#DCDCDC',
            );

            // on va chercher les couleurs
            $liste_couleurs_polices = array(
                1 => 'unset',
            );
        }

        // Chaleur des leads / prospects / projets (prospection)
        if($id_liste_choix == 45) {

            $liste[0] = "Non spécifié";
            $liste[1] = "Froid";
            $liste[2] = "Tiède";
            $liste[3] = "Chaud";
            $liste[4] = "Bouillant";

        }

        // Type de question dans les questionnaires
        if($id_liste_choix == 46) {

            $liste[1] = "Note";
            $liste[2] = "Commentaire";
            $liste[5] = "Liste";
            $liste[6] = "Date";
            $liste[7] = "Texte";
            $liste[8] = "Choix multiples";
            $liste[9] = "Oui/Non";
        }

        // Entrepots
        if($id_liste_choix == 50) {

            $liste = modele('entrepot')->get()->pluck('nom', 'id')->toArray();

        }

        // Statut des activités sur les projets
        if($id_liste_choix == 51) {

            $liste[0] = "Enregistrée";
            $liste[1] = "En cours";
            $liste[2] = "Terminée";
            $liste[3] = "Vérifiée";

        }

        // Statut des projets
        // cette liste est un peu particulière,
        // chaque projet a un statut plus global impliqué par le statut de cette liste
        // si le statut <= 100 => le projet est en prospection
        // si le statut <= 200 => le projet est en cours de prod
        // si le statut >= 300 => le projet est clôturé
        if($id_liste_choix == 52) {

            $liste[0] = "Sans statut";
            $liste[10] = "Prospect";
            $liste[15] = "Premier contact effectué";
            $liste[20] = "Premier RDV effectué";
            $liste[25] = "Discussion avancée";
            $liste[30] = "Proposition envoyée";
            $liste[50] = "En attente";
            $liste[150] = "Gagné";
            $liste[220] = "En cours";
            $liste[250] = "Facturé";
            $liste[320] = "Terminé";
            $liste[350] = "Clôturé";

        }

        // Périodes de debut de demande de congés
        if($id_liste_choix == 53) {

            $liste[0] = "Matin";
            $liste[1] = "Après-midi";
            // $liste[2] = "Journée entière";

        }

        // Raisons de demande de congés
        if($id_liste_choix == 54) {

            $liste[0] = "CP";
            $liste[1] = "RTT";
            $liste[2] = "Congés sans solde";
            $liste[3] = "Enfant malade";
            $liste[4] = "Mariage";
            $liste[5] = "Décès d'un proche";
            $liste[20] = "Absence maladie";
            $liste[6] = "Autre";

            $liste[7] = "Autre 2";
            $liste[8] = "Autre 3";
            $liste[9] = "Autre 4";
            $liste[10] = "Autre 5";
            $liste[11] = "Autre 6";
            $liste[12] = "Autre 7";
            $liste[13] = "Autre 8";
            $liste[14] = "Autre 9";
            $liste[15] = "Autre 10";
            $liste[16] = "Autre 11";

        }

        // Types de coupons réduction
        if($id_liste_choix == 55) {

            $liste = array(3 => 'Offre promotionnelle', 1 => 'Chèque fidélité', 2 => 'Chèque cadeau');

        }

        // Statut des transactions budget insight
        if($id_liste_choix == 56) {

            $liste = array(

                0 => 'Non traitée',
                1 => 'Partiellement rapprochée',
                2 => 'Rapprochée',
                3 => 'Reportée',
            );

        }

        // Type de relance
        if($id_liste_choix == 57) {

            $liste = modele('type_relance_recouvrement')->get()->pluck('nom', 'id')->toArray();

        }

        // Types de déclinaisons sur les articles
        if($id_liste_choix == 58) {

            $liste = array(

                0 => 'Aucune',
                1 => 'Unique',
                2 => 'Multiple',
            );

        }

        // Familles de déclinaisons
        if($id_liste_choix == 59) {

            $liste = modele('famille_declinaison')->get()->pluck('nom', 'id')->toArray();

        }

        // Types de récurrence
        if($id_liste_choix == 60) {

            $liste = array(

                0 => 'Mensuelle',
                1 => 'Période définie',
                2 => 'Annuelle',
                3 => 'Trimestrielle',
            );

        }

        // Modèle à utiliser pour la récurrence
        if($id_liste_choix == 61) {

            $liste = array(

                0 => 'Le 1er élément généré',
                1 => 'Le dernier élément généré',
            );

        }

        // Type d'article
        if($id_liste_choix == 62) {

            $liste = array(

                0 => 'Article classique',
                1 => 'Nomenclature (non stockable)',
                3 => 'Produit assemblé (stockable)',
                2 => 'Article frais de port',
            );

        }

        // Type statut demande de prix
        if($id_liste_choix == 63) {

            $liste = array(

                0 => 'Non traitée',
                1 => 'Traitée',
                2 => 'Abandonnée',
                3 => 'En attente retour client',
            );

        }

        // statuts des bugs remontés lors des recettes Easy Dév / Clients
        if($id_liste_choix == 65) {

            $liste = array(

                5 => 'A spécifier',
                7 => 'Ouvert',
                2 => 'À tester',
                3 => 'Cloturé',
            );

		}

        // équipe easy dév pour la gestion des bugs
        if($id_liste_choix == 66) {

            $liste = array(

                0 => 'Non affecté',
                1 => 'Frédéric Bry',
                2 => 'Samir Guenouni',
                3 => 'Steven Proag',
                4 => 'Emmanuel Bouillon',
                5 => 'Matthieu Villalonga',
            );

        }

        // Criticité des bugs pour ticket_client
        if($id_liste_choix == 67) {

            $liste = array(

                0 => 'Non précisé',
                1 => 'Faible',
                2 => 'Moyenne',
                3 => 'Urgent',
                4 => 'Critique',
            );

        }

        // Criticité des bugs pour suivi_recette_easydev
        if($id_liste_choix == 145) {

            $liste = array(

                0 => 'Non précisé',
                1 => 'Faible',
                2 => 'Moyenne',
                3 => 'Urgent',
                4 => 'Critique',
            );

        }

        // 68 : Comptes bancaires budget insight
        if($id_liste_choix == 68) {

            $liste = modele('budget_insight_comptes')->get()->pluck('original_name', 'id')->toArray();

        }

        // 69 : Synchros mails
        if($id_liste_choix == 69) {

            $liste = modele('synchro_mail')->get()->pluck('identifiant', 'id')->toArray();

        }

        // 70 : Types d'action pour les workflow
        if($id_liste_choix == 70) {

            $liste = array(

                1 => 'Envoyer un email',
                2 => 'Modifier un élément',
                3 => 'Créer un élément',
                4 => 'Notifier',
            );

        }

        // 71 : Liste des type element
        if($id_liste_choix == 71) {

            $liste = Table_libre::leftJoin('traduction_valeur',function($join) {
                    $join->on('index',DB::raw("CONCAT(index_traduction,'.nom_table')"))
                        ->where(function($condition){
                            $condition->where('traduction_valeur.inactif',0)
                                ->orWhereNull('traduction_valeur.inactif');
                        })
                        ->where('langue','fr');
                })
                ->where(function($condition){
                    $condition->where('table_systeme',0)
                        ->orWhereNull('table_systeme');
                })
                ->select(DB::raw('IF(COALESCE(traduction_specifique,traduction_standard) IS NULL,eden_tableslibres.type_element,CONCAT(COALESCE(traduction_specifique,traduction_standard), " (",eden_tableslibres.type_element,")")) as nom_element'),'eden_tableslibres.id')
                ->orderBy('nom_element')->get()->pluck('nom_element', 'id')->toArray();
        }

        // 72 : Liste des triggers possibles pour les workflows
        if($id_liste_choix == 72) {

            $liste = array(

                1 => 'Création',
                2 => 'Modification',
                3 => 'Suppression',
                4 => 'Validation',
                5 => 'Acceptation (devis)',
            );

        }

        // 73 : Transports de notification
        if($id_liste_choix == 73) {

            $liste = array(

                1 => 'Notification (navbar)',
                2 => 'Email',
            );

        }

        // 75 : Type de facture (avancement)
        if($id_liste_choix == 75) {

            $liste = array(

                0 => 'Classique',
                1 => 'Facture d\'avancement',
                2 => 'Situation finale',
            );

        }

        // 76 : Liste des fournisseurs
        if($id_liste_choix == 76) {

            $liste = modele('fournisseur')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();

        }

        // 78 : Type de d'élément dans le tableau de bord
        if($id_liste_choix == 78) {

            $liste = array(

                1 => 'Rapport',
                2 => 'Bloc',
				3 => 'Composant',
				4 => 'Bloc HTML',
                //2 => 'Formulaire',
            );

        }

        // 80 : Statut des interventions de maintenance
        if($id_liste_choix == 80) {

            $liste = array(

                1 => 'Date proposée',
                2 => 'Date fixée',
                3 => 'Réalisée',
                4 => 'Facturée',
            );

        }

        // 81 : Unités de vente pour les articles
        if($id_liste_choix == 81) {

            $liste = modele('article_unite')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();

        }

        // 83 : Liste de tous les modèles pour tous le type_element devis_vente
        if($id_liste_choix == 83) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('devis_vente');
        }

        // 84 : Liste de tous les modèles pour tous le type_element acompte_vente
        if($id_liste_choix == 84) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('acompte_vente');
        }

        // 85 : Liste de tous les modèles pour tous le type_element avoir_vente
        if($id_liste_choix == 85) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('avoir_vente');
        }

        // 86 : Liste de tous les modèles pour tous le type_element bl_vente
        if($id_liste_choix == 86) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('bl_vente');
        }

        // 87 : Liste de tous les modèles pour tous le type_element facture_vente
        if($id_liste_choix == 87) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('facture_vente');
        }

        // 88 : Liste de tous les modèles pour tous le type_element avoir_achat
        if($id_liste_choix == 88) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('avoir_achat');
        }

        // 89 : Liste de tous les modèles pour tous le type_element bl_achat
        if($id_liste_choix == 89) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('bl_achat');
        }

        // 90 : Liste de tous les modèles pour tous le type_element commande_achat
        if($id_liste_choix == 90) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('commande_achat');
        }

        // 91 : Liste de tous les modèles pour tous le type_element devis_achat
        if($id_liste_choix == 91) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('devis_achat');
        }

        // 92 : Liste de tous les modèles pour tous le type_element facture_achat
        if($id_liste_choix == 92) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('facture_achat');
        }

        // 93 : Liste de tous les modèles pour tous le type_element commande_vente
        if($id_liste_choix == 93) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('commande_vente');
        }

        // 190 : Liste de tous les modèles pour tous le type_element acompte_achat
        if($id_liste_choix == 190) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('acompte_achat');
        }

        // 191 : Liste de tous les modèles pour tous le type_element bon_preparation_vente
        if($id_liste_choix == 191) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('bon_preparation_vente');
        }

        // 192 : Liste de tous les modèles pour tous le type_element bon_retour_vente
        if($id_liste_choix == 192) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('bon_retour_vente');
        }

        // 193 : Liste de tous les modèles pour tous le type_element bon_retour_achat
        if($id_liste_choix == 193) {
            $liste = $service_liste_formatee->recupere_modeles_de_document_par_type_element('bon_retour_achat');
        }

        // 94 : Thèmes de filtres
        if($id_liste_choix == 94) {

            $liste = modele('theme_de_filtres')->get()->pluck('nom', 'id')->toArray();

        }

        // 95 : Transporteurs
        if($id_liste_choix == 95) {

            $liste = modele('transporteur')->get()->pluck('nom', 'id')->toArray();

        }

        // 96 : Etapes des propositions commerciales
        if($id_liste_choix == 96) {

            $liste = array(
                1 => 'Brouillon',
                2 => 'Prête',
                3 => 'Relue',
                4 => 'Envoyée au prospect',
                5 => 'Abondonnée',
            );

        }

        // 97 : Statuts des propositions commerciales
        if($id_liste_choix == 97) {

            $liste = array(
                0 => 'En attente de réponse',
                1 => 'Acceptée',
                2 => 'Refusée',
            );

        }

        // 98 : Type de contenu pour les lignes de rapports paramétrables
        if($id_liste_choix == 98) {

            $liste = array(
                0 => 'Ventes',
                1 => 'Achats',
                2 => 'Ventes - achats',
            );

        }

        // 99 : Probabilité sur les projets
        if($id_liste_choix == 99) {

            $liste = array(
                0 => '0%',
                10 => '10%',
                20 => '20%',
                30 => '30%',
                40 => '40%',
                50 => '50%',
                60 => '60%',
                70 => '70%',
                80 => '80%',
                90 => '90%',
                100 => '100%',
            );

        }

        // 100 : Status des devis vente
        if($id_liste_choix == 100) {

			$liste = $service_liste_formatee->statuts_devis_vente();
        }

        // 101 : Status des commandes vente
        if($id_liste_choix == 101) {

            $liste = $service_liste_formatee->statuts_commandes_vente();

        }

        // 102 : Status des bl vente
        if($id_liste_choix == 102) {

            $liste = $service_liste_formatee->statuts_bl_vente();
        }

        // 103 : Status des acomptes vente
        if($id_liste_choix == 103) {

			$liste = $service_liste_formatee->statuts_acomptes_vente();
        }

        // 104 : Status des factures vente
        if($id_liste_choix == 104) {

			$liste = $service_liste_formatee->statuts_factures_vente();
		}

        // 107 : Status des avoirs vente
        if($id_liste_choix == 107) {

			$liste = $service_liste_formatee->statuts_avoirs_vente();
        }

		// 540 : Status des bons de preparation vente
        if($id_liste_choix == 540) {

            $liste = $service_liste_formatee->statuts_bon_preparation_vente();
        }

        // 108 : Status des commandes achat
        if($id_liste_choix == 108) {

            $liste = array(
                0 => 'Sans statut',
                10 => 'Attente retour fournisseur',
                15 => 'Réceptionnée partiellement',
                20 => 'Réceptionnée',
            );

        }

        // 109 : Type de mouvement de stock
        if($id_liste_choix == 109) {

            $liste = array(
                0 => 'Mouvements classiques',
                1 => 'Retour fournisseur',
                2 => 'Correction inventaire',
                3 => 'Casse / Disparitions',
                4 => 'Péremption',
                5 => "Transfert inter entrepôts",
                6 => 'Production nomenclature'
            );

        }

        // 110 : Préparation partielle
        if($id_liste_choix == 110) {

            $liste = array(
                0 => 'Non préparé',
                1 => 'Préparé',
            );

        }


        // 111 : Liste des adresses internes
        if($id_liste_choix == 111) {

            $liste = modele('adresse_interne')->select(DB::raw("CONCAT(societe,', ',adresse,', ',code_postal,' ',ville) as nom, id"))->get()->pluck('nom', 'id')->toArray();

        }

        // 112 : Type de TVA
        if($id_liste_choix == 112) {

            $liste = array(
                0 => 'Débits',
                1 => 'Encaissements',
            );

        }

        // 113 : Sens de TVA
        if($id_liste_choix == 113) {

            $liste = array(
                0 => 'Collectée',
                1 => 'Déductible',
                2 => 'Autoliquidation',
            );

        }

        // 114 : Catégories comptables
        if($id_liste_choix == 114) {

            $liste = modele('categorie_comptable')->get()->pluck('code', 'id')->toArray();

        }

        // 120 : Liste des types de bugs
        if($id_liste_choix == 120) {

            $liste = array(
                0 => 'Hotline',
                1 => 'Développement',
                // 2 => 'Amélioration'
            );

        }


        // 130 : Années civiles
        if($id_liste_choix == 130) {

            $liste = array();

            for($i=2010; $i<=date('Y')+5; $i++)
                $liste[$i] = $i;

        }

        // 121 : Liste des types d'actions
        if($id_liste_choix == 121) {

            $liste = array(
                0 => 'Destinataire classique',
                1 => 'Copie',
                2 => 'Copie cachée',
            );

        }

		// 124 : Statuts des devis achat
		if($id_liste_choix == 124) {

			$liste = $service_liste_formatee->statuts_devis_achat();
        }

		// 125 : Statuts des commande achat
		if($id_liste_choix == 125) {

			$liste = $service_liste_formatee->statuts_commande_achat();
        }

		// 126 : Statuts des BL achat
		if($id_liste_choix == 126) {

			$liste = $service_liste_formatee->statuts_bl_achat();
        }

		// 127 : Statuts des acompte achat
		if($id_liste_choix == 127) {

			$liste = $service_liste_formatee->statuts_acompte_achat();
        }

		// 128 : Statuts des facture achat
		if($id_liste_choix == 128) {

			$liste = $service_liste_formatee->statuts_facture_achat();
        }

		// 129 : Statuts des avoirs achat
		if($id_liste_choix == 129) {

			$liste = $service_liste_formatee->statuts_avoir_achat();
        }



        // 131 : Liste des types d'actions pour les approbations
        if($id_liste_choix == 131) {

            $liste = array(
                0 => 'Relecture',
                1 => 'Validation',
                2 => 'Refus',
                3 => 'Demande manuelle',
            );

        }

        // 149 : Status pour les approbations
        if($id_liste_choix == 149) {

            $liste = array(
                0 => 'En attente',
                1 => 'Validée',
                2 => 'Refusée',
            );

			// on va chercher les couleurs
            $liste_couleurs = array(
				0 => '#DCDCDC',
				1 => '#669e24',
				2 => '#ed6f56',
			);

            $liste_couleurs_polices = array(
				0 => '#212121',
				1 => '#ffffff',
				2 => '#ffffff',
			);
		}

        // 150 : Status pour les tickets hotline (les clients des clients)
        if($id_liste_choix == 150) {

            $liste = array(
                0 =>    'En attente',
                10 =>   'En cours',
                20 =>   'Besoin de précision',
                30 =>   'Résolu',
                50 =>   'Clôturé',
                60 =>   'Clôturé extranet',
                70 =>   'Statut 2',
                80 =>   'Statut 3',
                90 =>   'Statut 4',
                100 =>  'Statut 5',
            );
		}

        // 160 : Type d'utilisateur
        if($id_liste_choix == 160) {

            $liste = array(
                0 => 'Simple',
                1 => 'Administrateur',
                2 => 'Éditeur',
                3 => 'Ressource',
            );
		}

        // 200 : Type de rubrique pour le budget
        if($id_liste_choix == 200) {

            $liste = array(
                0 => 'Saisie',
                1 => 'Salaires',
            );
		}

        // oui / non
        if($id_liste_choix == 14) {

            if(!empty($traductions)) {

                $valeurs_listes_formatees_14_valeur_0 = 'valeurs_listes_formatees.14.valeur_0';

                if (isset($traductions['valeurs_listes_formatees.14.valeur_0']))
                    $valeurs_listes_formatees_14_valeur_0 = $traductions['valeurs_listes_formatees.14.valeur_0'];

                $valeurs_listes_formatees_14_valeur_1 = 'valeurs_listes_formatees.14.valeur_1';

                if (isset($traductions['valeurs_listes_formatees.14.valeur_1']))
                    $valeurs_listes_formatees_14_valeur_1 = $traductions['valeurs_listes_formatees.14.valeur_1'];

            }
            else{
                $valeurs_listes_formatees_14_valeur_0 = traduction('valeurs_listes_formatees.14.valeur_0');
                $valeurs_listes_formatees_14_valeur_1 = traduction('valeurs_listes_formatees.14.valeur_1');
            }

            // Si on a une liste de valeurs définies
            if(!empty($modele->contenu) && is_array(json_decode($modele->contenu))) {

                $liste = json_decode($modele->contenu);

                if($liste[0] == 'icone')
                    $liste = array(0 => $valeurs_listes_formatees_14_valeur_0, 1 => $valeurs_listes_formatees_14_valeur_1);

                else {
                    // On va chercher les traductions
                    foreach ($liste as $clef => $valeur) {

                        $index_traduction = 'valeurs_listes_formatees.14.'.$modele->type_element . '.' . $modele->nom_sql . '.valeur_' . $clef;

                        if(!empty($traductions)) {

                            $traduction = $index_traduction;

                            if(isset($traductions[$index_traduction]))
                                $traduction = $traductions[$index_traduction];
                        }
                        else
                            $traduction = traduction($index_traduction);

                        if((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction){

                            service('traduction')->calcul_index_traduction(
                                9,
                                array(
                                    'valeurs_listes_formatees',
                                    $id_liste_choix,
                                    $modele->type_element,
                                    $modele->nom_sql
                                ),
                                array(
                                    'valeur_'.$clef => $valeur,
                                )
                            );
                        }
                        else
                            $liste[$clef] = $traduction;
                    }
                }

                // Sinon, on renvoie les valeurs normales
            } else{

                $liste = array(0 => $valeurs_listes_formatees_14_valeur_0, 1 => $valeurs_listes_formatees_14_valeur_1);
            }

            if(empty($liste[0]))
                $liste[0] = $valeurs_listes_formatees_14_valeur_0;

            if(empty($liste[1]))
                $liste[1] = $valeurs_listes_formatees_14_valeur_1;

        }

        // 117 : Liste du theme pour un indicateur dans un bloc
        if($id_liste_choix == 117) {

            $liste = array(

                1 => 'Prioritaire',
                2 => 'Secondaire',
            );

        }

        // 116 : Liste des exercices
        if($id_liste_choix == 116) {

            $liste = modele('exercice')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();

        }

        //140 : Disponibilité des articles pour leur saisie
        if($id_liste_choix == 140) {

            $liste = array(

                0 => 'Pour tous les documents',
                1 => 'Uniquement pour les ventes',
                2 => 'Uniquement pour les achats',
                3 => 'Non utilisable',
                4 => 'Uniquement pour les Avoirs et les Bons de retour Ventes',
            );

        }

        //143 : Liste des types de numéros de série des articles
        if($id_liste_choix == 143) {

            $liste = array(

                0 => 'Pas de numéro',
                1 => 'Numéro unique',
                2 => 'Numéro multiple',
            );

        }

        // 144 : Nouveaux statuts des devis, avec le "annulé"
        if($id_liste_choix == 144) {

            $liste = array(

                0 => 'En attente',
                1 => 'Accepté',
                2 => 'Refusé',
                3 => 'Annulé',
            );

        }

        // statut bordereau
        if($id_liste_choix == 141) {

            $liste = array(
                0 => 'Saisie',
                10 => 'Rapproche',
            );

        }

        if($id_liste_choix == 502){

            $liste = array(
                0 => 'Inconnu',
                1 => 'Prélèvement',
                2 => 'Chèque',
                3 => 'Dépôt',
                4 => 'Carte bancaire',
                5 => 'Carte récapitulative',
                6 => 'Retrait',
                7 => 'Virement',
                8 => 'Commissions',
                9 => 'Remboursement',
                10 => 'Remboursement de prêt',
                11 => 'Carte à paiement différé',
            );

        }



        if($id_liste_choix == 300){

            $liste = array(
                0 => 'Select',
                1 => 'Union',
            );

        }

        // Statut note de frais
        if($id_liste_choix == 301){

            $liste = array(
                0 => 'En attente',
                1 => 'Validée',
                2 => 'Payée',
            );

        }

        // Statut des lignes sur les documents
        if($id_liste_choix == 302){

            $liste = array(
                0 => 'Non traité',
                1 => 'Partiellement traité',
                2 => 'Traité',
                3 => 'Annulé',
            );
        }

        // Statut des lignes sur les documents (commande_vente => commande_achat) => le fait de commander chez le fournisseur
        if($id_liste_choix == 303){

            $liste = array(
                0 => 'Non commandé',
                1 => 'Partiellement commandé',
                2 => 'Commandé',
                3 => 'Pris sur stock',
            );
        }

        // Statut des lignes sur les documents (commande_vente => commande_achat) => livraison
        if($id_liste_choix == 304){

            $liste = array(
                0 => 'Non reçu',
                1 => 'Partiellement reçu',
                2 => 'Reçu',
                3 => 'Pris sur stock',
            );
		}

        // 310 : Natures des articles
        if($id_liste_choix == 310){

            $liste = modele('nature_article')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();
		}


        // 311 : Modèles de natures des articles
        if($id_liste_choix == 311){

            $liste = modele('nature_article_modele')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();
		}

        // 312 : Types de tableaux de bord
        if($id_liste_choix == 312){

            $liste = array(

				0 => 'Full paramétrable',
				1 => 'Liste de rapports',
			);
		}

        // 315 : Types de statuts docusign pour les devis
		if($id_liste_choix == 315){

            $liste = array(

				0 => 'Non envoyé',
				1 => 'Corrigé',
                2 => 'Signé',
                3 => 'Créé',
                4 => 'Refusé',
                5 => 'Supprimé',
                6 => 'Délivré',
                7 => 'Envoyé',
                8 => 'Signé',
                9 => 'Transféré',
                10 => 'Invalidé'
			);
		}

        // Types de tache todo
        if($id_liste_choix == 503) {

            $liste = $service_liste_formatee->types_tache_todo();
        }

        // Types de tache rdv
        if($id_liste_choix == 504) {

            $liste = $service_liste_formatee->types_tache_rdv();

            // Traitement spécifique en fonction des équipes
            if(moi() != null && property_exists(moi(),'equipe') && moi()->equipe != null) {

                $type_rendez_vous_disponible = \DB::table('equipe_type_rendez_vous_disponible')->where('cle_locale', moi()->equipe)->get()->pluck('valeur')->toArray();

                foreach($liste as $index_liste => $element_liste){

                    if(!in_array($index_liste,$type_rendez_vous_disponible))
                        unset($liste[$index_liste]);
                }

            }

        }

        // Liste des comptes emails
        if($id_liste_choix == 505) {

            $liste = modele('compte_email')->orderBy('adresse_email')->get()->pluck('adresse_email', 'id')->toArray();
        }

        // Liste des comptes emails publics
        if($id_liste_choix == 506) {

            $liste = modele('compte_email')->where('mail_public', 1)->orderBy('adresse_email')->get()->pluck('adresse_email', 'id')->toArray();
		}

        // Liste des questionnaires
        if($id_liste_choix == 507) {

            $liste = modele('questionnaire')->get()->pluck('nom', 'id')->toArray();
        }

		// 520 : Types de fichiers pour les exports comptables
        if($id_liste_choix == 520){

            $liste = array(

				1 => 'CSV (séparateur point virgule)',
				2 => 'CSV (séparateur tab)',
				5 => 'CSV (positionné)',
				3 => 'TXT (séparateur point virgule)',
				4 => 'TXT (séparateur tab)',
				6 => 'TXT (positionné)',
			);

        }

		// 521 : Formats de date pour les exports comptables
        if($id_liste_choix == 521){

            $liste = array(

				1 => 'YYYYMMDD',
				2 => 'DD/MM/YYYY',
				3 => 'DDMMYY',
                4 => 'DDMMYYYY'
			);
		}

		//523 Alignement de la valeur dans le cas du format positionné
		if($id_liste_choix == 523){
			$liste = array(
				1 => 'Gauche',
				2 => 'Droite',
			);
		}

        // Statut d'évenement de campagne emailing
        if($id_liste_choix == 511) {

            $liste = array(

                0 => 'Envoyé',
                1 => 'Ouvert',
                2 => 'Cliqué',
                3 => 'Soft Bounce',
                4 => 'Hard Bounce',
                5 => 'Complainte',
                6 => 'Désinscription',
            );

        }

		// 530 : Types de paiements encaissements / décaissements
        if($id_liste_choix == 530){

            $liste = array(
                0 => 'Encaissement',
                1 => 'Décaissement',
            );

        }

        // 539 : Conditions des notifications manuelles
        if($id_liste_choix == 539){

            $liste = array(
                1 => 'Création',
                2 => 'Modification',
                3 => 'Condition SQL',
            );

        }

        // 541 : Type de notification manuelle
        if($id_liste_choix == 541){

            $liste = array(
                1 => 'Notification ERP',
                2 => 'Notification Email',
            );

        }

        // 560 : Encryptage mail synchro
        if($id_liste_choix == 560){

            $liste = array(
                1 => 'SSL',
                2 => 'TLS',
            );

        }

        // 561 : Type de connexion
        if($id_liste_choix == 561){

            $liste = array(
                    1 => 'Eden',
                    2 => 'Extranet',
                    3 => 'Intranet',
            );

        }

        // 570 : Statut import
        if($id_liste_choix == 570){

            $liste = array(
                0 => 'Sans valeur',
                1 => 'En cours',
                2 => 'Arrêté',
                3 => 'Terminé',
                4 => 'Annulé',
						);
				}

        // 580 : Type de message des échanges de ticket client
        if($id_liste_choix == 580){

            $liste = array(
                0 => 'Externe',
                1 => 'Interne',
                2 => 'Clotûre',
            );
        }

        // 590 : Catégorie de traductions
        if($id_liste_choix == 590){

            $liste = array(
                1 => 'Champs libres (1)',
                2 => 'Tables libres (2)',
                3 => 'Interface (global) (3)',
                4 => 'Messages (PHP & JS) (4)',
                5 => 'Listes libres colonnes (5)',
                6 => 'Listes libres calculs (6)',
                7 => 'Menus (7)',
                8 => 'Valeurs listes libres (8)',
                9 => 'Valeurs listes formatées (9)',
                10 => 'Rapports (10)',
                11 => 'Tableau de bord (11)',
                12 => 'Formulaires (12)',
                14 => 'Profil (14)',
                15 => 'Intranet (15)',
                16 => 'Composants (16)',
                17 => 'Document (17)',
                18 => 'Modules sur fiche (18)',
                19 => 'PDF (19)',
                20 => 'Mails (20)',
                21 => 'Filtres (21)',
            );
        }

        // 581 : Statut de contact
        if($id_liste_choix == 581){

            $liste = array(
                0 => 'Actif',
                1 => 'Inactif',
            );

        }

        // 591 : Liste des stockages externes
        if($id_liste_choix == 591) {

            $liste = array(
                0 => 'Sans valeur',
                1 => 'Sharepoint',
                2 => 'M-Files',
                3 => 'Google Drive',
            );
        }

        //592 : Types de configuration email
        if($id_liste_choix == 592){

            $liste = array(
                0 => 'Sans valeur',
                1 => 'Eden',
                2 => 'Client',
                3 => 'Ticket client',
                4 => 'Extranet',
            );
        }
        // 600 : Statuts d'annulation
        if($id_liste_choix == 600){

            $liste = array(
                0 => 'Non',
                1 => 'Oui',
                2 => 'Partiellement',
            );
        }

        //601 : Protocoles email
        if($id_liste_choix == 601){

            $liste = array(
                0 => 'IMAP',
                1 => 'POP',
                2 => 'SMTP',
            );
        }

        //602 : Types synchronisation email
        if($id_liste_choix == 602){

            $liste = array(
                0 => 'Configuration email',
                1 => 'Microsoft',
            );
        }

        //605 : Autorisation Intranet
        if($id_liste_choix == 605){

            $liste = array(
                1 => 'Intranet autorisé',
                2 => 'Intranet uniquement',
                3 => 'Intranet refusé',
            );
        }

        //610 : Statuts campagne de prospection
        if($id_liste_choix == 610){

            $liste = array(
                1 => 'Affecté',
                2 => 'Traité',
            );
        }

        //615 : Catégories dépenses MINDEE
        if($id_liste_choix == 615){

            $liste = array(
                1 => 'toll',
                2 => 'food',
                3 => 'parking',
                4 => 'transport',
                5 => 'accommodation',
                6 => 'gasoline',
                7 => 'telecom',
                8 => 'miscellaneous',
                9 => 'food dinner',
            );
        }

        //620 : Type destinataire email
        if($id_liste_choix == 620){

            $liste = array(
                1 => 'A',
                2 => 'CC',
                3 => 'CCi',
						);
				}

        //621 : Type d'élément pour les licences
        if($id_liste_choix == 621){

            $liste = array(
                1 => 'Route',
                2 => 'Type élément',
                3 => 'Module',
            );
        }

        //622 : Niveau du destinataire email
        if($id_liste_choix == 622){
            $liste = array(
                1 => 'Suggéré',
                2 => 'Rempli automatiquement',
                3 => 'Obligatoire',
            );
        }

        //626 : Type d'indisponibilité
        if($id_liste_choix == 626){

            $liste = array(
                1 => 'Jour férié',
                2 => 'RTT employeur',
                3 => 'Fermeture de société',
                4 => 'Autre'
            );

            $liste_couleurs = array(
								1 => '#DCDCDC',
								2 => '#669e24',
								3 => '#ed6f56',
								4 => '#ed6f56',
							);

				     $liste_couleurs_polices = array(
								1 => '#212121',
								2 => '#ffffff',
								3 => '#ffffff',
								4 => '#ffffff',
						);
				}

        //630 : Type de document : Doc Achat/Vente / Autres documents
        if($id_liste_choix == 630){

            $liste = array(
                0 => 'Document Achats/Ventes',
                1 => 'Autres documents',
            );
        }

        //631 : Mode d'affichage de saisie des temps
        if($id_liste_choix == 631){

            $liste = array(
                1 => 'Jour',
                2 => "Semaine",
                3 => "Mois",
            );
        }

        //635 : Type d'application d'éco-contribution
        if($id_liste_choix == 635){

            $liste = array(
                0 => 'Inclue dans le montant HT',
                1 => "En sus du montant HT",
            );
        }

        //640 : Type de contrat utilisateur
        if($id_liste_choix == 640){

            $liste = array(
                1 => 'Base horaire',
                2 => 'Forfait jours',
            );
        }

        //650 : Opérateur recheche avancée
        if($id_liste_choix == 650){
            $liste = array(
                0 =>  'Et',
                1 =>  'Ou'
            );
        }

        //700 : Type de valeur pour les chronomètres
        if($id_liste_choix == 700){
            $liste = array(
                0 =>  'Heures',
                1 =>  'Minutes'
            );
        }

        //701 : Statut du suivi des jours travaillés
        if($id_liste_choix == 701){
            $liste = array(
                0 =>  'Sans Valeur',
                1 =>  'Terminé',
                2 =>  'Validé',
            );
        }

        //710 : Niveau de détail modele de facturation des temps
        if($id_liste_choix == 710) {
            $liste = array(
                0 => "Détails à l'activité",
                1 => "Détails à l'activité par personne",
                2 => "Détails à l'activité par personne par jour"
            );
        }

        //715 : Type de requête trigger eden
        if($id_liste_choix == 715) {
            $liste = array(
                0 => "UPDATE",
                1 => "INSERT",
            );
        }

        //716 : Type de champs pour l'enrichissement des données
        if($id_liste_choix == 716) {
            $liste = array(
                1 => "Champs à enrichir",
                2 => "Champs d'aide à l'enrichissement",
            );
        }

        //720 : Possibilité double facteur d'authentification
        if($id_liste_choix == 720) {
            $liste = array(
                0 => "Désactivé",
                1 => "Email",
                2 => "Application double authentification"
            );
        }

        //725 : Type de service pour la synchronisation
        if($id_liste_choix == 725) {
            $liste = array_map(function($service){
                return ucfirst($service);
            }, management('synchronisation_service')->services_disponibles());
        }

        //726 : Sens des données des champs pour la synchronisation
        if($id_liste_choix == 726) {
            $liste = array(
                0 => "EDEN => Service de synchronisation",
                1 => "Service de synchronisation => EDEN"
            );
        }

        //727 : Type de synchronisation service externe
        if($id_liste_choix == 727) {
            $liste = array(
                0 => "API service de synchronisation",
                1 => "Webhook service de synchronisation",
                2 => "Cron EDEN pour récupération des données",
                3 => "Lecture déclenchée par élément",
            );
        }

        //728 : Evenements de synchronisation pour les logs
        if($id_liste_choix == 728) {
            $liste = array(
                1 => "Création",
                2 => "Modification",
                3 => "Suppression",
                4 => "Lecture",
            );
        }

        //729 : Statut facturation électronique (facture_vente)
        if($id_liste_choix == 729) {
            $liste = array(
                1 => "À envoyer",
                2 => "Envoyée (en attente de statut)",
                3 => "Erreur technique",
                200 => "Déposée",
                201 => "Émise par la plateforme",
                202 => "Reçue par la plateforme",
                203 => "Mise à disposition",
                204 => "Prise en charge",
                205 => "Approuvée",
                206 => "Approuvée partiellement",
                207 => "En litige",
                208 => "Suspendue",
                209 => "Complétée",
                210 => "Refusée",
                211 => "Paiement transmis",
                212 => "Encaissée",
                213 => "Rejetée",
            );
        }

        //730 : Type de document facturation électronique (BT-3, UNTDID 1001)
        if($id_liste_choix == 730) {
            $liste = array(
                380 => "380 - Facture commerciale",
                381 => "381 - Avoir",
                384 => "384 - Facture rectificative",
                386 => "386 - Facture d'acompte",
                389 => "389 - Facture auto-facturée",
                261 => "261 - Avoir auto-facturé",
                262 => "262 - Avoir pour remise globale",
                393 => "393 - Facture affacturée",
                396 => "396 - Avoir affacturé",
            );
        }

        //731 : Cadre de facturation facturation électronique (BT-23)
        if($id_liste_choix == 731) {
            $liste = array(
                'B1' => "B1 - Dépôt d'une facture de bien",
                'S1' => "S1 - Dépôt d'une facture de service",
                'M1' => "M1 - Dépôt d'une facture mixte (biens et services)",
                'B2' => "B2 - Dépôt d'une facture de bien déjà payée",
                'S2' => "S2 - Dépôt d'une facture de service déjà payée",
                'M2' => "M2 - Dépôt d'une facture mixte déjà payée",
                'B4' => "B4 - Dépôt d'une facture définitive (après acompte) de bien",
                'S4' => "S4 - Dépôt d'une facture définitive (après acompte) de service",
                'M4' => "M4 - Dépôt d'une facture définitive (après acompte) mixte",
            );
        }

        //732 : Zone fiscale facturation électronique (catégorie comptable)
        if($id_liste_choix == 732) {
            $liste = array(
                1 => "France",
                2 => "Union européenne",
                3 => "Export (hors UE)",
                4 => "DOM-TOM",
            );
        }

        //736 : Statut d'envoi CDAR (facturation_electronique_cycle_de_vie)
        if($id_liste_choix == 736) {
            $liste = array(
                1 => "À envoyer",
                2 => "Envoyée",
                3 => "Erreur technique",
                4 => "Reçue",
            );
        }

		if(!is_array($liste))
            $liste = array();

        // on met une valeur par défaut
		if(!isset($liste[0]) && empty($modele->obligatoire)) {

			$nouvelle_liste = array();

			$nouvelle_liste[0] = 'Sans valeur';

			foreach($liste as $id => $valeur) {

				$nouvelle_liste[$id] = $valeur;
			}

			$liste = $nouvelle_liste;
		}

        //On gère les traductions
        if(in_array($id_liste_choix,Variables::liste_formatees_editable()) && $uniquement_standard === false){

            $valeurs_standard = self::recuperer_valeur_listes_preenregistrees($id_liste_choix,$modele,true)['liste'];

            foreach($liste as $cle => $valeur){

                $index_traduction = 'valeurs_listes_formatees.'.$id_liste_choix.'.valeur_'.$cle;

                if(!empty($traductions)) {

                    $traduction = $index_traduction;

                    if(isset($traductions[$index_traduction]))
                        $traduction = $traductions[$index_traduction];
                }
                else
                    $traduction = traduction($index_traduction);

                if((empty(moi()) || empty(moi()->langue) || moi()->langue == 'fr') && $traduction == $index_traduction){

                    service('traduction')->calcul_index_traduction(
                        9,
                        array(
                            'valeurs_listes_formatees',
                            $id_liste_choix
                        ),
                        array(
                            'valeur_'.$cle => $valeur,
                        ),
                        in_array($cle,array_keys($valeurs_standard))
                    );
                }
                else
                    $liste[$cle] = $traduction;

            }
        }

		return array(
	        'liste' => $liste,
            'liste_couleurs' => $liste_couleurs,
            'liste_couleurs_polices' => $liste_couleurs_polices
        );

	}

    /**
     *
     * Crée un filtre pour les listes libres
     *
     */
    public function filtre_liste_libre($valeurs = false, $filtre = false){

        $html = '<div class="css_liste_checkbox_popover">';

        if (isset($this->filtre_pour_rapport) && $this->filtre_pour_rapport === true) {

            foreach ($this->valeurs_possibles as $id => $valeur) {

                $html .= '<span class="badge badge-default js_filtre_sur_liste" :class="{\'badge-success\': ' . $this->attributs['v-model'] . ' && ' . $this->attributs['v-model'] . '.indexOf(\'' . $id . '\') >= 0}" @click="inverse_valeur_filtre_creation_rapport(' . $this->attributs['v-model'] . ', \'' . $id . '\')">' . $valeur . '</span> ';
            }

            // la possibilité d'avoir un paramètre sur le rapport
            $html .= '<span class="badge badge-default js_filtre_sur_liste" :class="{\'badge-success\': ' . $this->attributs['v-model'] . ' && ' . $this->attributs['v-model'] . '.indexOf(\'parametre1\') >= 0}" @click="inverse_valeur_filtre_creation_rapport(' . $this->attributs['v-model'] . ', \'parametre1\')">Paramètre #1</span> ';

        } else {

            $id_cl = $this->modele->id_cl;

            if(!empty($this->modele->liste_choix))
                $id_cl = $this->modele->liste_choix;

            $type_element_filtre = $filtre->type_element;

            if($type_element_filtre == null)
                $type_element_filtre = $this->modele->type_element;

            $html .= '<template v-if="$root.valeurs_listes_libres[' . $id_cl . '] != undefined">';

            $html .= '
                <div v-if="$root.valeurs_listes_libres[' . $id_cl . '][\'sans_categorie\'] != undefined"
                    v-for="element in $root.affichage_valeur_liste_libre(valeurs_filtres.'.$type_element_filtre.',$root.valeurs_listes_libres[' . $id_cl . '][\'sans_categorie\'],$root.valeurs_listes_libres[' . $id_cl . '].liaisons,\''.$type_element_filtre.'\',\''.$this->modele->nom_sql.'\')"
                    class="form-check form-check-inline css_checkbox_popover css_checkbox_utilisateur js_utilisateurs_eden" >
                    <label class="d-flex align-items-center">

                        <input
                            @change="changement_filtres_liste_libre(valeurs_filtres.'.$type_element_filtre.',$root.valeurs_listes_libres[' . $id_cl . '].liaisons,\''.$this->modele->type_element.'\',\''.$this->modele->nom_sql.'\');"
                            class="form-check-input js_filtre_sur_liste filtre_sur_liste_liste_libre"
                            type="checkbox"
                            type_filtre="checkbox"
                            :value="element.id_valeur"
                            v-model="valeurs_filtres.'.$type_element_filtre.'.'.$filtre->nom_sql.'[element.id_valeur]"
                            nom_sql="' . $this->modele->nom_sql . '"
                            name="' . $type_element_filtre.'_'.$filtre->nom_sql . '"
                            :data-id="element.id_valeur"
                            >
                            &nbsp;&nbsp;
                        <div class="d-flex flex-column" v-html="element.valeur">

                        </div>

                    </label>
                </div>';

            $html .= '<template v-if="$root.valeurs_listes_libres[' . $id_cl . '].categorie != undefined && $root.valeurs_listes_libres[' . $id_cl . '].categorie">';

            $html .= '<template v-for="(listes,nom_categorie) in $root.valeurs_listes_libres[' . $id_cl . ']" v-if="nom_categorie != \'categorie\' && nom_categorie != \'sans_categorie\' && nom_categorie != \'liaisons\'">';

            $html .= '<div class="css_sous_titre_filtre_utilisateurs js_sous_titre_filtre_utilisateurs"
                    @click="categorie_onglet_filtre(\'' . $this->modele->type_element . '_' . $this->modele->nom_sql.'\',nom_categorie)">
                    <span v-html="nom_categorie"></span>
                    <span class="css_arrow_down"><i class="fas fa-angle-down"></i></span>
                </div>';

            $html .= '
                <div
                    v-for="element in $root.affichage_valeur_liste_libre(valeurs_filtres.'.$type_element_filtre.',listes,$root.valeurs_listes_libres[' . $id_cl . '].liaisons,\''.$type_element_filtre.'\',\''.$this->modele->nom_sql.'\')"
                    :class="\'form-check form-check-inline css_checkbox_popover css_checkbox_utilisateur js_utilisateurs_eden js_' . $this->modele->type_element . '_' . $this->modele->nom_sql . '_categorie_\'+nom_categorie.replace(\' \',\'\')">
                    <label class="d-flex align-items-center">

                        <input
                            @change="changement_filtres_liste_libre(valeurs_filtres.'.$type_element_filtre.',$root.valeurs_listes_libres[' . $id_cl . '].liaisons,\''.$this->modele->type_element.'\',\''.$this->modele->nom_sql.'\');"
                            class="form-check-input js_filtre_sur_liste filtre_sur_liste_liste_libre"
                            type="checkbox"
                            type_filtre="checkbox"
                            :value="element.id_valeur"
                            v-model="valeurs_filtres.'.$type_element_filtre.'.'.$filtre->nom_sql.'[element.id_valeur]"
                            nom_sql="' . $this->modele->nom_sql . '"
                            name="' . $type_element_filtre.'_'.$filtre->nom_sql . '"
                            :data-id="element.id_valeur"
                            >
                            &nbsp;&nbsp;
                        <div class="d-flex flex-column" v-html="element.valeur">

                        </div>

                    </label>
                </div>';

            $html .= '</template>';

            $html .= '</template>';

            $html .= '</template>';

        }

        $html .= '</div>';

		return $html;
    }

    /**
     *
     * Crée le paterne du champ ce qui permet ensuite de rajouter le champ en fonction du type
     *
     */
    public function paterne_champ(){

		$conditions_v_show_manuelle = '';
		if(!empty($this->modele->conditions_v_show_manuelle))
			$conditions_v_show_manuelle = 'v-show="'.$this->modele->conditions_v_show_manuelle.'"';

		$conditions_v_if_manuelle = '';
		if(!empty($this->modele->conditions_v_if_manuelle))
			$conditions_v_if_manuelle = 'v-if="'.$this->modele->conditions_v_if_manuelle.'"';

        $title = '';

        if(fonctionnalite('accessibilite')) {
            $json_champ = "{nom_sql : '" . $this->modele->nom_sql . "',index_traduction : '" . $this->modele->index_traduction . "',liste_choix : " . ($this->modele->liste_choix > 0 ? $this->modele->liste_choix : 0) . ",id_cl : " . $this->modele->id_cl . ",type : " . $this->modele->type . "}";
            $title = ':title="$root.valeur_titre_champ(' . $this->attributs['v-model']['valeur'] . ',' . $json_champ . ')"';
        }

        $paterne = '<div class="bloc_champ_formulaire formulaire_champ_'.$this->modele->nom_sql.'" '.$conditions_v_if_manuelle.' '.$conditions_v_show_manuelle.' ' . $title . '>
                    [eden_champ]';

        if(!empty($this->modele->aide))
            $paterne .= '<i class="fas fa-question-circle css_pointer" title="'.$this->modele->aide.'" data-toggle="tooltip_fiche" style="padding: 10px; background: '.maquette('background_sous_menus').';color: white;height:30px;text-align: center;"></i>';

        $paterne .= $this->gestion_obligatoire();

        $paterne .= '</div>';

        return $paterne;
    }

    /**
     *
     * Permet de gérer l'obligatoire d'un champ
     *
     */
    public function gestion_obligatoire(){

        $obligatoire = '';

        $modele_obligatoire = '<div class="champ_obligatoire" [v-if]>*</div>';

        if(!empty($this->modele->obligatoire))
            $obligatoire = str_replace('[v-if]','',$modele_obligatoire);
        else if(!empty($this->champ_formulaire->condition_obligatoire)) {
            $obligatoire = str_replace('[v-if]','v-if="'.$this->champ_formulaire->condition_obligatoire.'"',$modele_obligatoire);
        }

        return $obligatoire;
    }

    /**
     *
     * Permet de gérer le disabled d'un champ
     *
     */
    public function lecture_seule($valeur = null){
        if($valeur === null){
            if($this->modele->lecture_seule == 1) {
                $valeur = true;
            } else if(!empty($this->champ_formulaire->condition_lecture_seule)) {
                $valeur = $this->champ_formulaire->condition_lecture_seule;
            }
            if(!$valeur)
                return $this;
        }
        $this->attr('disabled', $valeur,1);
        $this->attr('lecture_seule', $valeur,1);
        
        return $this;
    }

    /**
     *
     * Affectation du champ du formulaire
     *
     */
    public function affectation_champ_formulaire($champ_formulaire){

        $this->champ_formulaire = $champ_formulaire;

        $this->lecture_seule();
    }

	public function indentation_champ_famille($id_parent = 0, $familles = array(), $indentation = 0, $ajout_indentation = 1, $familles_globales = null) {

        if($familles_globales === null){

            $familles_globales = modele('famille')->orderBy('nom')->get();
            $familles_globales_tries = [];

            foreach($familles_globales as $famille_globale){

                $id_parent_globale = empty($famille_globale->parent_id) ? 0 : $famille_globale->parent_id;

                $familles_globales_tries[$id_parent_globale][] = $famille_globale;
            }

            $familles_globales = $familles_globales_tries;
        }


		if(empty($id_parent)) {

			$sous_familles = isset($familles_globales[0]) ? $familles_globales[0] : array();
		}
		else {

			$sous_familles = isset($familles_globales[$id_parent]) ? $familles_globales[$id_parent] : array();
		}

		foreach($sous_familles as $famille) {

			$familles[$famille->id] = '';

			for($i=1; $i<=$indentation; $i++) {

				$familles[$famille->id] .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}

			$familles[$famille->id] .= $famille->nom;

			$indentation += $ajout_indentation;
			$familles = $this->indentation_champ_famille($famille->id, $familles, $indentation,$ajout_indentation,$familles_globales);
			$indentation -= $ajout_indentation;
		}

		return $familles;
	}

    public function cree_web($valeur_par_defaut = false){

        $paterne_champ = $this->paterne_champ_web();

        $obligatoire = '';

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $obligatoire = "required";

        $input = '<input '.$obligatoire.' '.($valeur_par_defaut!==false ? 'value="'.$valeur_par_defaut.'"' : '').' name="'.$this->attributs['name']['valeur'].'" />';

        return str_replace('[eden_champ]',$input,$paterne_champ);
    }

    public function paterne_champ_web(){

        $paterne = '<div class="bloc_champ_formulaire formulaire_champ_'.$this->modele->nom_sql.'">
                    [eden_champ]';

        if(!empty($this->modele->aide))
            $paterne .= '<i class="fas fa-question-circle css_pointer" title="'.$this->modele->aide.'" data-toggle="tooltip_fiche" style="padding: 10px; background: '.maquette('background_sous_menus').';color: white;height:30px;text-align: center;"></i>';

        $paterne .= '</div>';

        return $paterne;
    }

    public function nom_web(){

        $paterne = '<div class="formulaire_titre_'.$this->modele->nom_sql.'" style="position:relative;width:fit-content">';

        $paterne .= $this->nom();

        if(!empty($this->modele->obligatoire) || !empty($this->champ_formulaire->condition_obligatoire))
            $paterne .= '<div class="indicateur_champ_obligatoire">
                <i style="top: -2px;position: absolute;">*</i>
                </div>';

        $paterne .= '</div>';

        return $paterne;
    }

    public function application_tri_requete($requete, $sens, &$joins){

        $alias_table = $this->modele->alias_table ?? $this->modele->type_element;
        return $requete->orderBy($alias_table.'.'.$this->modele->nom_sql, $sens);
    }
}
