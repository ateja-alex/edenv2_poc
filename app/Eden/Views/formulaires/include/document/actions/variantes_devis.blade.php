@if($management->existe())

	<span class="dropdown"  data-toggle="tooltip" :title="traduction('document.actions.variantes_devis.variantes')">
		<i class="css_action_icon primaire fas fa-file-alt"data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
		<div class="dropdown-menu">
            
            @if($management->modele->variante_devis_vente_id!=null)
			
				 <a class="dropdown-item"  href="{{ route('document.afficher', [ 'devis_vente', $management->modele->variante_devis_vente_id]) }}">  {!! management('devis_vente',$management->modele->variante_devis_vente_id)->affiche_variante_devis() !!}  </a>
                @foreach(modele('devis_vente')->where(['variante_devis_vente_id'=>$management->modele->variante_devis_vente_id])->get() as $variantes_du_devis)
                        @if($variantes_du_devis->id!=$management->modele->id)
                        <a class="dropdown-item"  href="{{ route('document.afficher', [ 'devis_vente',$variantes_du_devis->id ]) }}">{!! management('devis_vente',$variantes_du_devis->id)->affiche_variante_devis() !!}   </a>
                        @endif
                @endforeach
            @else
                @foreach(modele('devis_vente')->where(['variante_devis_vente_id'=>$management->modele->id])->get() as $variantes_du_devis)
                <a class="dropdown-item"  href="{{ route('document.afficher', [ 'devis_vente',$variantes_du_devis->id ]) }}">  {!! management('devis_vente',$variantes_du_devis->id)->affiche_variante_devis() !!} </a>
                @endforeach
            @endif
		</div>
	</span>

@endif