@extends('eden::templates.template')

@section('title') Paramétrage CSS @if(isset($extranet) && $extranet == true) Extranet @endif @endsection

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'CSS personnalisé')
			)])
			
			@if(!empty($erreur_parametrage))
				<div class="alert alert-danger">
	      			{!! implode('<br/>', $erreur_parametrage) !!}
	      		</div>
	      	@endif

			<div class="row">
				<div class="col-md-12">
				
					<div class="card mb-3">
						<div class="card-header">
							<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center">
								CSS @if(isset($extranet) && $extranet == true) Extranet @endif
								<span data-toggle="tooltip" data-placement="top" title="" class="css_ajouter_element ml-auto" data-original-title="Enregistrer" @click="enregistre_css_specifique_erp">
									<i aria-hidden="true" class="css_action_icon fa fa-fw fa-save"></i>
								</span>
							</h4>
						</div>
						<div class="card-body">
							<div class="row">
								<div id="js_css_specifique_erp" class="css_bloc_edition_html js_bloc_edition_html"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('scripts')

	<script>
	require.config({ paths: { 'vs': 'https://unpkg.com/monaco-editor@latest/min/vs' }});
	window.MonacoEnvironment = { getWorkerUrl: () => proxy };

	let proxy = URL.createObjectURL(new Blob([`
		self.MonacoEnvironment = {
			baseUrl: 'https://unpkg.com/monaco-editor@latest/min/'
		};
		importScripts('https://unpkg.com/monaco-editor@latest/min/vs/base/worker/workerMain.js');
		`], { type: 'text/javascript' }));
	require(["vs/editor/editor.main"], function () {});
	</script>

	<script type="text/javascript">

			
	

	</script>
@endsection


@push('donnees_pour_vuejs_created')
	
		setTimeout(() => {
			require(["vs/editor/editor.main"],() => {
				let editor = monaco.editor.create(document.getElementById('js_css_specifique_erp'), {
					
					value: this.css_specifique_erp,
					language: 'html',
					theme: 'vs-dark'
				});
				editor.getModel().onDidChangeContent((event) => {
					
					this.css_specifique_erp = editor.getValue();
					this.$forceUpdate();
				});
			});
		}, 300);
@endpush

@push('donnees_pour_vuejs_methods')
	enregistre_css_specifique_erp: function() {
		
		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('parametrage.css.enregistrer') }}",
			dataType: "json",
			method: 'POST',
			data: {
				css:this.css_specifique_erp,
				extranet: "{{ $extranet }}"
			}

		}).done(async (donnees) => {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			toastr.success('Modifications enregistrées !')
		});
	},
@endpush

@push('donnees_pour_vuejs_data')

	css_specifique_erp: `{!! $css_specifique_erp !!}`,
@endpush