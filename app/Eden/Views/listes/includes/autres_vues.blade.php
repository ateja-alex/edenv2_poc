@if($autres_vues->isNotEmpty())

    <div class="dropdown dropdown_hover" style="display: inline-block;" data-toggle="tooltip" data-placement="top" :title="traduction('interface.listes.autres_vues')">
        <span class="css_ajouter_element css__lien dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-ellipsis-v css_action_icon"></i>
        </span>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            @foreach($autres_vues as $autre_vue)
                <a class="dropdown-item" href="{{ route('base_eden.liste.rapport', [$autre_vue->id_rapport], false) }}"> <span v-html="traduction('rapport.{{$autre_vue->id_rapport}}.titre')"></span></a>
            @endforeach
        </div>
    </div>
    <span class="css_separateur_icon_action"></span>
@endif
