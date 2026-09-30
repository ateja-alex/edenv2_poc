@extends('eden::templates.template')

@section('title'){!! traduction('interface.comptabilite.export') !!}@stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid css_form">
			
			@if(session()->has('erreur'))
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger">{{ session()->get('erreur') }}</div>
					</div>
				</div>
			@endif
			
			<div class="row">
			
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.comptabilite.export_comptable')
							</h4>
						</div>
						<div class="card-body">
							<form action="{{ route('comptabilisation.export_post') }}" method="post">
							
								<div class="row">
									<div class="col-md-6">@traduction('interface.comptabilite.journal')</div>
									<div class="col-md-6">
										<select name="journal">
											<option value="0" v-html="traduction('interface.comptabilite.tous')"></option>
											@foreach(modele('journal_comptable')->get() as $journal)
												<option value="{{ $journal->id }}">{{ $journal->nom }}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">@traduction('interface.comptabilite.entite')</div>
									<div class="col-md-6">
										<select name="entite_id" v-model="export_comptable_entite_id" @change="trouve_modeles_dispo_pour_entite_export_comptable()">
											<!-- <option value="0">Toutes</option> -->
											@foreach(modele('entite')->get() as $entite)
												<option value="{{ $entite->id }}">{{ $entite->nom }}</option>
											@endforeach
										</select>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">@traduction('interface.comptabilite.type')</div>
									<div class="col-md-6">
										<select name="type_export" v-model="export_comptable_type_export">
											<option value="FEC" v-html="traduction('interface.comptabilite.fec')"></option>
											<option value="CUSTOM" v-html="traduction('interface.comptabilite.parametrable')"></option>
										</select>
									</div>
								</div>
								
								<div class="row" v-if="export_comptable_type_export == 'CUSTOM'">
									<div class="col-md-6">@traduction('interface.comptabilite.modele')</div>
									<div class="col-md-6">
										<select name="export_compta_modele_id">
											<option :value="export_compta_modele.id" v-for="export_compta_modele in export_compta_modeles">@{{ export_compta_modele.nom }}</option>
										</select>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">@traduction('interface.comptabilite.exporter')</div>
									<div class="col-md-6">
										<select name="choix_ecritures" v-model="choix_ecritures">
											<option value="0" v-html="traduction('interface.comptabilite.toutes_les_ecritures')"></option>
											<option value="1" v-html="traduction('interface.comptabilite.nouvelles_ecritures')"></option>
											<option value="2" v-html="traduction('interface.comptabilite.ecritures_exportees')"></option>
										</select>
									</div>
								</div>
							
								<div class="row">
									<div class="col-md-6">@traduction('interface.comptabilite.dates')</div>
									<div class="col-md-2"><input type="text" class="datepicker" name="debut" value="{{ date('01/m/Y', strtotime('-1 months')) }}" /></div>
									<div class="col-md-2">@traduction('interface.comptabilite.au')</div>
									<div class="col-md-2"><input type="text" class="datepicker" name="fin" value="{{ date('t/m/Y', strtotime('-1 months')) }}" /></div>
								</div>
								<div class="row">
									<div class="col-md-6"></div>
									<div class="col-md-6"><input type="submit" class="btn btn-primary" :value="traduction('interface.comptabilite.exporter')" /></div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	

@endsection

@push('donnees_pour_vuejs_data')
	export_comptable_entite_id: {{ modele('entite')->first()->id }},
	export_comptable_type_export: 'CUSTOM',
	export_compta_modeles: {},
	export_compta_modeles_par_entite: {!! $modeles_export !!},
	choix_ecritures : 1,
@endpush

@push('donnees_pour_vuejs_created')
	this.trouve_modeles_dispo_pour_entite_export_comptable();
@endpush

@push('donnees_pour_vuejs_methods')

	trouve_modeles_dispo_pour_entite_export_comptable: function() {
		
		//console.log(this.export_compta_modeles_par_entite);
		//console.log(this.export_comptable_entite_id);
		//console.log(this.export_compta_modeles_par_entite[this.export_comptable_entite_id]);
		
		this.export_compta_modeles = this.export_compta_modeles_par_entite[this.export_comptable_entite_id];
	},
	
@endpush

