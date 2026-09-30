@extends('eden::templates.template')

@section('title') Gestion des stocks @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => 'Gestion des stocks')
				)])
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-body">
							<div style="display: flex;gap:20px;">
								<div class="css_conteneur_gauche_ticket" style="display: flex;flex-direction: column;gap: 15px;min-width: fit-content;height: fit-content;" v-if="affichage_entrepot">
									@if(!empty($id_liste_conditionnement))
										<div style="display:flex;gap:10px;align-items:center;">
											<h5>@traduction('interface.gestion_des_stocks.par_conditionnement') : </h5>
											<label class="switch">
												<input type="checkbox" v-model="par_conditionnement" @change="changement_liste_par_conditionnement()">
												<span class="slider round"></span>
											</label>
										</div>
									@endif
									<div>
										<div style="position:relative;background: #b7b7b7;display: flex;padding: 12px;justify-content: center;">
											<h4>@traduction('tables_libres.entrepot.nom_table')</h4>
										</div>
										@if(!empty($id_liste_tous_les_entrepots))
											<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" @click="changement_entrepot(null); tous_les_entrepots = true;changement_liste_entrepots();">
												<span :style="(tous_les_entrepots ? 'fontWeight:bold;' :'')">
													<span class="js_filtre_sur_liste">
														<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
													</span>
													<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
														@traduction('interface.gestion_des_stocks.tous_les_entrepots')
													</span>
												</span>
											</div>
										@endif
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" v-if="entrepots === false">
											<span class="js_filtre_sur_liste">
												<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
											</span>
											<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste css_form">
												{!! management('mouvement_de_stock')
													->champ('entrepot_id')
													->vmodel(true,'filtres_pour_fiche')
													->cree() !!}
											</span>
										</div>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre" v-for="entrepot in entrepots" @click="changement_entrepot(entrepot.id);">
											<span :style="(filtres_pour_fiche.entrepot_id == entrepot.id ? 'fontWeight:bold;' :'')">
												<span class="js_filtre_sur_liste">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span>
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@{{ entrepot.nom }}
												</span>
											</span>
										</div>
									</div>
								</div>
								<div style="position:relative;width: 100%;">
									<div style="position: absolute;top: -10px;left: -10px;z-index: 500" @click="affichage_entrepot = !affichage_entrepot">
										<div class="bulle_option css_pointer" :style="(affichage_entrepot == false ? 'width:fit-content;padding: 10px;gap: 5px;' : '')">
											<i :class="'fas fa-chevron-'+(affichage_entrepot ? 'left' : 'right')"></i>
											<span v-if="affichage_entrepot == false" v-html="entrepot_choisi.nom"></span>
										</div>
									</div>

									<div v-show="par_conditionnement === false && tous_les_entrepots === false">
										<liste-libre-{{$id_liste}}
											ref="liste_libre_{{$id_liste}}"

											:filtres_pour_fiche="filtres_pour_fiche"

											@if(!empty($indicateur_source))
												indicateur_source='{{$indicateur_source}}'
											@endif

											@if(super_admin() || mode_parametrage())
												:mode_parametrage=1
											@endif
										>
										</liste-libre-{{$id_liste}}>
									</div>
									@if(!empty($id_liste_conditionnement))
										<div v-show="par_conditionnement === true && tous_les_entrepots === false">
											<liste-libre-{{$id_liste_conditionnement}}
												ref="liste_libre_{{$id_liste_conditionnement}}"

												:filtres_pour_fiche="filtres_pour_fiche"

												@if(super_admin() || mode_parametrage())
													:mode_parametrage=1
												@endif
											>
											</liste-libre-{{$id_liste_conditionnement}}>
										</div>
									@endif
									@if(!empty($id_liste_tous_les_entrepots))
										<div v-show="par_conditionnement === false && tous_les_entrepots === true">
											<liste-libre-{{$id_liste_tous_les_entrepots}}
													ref="liste_libre_{{$id_liste_tous_les_entrepots}}"

											@if(super_admin() || mode_parametrage())
												:mode_parametrage=1
											@endif
											>
											</liste-libre-{{$id_liste_tous_les_entrepots}}>
										</div>
									@endif
									@if(!empty($id_liste_tous_les_entrepots) && !empty($id_liste_tous_les_entrepots_conditionnement))
										<div v-show="par_conditionnement === true && tous_les_entrepots === true">
											<liste-libre-{{$id_liste_tous_les_entrepots_conditionnement}}
													ref="liste_libre_{{$id_liste_tous_les_entrepots_conditionnement}}"

											@if(super_admin() || mode_parametrage())
												:mode_parametrage=1
											@endif
											>
											</liste-libre-{{$id_liste_tous_les_entrepots_conditionnement}}>
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('composants_vue')

	@foreach($listes_id as $liste_id)
        <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$liste_id.'.js') }}"></script>
	@endforeach
