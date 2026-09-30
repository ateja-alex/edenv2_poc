
<?php // temps_execution(' => debut vue liste_standard', 3); ?>

@php

	// on instancie cette variable pour récupérer eventuellement le nom d'une variable qu'on aura instancié sur une liste
	$type_element_fiche_client = $type_element.'_fiche_client';

	$colonnes_kanban = false;

	$nom_indicateur = 'indicateurs_'.$type_element;

	$version_composants = parametre('version_composants');

@endphp

@push('composants_vue')

	@if(empty($kanban))
        <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste.'.js') }}?version={{$version_composants}}"></script>
	@endif
@endpush

@if(!empty($kanban))

	<kanban-libre
			:id_liste="{{$id_liste}}"
			ref="kanban_libre_{{$id_liste}}"
			kanban="{{$kanban}}"
			:session="{
			@if(session()->has('message'))
				message : `{{ session()->get('message') }}`,
			@endif
			@if(session()->has('erreur'))
				erreur : `{{ session()->get('erreur') }}`,
			@endif
			@if(session()->has('erreurs'))
				erreurs : `{!! implode('<br/>', session()->get('erreurs')) !!}`,
			@endif
			}"

			@if(isset($options_liste['filtres_pour_fiche']) || (isset($filtres_pour_fiche) && !empty($filtres_pour_fiche[$id_liste])))
				:filtres_pour_fiche="filtres_pour_fiche_{{ $id_liste }}"
			@elseif(!empty($tableau_de_bord) && isset($id_rapport))
				:filtres_pour_fiche="filtres_rapports.{{ $id_rapport }}"
			@endif

			@if(isset(request()->recherche))
				recherche_par_defaut="{{ request()->recherche }}"
			@endif

			:informations_pour_fiche="{
				@if(isset($$nom_indicateur))
					indicateurs:'{{$nom_indicateur}}'
				@endif
			}"

			@if(isset($modele_par_defaut))
				:modele_par_defaut='{{ collect($modele_par_defaut) }}'
			@endif

			@if(super_admin() || mode_parametrage())
			:mode_parametrage=1
			@endif

			@if(!empty($options_liste['seulement_inactif']))
			:seulement_inactif="{{ $options_liste['seulement_inactif'] }}"
			@endif

			@if(!empty($options_liste['indicateur_source']))
			indicateur_source="{{ $options_liste['indicateur_source'] }}"
			@endif

			@if(!empty($options_liste['kanban_unite']))
			kanban_unite="{{ $options_liste['kanban_unite'] }}"
			@endif

			@if(!empty($options_liste['kanban_colonne_somme']))
			kanban_colonne_somme="{{ $options_liste['kanban_colonne_somme'] }}"
			@endif

			@if(!empty($options_liste['kanban_colonne_count']))
			:kanban_colonne_count="true"
			@endif

			@if(empty($tableau_de_bord))
			:pleine_hauteur="true"
			@endif
	>
	</kanban-libre>

@else

	<liste-libre-{{$id_liste}}
			ref="liste_libre_{{$id_liste}}"
			:session="{
			@if(session()->has('message'))
				message : `{{ session()->get('message') }}`,
			@endif
			@if(session()->has('erreur'))
				erreur : `{{ session()->get('erreur') }}`,
			@endif
			@if(session()->has('erreurs'))
				erreurs : `{!! implode('<br/>', session()->get('erreurs')) !!}`,
			@endif
			}"

			@if(isset($options_liste['filtres_pour_fiche']) || (isset($filtres_pour_fiche) && !empty($filtres_pour_fiche[$id_liste])))
				:filtres_pour_fiche="filtres_pour_fiche_{{ $id_liste }}"
			@elseif(!empty($tableau_de_bord) && isset($id_rapport))
				:filtres_pour_fiche="filtres_rapports.{{ $id_rapport }}"
			@endif

			@if(isset(request()->recherche))
				recherche_par_defaut="{{ request()->recherche }}"
			@endif

			:informations_pour_fiche="{
				@if(isset($$nom_indicateur))
					indicateurs:'{{$nom_indicateur}}'
				@endif
			}"

			@if(isset($modele_par_defaut))
				:modele_par_defaut='{{ collect($modele_par_defaut) }}'
			@endif
			@if(super_admin() || mode_parametrage())
			:mode_parametrage=1
			@endif

			@if(!empty($options_liste['seulement_inactif']))
			:seulement_inactif="{{ $options_liste['seulement_inactif'] }}"
			@endif

			@if(!empty($options_liste['indicateur_source']))
			indicateur_source="{{ $options_liste['indicateur_source'] }}"
			@endif
	>
	</liste-libre-{{$id_liste}}>
@endif

@push('donnees_pour_vuejs_data')

	afficher_transformation_stock: false,
	details_transformation_stock: {},
	transformation_stock_id: '',

	filtres_pour_fiche_{{ $id_liste }}: {},

	@if(isset($$nom_indicateur) )
		{{$nom_indicateur}}: {!! collect($$nom_indicateur) !!},
	@endif


@endpush

@push('donnees_pour_vuejs_created')
    @if(!empty($options_liste['filtres_pour_fiche']))
        @foreach($options_liste['filtres_pour_fiche'] as $cle => $valeur)
            @if($valeur === null)
                this.$set(this.filtres_pour_fiche_{{ $id_liste }}, '{{ $cle }}', null)
            @else
                this.$set(this.filtres_pour_fiche_{{ $id_liste }}, '{{ $cle }}', `{!! $valeur !!}`)
            @endif
        @endforeach
    @endif
@endpush
