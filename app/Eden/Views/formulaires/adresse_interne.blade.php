<div class="row">
	@champ('adresse_interne', 'nom_adresse', 4,8)
</div>
<div class="row">
	@champ('adresse_interne', 'societe', 4,8)
</div>
<div class="row">
	<div class="col-sm-4">
		{!! management('adresse_interne')->champ('prenom')->nom() !!} & {!! management('adresse_interne')->champ('nom')->nom() !!}
	</div>
	<div class="col-sm-4">{!! management('adresse_interne')->champ('prenom')->cree() !!}</div>
	<div class="col-sm-4">{!! management('adresse_interne')->champ('nom')->cree() !!}</div>
</div>
<div class="row">
	@champ('adresse_interne', 'adresse', 4,8)
</div>
<div class="row">
	@champ('adresse_interne', 'adresse_complement', 4,8)
</div>
<div class="row">
	<div class="col-sm-4">
		{!! management('adresse_interne')->champ('code_postal')->nom() !!} & {!! management('adresse_interne')->champ('ville')->nom() !!}
	</div>
	<div class="col-sm-4">{!! management('adresse_interne')->champ('code_postal')->cree() !!}</div>
	<div class="col-sm-4">{!! management('adresse_interne')->champ('ville')->cree() !!}</div>
</div>
<div class="row">
	@champ('adresse_interne', 'pays_id', 4,8)
</div>
<div class="row">
	<div class="col-sm-4">
		{!! management('adresse_interne')->champ('telephone_portable')->nom() !!}
	</div>
	<div class="col-sm-4">{!! management('adresse_interne')->champ('telephone_portable')->cree() !!}</div>
	<div class="col-sm-4">{!! management('adresse_interne')->champ('telephone_fixe')->cree() !!}</div>
</div>
