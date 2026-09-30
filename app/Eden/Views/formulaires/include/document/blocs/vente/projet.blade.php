<div class="card mb-3">
	<div class="card-header @if(!isset($onglet)) js_fermeture_bloc @endif">
		<h4>
			<span>@traduction('document.blocs.projet.titre')</span>
			@if(!isset($onglet) && $management->existe())
				@if(fonctionnalite('gescom_ne_pas_plier_par_defaut_les_blocs_sur_saisie_document'))
					<span class="ml-2 css_toggle_card_panel">
						<span class="fa fa-chevron-up"></span>
					</span>
				@else
					<span class="ml-2 css_toggle_card_panel">
						<span class="fa fa-chevron-down"></span>
					</span>
				@endif
			@endif
		</h4>
	</div>
	<div class="card-body" @if(!isset($onglet) && $management->existe() && !fonctionnalite('gescom_ne_pas_plier_par_defaut_les_blocs_sur_saisie_document')) style="display: none;" @endif>
		<div class="row" v-if="client.projets != null">

			@if($management->champ_modifiable('projet_id'))
				@if(fonctionnalite('gescom_document_onglet_projet_uniquement_projet_client'))
					<div class="col-sm-12">
						<b><span>@traduction('document.blocs.projet.projets_client')</span></b>
					</div>
					<div class="col-sm-12" v-for="projet_client in client.projets">
						<div  class="css_option_choix_sur_formulaire" :class="{css_option_choix_sur_formulaire_actif : document.projet_id == projet_client.id}" style="margin-bottom: 10px; text-align: left; padding: 10px;line-height: 15px; cursor: pointer;" @click="document.projet_id = projet_client.id">
							<p>@{{ projet_client.nom }}</p>
						</div>
					</div>
					<div class="col-sm-12" v-show="client.projets.length == 0">
						<span>@traduction('document.blocs.projet.aucun_projet_client')</span>
					</div>
					<div class="col-sm-12">
						<b><span>@traduction('document.blocs.projet.selectionner_autre')</span></b>
						{!! $management->champ('projet_id')->attr('ref', 'selection_projet_document')->cree() !!}
					</div>
				@else
					<div class="col-sm-12">
						<b><span>@traduction('document.blocs.projet.selectionner')</span></b>
						{!! $management->champ('projet_id')->attr('ref', 'selection_projet_document')->cree() !!}
					</div>
				@endif
			@else
				<div class="col-md-12">
					<b>{{ ucfirst(table_libre('projet')->element) }} : </b>
					@if(empty($management->modele->projet_id))
						<i>@traduction('document.blocs.projet.aucune_selection')</i>
					@else
						{!! $management->champ('projet_id')->cree_affichage() !!}
					@endif
				</div>
			@endif
		</div>
		<div class="row" v-if="projet && projet.modele">
			@include('eden::formulaires.include.document.vues_a_surcharger.description_du_projet')
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')

	@if($management->existe())
		projet: {!! collect(management('projet', $management->modele->projet_id)->infos_projet_pour_gestion_commerciale()) !!},
		@if(!empty($management->modele->projet_id))
			derniere_info_recuperee_projet: {{$management->modele->projet_id}},
		@else
			derniere_info_recuperee_projet: 0,
		@endif
	@elseif(!empty($management->modele) && !empty($management->modele->projet_id))
		projet: {!! collect(management('projet', $management->modele->projet_id)->infos_projet_pour_gestion_commerciale()) !!},
		derniere_info_recuperee_projet: {{$management->modele->projet_id}},

	@else
		projet: {!! collect(management('projet')->infos_projet_pour_gestion_commerciale()) !!},
		derniere_info_recuperee_projet: 0,
	@endif


@endpush

@push('donnees_pour_vuejs_methods')

	recupere_info_projet : function() {

		var projet_id = this.document.projet_id;

		if(!(projet_id > 0)) {

			this.projet = {!! collect(management('projet')->infos_projet_pour_gestion_commerciale()) !!};
			return;
		}

		$.get({
			url: "{{ URL::to('eden/document/infos_projet') }}/"+projet_id,
			dataType: "json"
		}).done((donnees) => {
			this.projet = donnees;
			this.$emit('changement_projet',donnees);
		});

	},
@endpush
