<h3>{!! traduction('pdf.devis.titre', $langue) !!}</h3>

@if($donnees->isNotEmpty())

	<table class="css_tableau_donnees_fiche_pdf">
		<tbody>
			<tr>
				<td><b>{{ management('devis_vente')->champ('reference_document')->modele->nom }}</b></td>
				<td><b>{{ management('devis_vente')->champ('date')->modele->nom }}</b></td>
				<td><b>{{ management('devis_vente')->champ('montant_document_ht')->modele->nom }}</b></td>
				<td><b>{{ management('devis_vente')->champ('montant_document_ttc')->modele->nom }}</b></td>
				<td><b>{{ management('devis_vente')->champ('statut')->modele->nom }}</b></td>
			</tr>
			@foreach($donnees as $donnee)
				<tr>
					<td>{{ $donnee->reference_document }}</td>
					<td>{{ formate_date('d/m/Y', $donnee->date) }}</td>
					<td style="white-space: nowrap;">{{ management('devis_vente')->champ('montant_document_ht')->affiche($donnee->montant_document_ht) }}{!! maquette('devise_application_symbole') !!}</td>
					<td style="white-space: nowrap;">{{ management('devis_vente')->champ('montant_document_ttc')->affiche($donnee->montant_document_ttc) }}{!! maquette('devise_application_symbole') !!}</td>
					<td><b>{!! traduction('pdf.devis.a_definir', $langue) !!}</b></td>
				</tr>
			@endforeach
		</tbody>
	</table>

@else
	<h4>{!! traduction('pdf.devis.pas_de_donnees', $langue) !!}</h4>
@endif

