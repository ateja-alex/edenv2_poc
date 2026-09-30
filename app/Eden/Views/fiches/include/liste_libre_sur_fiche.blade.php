
@php
	$version_composants = parametre('version_composants');
	$liste_libre = $listes_sur_fiche['unitaire'][$module];
    $liste_id = $liste_libre['liste_libre']->id;
    $v_model = $liste_libre['v_model'];

    if(!empty($liste_libre['contenu_cle_primaire']))
    	$contenu_cle_primaire = $liste_libre['contenu_cle_primaire'];

    if(!empty($liste_libre['type_element_primaire']))
    	$type_element_primaire = $liste_libre['type_element_primaire'];

    $rapport_sur_fiche = \App\Eden\Models\Rapport_libre::where('id_rapport', $liste_libre['liste_libre']->id_rapport)->first();
    $kanban_sur_fiche = $rapport_sur_fiche->kanban ?? false;
@endphp

@if(!empty($kanban_sur_fiche))

	<kanban-libre
		:id_liste="{{ $liste_id }}"
		ref="kanban_libre_{{ $liste_id }}"

		@if(!empty($contenu_cle_primaire) && !empty($type_element_primaire))
			v-if="{{ $v_model }}.{{ $contenu_cle_primaire }} == '{{ $type_element_primaire }}'"
		@endif
		:session="{}"
		:seulement_inactif="0"
		kanban="{{ $kanban_sur_fiche }}"

		:filtres_pour_fiche="filtres_pour_fiche[{{ $liste_id }}]"
		:modele_par_defaut="modele_par_defaut_fiche[{{ $liste_id }}]"

		@if(super_admin() || mode_parametrage())
			:mode_parametrage=1
		@endif
	>
	</kanban-libre>

@else

	@push('composants_vue')
		<script type="text/javascript" src="{{ '/storage/composants/liste_libre_'.$liste_id.'.js' }}?version={{$version_composants}}"></script>
	@endpush

	<liste-libre-{{ $liste_id }}
		ref="liste_libre_{{ $liste_id }}"

		@if(!empty($contenu_cle_primaire) && !empty($type_element_primaire))
			v-if="{{ $v_model }}.{{ $contenu_cle_primaire }} == '{{ $type_element_primaire }}'"
		@endif
		:session="{}"
		:seulement_inactif="0"

		:filtres_pour_fiche="filtres_pour_fiche[{{ $liste_id }}]"
		:modele_par_defaut="modele_par_defaut_fiche[{{ $liste_id }}]"

		@if(super_admin() || mode_parametrage())
			:mode_parametrage=1
		@endif
	>
	</liste-libre-{{ $liste_id }}>

@endif