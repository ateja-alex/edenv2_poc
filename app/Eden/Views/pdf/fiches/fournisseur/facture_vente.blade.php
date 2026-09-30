<h3>{!! traduction('pdf.facture_vente.factures', $langue) !!}</h3>

@if($donnees->isNotEmpty())

	<table class="css_tableau_donnees_fiche_pdf">
		<tbody>
			<tr>
				<td><b>{!! traduction('pdf.facture_vente.reference', $langue) !!}</b></td>
				<td><b>{!! traduction('pdf.facture_vente.date', $langue) !!}</b></td>
				<td><b>{!! traduction('pdf.facture_vente.ht', $langue) !!}</b></td>
				<td><b>{!! traduction('pdf.facture_vente.ttc', $langue) !!}</b></td>
				<td><b>{!! traduction('pdf.facture_vente.solde_du', $langue) !!}</b></td>
				<td><b>{!! traduction('pdf.facture_vente.statut', $langue) !!}</b></td>
			</tr>
			@foreach($donnees as $donnee)
				<tr>
					<td>{{ $donnee->reference_document }}</td>
					<td>{{ formate_date('d/m/Y', $donnee->date) }}</td>
					<td>{{ management($type_element)->champ('montant_document_ht')->affiche($donnee->montant_document_ht) }}{!! maquette('devise_application_symbole') !!}</td>
					<td>{{ management($type_element)->champ('montant_document_ttc')->affiche($donnee->montant_document_ttc) }}{!! maquette('devise_application_symbole') !!}</td>
					<td>{{ management($type_element)->champ('solde_document_ttc')->affiche($donnee->solde_document_ttc) }}{!! maquette('devise_application_symbole') !!}</td>
					<td><b>{!! traduction('pdf.facture_vente.a_definir', $langue) !!}</b></td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	<h4>{!! traduction('pdf.facture_vente.pas_de_donnees', $langue) !!}</h4>
@endif
