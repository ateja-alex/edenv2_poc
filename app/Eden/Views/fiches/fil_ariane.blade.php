@php
    $traduction_element = \Illuminate\Support\Facades\Blade::compileString('@traduction("'.table_libre($type_element)->index_traduction.'","element")');

    $traduction_element_pluriel = \Illuminate\Support\Facades\Blade::compileString('@traduction("'.table_libre($type_element)->index_traduction.'","element_pluriel")');
@endphp

@if(admin() && mode_parametrage() === true)
	
	@include('eden::includes.fil_ariane', ['fil_ariane' => array(
		array('route' => 'base_eden.liste.index', 'arguments' => [table_libre($type_element)->type_element], 'nom' => $traduction_element_pluriel),
		array('nom' => $traduction_element.' - <span v-pre>' . str_replace(array('<br/>', '<br>'), ', ', management($type_element, $id_element)->affiche()) . '</span> <a href="'.route('parametrage.table_libre.zoom', ['type_element' => $type_element]).'" class="css_bouton_modifier_liste_primaire">
			<i class="fas fa-cog"></i> Paramétrer "'.$traduction_element_pluriel.'"
		</a>')
	)])


@else
	
	@include('eden::includes.fil_ariane', ['fil_ariane' => array(
		array('route' => 'base_eden.liste.index', 'arguments' => [table_libre($type_element)->type_element], 'nom' => $traduction_element_pluriel),
		array('nom' => $traduction_element.' - <span v-pre>' . str_replace(array('<br/>', '<br>'), ', ', management($type_element, $id_element)->affiche()).'</span>')
	)])
@endif