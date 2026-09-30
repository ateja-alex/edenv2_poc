@extends('eden::templates.template')
@section('content')
    <div class="content-wrapper" >
        <div id="base-content" class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>
                                @traduction('interface.extranet.accueil')
                            </h4>
                        </div>
                        <div class="card-body" >
                            <div class="row">
                                <div class="col-sm-12">
                                    @traduction('interface.extranet.accueil.bienvenue')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection