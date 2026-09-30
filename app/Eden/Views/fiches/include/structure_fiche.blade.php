@include('eden::fiches.include.filtres_et_modele_par_defaut_listes_sur_fiche')
@if(isset($presence_carte) && $presence_carte)
    @push('link_styles')
        <link rel="stylesheet" href="/eden/vendors/leaflet/leaflet.css" />
        <link rel="stylesheet" href="/eden/vendors/leafletmarkercluster/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="/eden/vendors/leafletmarkercluster/dist/MarkerCluster.Default.css" />
        <script src="/eden/vendors/leaflet/leaflet.js"></script>
        <script src="/eden/vendors/leafletmarkercluster/dist/leaflet.markercluster.js"></script>
    @endpush
@endif
@if(session()->has('message'))
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger text-center" role="alert">{{session()->get('message')}}</div>
        </div>
    </div>
@endif
@if(session()->has('erreur'))
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger text-center" role="alert">{{session()->get('erreur')}}</div>
        </div>
    </div>
@endif
@if(session()->has('erreurs'))
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger text-center" role="alert">implode('<br/>', session()->get('erreurs'))</div>
        </div>
    </div>
@endif

@if(isset($structure) && isset($structure['colonne_droite']) && !empty($structure['colonne_droite'])&& !est_un_mobile())

    <!-- la colonne de droite -->
    <div class="css_colonne_droite_fiche" id="partie_droite_fiche" @if(env('APP_ENV') == 'preprod') style="top: 192px;" @endif>

        <!-- replier -->
        <div class=" d-flex justify-content-between align-items-center">
            <span class="fa fa-arrow-right css_colonne_droite_fiche_fleche_replier"
					style="font-size: 25px; cursor: pointer;"
					onClick="replie_partie_droite_fiche()"></span>
        </div>
        <div class="d-flex flex-column">
            <span class="fa fa-arrow-left css_colonne_droite_fiche_fleche_deplier"
                  style="font-size: 25px; cursor: pointer;margin-bottom:10px;"
                  onClick="deplie_partie_droite_fiche()"></span>
        </div>


        <div class="css_contenu_fiche_colonne_droite">
            @foreach($structure['colonne_droite'] as $module)
                <?php
                    if(!isset($module['cacher_bloc_v_if'])){
                        $module['cacher_bloc_v_if'] = true;
                    }
                ?>
                <div class="col-md-12" v-if="{{ $module['cacher_bloc_v_if'] }}">

                    @if($management_fiche->modules_pour_fiche($module['module']) !== false)
                        @include($management_fiche->modules_pour_fiche($module['module']), $module)
                    @else
                        @include('eden::fiches.include.liste_libre_sur_fiche', $module)
                    @endif
                    
                </div>
                <div class="css_separation"></div>

            @endforeach
        </div>

    </div>
@endif

<?php temps_execution('debut structure fiche classique', 1); ?>

