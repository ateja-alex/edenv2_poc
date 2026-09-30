@extends('eden::templates/template')


@section('content')
<div class="content-wrapper" >
	<div id="base-content" class="container-fluid formulaire_ajour_rapide">
		<div class="row">
			@if(isset($errors) && !empty($errors->all()))
				<br/>
				<br/>
				<div class="col-md-12">
					@foreach($errors->all() as $message)
						<div class="alert alert-danger">{{ $message }}</div>
					@endforeach
				</div>
				<br/>
				<br/>
				<br/>
			@endif
			<div class="col-md-12" id="affichage_alerte_formulaire_generique"></div>
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header card-header-form">
							<h4>
								{!! ucfirst(traduction('tables_libres.'.$type_element.'.element')) !!}
							</h4>
                            <div class="save-button-container">
                                <span @click="enregistrer_element" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" :title="$root.traduction('interface.enregistrer')">
                                    <i class="css_action_icon secondaire fa fa-fw fa-save"></i>
                                </span>
                                <span class="css_ajouter_element bouton_dropdown_enregistrer" style="padding: 0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="css_action_icon secondaire fas fa-angle-down"></i>
                                </span>
                                <div class="dropdown-menu">
                                    <span class="dropdown-item" @click="enregistrer_element(1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
                                    <span class="dropdown-item" @click="enregistrer_element(2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
                                </div>
                            </div>
						</div>

						<div class="card-body">
							<formulaire ref="formulaire" nom_formulaire="{{$type_element}}"></formulaire>
						</div>

						<div class="card-footer">

								@if(!empty($_SERVER['HTTP_REFERER']))
									<a class="btn btn-secondary" href="{{$_SERVER['HTTP_REFERER']}}">
										@traduction('formulaire.generique.fermer')
									</a>
								@endif

								<div class="conteneur_boutons_enregistrement">
									<button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_element()">
										@traduction('interface.listes.enregistrer')
									</button>
									<span class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="fas fa-angle-down"></i>
									</span>
									<div class="dropdown-menu">
										<span class="dropdown-item" @click="enregistrer_element(1)">@traduction('interface.listes.enregistrer_et_nouveau')</span>
										<span class="dropdown-item" @click="enregistrer_element(2)">@traduction('interface.listes.enregistrer_et_dupliquer')</span>
									</div>
								</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('donnees_pour_vuejs_data')

	type_element: '{{ $type_element }}',
	{{ $type_element }}: {!! management($type_element)->modele_par_defaut() !!},

@endsection


@section('donnees_pour_vuejs_methods')

	enregistrer_element : async function(type_enregistrement = 0) {

		loading(true);

		var donnees = await this.$refs.formulaire.enregistrer({}, null, type_enregistrement);

		if(donnees.retour !== true){
			loading(false);
			return;
		}

		if(type_enregistrement === 0)
			document.location = donnees.lien_vers_element;
		else
			toastr.success(this.$root.traduction('interface.listes.element_enregistre_avec_succes'));

		if(type_enregistrement === 2)
			this[this.type_element] = this.$refs.formulaire.element;

		loading(false);
	},

@endsection
