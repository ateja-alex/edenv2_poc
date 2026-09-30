@if(\App\Eden\Models\Table_libre::where('creation_rapide', 1)->first() !== null)
    <div class="nav-item dropdown dropdown_hover" aria-haspopup="true" aria-expanded="false">
        <div class="css_ajouter_element ">
            <i class="css_btn_action_header fas fa-plus" data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_ajout_rapide')"></i>
        </div>
        <div class="dropdown-menu dropdown_menu_haut_action" aria-labelledby="dropdownMenuButton">
            @foreach(\App\Eden\Models\Table_libre::where('creation_rapide', 1)->get() as $table_libre)
                @if(profil_creation($table_libre->type_element))
                    @if(!in_array($table_libre->type_element, array('devis_vente', 'commande_vente', 'acompte_vente', 'facture_vente', 'bl_vente', 'avoir_vente')))
                        <a class="dropdown-item item_responsive" href="{{ route('base_eden.formulaire.index',[$table_libre->type_element]) }}"><i class="fa fa-fw fa-plus-square"></i>
                            @traduction('{{$table_libre->index_traduction}}','element')
                        </a>
                    @else
                        <a class="dropdown-item item_responsive" href="{{ route('document.creer',[$table_libre->type_element]) }}"><i class="fa fa-fw fa-plus-square"></i>
                            @traduction('{{$table_libre->index_traduction}}','element')
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
@endif

<?php temps_execution('navbar haut creation_rapide'); ?>

<!-- Corbeille -->
@if(\App\Eden\Models\Table_libre::where('corbeille', 1)->first() !== null && admin())
    <div class="nav-item dropdown dropdown_hover" aria-haspopup="true" aria-expanded="false">
        <div class="css_ajouter_element ">
            <i class="css_btn_action_header fas fa-trash-restore-alt" data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_corbeille')"></i>
        </div>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            @foreach(\App\Eden\Models\Table_libre::where('corbeille', 1)->get() as $table_libre)
                <a class="dropdown-item" href="{{ route('base_eden.corbeille.liste',[$table_libre->type_element]) }}"><i class="fas fa-trash-restore-alt"></i> @traduction('{{$table_libre->index_traduction}}','element')</a>
            @endforeach
        </div>
    </div>
@endif

<?php temps_execution('navbar haut corbeille'); ?>
<!-- Historique -->
@if(fonctionnalite('utiliser_historique_navbar'))
    @php
        $nombre_ligne = fonctionnalite('nombre_de_lignes_historique_bouton');
        $logs_historique_nav_bar = \DB::table('log_historique')
            ->select(DB::raw('*, MAX(date) as date'))
            ->orderBy('date','DESC')
            ->where('utilisateur_id', moi()->id)
            ->groupBy('nom_page')
            ->take($nombre_ligne)->get();
    @endphp

    @if($logs_historique_nav_bar->count() > 0)
        <div class="nav-item dropdown dropdown_hover" aria-haspopup="true" aria-expanded="false">
            <div class="css_ajouter_element ">
                <i class="css_btn_action_header fas fa-clipboard-list" data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_historique')"></i>
            </div>
            <div class="dropdown-menu dropdown_menu_haut_historique" aria-labelledby="dropdownMenuButton">
                @foreach($logs_historique_nav_bar as $historique )
                    <a class="dropdown-item item_responsive" href="{{ url($historique->url) }}"><span v-html="`{!! htmlentities(str_replace('`', '&#96;', $historique->nom_page)) !!}`"></span></a>
                @endforeach
            </div>
        </div>
    @endif
    <?php temps_execution('navbar haut apres foreach'); ?>
@endif

<?php temps_execution('navbar haut historique'); ?>
<!-- système de tickets -->
@if(empty(moi()->cacher_acces_support))
    <a class="css_ajouter_element" href="{{ route('base_eden.liste.index', ['suivi_recette_easydev']) }}">
        <i class="css_btn_action_header far fa-question-circle" data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_support')"></i>
    </a>
@endif