<!-- la structure classique -->
<div class="row">

    @foreach($structure['modules'] as $id_module_1 => $module)

        @if(!isset($module['taille']))
			<div class="col-md-12">
                <div class="row">
                    @foreach($module as  $id_module_2 => $colonne_sous_module)
                        <?php
                        if(!isset($colonne_sous_module['cacher_bloc_v_if'])){
                            $colonne_sous_module['cacher_bloc_v_if'] = true;
                        }
                        ?>
                        <div class="col-md-{{ $colonne_sous_module['taille'] }} css_module_onglets">

                            <template v-if="{{ $colonne_sous_module['cacher_bloc_v_if'] }}">
                                @if(!empty($colonne_sous_module['onglets']))
                                    <?php temps_execution('debut onglets ', 2); ?>
                                    <div class="row">
                                        <div :class="'col-md-'+onglets.onglet_{{$id_module_1}}_{{$id_module_2}}.taille">
                                            <ul class="nav nav-tabs liste_onglets" style="position: relative; margin-top: 16px; border: 1px solid #dedede;background: #dedede;padding: 10px; padding-left: 0;border-top-left-radius: 10px;border-top-right-radius: 10px;gap: 21px 0px">
                                                @foreach($colonne_sous_module['modules'] as $sous_module)
                                                    <?php
                                                    if(!isset($sous_module['cacher_bloc_v_if'])){
                                                        $sous_module['cacher_bloc_v_if'] = 1;
                                                    }
                                                    ?>
                                                    <template v-if="{{$sous_module['cacher_bloc_v_if']}}">
                                                        <li>
                                                            <a :class="'js_onglet_sur_fiche css_background_couleur_primaire_active'+(onglets.onglet_{{$id_module_1}}_{{$id_module_2}}.module == '{{$sous_module['module']}}' ? ' active' : '')" style="position: relative"
                                                                @click="onglets.onglet_{{$id_module_1}}_{{$id_module_2}}.module = '{{$sous_module['module']}}';onglets.onglet_{{$id_module_1}}_{{$id_module_2}}.taille = {{$sous_module['taille']}}">
                                                                @traduction('{{ fiche($type_element, $id_element)->recupere_nom_module($sous_module["module"]) }}')

                                                                @if(!empty($listes_sur_fiche['unitaire'][$sous_module['module']]['liste_libre']['id']))
                                                                    <span class="nb_elements_liste_sur_fiche"
                                                                          v-if="$root.$refs.liste_libre_{{ $listes_sur_fiche['unitaire'][$sous_module['module']]['liste_libre']['id'] }} != undefined"
                                                                          v-html="$root.$refs.liste_libre_{{ $listes_sur_fiche['unitaire'][$sous_module['module']]['liste_libre']['id'] }}.liste.nombre_elements_nombres_sans_filtres">
                                                                    </span>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    </template>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <?php temps_execution('fin onglets ', 2); ?>
                                @endif
                                <div class="row">
                                    @foreach($colonne_sous_module['modules'] as $sous_module)
                                        <?php
                                        if(!isset($sous_module['cacher_bloc_v_if'])){
                                            $sous_module['cacher_bloc_v_if'] = true;
                                        }
                                        ?>
                                        <template v-if="{{$sous_module['cacher_bloc_v_if']}}">
                                            <div @if(!empty($colonne_sous_module['onglets'])) v-show="onglets.onglet_{{$id_module_1}}_{{$id_module_2}}.module == '{{$sous_module['module']}}'" @endif class="col-md-{{ $sous_module['taille'] }} js_onglet_fiche">

                                                <?php
                                                    $sous_module['onglets'] = null;
                                                    if(isset($colonne_sous_module['onglets']))
                                                        $sous_module['onglets'] = $colonne_sous_module['onglets'];
                                                ?>

                                                @if($management_fiche->modules_pour_fiche($sous_module['module']) !== false)
                                                    @include($management_fiche->modules_pour_fiche($sous_module['module']), $sous_module)
                                                @else
                                                    @include('eden::fiches.include.liste_libre_sur_fiche', $sous_module)
                                                @endif

                                                <?php
                                                    $sous_module['onglets'] = null;
                                                ?>
                                            </div>
                                        </template>
                                        <?php temps_execution('fin module '.$sous_module['module'], 2); ?>
                                    @endforeach
                                </div>
                            </template>
                        </div>
                    @endforeach
                </div>
            </div>

        @else
            <?php
            if(!isset($module['cacher_bloc_v_if'])){
                $module['cacher_bloc_v_if'] = true;
            }
            ?>
            <div class="col-md-{{ $module['taille'] }}" v-if="{{ $module['cacher_bloc_v_if'] }}">

                @if($management_fiche->modules_pour_fiche($module['module']) !== false)
                    @include($management_fiche->modules_pour_fiche($module['module']), $module)
                @else
                    @include('eden::fiches.include.liste_libre_sur_fiche', $module)
                @endif
                
            </div>

			<?php temps_execution('fin module '.$module['module'], 2); ?>
        @endif
    @endforeach
</div>

@if(isset($structure) && !empty($structure['colonne_droite']) && est_un_mobile())
    <div id="partie_droite_fiche_responsive" style="display:none">
         <div class="row">
            @foreach($structure['colonne_droite'] as $module)
                <?php
                    if(!isset($module['cacher_bloc_v_if'])){
                        $module['cacher_bloc_v_if'] = true;
                    }
                ?>
                <div class="col-md-12" v-if="{{ $module['cacher_bloc_v_if'] }}">

                    @if($management_fiche->modules_pour_fiche($module['module']) !== false)
                        @include($management_fiche->modules_pour_fiche($module['module']), $module)
                    @else
                        @include('eden::fiches.include.liste_libre_sur_fiche', $module)
                    @endif
                </div>
                <div class="css_separation"></div>

            @endforeach
        </div>
    </div>
@endif

