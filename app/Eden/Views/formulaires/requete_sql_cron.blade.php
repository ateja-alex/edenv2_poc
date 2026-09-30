<div class="row">
	@champ('requete_sql_cron', 'titre', 2,10)
</div>
<div class="row">
	@champ('requete_sql_cron', 'requete', 2,10)
</div>
<div class="row">
	<div class="col-sm-2">
		@traduction('formulaire.requete_sql_cron.lancement_tous_les')
	</div>
	<div class="col-sm-1">
		{!! management('requete_sql_cron')->champ('valeur_interval')->cree() !!}
	</div>
	<div class="col-sm-2">
		<select name="unite_interval" v-model="requete_sql_cron.unite_interval">
			<option value="minute">Minute(s)</option>
			<option value="heure">Heure(s)</option>
			<option value="jour">Jour(s)</option>
		</select>
	</div>
</div>
<div class="row">
	@champ('requete_sql_cron', 'derniere_execution', 2,8)
</div>
