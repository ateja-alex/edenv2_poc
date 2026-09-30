<div class="css_facturx_lisible">

	<div class="css_facturx_lisible_entete">
		<div>
			<h4 class="css_facturx_lisible_reference">{{ traduction('pdf.include.xml_facture_lisible.titre') }} {{ $reference_document }}</h4>
			<div data-verification-facturx="date_facture">
				@if(!empty($date))
					<span class="css_facturx_lisible_detail_label">{{ traduction('pdf.include.xml_facture_lisible.date_emission') }}</span> {{ $date }}<br>
					<span data-verification-facturx="date_echeance"><span class="css_facturx_lisible_detail_label">{{ traduction('pdf.include.xml_facture_lisible.echeance') }}</span> {{ $date_echeance ?: $date }}</span>
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.date_facture_manquante') }}</span>
				@endif
			</div>
			<div data-verification-facturx="reference_commande_client">
				<span class="css_facturx_lisible_detail_label">{{ traduction('pdf.include.xml_facture_lisible.reference_commande_client') }}</span>
				{{ $reference_commande_client ?: traduction('pdf.include.xml_facture_lisible.reference_commande_client_non_renseignee') }}
			</div>
		</div>
		<div class="css_facturx_lisible_entete_type">
			<div data-verification-facturx="type_code_facturx">
				<span class="css_facturx_lisible_detail_label">{{ traduction('pdf.include.xml_facture_lisible.type_code_facturx') }}</span>
				@if(!empty($type_code_facturx))
					<span class="badge badge-secondary">{{ $type_code_facturx }}</span>
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('messages.php.facture_vente.verification_facturx.type_code_facturx_manquant') }}</span>
				@endif
			</div>
			<div data-verification-facturx="cadre_facturation_facturx">
				<span class="css_facturx_lisible_detail_label">{{ traduction('pdf.include.xml_facture_lisible.cadre_facturation_facturx') }}</span>
				@if(!empty($cadre_facturation_facturx))
					<span class="badge badge-secondary">{{ $cadre_facturation_facturx }}</span>
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('messages.php.facture_vente.verification_facturx.cadre_facturation_facturx_manquant') }}</span>
				@endif
			</div>
		</div>
	</div>

	<div class="css_facturx_lisible_parties">
		<div class="css_facturx_lisible_partie">
			<div class="css_facturx_lisible_partie_titre">{{ traduction('pdf.include.xml_facture_lisible.vendeur') }}</div>
			<div class="css_facturx_lisible_partie_nom">{{ $vendeur_nom }}</div>
			<div data-verification-facturx="adresse_vendeur">
				@if(!empty($vendeur_adresse) && !empty($vendeur_code_postal) && !empty($vendeur_ville))
					{{ $vendeur_adresse }}<br>{{ $vendeur_code_postal }} {{ $vendeur_ville }}
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.adresse_vendeur_incomplete') }}</span>
				@endif
			</div>
			<div data-verification-facturx="identifiant_vendeur">
				@if(!empty($vendeur_siren))
					{{ traduction('pdf.include.xml_facture_lisible.siren') }} : {{ $vendeur_siren }}<br>{{ traduction('pdf.include.xml_facture_lisible.adresse_electronique') }} : {{ $vendeur_siren }}
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.siren_vendeur_manquant') }}</span>
				@endif
			</div>
			@if(!empty($vendeur_numero_tva))
				<div>{{ traduction('pdf.include.xml_facture_lisible.tva_intracommunautaire') }} : {{ $vendeur_numero_tva }}</div>
			@endif
		</div>
		<div class="css_facturx_lisible_partie">
			<div class="css_facturx_lisible_partie_titre">{{ traduction('pdf.include.xml_facture_lisible.acheteur') }}</div>
			<div data-verification-facturx="nom_acheteur">
				@if(!empty($acheteur_nom))
					{{ $acheteur_nom }}
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.nom_acheteur_manquante') }}</span>
				@endif
			</div>
			<div data-verification-facturx="adresse_acheteur">
				@if(!empty($acheteur_adresse))
					{{ $acheteur_adresse }}<br>{{ $acheteur_code_postal }} {{ $acheteur_ville }}
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.adresse_acheteur_manquante') }}</span>
				@endif
			</div>
			<div data-verification-facturx="identifiant_acheteur">
				@if(!empty($acheteur_siren))
					{{ traduction('pdf.include.xml_facture_lisible.siren') }} : {{ $acheteur_siren }}
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.siren_acheteur_manquant') }}</span>
				@endif
			</div>
			<div data-verification-facturx="annuaire_facturation">
				@if(!empty($acheteur_annuaire))
					{{ traduction('pdf.include.xml_facture_lisible.adresse_electronique') }} : {{ $acheteur_annuaire }}
				@else
					<span class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.annuaire_facturation_manquant') }}</span>
				@endif
			</div>
		</div>
	</div>

	<table class="css_facturx_lisible_table" data-verification-facturx="aucune_ligne">
		<thead>
			<tr>
				<th>#</th>
				<th>{{ traduction('document.colonnes.designation.titre') }}</th>
				<th>{{ traduction('document.colonnes.quantite.titre') }}</th>
				<th>{{ traduction('document.colonnes.tarif.titre') }}</th>
				<th>{{ traduction('document.colonnes.tva.titre') }}</th>
				<th>{{ traduction('document.colonnes.total.titre') }}</th>
			</tr>
		</thead>
		<tbody>
			@forelse($articles as $article)
			<tr>
				<td>{{ $article['ligne'] }}</td>
				<td>{{ $article['designation'] }}</td>
				<td>{{ $article['quantite'] }}</td>
				<td>{{ $article['tarif'] }} €</td>
				<td>{{ $article['tva'] }} %</td>
				<td>{{ $article['total'] }} €</td>
			</tr>
			@empty
			<tr>
				<td colspan="6" class="css_verification_facturx_manquant">{{ traduction('pdf.include.xml_facture_lisible.aucune_ligne_facturee') }}</td>
			</tr>
			@endforelse
		</tbody>
	</table>

	<div class="css_facturx_lisible_bas">
		<table class="css_facturx_lisible_table css_facturx_lisible_table_tva">
			<thead>
				<tr>
					<th>{{ traduction('document.colonnes.taux') }}</th>
					<th>{{ traduction('document.tableau_des_articles.categorie') }}</th>
					<th>{{ traduction('document.colonnes.total.titre') }}</th>
					<th>{{ traduction('document.colonnes.tva.titre') }}</th>
				</tr>
			</thead>
			<tbody>
				@foreach($tableau_tva as $informations)
				<tr>
					<td>{{ $informations['taux'] }} %</td>
					<td>{{ $informations['categorie'] }}</td>
					<td>{{ $informations['ht'] }} €</td>
					<td>{{ $informations['tva'] }} €</td>
				</tr>
				@endforeach
			</tbody>
		</table>

		<div class="css_facturx_lisible_totaux">
			<div><span>{{ traduction('pdf.document_gescom.total_ht') }}</span><strong>{{ $total_ht }} €</strong></div>
			<div><span>{{ traduction('pdf.document_gescom.total_tva') }}</span><strong>{{ $total_tva }} €</strong></div>
			<div class="css_facturx_lisible_total_ttc"><span>{{ traduction('pdf.document_gescom.total_ttc') }}</span><strong>{{ $total_ttc }} €</strong></div>
		</div>
	</div>

	<div class="css_facturx_lisible_mentions">
		<div data-verification-facturx="mention_indemnite_forfaitaire_facturx">
			@if(!empty($mention_indemnite_forfaitaire_facturx)){{ $mention_indemnite_forfaitaire_facturx }}@else<span class="css_verification_facturx_manquant">{{ traduction('messages.php.facture_vente.verification_facturx.mention_indemnite_forfaitaire_facturx_manquant') }}</span>@endif
		</div>
		<div data-verification-facturx="mention_penalites_retard_facturx">
			@if(!empty($mention_penalites_retard_facturx)){{ $mention_penalites_retard_facturx }}@else<span class="css_verification_facturx_manquant">{{ traduction('messages.php.facture_vente.verification_facturx.mention_penalites_retard_facturx_manquant') }}</span>@endif
		</div>
		<div data-verification-facturx="mention_escompte_facturx">
			@if(!empty($mention_escompte_facturx)){{ $mention_escompte_facturx }}@else<span class="css_verification_facturx_manquant">{{ traduction('messages.php.facture_vente.verification_facturx.mention_escompte_facturx_manquant') }}</span>@endif
		</div>
	</div>

</div>