@push('scripts')

    <script>
        if (screen && screen.width > 700) {

            $('#partie_droite_fiche_responsive').remove()

        }
        else{

        }
    </script>

@endpush

@if(!empty($structure['options']['actions_apres_evenement']))
    @include('eden::fiches.include.gestion_action_post_evenement',['options' => $structure['options']])
@endif

@push('donnees_pour_vuejs_watch')

    @if(!empty($watch_pour_vuejs))

        @foreach($watch_pour_vuejs as $cle_primaire => $watch)
            '{{ $type_element }}.{{ $cle_primaire }}': function(nouvelle_valeur, ancienne_valeur){
        
                if(nouvelle_valeur == ancienne_valeur)
                    return;
            
                var condition_remplie = true;
            
                @foreach($watch as $liste_id => $infos)

                    condition_remplie = true;
                    @if(!empty($infos['contenu_cle_primaire']) && !empty($infos['type_element_primaire']))
                        condition_remplie = this.{{ $type_element }}.{{ $infos['contenu_cle_primaire'] }} == '{{ $infos['type_element_primaire'] }}';
                    @endif
                    
                    if(condition_remplie){
                
                        @foreach($infos['donnees_pour_vuejs'] as $data_update => $cles_etrangeres)
                            @foreach($cles_etrangeres as $cle_etrangere)
                                this.{{ $data_update }}{{ $liste_id }}.{{ $cle_etrangere }} = nouvelle_valeur;
                            @endforeach
                        @endforeach
                
                        this.$nextTick(() => {
                            this.$refs.liste_libre_{{ $liste_id }}.actualisation_filtres()
                        });
                
                    }
                    
                @endforeach
        
            },
        @endforeach
    @endif

@endpush

@push('donnees_pour_vuejs_data')

    modules : {!! collect($structure['modules']) !!},

    onglets : {

        @foreach($structure['modules'] as $id_module_1 => $module)

            @if(!isset($module['taille']))

                @foreach($module as $id_module_2 => $colonne_sous_module)

                    @php
                        $module_selectionne = !empty($colonne_sous_module['modules'][0]['module']) ? $colonne_sous_module['modules'][0] : null;
                    @endphp

                    onglet_{{$id_module_1}}_{{$id_module_2}} : {
                        module : '{{$module_selectionne['module'] ?? null}}',
                        taille : '{{$module_selectionne['taille'] ?? 12}}'
                    },

                @endforeach

            @endif

        @endforeach

    },

@endpush

@push('donnees_pour_vuejs_mounted')
    this.verification_onglets();
    deplie_partie_droite_fiche();
@endpush

@push('donnees_pour_vuejs_methods')
    verification_onglets : function(){
        for(id_onglet in this.onglets){

            var modules_affiches = this.modules_affiches.filter(module => module.onglet == id_onglet).map(module => module.module);

            var onglet = this.onglets[id_onglet];

            if(modules_affiches.length > 0 && !modules_affiches.includes(onglet.module))
                onglet.module = modules_affiches[0];
        }
    },

@endpush

@push('donnees_pour_vuejs_watch')

    modules_affiches : function(){
        this.verification_onglets();
    },
@endpush

@push('donnees_pour_vuejs_computed')
    modules_affiches : function(){

        var modules_affiches = [];

        var modules = structuredClone(this.modules);

        for(id_module in modules){

            var module = modules[id_module];

            if(!module.taille){

                for(id_sous_module in module){

                    var sous_module = module[id_sous_module];

                    for(id_sous_sous_module in sous_module.modules){

                        var sous_sous_module = sous_module.modules[id_sous_sous_module];

                        sous_sous_module.onglet = 'onglet_'+id_module+'_'+id_sous_module;

                        if(sous_sous_module.cacher_bloc_v_if == null)
                            modules_affiches.push(sous_sous_module);
                        else{
                            var condition = true; 
                    
                            with(this) { 
                                condition = eval(sous_sous_module.cacher_bloc_v_if); 
                            }

                            if(condition)
                                modules_affiches.push(sous_sous_module);
                        }
                    }
                }
            }
            else{

                if(module.cacher_bloc_v_if == null)
                    modules_affiches.push(module);
                else{
                    var condition = true; 
                    
                    with(this) { 
                        condition = eval(module.cacher_bloc_v_if); 
                    }

                    if(condition)
                        modules_affiches.push(module);
                }
            }
        }

        return modules_affiches;
    },

@endpush