{{-- Paramètrage --}}
@if(admin())
<div class="css_ajouter_element" data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_parametrage')" onClick="$('#toggle_parametrage_eden').toggle()">
    <i id="bouton_toggle_parametrage_eden" class="css_btn_action_header fas fa-cog"></i>
    <div style="" id="toggle_parametrage_eden" class="css_panel_toggle_parametre_eden">
        {{-- Toggle Mode traduction --}}
        <div class="d-flex align-items-center mt-3">
            <span>@traduction('interface.eden_parametrage.mode_traduction') :</span>
            <label style="margin-left: auto;">
                <input class="toggle-checkbox css_toggle_traduction" type="checkbox" v-model="mode_traduction"></input>
                <div class='toggle-slot css_toggle_traduction'>
                    <div class='sun-icon-wrapper'>
                        <div class="fa fa-language" style="color: black;"></div>
                    </div>
                    <div class='toggle-button'></div>
                    <div class='moon-icon-wrapper'>
                        <div class="fa fa-language" style="color: black;"></div>
                    </div>
                </div>
            </label>
        </div>

        {{-- Toggle Mode paramètrage --}}
        <div class="d-flex align-items-center mt-3">
            <span>@traduction('interface.eden_parametrage.mode_parametrage') <i class="fas fa-wrench"></i> :</span>
            <label style="margin-left: auto;">
                <input class="toggle-checkbox css_toggle_traduction js_toggle_mode_parametrage" type='checkbox' @if(moi()->mode_parametrage === 1)checked="checked"@endif></input>
                <div class='toggle-slot css_toggle_traduction'>
                    <div class='toggle-button'></div>
                </div>
            </label>
        </div>

        {{-- Toggle Mode VueJS Dev. Inutile de l'afficher si l'utilisateur est admin. Ca ne sera utile qu'en usurpation, à priori --}}
        @if(editeur())
        <div class="d-flex align-items-center mt-3">
            <span>@traduction('interface.eden_parametrage.mode_vuejs') :</span>
            <label style="margin-left: auto;">
                <input class="toggle-checkbox css_toggle_traduction js_toggle_mode_vuejs" type='checkbox' @if(session()->get('vuejs_mode_dev') === 1)checked="checked"@endif></input>
                <div class='toggle-slot css_toggle_traduction'>
                    <div class='toggle-button'></div>
                </div>
            </label>
        </div>
        @endif

        {{-- Toggle Récupérer les droits du compte initial --}}
        @if(session()->has('eden_usurpation_origine'))
            <div class="d-flex align-items-center mt-3">
                <span>@traduction('interface.eden_parametrage.recup_droits_compte_initial') :</span>
                <label style="margin-left: auto;">
                    <input class="toggle-checkbox css_toggle_traduction js_toggle_recuperer_droits_compte_initial" type='checkbox' @if(session()->has('activer_recuperer_droits_compte_initial'))checked="checked"@endif></input>
                    <div class='toggle-slot css_toggle_traduction'>
                        <div class='toggle-button'></div>
                    </div>
                </label>
            </div>
        @endif
    </div>
</div>
@endif
{{-- Bouton aide contextuelle : commenté le 04/01/23 par Tom sur demande du ticket 4194 --}}
<!--<div class="css_ajouter_element " data-toggle="tooltip" data-placement="left" :title="moi.aide_contextuelle ? traduction('interface.tooltip.eden_desactiver_aide_contextuelle') : traduction('interface.tooltip.eden_activer_aide_contextuelle')">

    <a href="{{ route('base_eden.aide_contextuelle.index') }}">
        <i class="css_btn_action_header fas fa-life-ring" style="{{ moi()->aide_contextuelle ? "background: #fea204;color:white;" : "" }}"></i>

    </a>
</div>-->

@if(editeur())
    <div class="css_ajouter_element " data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_vider_cache')" onclick="js_vider_cache_menu()">

        <span>
            <i class="css_btn_action_header fas fa-eraser"></i>

        </span>
    </div>
@endif

@if(!empty(moi()) && (moi()->type_utilisateur == 1 || editeur()))
    @php
        $version_eden = modele('version_eden')->orderBy('numero_version','desc')->first();
        $management_version_eden = null;
        if($version_eden !== null)
            $management_version_eden = management('version_eden',$version_eden->id);
    @endphp

    @if(!empty($management_version_eden))
        <div class="css_ajouter_element " data-toggle="tooltip" data-placement="left" :title="traduction('interface.tooltip.eden_version')" onClick="$('#toggle_version_eden').toggle()">
            <i id="bouton_toggle_version_eden" class="css_btn_action_header icon-eden"></i>
            <div style="" id="toggle_version_eden" class="css_panel_toggle_version_eden">
                <div class="d-flex align-items-center">
                    <span>@traduction('interface.eden_versioning.version') : {!! $management_version_eden->champ('numero_version')->affiche() !!}</span>
                </div>
                <div class="d-flex align-items-center mt-3">
                    <span>@traduction('interface.eden_versioning.date_derniere_mise_a_jour') : {!! $management_version_eden->champ('date_ajout')->affiche() !!}</span>
                </div>

                @if(admin())

                    @inject('git_management', 'App\Eden\Managements\Git_management')

                    @foreach($git_management::retourne_branches() as $nom => $branche)

                        <div style="border-top: 1px solid #e9ecef;padding-top:10px;margin-top:10px;">
                            <strong>{{$nom}} :</strong><br>
                            {{ $branche }}
                        </div>

                    @endforeach
                @endif

            </div>
        </div>
    @endif
@endif

@if(fonctionnalite('utiliser_chronometre'))
    <chronometre ref="chronometre"/>
@endif

@push('scripts')

    <script type="text/javascript">

        /* Toggle vuejs dev */
        $('.js_toggle_mode_vuejs').change(function () {

            if($(this).prop('checked')) {

                $.ajax({
                    url: "eden/maintenance/mode_vuejs/"+1
                }).done(function(){
                    loading(true);
                    location.reload();
                });;
            }
            else {

                $.ajax({
                    url: "eden/maintenance/mode_vuejs/"+0
                }).done(function(){
                    loading(true);
                    location.reload();
                });

            }
        });


        /* Toggle Récupérer les droits du compte initial */
        $('.js_toggle_recuperer_droits_compte_initial').change(function (){
            $.ajax( {
                url: "{{URL::to('eden/maintenance/recuperer_droits_compte_initial')}}/"+($(this).prop('checked') ? 1 : 0)
            }).done(function() {
                loading(true);
                location.reload();
            });
        });
    </script>
@endpush
