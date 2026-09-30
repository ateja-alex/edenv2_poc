<div class="row">
	<div class="col-md-12">
		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.css_pdf')
				</h4>
			</div>
			<div class="card-body">
				<form id="formulaire_fiche" class="css_form">
					
					<div style="display: none">
						{!! $management_element->champ('css')->cree() !!}
					</div>
					
					<div id="js_html_edition_css_pdf" class="css_bloc_edition_html js_bloc_edition_html"></div>

					<div class="d-flex flex-row justify-content-end">
						<button type="button" @click="enregistrer_pdf_css_document()" class="btn btn-primary">{{traduction('interface.modales.enregistrer')}}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.numeros_de_page')
				</h4>
			</div>
			<div class="card-body">
				<form id="formulaire_fiche_numeros_de_page" class="css_form">
					
					<div class="row">
						<div class="col-md-2"></div>
						<div class="col-md-2" style="text-align:center">
							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.x')
							<span class="badge badge-sm" style="color:#fff;background-color:#343a40;float:left;" data-toggle="tooltip" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.coordonnees_x_numeros_pages') }}"><i class="fa fa-info" style="font-size:8px;"></i></span>
						</div>
						<div class="col-md-2" style="text-align:center">
							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.y')
							<span class="badge badge-sm" style="color:#fff;background-color:#343a40;float:left;" data-toggle="tooltip" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.coordonnees_y_numeros_pages') }}"><i class="fa fa-info" style="font-size:8px;"></i></span>
						</div>
						<div class="col-md-2">
							<span class="badge badge-sm" style="color:#fff;background-color:#343a40;float:left;" data-toggle="tooltip" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.chaine_numeros_pages') }}"><i class="fa fa-info" style="font-size:8px;"></i></span>&nbsp;
							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.chaine')
						</div>
						<div class="col-md-2" style="text-align:center">
							<span class="badge badge-sm" style="color:#fff;background-color:#343a40;float:left;" data-toggle="tooltip" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.taille_texte_numeros_pages') }}"><i class="fa fa-info" style="font-size:8px;"></i></span>
							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.font_size')
						</div>
						<div class="col-md-2" style="text-align:center">
							<span class="badge badge-sm" style="color:#fff;background-color:#343a40;float:left;" data-toggle="tooltip" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.couleur_numeros_pages') }}"><i class="fa fa-info" style="font-size:8px;"></i></span>
							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.couleur')
						</div>
					</div>
					<div class="row">
						<div class="col-md-2">@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.numero_1')</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_x_1')->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_y_1')->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_chaine_1')->attr('placeholder', traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.placeholder_chaine'))->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_taille_1')->attr('placeholder', '10')->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_couleur_1')->cree() !!}</div>
					</div>
					<div class="row">
						<div class="col-md-2">@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.numero_2')</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_x_2')->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_y_2')->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_chaine_2')->attr('placeholder', traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.placeholder_chaine'))->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_taille_2')->attr('placeholder', '10')->cree() !!}</div>
						<div class="col-md-2">{!! $management_element->champ('numero_page_couleur_2')->cree() !!}</div>
					</div>

					<div class="d-flex flex-row justify-content-end">
						<button type="button" @click="enregistrer_numeros_de_page()" class="btn btn-primary">{{traduction('interface.modales.enregistrer')}}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					Fonds de page
				</h4>
			</div>
			<div class="card-body">

				<form id="formulaire_fiche_fonds_de_page" class="css_form">

					<div class="row css_apercus_fonds_de_page">
						<div class="col-md-4">
							<label>Fond - Première page</label>
							{!! $management_element->champ('fond_page_premiere')->attr('hauteur_apercu', 200)->cree() !!}
						</div>
						<div class="col-md-4">
							<label>Fond - Pages intermédiaires</label>
							{!! $management_element->champ('fond_page_milieu')->attr('hauteur_apercu', 200)->cree() !!}
						</div>
						<div class="col-md-4">
							<label>Fond - Dernière page</label>
							{!! $management_element->champ('fond_page_derniere')->attr('hauteur_apercu', 200)->cree() !!}
						</div>
					</div>

					<div class="d-flex flex-row justify-content-end">
						<button type="button" @click="enregistrer_fonds_de_page()" class="btn btn-primary">{{traduction('interface.modales.enregistrer')}}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">

		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.header_pdf')
					<span @click="pdf_header.splice(0, 0, [])" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.ajouter_ligne')}}">
						<i class="css_action_icon fa fa-fw fa-plus-square"></i>
					</span>
				</h4>

			</div>
			<div class="card-body">
				@include('eden::fiches.include.modele_de_document.mise_en_page_pdf_lignes', ['lignes' => 'pdf_header', 'type_bloc' => 'header'])
			</div>
		</div>

		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.corps_pdf')
					<span @click="pdf_body.splice(0, 0, [])" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.ajouter_ligne')}}">
						<i class="css_action_icon fa fa-fw fa-plus-square"></i>
					</span>
					<span @click="pdf_body.splice(0, 0, {type:'div',contenu:''})" class="css_ajouter_element ml-5" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.ajouter_paragraphe')}}">
						<i class="css_action_icon fa fa-fw fa-paragraph"></i>
					</span>
					@if($management_element->modele->type_de_document == 0 || in_array($management_element->modele->type_element_autres,\App\Eden\Variables::$documents_gescom))
						<span @click="pdf_body.splice(index+1, 0, {type:'articles', colonnes:[]})" class="css_ajouter_element ml-5" data-toggle="tooltip" data-placement="top" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.ajouter_articles') }}">
							<i class="css_action_icon fa fa-fw fa-table"></i>
						</span>
					@endif
				</h4>

			</div>
			<div class="card-body">
				@include('eden::fiches.include.modele_de_document.mise_en_page_pdf_lignes', ['lignes' => 'pdf_body', 'type_bloc' => 'body'])
			</div>
		</div>


		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					<a title="" data-toggle="tooltip" aria-hidden="true" class="fab fa-css3-alt" data-original-title=".recap_footer"></a> @traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.recap_avant_footer')
					<span @click="pdf_recap_footer.splice(0, 0, [])" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.ajouter_ligne')}}">
						<i class="css_action_icon fa fa-fw fa-plus-square"></i>
					</span>
				</h4>

			</div>
			<div class="card-body">
				@include('eden::fiches.include.modele_de_document.mise_en_page_pdf_lignes', ['lignes' => 'pdf_recap_footer', 'type_bloc' => 'recap_footer'])
			</div>
		</div>


		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.footer_pdf')
					<span @click="pdf_footer.splice(0, 0, [])" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.ajouter_ligne')}}">
						<i class="css_action_icon fa fa-fw fa-plus-square"></i>
					</span>
				</h4>

			</div>
			<div class="card-body">
				@include('eden::fiches.include.modele_de_document.mise_en_page_pdf_lignes', ['lignes' => 'pdf_footer', 'type_bloc' => 'footer'])
			</div>
		</div>



		<div class="card mb-3 css_bloc_formulaire_fiche">
			<div class="card-header">
				<h4 class="css_titre_formulaire_fiche_element d-flex align-items-center" style="width: 100%;">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.annexes')
					<span @click="ajouter_annexe()" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.ajouter_ligne')}}">
						<i class="css_action_icon fa fa-fw fa-plus-square"></i>
					</span>
				</h4>

			</div>
			<div class="card-body">
				@include('eden::fiches.include.modele_de_document.mise_en_page_pdf_annexes', ['lignes' => 'pdf_annexes', 'type_bloc' => 'annexes'])
			</div>
		</div>


	</div>
