@if(super_admin() && isset(fonctionnalite('type_document_generer_pdf')[$management->_type_element]) && fonctionnalite('type_document_generer_pdf')[$management->_type_element] === true)
		<a class="css_action_icon secondaire fa fa-fw fa-file-pdf" 
			href="{{ route('base_eden.element.afficher_pdf', [$management->_type_element, $management->modele->id, basename($management->modele->pdf)]) }}?{{time()}}&regenerer=1"
			target="_blank"
			:title="traduction('document.actions.imprimer.afficher_pdf_avec_regeneration')"
			data-toggle="tooltip"></a>
@endif

@if(isset(fonctionnalite('type_document_generer_pdf')[$management->_type_element]) && fonctionnalite('type_document_generer_pdf')[$management->_type_element] === true)
	<a class="css_action_icon primaire fa fa-fw fa-file-pdf"
	   href="{{ route('base_eden.element.afficher_pdf', [$management->_type_element, $management->modele->id, basename($management->modele->pdf)]) }}?{{time()}}"
	   target="_blank"
		:title="traduction('document.actions.imprimer.afficher_pdf')"
		data-toggle="tooltip"></a>

@elseif($management->modele->facture_scannee != null)
	<a class="css_action_icon primaire fa fa-fw fa-file-pdf" 
			href="/storage/{{ basename($management->modele->facture_scannee) }}"  
			target="_blank"
			:title="traduction('document.actions.imprimer.afficher_pdf')"
			data-toggle="tooltip"></a>
@endif

@php

$modeles_de_document = modele('modele_de_document')
            ->where('type_de_document', 1)
            ->where('type_element_autres', $management->_type_element)
            ->orderBy('ordre')
            ->get();

@endphp

@if($modeles_de_document->isNotEmpty())

	<div class="btn-group">
		<span data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="css_action_icon primaire fa fa-fw fa-print"
			   :title="$root.traduction('interface.modele_de_document.impression')"
				data-toggle="tooltip"
			   ></i>
		</span>
		<div class="dropdown-menu">
			@foreach($modeles_de_document as $modele_de_document)
				<a class="dropdown-item" href="{{route('base_eden.fiche.generer_pdf_depuis_modele', [$management->_type_element, $management->modele->id, $modele_de_document->id])}}" target="_blank">{{$modele_de_document->nom}}</a>
			@endforeach
		</div>
	</div>

@endif