@endpush

@push('donnees_pour_vuejs_data')
	entrepots : {!! $entrepots == false ? 'false' : collect($entrepots) !!},
	par_conditionnement : false,
	tous_les_entrepots : false,
	filtres_pour_fiche : {
		entrepot_id :  {!! $entrepot_par_defaut->id !!},
	},
	affichage_entrepot : true,
@endpush

@push('donnees_pour_vuejs_methods')
	changement_entrepot : function(entrepot_id){

		var vue_instance = this;

		vue_instance.$set(this.filtres_pour_fiche,'entrepot_id',entrepot_id);

		if(this.tous_les_entrepots){
			this.tous_les_entrepots = false;
			this.changement_liste_entrepots();
		}

		vue_instance.$nextTick(() => {
			vue_instance.$refs.liste_libre_{{$id_liste}}.actualisation_filtres();
			@if(!empty($id_liste_conditionnement))
				vue_instance.$refs.liste_libre_{{$id_liste_conditionnement}}.actualisation_filtres();
			@endif
		});
	},

	changement_liste_entrepots : function(){

		if(this.par_conditionnement){
			if(this.tous_les_entrepots){
				liste_depart = this.$refs.liste_libre_{{$id_liste_conditionnement}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots_conditionnement}};
			}
			else{
				liste_depart = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots_conditionnement}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste_conditionnement}};
			}
		}
		else{
			if(this.tous_les_entrepots){
				liste_depart = this.$refs.liste_libre_{{$id_liste}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots}};
			}
			else{
				liste_depart = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste}};
			}
		}

		this.transfert_filtres(liste_depart,liste_arrive);
	},

	changement_liste_par_conditionnement : function(){

		if(this.tous_les_entrepots){
			if(this.par_conditionnement){
				liste_depart = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots_conditionnement}};
			}
			else{
				liste_depart = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots_conditionnement}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste_tous_les_entrepots}};
			}
		}
		else{
			if(this.par_conditionnement){
				liste_depart = this.$refs.liste_libre_{{$id_liste}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste_conditionnement}};
			}
			else{
				liste_depart = this.$refs.liste_libre_{{$id_liste_conditionnement}};
				liste_arrive = this.$refs.liste_libre_{{$id_liste}};
			}
		}

		this.transfert_filtres(liste_depart,liste_arrive);
	},

	transfert_filtres : function(liste_depart,liste_arrive){

		var filtres_a_transferer = [];

		var filtres = liste_depart.liste.options_liste.filtres;

		if(filtres == null){
			this.$set(liste_arrive.liste.options_liste,'filtres',[]);
			return;
		}

		for(filtre_actif of structuredClone(filtres)){

			var filtre_depart = liste_depart.liste.filtres.filter((filtre) => {
				return filtre.id == filtre_actif.id;
			})[0];

			var filtre_liste_arrive = liste_arrive.liste.filtres.filter((filtre) => {
				return filtre.nom_sql == filtre_depart.nom_sql;
			});

			if(filtre_liste_arrive.length > 0){
				filtre_actif.id = filtre_liste_arrive[0].id;
				filtres_a_transferer.push(filtre_actif);
			}
		}

		this.$set(liste_arrive.liste.options_liste,'filtres',filtres_a_transferer);

		liste_arrive.actualisation_filtres();
	},
@endpush

@push('donnees_pour_vuejs_computed')
	entrepot_choisi : function(){

		var entrepot_choisi = {};
		
		if(this.entrepots != false){
			for(entrepot of this.entrepots){
				if(entrepot.id == this.filtres_pour_fiche.entrepot_id)
					entrepot_choisi = entrepot;
			}
		}

		return entrepot_choisi;
	},
@endpush

@if($entrepots == false)
	@push('donnees_pour_vuejs_watch')

		'filtres_pour_fiche.entrepot_id' : function(){

			if(this.filtres_pour_fiche.entrepot_id > 0)
				this.tous_les_entrepots = false;
			else if(this.tous_les_entrepots == false)
				this.tous_les_entrepots = true;

			this.$nextTick(() => {
				this.$refs.liste_libre_{{$id_liste}}.actualisation_filtres();
				@if(!empty($id_liste_conditionnement))
					this.$refs.liste_libre_{{$id_liste_conditionnement}}.actualisation_filtres();
				@endif
			});
		},
	@endpush
@endif