</div>

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

		setTimeout(function(){
			require(["vs/editor/editor.main"], function () {
				let editor = monaco.editor.create(document.getElementById('js_html_edition_css_pdf'), {
					/*
					value: [
					champ.valeur_html
					].join('\n'),*/
					value: [
						vue_instance.modele_de_document.css
					].join('\n'),
					language: 'html',
					theme: 'vs-dark'
				});
				editor.getModel().onDidChangeContent((event) => {
					
					vue_instance.modele_de_document.css = editor.getValue();
					vue_instance.$forceUpdate();
				});
			});
		}, 300);

	</script>
@endsection

@push('scripts')

<script>

	$( function() {
		$( ".sortable" ).sortable();
		$( ".sortable" ).disableSelection();
	} );

</script>

@endpush

@push('donnees_pour_vuejs_data')

  	pdf_body: {!! json_encode($modele_de_document['body_json']) !!},
  	pdf_header: {!! json_encode($modele_de_document['header_json']) !!},
  	pdf_footer: {!! json_encode($modele_de_document['footer_json']) !!},
  	pdf_recap_footer: {!! json_encode($modele_de_document['recap_footer_json']) !!},
  	pdf_annexes: {!! json_encode($modele_de_document['annexes_json']) !!},

@endpush

@push('donnees_pour_vuejs_methods')

	ajouter_annexe() {

		vue_instance.pdf_annexes.splice(0, 0, '');
	},
		
	inserer_classe_au_css: function(nom_classe) {

		vue_instance.modele_de_document.css += "\n\n."+nom_classe+" { \n\n}";

		$('textarea[name=css]').focus();

	},

	creer_classe_css: function(index, index_colonne) {

		this.pdf_body[index].colonnes[index_colonne].classe = this.pdf_body[index].colonnes[index_colonne].label.normalize("NFD").replace(/[\u0300-\u036f.]/g, "").replace(' ','_').toLowerCase();

		//console.log(this.pdf_body[index].colonnes[index_colonne].classe);

		vue_instance.$forceUpdate();

	},

	enregistrer_pdf_css_document: function() {
		
		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.element.enregistrer', ['modele_de_document', $management_element->modele->id]) }}",
			dataType: "json",
			method: 'POST',
			data: {css:vue_instance.modele_de_document.css}

		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			toastr.success('{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.modifications_enregistrees')}}')
		});
		
	},

	enregistrer_numeros_de_page: function() {

		// On affiche le loader
		loading();

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.element.enregistrer', ['modele_de_document', $management_element->modele->id]) }}",
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_fiche_numeros_de_page').serialize()

		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			toastr.success('{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.modifications_enregistrees')}}')
		});

	},

	enregistrer_fonds_de_page: function() {

		// On affiche le loader
		loading();

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.element.enregistrer', ['modele_de_document', $management_element->modele->id]) }}",
			dataType: "json",
			method: 'POST',
			data: {
				fond_page_premiere: vue_instance.modele_de_document.fond_page_premiere,
				fond_page_milieu: vue_instance.modele_de_document.fond_page_milieu,
				fond_page_derniere: vue_instance.modele_de_document.fond_page_derniere,
			}

		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			toastr.success('{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.modifications_enregistrees')}}')
		});

	},

@endpush