@php
    $informations_type_element = service('traduction')->informations_type_element($type_element);
    $types_elements_gestion_droits = service('profil')->types_elements_gestion_droits();
    $table_libre = table_libre($type_element);
    $type_champ_precedent = false;
    $presence_sous_formulaire = false;
@endphp

<div class="formulaire_eden" :key="'formulaire_' + '{{ $formulaire_id }}'">

    @if(!empty($informations_type_element))
        <div class="col-sm-12" v-if="{{$type_element}}.index_traduction != undefined">
            <traduction-table :key="{{$type_element}}.index_traduction"
                              categorie="{{$informations_type_element['categorie']}}"
                              :filtrage_index="{{$type_element}}.index_traduction+'.'"></traduction-table>
        </div>
    @endif

    @if(in_array($type_element,$types_elements_gestion_droits))
        <template v-if="{{$type_element}}.id > 0">
            <div class="col-sm-2">
                Gestion des profils
            </div>
            <div class="col-sm-10">
                <profil-droits-divers type="{{$type_element}}" :index="{{$type_element}}.id"></profil-droits-divers>
            </div>
        </template>
    @endif

    @php

        $les_champs = \App\Eden\Models\Formulaires_champs::where('nom_formulaire', $formulaire_id)->orderBy('ordre')->get();
        $formulaire = \App\Eden\Models\Formulaire::where('nom_formulaire', $formulaire_id)->first();
        $vuejs_data = $formulaire['vuejs_data'];
        $vuejs_methods = $formulaire['vuejs_methods'];
        $utilisateurs = App\Eden\Models\Utilisateur::get();
        $options = (array) $options;

        $moi = moi();

        $champs_formulaires = [];

        foreach ($les_champs as $index => $champ) {

            if ($champ['type_champ'] == 0) {

                $champ_libre = champ_libre_modele($champ['type_element'], $champ['nom_sql']);

                if ($champ_libre === null)
                    unset($les_champs[$index]);

                elseif($champ_libre['type'] >= 0){
                    $champ_management = management($champ_libre->type_element)->champ($champ['nom_sql']);

                    $champ_management->affectation_champ_formulaire($champ);

                    $champs_formulaires[$champ['id']] = $champ_management;
                }
            }
        }
    @endphp


    @foreach($les_champs as $le_champ)

        @if($le_champ->type_champ == 0 && management($le_champ['type_element'])->champ($le_champ['nom_sql'])->modele->type == 42 && $type_element_formulaire_parent == management($le_champ['type_element'])->champ($le_champ['nom_sql'])->modele->type_element_ajax)
            @continue
        @endif

        @if($le_champ['profils'] != null)
            @php
                $profils = json_decode($le_champ['profils']);
                $autorisation = false;
                // Seulement si sans_profil
                if ($profils == 0) {
                    if (Empty(moi()->profil_id)) {
                        $autorisation = true;
                    }
                }
                // Liste de profil autorisé
                else{

                    foreach ($utilisateurs as $utilisateur) {
                        if ($utilisateur->id == $moi->profil_id) {
                            $autorisation = true;
                        }
                    }
                }
            @endphp
        @else
            @php
                $autorisation = true;
            @endphp
        @endif

        @if(!$autorisation)
            @continue
        @endif

        @if($le_champ['type_champ'] === 3 || $type_champ_precedent === 3)
            </div>
            <div class="formulaire_eden">
        @endif

        @php

            $type_champ_precedent = $le_champ['type_champ'];

        @endphp

        @if($le_champ['type_champ'] == 0)

            @php

                $champ_libre = champ_libre_modele($le_champ['type_element'], $le_champ['nom_sql']);

                $v_if = null;

                if(in_array($champ_libre->visibilite, ['creation','modification'])){
                    $data_vue = in_array($champ_libre->type_element, \App\Eden\Variables::$documents_gescom) ? 'document' : $champ_libre->type_element;

                    $v_if = [];

                    if(!empty($champ_libre->conditions_v_if_manuelle))
                        $v_if[] = "(" . $champ_libre->conditions_v_if_manuelle . ")";

                    if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null)
                        $v_if[] = "(" . $le_champ['condition_affichage_v_if'] . ")";

                    if($champ_libre->visibilite == 'creation')

                        $v_if[] = "(" . $data_vue . ".id == '' || " . $data_vue . ".id == undefined || " . $data_vue . ".id == null)";
                    else
                        $v_if[] = "(" . $data_vue . ".id != '' && " . $data_vue . ".id != undefined && " . $data_vue . ".id != null)";

                    $v_if = implode(" && ", $v_if);
                }
                else{
                    $v_if = [];

                    if(!empty($champ_libre->conditions_v_if_manuelle))
                        $v_if[] = "(" . $champ_libre->conditions_v_if_manuelle . ")";

                    if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null)
                        $v_if[] = "(" . $le_champ['condition_affichage_v_if'] . ")";

                    $v_if = implode(" && ", $v_if);
                }

            @endphp

            @if($champ_libre['type'] >= 0)

                @if(!empty($le_champ['taille_avant']))
                    <div class="col-sm-{!! $le_champ['taille_avant'] !!}"
                        @if(!empty($champ_libre->conditions_v_show_manuelle))
                            v-show="{!! $champ_libre->conditions_v_show_manuelle !!}"
                        @endif

                        @if(!empty($v_if))
                            {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if'!!}="{!! $v_if !!}"
                        @endif
                        >
                    </div>
                @endif

                @if(!empty($le_champ['taille_libelle']))
                    <div class="col-sm-{!! $le_champ['taille_libelle'] !!}" 
                        @if(!empty($champ_libre->conditions_v_show_manuelle))
                            v-show="{!! $champ_libre->conditions_v_show_manuelle !!}"
                        @endif

                        @if(!empty($v_if))
                            {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if'!!}="{!! $v_if !!}"
                        @endif>

                        {!! $champs_formulaires[$le_champ['id']]->nom_vue() !!}
                    </div>
                @endif

                <div class="col-sm-{!! $le_champ['taille_champ'] !!}"
                    @if(!empty($champ_libre->conditions_v_show_manuelle))
                        v-show="{!! $champ_libre->conditions_v_show_manuelle !!}"
                    @endif

                    @if(!empty($v_if))
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if'!!}="{!! $v_if !!}"
                    @endif>

                    @if(isset($options['formulaire_lecture_seule']) && $options['formulaire_lecture_seule'] == true)
                        {!! $champs_formulaires[$le_champ['id']]->lecture_seule(true)->cree() !!}                         
                    @elseif(( $uniquement_champs_editables == false || ($uniquement_champs_editables == true && !empty(champ_libre_modele($champ_libre->type_element, $le_champ['nom_sql'])->modification_post_validation)))
                    && ( isset($options['champs_disabled']) && !in_array($champ_libre->nom_sql, $options['champs_disabled']) || !isset($options['champs_disabled'])))
                        {!! $champs_formulaires[$le_champ['id']]->cree() !!}
                    @else
                        {!! $champs_formulaires[$le_champ['id']]->attr('disabled','true',1)->cree() !!}
                    @endif
                </div>

                @if(!empty($le_champ['taille_apres']))
                    <div class="col-sm-{!! $le_champ['taille_apres'] !!}"
                        @if(!empty($champ_libre->conditions_v_show_manuelle))
                            v-show="{!! $champ_libre->conditions_v_show_manuelle !!}"
                        @endif

                        @if(!empty($v_if))
                            {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if'!!}="{!! $v_if !!}"
                        @endif>
                    </div>
                @endif

            @elseif($champ_libre['type'] == -1)
                <div class="col-sm-{!! $le_champ['taille_libelle'] !!} css_form_ligne_titre"
                     @if(!empty($champ_libre->conditions_v_show_manuelle))
                         v-show="{!! $champ_libre->conditions_v_show_manuelle !!}"
                     @endif
                     @if(!empty($v_if))
                         {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $v_if !!}"
                     @endif
                >
                    {!! management($champ_libre->type_element)->champ($champ_libre->nom_sql)->nom_vue() !!}
                </div>

            @elseif($champ_libre['type'] == -2)
                <div class="col-sm-{!! $le_champ['taille_libelle'] !!}"
                    @if(!empty($champ_libre->conditions_v_show_manuelle))
                        v-show="{!! $champ_libre->conditions_v_show_manuelle !!}"
                    @endif
                    @if(!empty($v_if))
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $v_if !!}"
                    @endif
                >
                    {!! $champ_libre['contenu'] !!}
                </div>
            @elseif($champ_libre['type'] == -5)
                <template v-if="{{$champ_libre->type_element}}.id == '' || {{$champ_libre->type_element}}.id == undefined || {{$champ_libre->type_element}}.id == null">
                    <div class="col-md-12">
                        {!! formulaire($champ_libre->type_element_ajax, '', $champ_libre->type_element.'.'.$champ_libre->nom_sql) !!}
                    </div>
                </template>
            @endif

        @elseif($le_champ['type_champ'] == 1)

            @if(!empty($le_champ['taille_avant']))
                <div class="col-sm-{!! $le_champ['taille_avant'] !!}"
                    @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                    @endif>
                </div>
            @endif
            @if(!empty($le_champ['taille_champ']))
                <div class="col-sm-{!! $le_champ['taille_champ'] !!}" 
                    @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                    @endif>
                    {!! $le_champ['valeur_html'] !!}
                </div>
            @endif
            @if(!empty($le_champ['taille_apres']))
                <div class="col-sm-{!! $le_champ['taille_apres'] !!}"
                    @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                    @endif>
                </div>
            @endif

        @elseif($le_champ['type_champ'] == 3)

            @if(isset($type_element_formulaire_parent))
                @continue
            @endif

            <div class="col-sm-12" 
                @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                    {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                @endif>
                @include('eden::formulaires.include.sous_formulaire_dynamique_vuejs', [
                    'nom_formulaire' => $le_champ['nom_formulaire'],
                    'nom_sous_formulaire' => $le_champ['nom_sous_formulaire'],
                    'informations_type_element' => $informations_type_element,
                    'type_element' => $type_element
                ])
            </div>

            @php
                $presence_sous_formulaire = true;
            @endphp

        @else

            @if(!empty($le_champ['taille_avant']))
                <div class="col-sm-{!! $le_champ['taille_avant'] !!}"
                    @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                    @endif>
                </div>
            @endif
            <div class="col-sm-{!! $le_champ['taille_champ'] != 0 ? $le_champ['taille_champ'] : '12' !!}"
                @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                    {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                @endif>

                @includeFirst([
                    'eden::formulaires.'.$le_champ['type_element'].'.'.$le_champ['nom_vue'],
                    in_array($le_champ['type_element'], \App\Eden\Variables::$documents_vente_gescom) ?
                        'eden::formulaires.document.vente.'.$le_champ['nom_vue'] :
                        'eden::formulaires.document.achat.'.$le_champ['nom_vue'],
                    'eden::formulaires.document.'.$le_champ['nom_vue']
                ])
            </div>
            @if(!empty($le_champ['taille_apres']))
                <div class="col-sm-{!! $le_champ['taille_apres'] !!}" 
                    @if($le_champ['condition_affichage_v_if'] != '1' && $le_champ['condition_affichage_v_if'] != null) 
                        {!! $le_champ['condition_affichage_en_v_show'] == 1 ? 'v-show' : 'v-if' !!}="{!! $le_champ['condition_affichage_v_if']!!}" 
                    @endif>
                </div>
            @endif
        @endif
    @endforeach

</div>

@includeWhen(!empty($presence_sous_formulaire),'eden::formulaires.sous_formulaire_dynamique', ['type_element' => $type_element])

@push('donnees_pour_vuejs_data')

    @if(isset($type_element_formulaire_parent))
        type_element_formulaire_parent : '{{  $type_element_formulaire_parent }}',
    @else
        type_element_formulaire_parent : undefined,
    @endif

    @if($vuejs_data !== null)
        {{ $vuejs_data }}
    @endif

@endpush

@if($vuejs_methods !== null)
    @push('donnees_pour_vuejs_methods')

        {!! $vuejs_methods !!}

    @endpush
@endif
