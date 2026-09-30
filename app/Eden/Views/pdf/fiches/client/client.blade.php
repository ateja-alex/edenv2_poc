<h3>{!! traduction('pdf.client.informations', $langue) !!}</h3>
<table class="css_tableau_donnees_fiche_pdf css_tableau_info_client" style="z-index: 2;">
	<tbody>
		<tr>
			<td style="font-weight: bold;">{{ management('client')->champ('nom')->modele->nom }}</td>
			<td>{{$donnees->nom}} {{$donnees->prenom}}</td>
		</tr>
		<tr>
			<td style="font-weight: bold;">{{ management('client')->champ('adresse_email')->modele->nom }}</td>
			<td>{{$donnees->adresse_email}}</td>
		</tr>
		<tr>
			<td style="font-weight: bold;">{{ management('client')->champ('telephone')->modele->nom }}</td>
			<td>{{$donnees->telephone}}</td>
		</tr>

		<tr>
			<td style="font-weight: bold;">{{ management('client')->champ('responsable_commercial')->modele->nom }}</td>
			<td>{{ management('client')->champ('responsable_commercial')->affiche($donnees->responsable_commercial) }}</td>
		</tr>
	</tbody>
</table>
<table class="css_tableau_ca_indicatifs">
	<tbody>
		<tr>
			<td>
				<div class="css_chiffre_indicateur">
					{{ $donnees->ca['ca_n_moins_deux'] }} {!! maquette('devise_application_symbole') !!}
				</div>
				<div class="css_separateur_indicateur"></div>
				<span>{!! traduction('pdf.client.chiffre_affaires', $langue) !!} {{ date('Y', strtotime('-2 year')) }}</span>
			</td>
			<td>
				<div class="css_chiffre_indicateur">
					{{ $donnees->ca['ca_n_moins_un'] }} {!! maquette('devise_application_symbole') !!}
				</div>
				<div class="css_separateur_indicateur"></div>
				<span>{!! traduction('pdf.client.chiffre_affaires', $langue) !!} {{ date('Y', strtotime('-1 year')) }}</span>
			</td>
			<td>
				<div class="css_chiffre_indicateur">
					{{ $donnees->ca['ca'] }} {!! maquette('devise_application_symbole') !!}
				</div>
				<div class="css_separateur_indicateur"></div>
				<span>{!! traduction('pdf.client.chiffre_affaires', $langue) !!} {{ date('Y') }}</span>
			</td>
		</tr>
	</tbody>
</table>

