<p style="text-align: right;cursor: pointer;" class="js_fermer_detail_ligne"><i class="fa fa-times" aria-hidden="true"></i></p>

@if(!empty($infos_commande_client)) 
	<b>@traduction('interface.listes.details_commande_achat_lignes.client') :</b> {!! $infos_commande_client['client'] !!}<br/>
	@if(!empty($infos_commande_client['projet']))
		
		<b>@traduction('interface.listes.details_commande_achat_lignes.affaire') :</b> {!! $infos_commande_client['projet'] !!}<br/>
	@endif
@else
	@traduction('interface.listes.details_commande_achat_lignes.aucune_commande')
@endif
