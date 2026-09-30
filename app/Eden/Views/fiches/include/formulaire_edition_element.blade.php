@if($modification_possible)

<formulaire-fiche :route="'{{ route('base_eden.element.enregistrer', [$management_element->_type_element, $management_element->modele->id], false) }}'" :type_element="'{{$management_element->_type_element}}'" :element_id='{{ $management_element->modele->id }}' ref="formulaire_edition_element" @if(admin() && mode_parametrage() === true && isset($type_element) && table_libre($type_element) !== null) :mode_parametrage=1 @endif>
	@if(empty(moi_extranet()))
		<template slot="titre">
			<?php
			// temps_execution('formulaire_edition_element header : debut');
			?>
			<template v-pre>{!! $management_element->affiche_fiche_type() !!}</template>

			<?php
			// temps_execution('formulaire_edition_element header : fin');
			?>
		</template>
	@endif
</formulaire-fiche>

@push('donnees_pour_vuejs_data')
	@if(isset(${$management_element->_type_element}))
		{{ $management_element->_type_element }}: {!! ${$management_element->_type_element} !!},
	@else
		{{ $management_element->_type_element }}: {!! $management_element->modele !!},
	@endif
@endpush

@else
	@include('eden::fiches.include.formulaire_affichage_element')
@endif
