<h3>{!! traduction('pdf.avoir_vente.avoirs', $langue) !!}</h3>
@if($donnees->isNotEmpty())
	<table class="css_tableau_donnees_fiche_pdf">
		<tbody>
			<tr>
				<td><b>{{ management('avoir_vente')->champ('reference_document')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('date')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('montant_document_ht')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('montant_document_ttc')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('solde_document_ttc')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('statut')->modele->nom }}</b></td>
			</tr>
			<tr>
				@foreach($donnees as $donnee)
					<td>{{ $donnee->reference_document }}</td>
					<td>{{ formate_date('d/m/Y', $donnee->date) }}</td>
					<td>{{ management('avoir_vente')->champ('montant_document_ht')->affiche($donnee->montant_document_ht) }}{!! maquette('devise_application_symbole') !!}</td>
					<td>{{ management('avoir_vente')->champ('montant_document_ttc')->affiche($donnee->montant_document_ttc) }}{!! maquette('devise_application_symbole') !!}</td>
					<td>{{ $donnee->solde_document_ttc }}</td>
					<td><b>à définir</b></td>
				@endforeach	
			</tr>
		</tbody>
	</table>
@else
	<h4>{!! traduction('pdf.avoir_vente.pas_de_donnees', $langue) !!}</h4>
@endif
