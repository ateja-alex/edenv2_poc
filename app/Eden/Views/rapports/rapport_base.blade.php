<div id="{{$id_rapport}}" class="highcharts-light">
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-3 @stack('classes_specifiques_conteneur_rapport')" @stack('spectifique_conteneur_rapport')>
			
				@if(View::hasSection('header_rapport_'.$id_rapport))
					
					@yield('header_rapport_'.$id_rapport)
				@else
				
					<div class="card-header">
						<!-- les options du rapport -->
						@foreach($options as $option => $informations_pour_option)

							@if($option === "recherche")
								
								<div class="pull-right" style="display: flex; float: right">
									<div class="css_rapport_filtre_et_options_recherche">
										@include('eden::rapports.filtres_et_options.'.$option, $informations_pour_option)
									</div>
								</div>
							@endif

						@endforeach

						<div class="pull-right" style="display: flex; float: right">
							<!-- les options -->
							@foreach($options as $option => $informations_pour_option)

								@if($option === "recherche")
									@php continue; @endphp
								@endif
								
								@if($option === "pagination")
									@php continue; @endphp
								@endif

								<div class="css_rapport_filtre_et_options">
									@include('eden::rapports.filtres_et_options.'.$option, $informations_pour_option)
								</div>
							@endforeach
							
							@if(isset($rapport->donnees_a_conserver))
								@foreach($rapport->donnees_a_conserver as $cle_tmp => $valeur_tmp)
									<input type="hidden" class="js_champ_{{$id_rapport}}" name="donnees_a_conserver_{{$id_rapport}}[{{$cle_tmp}}]" value="{{ $valeur_tmp }}" />
								@endforeach
							@endif
						</div>
						<h4>
							@if(!empty($rapport->rapport_libre))
								{!! traduction($rapport->rapport_libre->index_traduction.'.titre') !!}
							@else
								{!! $titre_du_rapport !!}
							@endif
							
							@yield('option_abonnement')
						</h4>
					</div>
				@endif
				
				<div class="card-body">
					<div class="css_actualisation_rapport js_actualisation_rapport">@traduction('rapport.divers.cliquez_ici_pour_actualiser_le_rapport')</div>
					<div class="css_actualisation_rapport_en_cours js_actualisation_rapport_en_cours" style="display:none">
						<img src="{{ asset('eden/images/ajax_loader.gif') }}" />
						
					</div>
					<?php
					/*
					<div class="js_rapport"  style="{{ ($activer_scroll) ? 'overflow-x:scroll' : '' }}">
					*/
					?>
					<div class="js_rapport css_rapport" style="overflow-x: auto">
						@section('contenu_rapport')
                            <div id="graphique_highcharts_{{ $id_rapport }}" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        @show
					</div>
					@if(isset($options['pagination']))
						<div class="card-footer">
							<nav class='css_nav_pagination_listes'>
								<ul class="pagination css_pagination_perso">
									
									@if($options['pagination']['page'] > 1)
										<li class="page-item">
											<a class="page-link" onClick="$('#pagination_{{ $id_rapport }}').val(1); actualise_rapport('{{$id_rapport}}');">@traduction('rapport.divers.premiere_page')</a>
										</li>
									@endif
									
									@for($i=1; $i<=$options['pagination']['pages']; $i++)
										
										@if($i - 5 > $options['pagination']['page'])
											@continue
										@endif
										
										@if($i + 5 < $options['pagination']['page'])
											@continue
										@endif

										<li class="page-item @if($i == $options['pagination']['page']) active @endif" onClick="$('#pagination_{{ $id_rapport }}').val({{ $i }}); actualise_rapport('{{$id_rapport}}');">
											<a class="page-link">{{$i}}</a>
										</li>
									@endfor
									
									@if($options['pagination']['page'] < $options['pagination']['pages'])
										<li class="page-item">
											<a class="page-link" onClick="$('#pagination_{{ $id_rapport }}').val({{ $options['pagination']['pages'] }}); actualise_rapport('{{$id_rapport}}');">@traduction('rapport.divers.derniere_page')</a>
										</li>
									@endif
								</ul>
							</nav>
						</div>
						<input type="hidden" id="pagination_{{ $id_rapport }}" value="{{ $options['pagination']['page'] }}" />
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

@if(!empty($rapport->vue_standard_js))
    @push('scripts')

        <script>
            @include('eden::rapports.js.' . $rapport->vue_standard_js)
        </script>

    @endpush
@endif