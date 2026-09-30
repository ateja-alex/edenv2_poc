<div class="row" style="margin-bottom: 2%; @if(env('APP_ENV') == 'preprod') margin-bottom: 120px; @else margin-bottom: 53px; @endif">
	<div class="fil_ariane">
		@if(env('APP_ENV') == 'preprod' && !isset($tableau_de_bord))
			<div class="alert alert-warning" style="text-align: center; font-size: 25px; margin-top: -13px;">@traduction('interface.fil_ariane.preprod')</div>
		@endif
		<h5 class="contenu_fil_ariane">

			@if(!isset($tableau_de_bord) && (!isset($structure['options']['afficher_fil_ariane']) || $structure['options']['afficher_fil_ariane'] == 1))
				
				<span class="route_fil_ariane">
					
					<!-- le fil d'ariane -->
					<a href="{{ URL::to(empty(moi()) ? maquette('page_accueil') : 'eden/accueil') }}" style="color: #212121;" >
						<i class="fa fa-home" aria-hidden="true" onmouseover="this.style.transform='scale(1.5)';" onmouseout="this.style.transform='scale(1)';" style="transition: transform .2s;"></i>
					</a>

					@hasSection('fil_ariane')
						@yield('fil_ariane')
					@else

						@foreach($fil_ariane as $info_lien_fil_ariane)

							>

							@if(isset($info_lien_fil_ariane['route']) && isset($info_lien_fil_ariane['arguments']) && empty(moi_extranet()))
								<a href="{{ route($info_lien_fil_ariane['route'], $info_lien_fil_ariane['arguments']) }}" style="color: #212121;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
									@if(isset($info_lien_fil_ariane['nom']))
										{!! $info_lien_fil_ariane['nom'] !!}
									@elseif(isset($info_lien_fil_ariane['nom_vue']))
										<span v-html="{{$info_lien_fil_ariane['nom_vue']}}"></span>
									@endif
								</a>
							@elseif(isset($info_lien_fil_ariane['route']) && empty(moi_extranet()))
								<a href="{{ route($info_lien_fil_ariane['route']) }}" style="color: #212121;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
									@if(isset($info_lien_fil_ariane['nom']))
										{!! $info_lien_fil_ariane['nom'] !!}
									@elseif(isset($info_lien_fil_ariane['nom_vue']))
										<span v-html="{{$info_lien_fil_ariane['nom_vue']}}"></span>
									@endif
								</a>
							@else


								<span style="color: #a3a3a3;">
									@if(isset($info_lien_fil_ariane['nom']))
										{!! $info_lien_fil_ariane['nom'] !!}
									@elseif(isset($info_lien_fil_ariane['nom_vue']))
										<span v-html="{{$info_lien_fil_ariane['nom_vue']}}"></span>
									@endif
								</span>
							@endif
						@endforeach

					@endif
				</span>

			@else
				<span></span>
			@endif

			<!-- les options dans le fil d'ariane -->
			<div class="options_fil_ariane">

				@if(isset($options_fil_ariane))
					
					@foreach(['gauche', 'droite'] as $position_option)
						<div class="options_fil_ariane_{{ $position_option }}">
							@foreach($options_fil_ariane as $option_fil_ariane)

								@if(($position_option === 'gauche' && !empty($option_fil_ariane['option_a_droite'])) ||
									($position_option === 'droite' && empty($option_fil_ariane['option_a_droite'])))
									@continue
								@endif

								<template v-if="{{ $option_fil_ariane['v-if'] ?? 'true' }}">
									@if(in_array($type_element, \App\Eden\Variables::$documents_gescom) && ($management->existe() || !empty($option_fil_ariane['disponible_creation'])) && view()->exists("eden::formulaires.include.document.actions.".$option_fil_ariane['id']))
										@include("eden::formulaires.include.document.actions.".$option_fil_ariane['id'], $option_fil_ariane['parametres'] ?? [])
									@elseif(view()->exists("eden::fiches.include." . $type_element . ".options_fil_ariane." . $option_fil_ariane['id']))
										@include("eden::fiches.include." . $type_element . ".options_fil_ariane." . $option_fil_ariane['id'], $option_fil_ariane['parametres'] ?? [])
									@elseif(view()->exists("eden::fiches.include.options_fil_ariane." . $option_fil_ariane['id']))
										@include("eden::fiches.include.options_fil_ariane." . $option_fil_ariane['id'], $option_fil_ariane['parametres'] ?? [])
									@endif
								</template>
							@endforeach
						</div>
					@endforeach
				@endif

				@yield('options_fil_ariane')

			</div>
		</h5>
	</div>
</div>