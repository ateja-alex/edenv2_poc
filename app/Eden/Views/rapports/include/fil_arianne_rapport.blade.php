@if(empty(moi_extranet()))
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-md-12">
            <h5>
                <a href="{{ URL::to(maquette('page_accueil')) }}" style="color: #212121;" >
                    <i class="fa fa-home zoom" aria-hidden="true" onmouseover="this.style.transform='scale(1.5)';" onmouseout="this.style.transform='scale(1)';" style="transition: transform .2s;"></i>
                </a>
                >
                <a href="{{ route('base_eden.rapport.liste') }}" style="color: #212121;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">@traduction('rapport.divers.rapports')</a>
                >
                <span style="color: #a3a3a3;">
                    @php

                        $rapport = App\Eden\Models\Rapport_libre::where('id_rapport', $id_rapport)->first();
                        if(!empty($rapport) && !empty($rapport->index_traduction))
                            echo traduction($rapport->index_traduction.'.titre');
                        else
                            echo $id_rapport;

                    @endphp
                </span>
            </h5>
        </div>
    </div>
@endif