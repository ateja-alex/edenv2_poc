@php 
	$champ = management('modele_commentaire')->champ('commentaire');
	if(fonctionnalite('commentaires_wysiwyg_documents') !== true)
		$champ->modele->format_champ = '';
@endphp	
<div class="row">
	<div class="col-sm-2">
		{!! $champ->nom() !!}
	</div>
	<div class="col-sm-10">
		{!! $champ->cree() !!}
	</div>
</div>