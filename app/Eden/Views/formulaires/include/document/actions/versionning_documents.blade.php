@if($management->existe())
	<span class="dropdown"  data-toggle="tooltip" :title="traduction('document.actions.versionning_documents.versionning')">
		<i class="css_action_icon primaire fa fa-fw fa-book"data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
		<div class="dropdown-menu">
			@foreach(modele('versionning_document')->where(['type_element' => $management->_type_element, 'id_document' => $management->modele->id])->get() as $document_versionning )
				<a class="dropdown-item" href="{{ route('document.afficher_pdf_versionning', [ $document_versionning->id ]) }}">Document du {{ strftime("%d/%m/%G", strtotime($document_versionning->date))  }}@if(!empty($document_versionning->reference)) - {{ $document_versionning->reference }}@endif - {{ montant($document_versionning->montant, 2) }}{!! maquette('devise_application_symbole') !!}</a>
			@endforeach
		</div>
	</span>
@endif