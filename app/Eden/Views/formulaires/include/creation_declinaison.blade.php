<form action="#" id="formulaire_ajout_article_declinaison" method="post" class="css_form">
    <input type="hidden" name="id" v-model="article_declinaison.id" />
    <input type="hidden" name="article_id" :value="article.id" />
    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.designation')</div>
        <div class="col-sm-10"><input v-model="article_declinaison.designation" type="text" name="designation" /></div>
    </div>
    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.code_article')</div>
        <div class="col-sm-10"><input v-model="article_declinaison.code_article" type="text" name="code_article" /></div>
    </div>
    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.reference')</div>
        <div class="col-sm-10">
            {!! management('article_declinaison')->champ('reference')->cree() !!}
        
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.tarif')</div>
        <div class="col-sm-10"><input v-model="article_declinaison.tarif" type="text" name="tarif" /></div>
    </div>
    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.cout_de_revient')</div>
        <div class="col-sm-10"><input v-model="article_declinaison.cout_de_revient" type="text" name="cout_de_revient" /></div>
    </div>
    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.article')</div>
        <div class="col-sm-10">{!! management('article_declinaison')->champ('article_declinaison_id')->cree() !!}</div>
    </div>

    <div class="row">
        <div class="col-sm-2">@traduction('formulaire.creation_declinaison.photo')</div>
        <div class="col-sm-10"><champ-file v-model="article_declinaison.image" :name="'image'" :valeur="article_declinaison.image"></champ-file></div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.nom_de_la_famille_declinaison') #1</div>
        <div class="col-sm-8">
			{!! management('article_declinaison')->champ('famille_declinaison_1')->cree() !!}
	    </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.valeur_declinaison') #1</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('valeur_declinaison_1')->cree() !!}
        </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.nom_de_la_famille_declinaison') #2</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('famille_declinaison_2')->cree() !!}
        
        </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.valeur_declinaison') #2</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('valeur_declinaison_2')->cree() !!}
        </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.nom_de_la_famille_declinaison') #3</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('famille_declinaison_3')->cree() !!}
        </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.valeur_declinaison') #3</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('valeur_declinaison_3')->cree() !!}
        </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.nom_de_la_famille_declinaison') #4</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('famille_declinaison_4')->cree() !!}
        </div>
    </div>

    <div class="row" v-show="article.type_declinaison == 2">
        <div class="col-sm-4">@traduction('formulaire.creation_declinaison.valeur_declinaison') #4</div>
        <div class="col-sm-8">
            {!! management('article_declinaison')->champ('valeur_declinaison_4')->cree() !!}
        </div>
    </div>

</form>