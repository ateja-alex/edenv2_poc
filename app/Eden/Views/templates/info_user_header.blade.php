<div class="eden_info_user_header">
	<div class="d-md-flex ml-auto align-items-center" id="profil">

		<span class="toggle_bouton_header_responsive" @click="toggle_bouton_header_responsive = !toggle_bouton_header_responsive">
			<i class="fas fa-chevron-down" v-if="toggle_bouton_header_responsive == false"></i>
			<i class="fas fa-chevron-up" v-else></i>
		</span>

		@if(fonctionnalite('notifications') !== false && !empty(moi()))
			<notification></notification>
		@endif

		<div id="image_nom">
			@if(empty(moi_extranet()) && moi()->avatar !== 'null' && !empty(moi()->avatar))
			<img src="{{ asset('storage/'.moi()->avatar) }}" alt="">
			@endif

			@if(empty(moi_extranet()))
                <a href="{{ route('base_eden.utilisateur_connecte.index') }}">{{ moi()->prenom }} {{ moi()->nom }}</a>
			@else
				<a href="{{ route('extranet.utilisateur_connecte.index') }}">
					@{{ moi_extranet.prenom }} @{{ moi_extranet.nom }} 
					<span v-if="moi_extranet.contacts_associes.length > 1"> / @{{ moi_extranet.contact_selectionne.client.chaine_affichage ?? '' }}</span>
				</a>
			@endif

			@if(session()->has('eden_usurpation_origine'))
			    <a href="{{ route('parametrage.usurpation.retour') }}" style="margin-left: 5px;">[retour à ma session]</a>
			@endif

			@if(session()->has('extranet_usurpation_retour_eden'))
				<a href="{{session()->get('extranet_usurpation_retour_eden')}}" style="margin-left: 5px;">[retour à EDEN]</a>
			@endif
		</div>

		@if(super_admin() || (!empty(moi()) && moi()->droit_usurpation == 1))
			<menu-usurpation></menu-usurpation>
		@endif

		@if(!empty(moi_extranet()))
			<div class="nav-item dropdown dropleft dropdown_hover css_block_nav_systeme_ticket" v-if="moi_extranet.contacts_associes.length > 1">
				<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
				   aria-expanded="false"><i class="fas fa-people-arrows css_btn_action_header"></i></a>
				<div class="dropdown-menu">
					<a class="dropdown-item" :href="'/extranet/changement_contact/' + contact.id" v-for="contact in moi_extranet.contacts_associes.filter(c => c.id != moi_extranet.contact_selectionne.id)" :key="contact.id"
						 v-html="contact.client.chaine_affichage ?? ''">
					</a>
				</div>
			</div>
		@endif

		@if(fonctionnalite('intranet') && !empty(moi()) && moi()->autorisation_intranet != 3)
			<a href="{{ route('intranet.index') }}" :title="traduction('interface.tooltip.eden_intranet')" data-toggle="tooltip" data-placement="left" class="">
				<i class="fas fa-network-wired css_btn_action_header" aria-hidden="true"></i>
			</a>
		@endif

		@include('eden::templates.deconnexion')
	</div>

	@if(empty(moi_extranet()))
	<div class="css_groupe_bouton_action_navbar_haut_responsive" style="display: none" v-if="toggle_bouton_header_responsive">
		@include('eden::templates.boutons_navbar_haut')
	</div>
	@endif
</div>



@push('donnees_pour_vuejs_data')
	toggle_bouton_header_responsive : false,

@endpush
