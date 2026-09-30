@extends('eden::templates.template')

@section('title') Rapports @stop

@section('content')
	
	
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => 'Paramétrage'),
					array('nom' => 'Rapports')
				)])

			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Rapports
							</h4>
							<div class="ml-auto">
								<a href="{{ route('parametrage.rapport.creer') }}" class="css_ajouter_element" data-toggle="tooltip" data-placement="left" title="Créer un nouveau rapport">
									<i class="css_action_icon fas fa-plus"></i>
								</a>
								<span class="css_ajouter_element" data-toggle="tooltip" title="Enregistrer" @click="enregistrer_rapports">
									<i class="css_action_icon far fa-save"></i>
								</span>
							</div>
						</div>
						<div class="card-body css_parametrage_menu">
							<template v-for="categorie in categories">
								
								<div class="row" v-show="categorie.rapports.length > 0">
									<div class="col-md-12">
										<h4>@{{ categorie.nom }}</h4>
									
										<div class="row" v-if="rapport.suppression != 1" :class="{'css_inactif': rapport.inactif}" style="padding: 9px; border-bottom: 1px solid #aaa;margin-top: 10px;" v-for="rapport in categorie.rapports">
											<div class="col-md-12" style="display: inline-flex;align-items: center;">
												<span data-toggle="tooltip" title="Standard" v-if="rapport.standard == 1" style="display: inline-flex;align-items: center;justify-content: center;height: 100%;margin-right: 10px;">
													<img class="image_relative" style="width: 20px;" src="{{ asset('eden/images/pictos/pastille_standard.png')}}" alt="">
												</span>
												<span v-else style="display: block;width:20px;align-items: center;justify-content: center;height: 100%;margin-right: 10px;">
												</span>

												@traduction('rapport.index_traduction','titre',true) (@{{ rapport.id_rapport }})
												<a class="css_ajouter_element css__lien" style="margin-left: 30px;" :href="'{{URL::to('eden/parametrage/rapport/modifier/')}}/'+rapport.id_rapport" v-show="(rapport.inactif == 0 || rapport.inactif == null) && rapport.type_rapport != '' && rapport.type_rapport != null"><i class="fa fa-fw fa-pencil"></i> Modifier</a>

												<a v-show="(rapport.inactif == 0 || rapport.inactif == null) && rapport.type_rapport != '' && rapport.type_rapport != null" :href="'eden/parametrage/rapport/dupliquer/'+rapport.id_rapport" class="css_ajouter_element css__lien" style="margin-left: 30px;" ><i class="fa fa-fw fa-copy"></i> Dupliquer</a>

												<profil-droits-divers type="eden_rapports" :index="rapport.id" :bouton="true">
													<template v-slot:bouton="{gestion_profil,profil_droits_divers}">
														<span v-show="(rapport.inactif == 0 || rapport.inactif == null) && rapport.type_rapport != '' && rapport.type_rapport != null" @click="gestion_profil()" class="css_ajouter_element css__lien" style="margin-left: 30px;position:relative;" >
															<i class="fa fa-fw fa-users"></i> Profils
															<span v-if="profil_droits_divers.profils.length > 0" class="icone_droits_profils" style="width: 15px;height: 15px;right:-8px">
																@{{ profil_droits_divers.profils.length }}
															</span>
														</span>
													</template>
												</profil-droits-divers>

												<template v-if="rapport.standard === true">
													<span class="css_ajouter_element css__lien" style="margin-left: 30px;" @click="desactive_rapport(rapport)" v-show="rapport.inactif == 0 || rapport.inactif == null"><i class="fa fa-fw fa-trash"></i> Désactiver</span>

													<span class="css_ajouter_element css__lien" style="margin-left: 30px;" @click="active_rapport(rapport)" v-show="rapport.inactif == 1"><i class="fa fa-fw fa-trash"></i> Activer</span>
												</template>
												<template v-else>
													<span class="css_ajouter_element css__lien" style="margin-left: 30px;" @click="supprimer_rapport(rapport)"><i class="fa fa-fw fa-trash"></i> Supprimer</span>
												</template>

											</div>
										</div>
									</div>
								</div>
								<br/>
							</template>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>

	

@endsection

@push('donnees_pour_vuejs_data')
	categories: {!! collect($categories) !!},
    rapports_modifies: [],
@endpush

@push('donnees_pour_vuejs_methods')

	desactive_rapport: function(rapport) {
		
		rapport.inactif = 1;
        this.modification_rapport(rapport);
	},

	active_rapport: function(rapport) {
		
		rapport.inactif = 0;
        this.modification_rapport(rapport);
	},

	enregistrer_rapports: function() {
		
		loading(true);

		$.post({
			
			url: "{{ route('parametrage.rapport.liste_enregistrer') }}",
			dataType: "json",
			data: {
                ...this.rapports_modifies
            }
		}).done((donnees) => {

            this.rapports_modifies = [];
			
			loading(false);

			info(this.$root.traduction('messages.js.rapports.enregistrement_liste_rapport'))
		});
	},

	supprimer_rapport: function(rapport){

		rapport.suppression = 1;
        this.modification_rapport(rapport);
		this.$forceUpdate();
	},

    modification_rapport: function(rapport) {
        var rapport_trouve = this.rapports_modifies.findIndex((rapport_tmp) => rapport_tmp.id_rapport === rapport.id_rapport);

        if(rapport_trouve >= 0)
            rapport = this.rapports_modifies[rapport_trouve];

        var rapport_modification = {
            'id_rapport' : rapport.id_rapport,
            'inactif' : rapport.inactif,
            'suppression' : rapport.suppression,
        };

        if(rapport_trouve >= 0)
            this.rapports_modifies[rapport_trouve] = rapport_modification;
        else
            this.rapports_modifies.push(rapport_modification);

    },

@endpush



