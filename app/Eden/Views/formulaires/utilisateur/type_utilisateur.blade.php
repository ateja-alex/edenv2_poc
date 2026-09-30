<div class="row">
    <div class="col-sm-2">@traduction('formulaire.utilisateur.type_d_utilisateur')</div>
    <div class="col-sm-4">
        <select name="type_utilisateur" v-model="utilisateur.type_utilisateur">
            @foreach(management('utilisateur')->champ('type_utilisateur')->valeurs_possibles as $id_type => $type)
                @if($id_type != 2 || editeur())
                    <option value="{{$id_type}}">{{$type}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>