@extends('eden::templates.template')

@include('eden::modales_alertes.rappels_versions')

@section('title') Test des erreurs 500 @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
					array('route' => 'maintenance.index', 'nom' => 'Maintenance'),
					array('nom' => 'Test des erreurs 500')
				)])
			<div class="row">

				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<div class="row">
								<h4 class="col-md-8">
									Statut :
									<span v-text="url_en_cours"></span>
								</h4>
								<div class="col-md-4">

									<div v-if="urls_a_tester.length > 0" style="float:right;padding:5px;font-size:24px;width:75px;margin:0px 5px 0px 0px;" class="alert alert-info">
										<i class="fas fa-question"></i>
										<span v-text="urls_a_tester.length"></span>
									</div>

									<div v-if="urls_ok.length > 0" style="float:right;padding:5px;font-size:24px;width:75px;margin:0px 5px 0px 0px;" class="alert alert-success">
										<i class="fas fa-check"></i>
										<span v-text="urls_ok.length"></span>
									</div>

									<div v-if="urls_ko.length > 0" style="float:right;padding:5px;font-size:24px;width:75px;margin:0px 5px 0px 0px;" class="alert alert-danger">
										<i class="fas fa-skull-crossbones"></i>
										<span v-text="urls_ko.length"></span>
									</div>

									<div v-if="urls_a_tester.length > 0" style="float:right;padding:5px;font-size:16px;margin:0px 5px 0px 0px;">
										<a href="{{ route('maintenance.migrations') }}">Relancer les migrations</a>
									</div>

								</div>
							</div>
						</div>
						<div class="card-body">

							<progress id="progress_test" style="width:100%;height:50px;" max="{{ $urls_a_tester->count() }}"></progress>

						</div>
					</div>
				</div>

				<div class="col-md-12" v-if="urls_ko.length > 0">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								URL en erreur
							</h4>
						</div>
						<div class="card-body">

							<div class="row mx-md-n5" v-for="url in urls_ko">
								<div class="col-md-8">
									<a :href="url.url" target="_blank" class="badge badge-danger" v-html="url.url" style="display: inline-block;"></a>
								</div>
								<div class="col-md-2">
									@{{ url.timing }}
								</div>
								<div class="col-md-2">
									@{{ url.erreur }}
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12" v-if="urls_redirigees.length > 0">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								URL redirigées
							</h4>
						</div>
						<div class="card-body">

							<div class="row mx-md-n5" v-for="url in urls_redirigees">
								<div class="col-md-3">
									<a :href="url.url" target="_blank" class="badge badge-warning" v-html="url.url" style="display: inline-block;"></a>
								</div>
								<div class="col-md-0">
									<p>=></p>
								</div>
								<div class="col-md-4">
									<a :href="url.redirection" target="_blank" class="badge badge-warning" v-html="url.redirection" style="display: inline-block;"></a>
								</div>
								<div class="col-md-2">
									@{{ url.timing }}
								</div>
								<div class="col-md-2">
									@{{ url.erreur }}
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12" v-if="urls_ok.length > 0">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								URL OK
							</h4>
						</div>
						<div class="card-body">

							<div class="row mx-md-n5" v-for="url in urls_ok">
								<div class="col-md-8">
									<a :href="url.url" target="_blank" class="badge badge-success" v-html="url.url" style="display: inline-block;"></a>
								</div>
								<div class="col-md-2">
									@{{ url.timing }}
								</div>
								<div class="col-md-2">
									@{{ url.erreur }}
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12" v-if="urls_passees.length > 0">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								URL non testées
							</h4>
						</div>
						<div class="card-body">

							<div class="row mx-md-n5" v-for="url in urls_passees">
								<div class="col-md-8">
									<a :href="url.url" target="_blank" class="badge badge-warning" v-html="url.url" style="display: inline-block;"></a>
								</div>
								<div class="col-md-2">
									@{{ url.timing }}
								</div>
								<div class="col-md-2">
									@{{ url.erreur }}
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12" v-if="urls_a_tester.length > 0">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								URL à tester
							</h4>
						</div>
						<div class="card-body">

							<div class="row mx-md-n5">
								<div v-for="url in urls_a_tester" class="col-md-12">
									<a :href="url" target="_blank" class="badge badge-warning" v-html="url" style="display: inline-block;"></a>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

@endsection


@push('donnees_pour_vuejs_data')
	urls_a_tester: {!! $urls_a_tester !!},
	urls_ok: [],
	urls_ko: [],
	urls_redirigees: [],
	urls_passees: [],
	url_en_cours: '',
@endpush

@push('donnees_pour_vuejs_methods')
	
	test_url() {
		
		
		if(this.urls_a_tester.length > 0) {

			this.url_en_cours = this.urls_a_tester[0];

			$.ajax({
				
				url: "{{ route('maintenance.test_url.index') }}",
				dataType: "json",
				method: "post",
				data: { url: vue_instance.url_en_cours }

			}).done(function(retour) {

				//console.log(retour);
				
				var info_url = {
					
					url: vue_instance.url_en_cours,
					timing: retour.duree+' secondes',
					erreur: retour.code_retour,
				};

				if(retour.success) {

					vue_instance.urls_a_tester.splice(0,1);
					vue_instance.urls_ok.splice(1,0, info_url);

				} else if(retour.code_retour == 'HTTP/1.0 401 Unauthorized') {

					vue_instance.urls_a_tester.splice(0,1);
					vue_instance.urls_passees.splice(1,0, info_url);

				} else if(retour.code_retour == 'HTTP/1.0 302 Found' || retour.code_retour == 'HTTP/1.1 302 Found') {

					vue_instance.urls_a_tester.splice(0,1);
					info_url.redirection = retour.redirection;
					vue_instance.urls_redirigees.splice(1,0, info_url);

				} else if(retour.success === false && retour.erreur) {
					
					info_url.erreur += ' ('+retour.erreur+')';

					vue_instance.urls_a_tester.splice(0,1);
					vue_instance.urls_ko.splice(1,0, info_url);
				} else {

					vue_instance.urls_a_tester.splice(0,1);
					vue_instance.urls_ko.splice(1,0, info_url);
				}

				$('#progress_test').val({{ $urls_a_tester->count() }} - vue_instance.urls_a_tester.length);

				vue_instance.test_url();

			});
		} else {

			vue_instance.url_en_cours = 'Terminé !';

		}
		
	},	
	
@endpush

@push('scripts')

	<script type="text/javascript">
		setTimeout('vue_instance.test_url();', 500);
	</script>

@endpush