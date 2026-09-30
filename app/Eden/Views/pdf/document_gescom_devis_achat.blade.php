@php
	$langue = maquette('langue_par_defaut_code');
@endphp

@extends('eden::pdf.document_gescom')

@section('informations_document') 

<p style="font-size: 18px;text-align: right;margin: 0;">{!! traduction('pdf.devis.titre', $langue) !!} {{ $document->reference_document }}</p>
<table style="width: 100%;text-align: right;"cellpadding="0">
	<tbody>
		<td>
			{!! traduction('pdf.devis.date_devis', $langue) !!}
			<br>
			{!! traduction('pdf.devis.date_expiration', $langue) !!}
		</td>
		<td style="text-align: right;">
			{{ formate_date('d/m/Y', $document->date) }}
			<br>
			{{ formate_date('d/m/Y', $document->date_expiration) }}
		</td>
	</tbody>
</table>

@endsection

@section('texte_sous_totaux')
@if($document->commentaires != null)
	<p class="css_info_retard_paiement">
		{{ nl2br($document->commentaires) }}
	</p>
@endif
@endsection

@section('articles')
	@include('eden::pdf.include.document.articles_sans_tarifs')
@endsection

