<option value="">Supprimer</option>
@if($document)
    @foreach($modules as $module => $informations)
        <option value="{{ $module }}">{{ $informations['nom'] }}</option>
    @endforeach
@if(count($listes_libres) >0)
    <optgroup label="Listes libres">
        @foreach($listes_libres as $liste_libre)
            <option value="{{ $liste_libre->id_rapport }}">{{ traduction($liste_libre->index_traduction.'.titre') }}</option>
        @endforeach
    </optgroup>
@endif
@elseif($independant)
    @foreach($modules as $module => $informations)
        <option value="{{ $module }}">{{ $informations['nom'] }}</option>
    @endforeach
@else
    <optgroup label="Vues spécifiques">
    @foreach($modules as $module => $nom_module)
        <option value="{{ $module }}">{{ $nom_module }}</option>
    @endforeach
    </optgroup>
    <optgroup label="Listes libres">
    @foreach($listes_libres as $liste_libre)
        <option value="{{ $liste_libre->id_rapport }}">{{ traduction($liste_libre->index_traduction.'.titre') }}</option>
    @endforeach
    </optgroup>
    <optgroup label="Rapports libres">
    @foreach($rapports_libres as $rapport_libre)
        <option value="{{ $rapport_libre->id_rapport }}">{{ traduction($rapport_libre->index_traduction.'.titre') }}</option>
    @endforeach
    </optgroup>
@if(count($cartes) > 0)
    <optgroup label="Cartes">
        @foreach($cartes as $carte)
            <option value="{{ $carte->id_rapport }}">{{ traduction($carte->index_traduction.'.titre') }}</option>
        @endforeach
    </optgroup>
@endif

    
    <optgroup label="Formulaires libres">
    @foreach($formulaires_libres as $formulaire)
        <option value="{{ $formulaire->nom_formulaire }}" v-html="traduction('{{$formulaire->index_traduction}}','titre')"></option>
    @endforeach
    </optgroup>
    <optgroup label="Autres">
        <option value="formulaire_edition_element">Formulaire edition element</option>
        <option value="formulaire_affichage_element">Formulaire affichage element</option>
        <option value="pieces_jointes">Pièces jointes</option>
        <option value="commentaires">Commentaires</option>
        <option value="liste_taches">Tâches</option>
        <option value="messages">Messages</option>
        <option value="affichage_calendrier">Calendrier</option>
        <option value="abonnement_fiche">Abonnement</option>
        <option value="historique">Historique</option>
        <option value="timeline">Timeline</option>
        <option value="logo">Logo</option>
        @foreach($champ_type_liste as $champ)
            <option value="workflow_{{ $champ['nom_sql'] }}">Worflow {{ $champ['nom'] }}</option>
        @endforeach
    </optgroup>
@endif
