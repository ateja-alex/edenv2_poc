@php
	$langue = maquette('langue_par_defaut_code');
@endphp

@extends('eden::pdf.document_gescom')

@section('informations_document') 

<p style="font-size: 18px;text-align: right;margin: 0;">{!! traduction('pdf.commande_vente.titre', $langue) !!} {{ $document->reference_document }}</p>
<table style="width: 100%;text-align: right;"cellpadding="0">
	<tbody>
		<td>
			{!! traduction('pdf.document_gescom.date', $langue) !!}
			<br>                           
			@if(!empty($document->modalite_paiement_id))
			<br>
				{!! traduction('pdf.document_gescom.conditions_paiement', $langue) !!}
			@endif
		</td>
		<td style="text-align: right;">
			{{ formate_date('d/m/Y', $document->date) }}
			<br>
			@if(!empty($document->modalite_paiement_id))
			<br>
			{{ $document_management->champ('modalite_paiement_id')->affiche() }}
			@endif
		</td>
	</tbody>
</table>

@endsection

@section('texte_sous_totaux')
<p class="css_info_retard_paiement">
	@include('eden::pdf.include.document.texte_retard_paiement')
</p>
@endsection