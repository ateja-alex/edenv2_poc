<div class="row">
    <div class="col-md-2">
        {!! management('ticket_client')->champ('titre')->nom() !!}
    </div>
    <div class="col-md-10">
        {!! management('ticket_client')->champ('titre')->disabled(true)->cree() !!}
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        {!! management('ticket_client')->champ('description')->nom() !!}
    </div>
    <div class="col-md-12">
        {!! management('ticket_client')->champ('description')->disabled(true)->cree() !!}
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        {!! management('ticket_client')->champ('utilisateur_id')->nom() !!}
    </div>
    <div class="col-md-6">
        {!! management('ticket_client')->champ('utilisateur_id')->disabled(true)->cree() !!}
    </div>
</div>