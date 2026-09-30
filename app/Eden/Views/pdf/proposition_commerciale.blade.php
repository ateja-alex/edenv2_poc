@php
	$langue = maquette('langue_par_defaut_code');
@endphp

{!! traduction('pdf.proposition_commerciale.details', $langue) !!}<br/><br/>
@foreach($articles as $article)
	<b>{{ $article->designation }}</b> : {{ $article->tarif }} {!! maquette('devise_application_symbole') !!} {!! traduction('pdf.proposition_commerciale.ht', $langue) !!}<br/>
@endforeach