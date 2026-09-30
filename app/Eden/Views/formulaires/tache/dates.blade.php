<div class="row" style="margin-top: 5px;">
    <div class="col-sm-2">{!! management('tache')->champ('date_de_debut')->modele->nom !!}</div>
    <div style="padding: 0 15px;min-width: 251px;">
        {!!
            management('tache')->champ('date_de_debut')
                ->attr(':cacher_champ_time','tache.journee_entiere == 1')
                ->attr(':lecture_seule', 'tache.participant === 1')
                ->cree()
        !!}
    </div>
    <div style="display: flex;gap: 10px;align-items: center">
        {!! management('tache')->champ('journee_entiere')->attr(':lecture_seule', 'tache.participant === 1')->cree() !!}
        <div>{!! management('tache')->champ('journee_entiere')->modele->nom !!}</div>
    </div>

</div>
<div class="row" style="margin-top: 5px;">
    <div class="col-sm-2">{!! management('tache')->champ('date_de_fin')->modele->nom !!}</div>
    <div class="col-sm-4">
        {!!
            management('tache')->champ('date_de_fin')
                ->attr(':cacher_champ_time','tache.journee_entiere == 1')
                ->attr(':lecture_seule', 'tache.participant === 1')
                ->cree()
        !!}
    </div>
</div>