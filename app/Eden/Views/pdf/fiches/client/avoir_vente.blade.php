<h3>{!! traduction('pdf.avoir_vente.avoirs', $langue) !!}</h3>
@if($donnees->isNotEmpty())
	<table class="css_tableau_donnees_fiche_pdf">
		<tbody>
			<tr>
				<td><b>{{ management('avoir_vente')->champ('reference_document')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('date')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('objet')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('montant_document_ht')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('montant_document_ttc')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('solde_document_ttc')->modele->nom }}</b></td>
				<td><b>{{ management('avoir_vente')->champ('statut')->modele->nom }}</b></td>
			</tr>
				@foreach($donnees as $donnee)
					<tr>
						<td>{{ $donnee->reference_document }}</td>
						<td>{{ formate_date('d/m/Y', $donnee->date) }}</td>
						<td>{{ $donnee->objet }}</td>
						<td>{{ management('avoir_vente')->champ('montant_document_ht')->affiche($donnee->montant_document_ht) }}{!! maquette('devise_application_symbole') !!}</td>
						<td>{{ management('avoir_vente')->champ('montant_document_ttc')->affiche($donnee->montant_document_ttc) }}{!! maquette('devise_application_symbole') !!}</td>
						<td>{{ $donnee->solde_document_ttc }}{!! maquette('devise_application_symbole') !!}</td>
						@if($donnee->regle == 1)
							<td><b>{!! traduction('pdf.avoir_vente.oui', $langue) !!}</b></td>
						@else
							<td><b>{!! traduction('pdf.avoir_vente.non', $langue) !!}</b></td>
						@endif
					</tr>
				@endforeach
		</tbody>
	</table>
@else
	<h4>{!! traduction('pdf.avoir_vente.pas_de_donnees', $langue) !!}</h4>
@endif
