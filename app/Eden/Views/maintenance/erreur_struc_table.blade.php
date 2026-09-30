@extends('eden::templates.template')


@section('title')
    Erreurs de structure
@stop

@section('content')
    <div class="content-wrapper">
        <div id="base-content" class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3" v-for="data in datas">
                        <div class="card-header" @click="showCards.includes(data.champ.nom) ? showCards.splice(showCards.indexOf(data.champ.nom), 1) :  showCards.push(data.champ.nom);">
                            <h4>
                                <span>@{{data.champ.nom}}</span>
                                <span data-placement="top" title="" class="ml-auto" ><div class="fa fa-chevron-up" aria-hidden="true" v-if="showCards.includes(data.champ.nom)"></div></span>
                                <span data-placement="top" title="" class="ml-auto" ><div class="fa fa-chevron-down" aria-hidden="true" v-if="!showCards.includes(data.champ.nom)"></div></span>
                            </h4>
                        </div>

                        <Transition>
                            <div v-if="showCards.includes(data.champ.nom)" class="card-body">
                                <div>
                                    <h5> Requete SQL : </h5>
                                    <p>@{{ data.query }}</p>
                                </div>
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <td>Id de la ligne</td>
                                        <td>Valeur KO</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="dataline in data.data">
                                        <td>@{{ dataline.id }}</td>
                                        <td>@{{ dataline.valeur_ko }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('donnees_pour_vuejs_data')
    showCards : [],
    datas : @json($data),
@endpush