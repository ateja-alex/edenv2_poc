@php
	$langue = maquette('langue_par_defaut_code');
@endphp

@extends('eden::pdf.document_gescom')

@section('informations_document') 

<p style="font-size: 18px;text-align: right;margin: 0;">{!! traduction('pdf.bl_vente.titre', $langue) !!} {{ $document->reference_document }}</p>
<table style="width: 100%;text-align: right;"cellpadding="0">
	<tbody>
		<td>
			{!! traduction('pdf.document_gescom.date', $langue) !!}
		</td>
		<td style="text-align: right;">
			{{ formate_date('d/m/Y', $document->date) }}
		</td>
	</tbody>
</table>

@endsection

@section('articles')
	@include('eden::pdf.include.document.articles_sans_tarifs')
@endsection