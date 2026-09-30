@if(!isset($type_element_formulaire_parent) || $type_element_formulaire_parent != 'article')
    <template>
        <div class="row" v-show="this.$root.element_id == undefined && this.$root.type_element != 'article'">
            <div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.choix_elements_concernes.elements_concernes')</div>
            <button type="button" class="js_bouton_elements_concernes btn btn-success css_background_couleur_primaire m-2" @click="changer_type_input(1)">{{ traduction('formulaire.choix_elements_concernes.un_article') }}</button>
            <button type="button" class="js_bouton_elements_concernes btn btn-success css_background_couleur_primaire m-2" @click="changer_type_input(2)">{{ traduction('formulaire.choix_elements_concernes.par_fournisseur') }}</button>
            <button type="button" class="js_bouton_elements_concernes btn btn-success css_background_couleur_primaire m-2" @click="changer_type_input(3)">{{ traduction('formulaire.choix_elements_concernes.par_famille_article') }}</button>
            <button type="button" class="js_bouton_elements_concernes btn btn-success css_background_couleur_primaire m-2" @click="changer_type_input(4)">{{ traduction('formulaire.choix_elements_concernes.tous_les_articles') }}</button>
        </div>

        <div class="row js_changer_type_input" v-show="elements_concernes == 1">
            <div class="col-sm-2">{!! management('article_evolution_prix')->champ('article_id')->nom() !!}</div>
            <div class="col-sm-4 js_vider_input">{!! management('article_evolution_prix')->champ('article_id')->cree() !!}</div>
        </div>

        <div class="row js_changer_type_input" v-show="elements_concernes == 2">
            <div class="col-sm-2">{!! management('article_evolution_prix')->champ('fournisseur_id')->nom() !!}</div>
            <div class="col-sm-4 js_vider_input">{!! management('article_evolution_prix')->champ('fournisseur_id')->cree() !!}</div>
        </div>

        <div class="row js_changer_type_input" v-show="elements_concernes == 3">
            <div class="col-sm-2">{!! management('article_evolution_prix')->champ('famille_id')->nom() !!}</div>
            <div class="col-sm-4 js_vider_input">{!! management('article_evolution_prix')->champ('famille_id')->cree() !!}</div>
        </div>

        <div class="row" v-show="elements_concernes == 4">
            <div class="col-sm-12 js_vider_input">{!! management('article_evolution_prix')->champ('avertissement')->cree() !!}</div>
        </div>

    </template>
@endif

@push('donnees_pour_vuejs_data')

        elements_concernes : 1,
@endpush

@push('donnees_pour_vuejs_methods')

    changer_type_input: function(type_input) {

        this.article_evolution_prix.article_id = undefined;
        this.article_evolution_prix.fournisseur_id = undefined;
        this.article_evolution_prix.famille_id = undefined;

        this.elements_concernes = type_input;
    },
@endpush