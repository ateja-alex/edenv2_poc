<div class="row">
	@champ('ticket','titre', 2,7)
    <div class="col-sm-2"><input name="urgent" type="checkbox" value="1" v-model="ticket.urgent"> @traduction('formulaire.ticket.urgent')</div>
</div>
<div class="row">
	@champ('ticket','message', 2,10)
</div>
<div class="row">
	@champ('ticket','piece_jointe', 2,10)
</div>