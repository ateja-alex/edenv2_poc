@push('donnees_pour_vuejs_data')
    exoneration_tva : false,

	@if($management->existe())
		fournisseur: {!! collect(management('fournisseur', $management->modele->fournisseur_id)->infos_fournisseur_pour_gestion_commerciale()) !!},
		@if(!empty($management->modele->fournisseur_id))
			derniere_info_recuperee_fournisseur: {{$management->modele->fournisseur_id}},
		@else
			derniere_info_recuperee_fournisseur: 0,
		@endif
	@else
		fournisseur: {!! collect(management('fournisseur')->infos_fournisseur_pour_gestion_commerciale()) !!},
		derniere_info_recuperee_fournisseur: 0,
	@endif
@endpush

@push('donnees_pour_vuejs_created')

	@if($management->existe() && !empty($management->modele->fournisseur_id))
		this.recupere_info_fournisseur();
	@endif
@endpush

@push('donnees_pour_vuejs_methods')

	recupere_info_fournisseur: function() {

		loading(true);

		var fournisseur_id = this.document.fournisseur_id;

		@if(fonctionnalite('gescom_mode_selection_articles_via_fournisseur') === true && ($management->_type_element == 'devis_achat' || $management->_type_element == 'commande_achat') )
			this.fournisseur_articles.id = fournisseur_id;
			this.fournisseur_articles_select();
		@endif

		$.get({

			url: "{{ URL::to('eden/document/achat/infos_fournisseur') }}/"+fournisseur_id,
			dataType: "json"
		}).done((donnees) => {

			// on renseigne le fournisseur
			this.fournisseur.encours = donnees.encours;
			this.fournisseur.adresses_facturation = donnees.adresses_facturation;
			this.fournisseur.adresses_livraison = donnees.adresses_livraison;
			@if(!$management->existe())
				@foreach(App\Eden\Models\Champ_libre::where('type_element', $management->_type_element)->where('correspondance_fiche_tiers', '!=', '')->get() as $champ_libre)

					if(donnees.modele.{{$champ_libre->correspondance_fiche_tiers}} != undefined && donnees.modele.{{$champ_libre->correspondance_fiche_tiers}} != null && donnees.modele.{{$champ_libre->correspondance_fiche_tiers}} != "") {

						this.document.{{$champ_libre->nom_sql}} = donnees.modele.{{$champ_libre->correspondance_fiche_tiers}};
					}
				@endforeach
			@endif

			// le modèle de document par défaut
			if(donnees.modele.modele_document_defaut_{{ $management->_type_element }} != undefined && donnees.modele.modele_document_defaut_{{ $management->_type_element }} != null && donnees.modele.modele_document_defaut_{{ $management->_type_element }} != "") {

				this.document.type_modele_document = donnees.modele.modele_document_defaut_{{ $management->_type_element }};
			}

			this.gestion_categorie_comptable(donnees.modele.categorie_comptable_id);

		});
		loading(false);
	},

	recupere_info_projet : function() {

		var projet_id = this.document.projet_id;

		if(projet_id === null || projet_id == undefined || projet_id == 0 || projet_id == '') {

			this.projet = {!! collect(management('projet')->infos_projet_pour_gestion_commerciale()) !!};
			return;
		}


		$.get({

			url: "{{ URL::to('eden/document/infos_projet') }}/"+projet_id,
			dataType: "json"
		}).done(function(donnees) {

			vue_instance.projet = donnees;
		});
	},
@endpush

@push('donnees_pour_vuejs_watch')
	fournisseur: {
		handler: function() {

			// On vérifie si il y a une adresse par défaut du fournisseur
			$.post({

				url: "{{ route('document.achat.adresse_par_defaut_fournisseur') }}",
				data: {

					fournisseur_id: vue_instance.fournisseur.entite_id,
				},
				dataType: "json"
			}).done(function(retour) {

				if(retour.retour == true){

					vue_instance.document.adresse_de_facturation = retour.adresse_id;
					vue_instance.document.adresse_de_livraison = retour.adresse_id;
				}

			});
		},
	},
@endpush