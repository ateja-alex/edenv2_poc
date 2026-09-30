@php
	$version_composants = parametre('version_composants');
@endphp

<div id="sortable" class="row">
	<?php $premier = true; ?>
	@foreach($contenu as $index => $element)
		
		@if($element->type == 1)

			<div	id="tdb_element_{{ $element->id }}"
					class="col-lg-{{$element->width}} rapport_sur_tdb"
					:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
					style="min-height:30px;float:left;overflow:auto; margin:0px 0px 10px 0px">

				<span
					v-if="mode_edition == 1"
					@click="modification_contenu({{ $element->id }})"
					style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
					>
						<i class="fa fa-fw fa-edit"></i>
				</span>

                @if($element->type_rapport == 'kanban')
					@include('eden::listes.includes.liste_standard', $element['donnees_liste'])
				@elseif($element->type_rapport == 'liste_libre')
					<liste-libre-{{$element['donnees_liste']['id_liste']}}
						ref="liste_libre_{{$element['donnees_liste']['id_liste']}}"
				
						@if(isset($element['donnees_liste']['id_rapport']))
							:filtres_pour_fiche="filtres_rapports.{{ $element['donnees_liste']['id_rapport'] }}"
						@endif

						@if(isset($element['donnees_liste']['modele_par_defaut']))
							:modele_par_defaut="{{ collect($element['donnees_liste']['modele_par_defaut']) }}"
						@endif
						@if(super_admin() || mode_parametrage())
						:mode_parametrage=1
						@endif
					>
					</liste-libre-{{$element['donnees_liste']['id_liste']}}>

					@push('composants_vue')
						<script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$element['donnees_liste']['id_liste'].'.js') }}?version={{$version_composants}}"></script>
					@endpush
                @else
                    <rapport
						ref="rapport_{{ $element->element }}"
                        id_rapport="{{ $element->element }}"
						:filtres_pour_fiche="filtres_rapports.{{ $element->element }}"
                    ></rapport>
                @endif

			</div>
		@elseif($element->type == 2)
			<div	id="tdb_element_{{ $element->id }}"
					class="col-lg-{{$element->width}}"
					v-bind:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
					style="min-height:30px;float:left;overflow:auto;margin:0px 0px; 10px; 0px">

				<span v-if="mode_edition == 1"
					@click="modification_contenu({{ $element->id }})"
					style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
					>
						<i class="fa fa-fw fa-edit"></i>
				</span>
				<a  href="javascript:;"
					data-toggle="modal"
					data-target="#modal_ajout_section"
					v-if="mode_edition == 1"
					@click="tableau_de_bord_contenu_section=JSON.parse(JSON.stringify(tableau_de_bord_contenu_vierge));tableau_de_bord_contenu=tableau_de_bord_tous_contenus[{{ $element->id }}];"
					style="position:absolute;right:40px;top:10px;z-index:1000;opacity:0.5"
					>
						<i class="fa fa-fw fa-plus"></i>
				</a>

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header">
								<h4>{!! $element->element !!} </h4>
							</div>
							<div class="card-body">
								@if($element->section != null)
									@foreach($element->section as $section)

										<!-- indicateur -->
										@if($section['type'] == 4)
											<div	id="tdb_element_{{ $section['id'] }}"
													class="col-lg-{{$section['width']}}"
													v-bind:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
													style="min-height:30px;h_eight:{{$section['height']*50-20}}px;float:left;overflow:auto;margin:10px 0px;">

												<span v-if="mode_edition == 1"
													@click="modification_contenu({{ $section['id'] }})"
													style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
													>
														<i class="fa fa-fw fa-edit"></i>
												</a>

												{!! $section['html'] !!}
											</div>
										<!-- rapport -->
										@elseif($section['type'] == 7)
											<div	id="tdb_element_{{ $section['id'] }}"
													class="col-lg-{{$section['width']}}"
													v-bind:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
													style="min-height:30px;h_eight:{{$section['height']*50-20}}px;float:left;overflow:auto;margin:10px 0px;">

												<span v-if="mode_edition == 1"
													@click="modification_contenu({{ $section['id'] }})"
													style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
													>
														<i class="fa fa-fw fa-edit"></i>
												</span>

												{!! $section['html'] !!}
											</div>
										<!-- section -->
										@elseif($section['type'] == 3)
											<div	id="tdb_element_{{ $section['id'] }}"
													class="col-lg-{{$section['width']}}"
													v-bind:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
													style="min-height:30px;h_eight:{{$section['height']*50-20}}px;float:left;overflow:auto;margin:10px 0px;">

												<span v-if="mode_edition == 1"
													@click="modification_contenu({{ $section['id'] }})"
													style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
													>
														<i class="fa fa-fw fa-edit"></i>
												</span>

												<div class="css_rang_utilisateurs">
													{!! $section['element'] !!}
												</div>

											</div>
										<!-- début de boucle -->
										@elseif($section['type'] == 5)


										@endif
									@endforeach
								@else
									{!! traduction('interface.tableau_de_bord.bloc_vide') !!}
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>

		@elseif($element->type == 3)

			<div	id="tdb_element_{{ $element->id }}"
					class="col-lg-{{$element->width}}"
					:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
					style="min-height:30px;float:left;overflow:auto;margin:0px 0px 10px 0px">

				<span v-if="mode_edition == 1"
					@click="modification_contenu({{ $element->id }})"
					style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
					>
						<i class="fa fa-fw fa-edit"></i>
				</span>

				<div class="row">
					<div class="col-md-12">
						@if(view()->exists('eden::tableau_de_bord.composants.'.$element->element))
							{!! view('eden::tableau_de_bord.composants.'.$element->element,['element' => $element]) !!}
						@endif
					</div>
				</div>
			</div>

		@elseif($element->type == 4)

			<div	id="tdb_element_{{ $element->id }}"
					class="col-lg-{{$element->width}}"
					:style="{ border: 'solid ' + mode_edition + 'px #aaa' }"
					style="min-height:30px;h_eight:{{$element->height*50-20}}px;float:left;overflow:auto;margin:10px 0px;">

				<span v-if="mode_edition == 1"
					@click="modification_contenu({{ $element->id }})"
					style="position:absolute;right:10px;top:10px;z-index:1000;opacity:0.5"
					>
						<i class="fa fa-fw fa-edit"></i>
				</span>

				<div class="row">
					<div class="col-md-12" v-html="tableau_de_bord_tous_contenus[{{ $element->id }}].html"></div>
				</div>
			</div>

		@endif

		<?php $premier = false; ?>

	@endforeach

</div>