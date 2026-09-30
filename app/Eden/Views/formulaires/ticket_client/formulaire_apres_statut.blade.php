<div class="row">
    <div class="col-md-6">
        {!! management('ticket_client')->champ('priorite')->nom() !!}
    </div>
    <div class="col-md-6">
        {!! management('ticket_client')->champ('priorite')->disabled(true)->cree() !!}
    </div>
</div>