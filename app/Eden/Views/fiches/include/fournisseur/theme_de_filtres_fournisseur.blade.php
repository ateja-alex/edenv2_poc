<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('interface.fournisseur.theme_de_filtres_fournisseur.titre')
					<span style="font-size: 12px; margin-left: 50px;" id="enregistrement_des_donnees_themes_de_filtres">@traduction('interface.fournisseur.theme_de_filtres_fournisseur.donnees_a_jour')</span>
				</h4>
			</div>
			<div class="card-body">
				@if(isset($themes_de_filtres))
					@foreach($themes_de_filtres as $infos)

						<b>{{ $infos['theme_de_filtres']->nom }}</b><br/>

						<div class="js_badge_selectionnables">
							@foreach($infos['filtres_dispo'] as $id => $filtre)
								<span class="js_selection_filtre_sur_article badge @if(isset($infos['filtres_choisis'][$id])) badge-success @else badge-default @endif" id_filtre="{{ $id }}">{{ $filtre }}</span>
							@endforeach
						</div>

						<br/>
						<br/>
					@endforeach
				@endif
			</div>
		</div>
	</div>
</div>

@section('scripts')
<script>
				
	// on gère l'enregistrement des thèmes de filtres
	$('.js_selection_filtre_sur_article').on('click', function() {
		
		setTimeout(function() {
			
			$('#enregistrement_des_donnees_themes_de_filtres').text(vue_instance.traduction('interface.fournisseur.theme_de_filtres_fournisseur.enregistrement_donnees'));
			
			var filtres = [];
			$('.js_selection_filtre_sur_article.badge-success').each(function() {
				
				filtres.push($(this).attr('id_filtre'));
			});
			
			// on va chercher l'ensemble des filtres sélectionnés
			$.ajax({
				type: 'POST',
				url : "{{ route('base_eden.fiche.index_post', ['fournisseur', $management_element->modele->id, 'enregistre_filtres']) }}",
				data : {
					filtres: filtres
				},
				success: function (result) {
					
					$('#enregistrement_des_donnees_themes_de_filtres').text(vue_instance.traduction('interface.fournisseur.theme_de_filtres_fournisseur.donnees_a_jour'));
				}
			});
			
		}, 250);
		
	});
	
</script>
@endsection