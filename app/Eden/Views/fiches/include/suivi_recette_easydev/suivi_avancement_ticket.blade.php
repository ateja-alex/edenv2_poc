<div class="css_conteneur_progression_ticket">
	<div class="steps-container">
	
		<div class="step" style="cursor: pointer;" @click="changer_etape_du_ticket(1)"
			:class="{'completed':(etape_du_ticket > 1), 'in-progress':(etape_du_ticket == 1)}">
			
				<svg v-if="etape_du_ticket > 1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
				</svg>
				<div v-if="etape_du_ticket == 1" class="preloader"></div>
			
			<div class="label" 
				:class="{'completed':(etape_du_ticket > 1), 'loading':(etape_du_ticket == 1)}">
				@traduction('module_sur_fiche.suivi_recette_easydev.suivi_avancement.enregistre')
			</div>
			<div class="icon" 
				:class="{'completed':(etape_du_ticket > 1), 'in-progress':(etape_du_ticket == 1)}">
				<i class="fas fa-thumbtack"></i>
			</div>
		</div>
		
		<div 
			class="line"
			:class="{'completed':(etape_du_ticket > 2), 'next-step-in-progress':(etape_du_ticket == 2), 'prev-step-in-progress':(etape_du_ticket == 1)}"></div>
		
		<div class="step" style="cursor: pointer;" @click="changer_etape_du_ticket(2)"
			:class="{'completed':(etape_du_ticket > 2), 'in-progress':(etape_du_ticket == 2)}">
				<svg v-if="etape_du_ticket > 2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
				</svg>
				<div v-if="etape_du_ticket == 2" class="preloader"></div>
			<div class="label"
				:class="{'completed':(etape_du_ticket > 2), 'loading':(etape_du_ticket == 2)}">
				@traduction('module_sur_fiche.suivi_recette_easydev.suivi_avancement.en_cours_de_dev')
			</div>
			<div class="icon" 
				:class="{'completed':(etape_du_ticket > 2), 'in-progress':(etape_du_ticket == 2)}">
				<i class="fab fa-laravel"></i>
			</div>
		</div>
		<div class="line" 
			:class="{'completed':(etape_du_ticket > 3), 'next-step-in-progress':(etape_du_ticket == 3), 'prev-step-in-progress':(etape_du_ticket == 2)}">
		</div>
		<div class="step" style="cursor: pointer;" @click="changer_etape_du_ticket(3)"
			:class="{'completed':(etape_du_ticket > 3), 'in-progress':(etape_du_ticket == 3)}">
				<svg v-if="etape_du_ticket > 3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
				</svg>
				<div v-if="etape_du_ticket == 3" class="preloader"></div>
			<div class="label" 
				:class="{'completed':(etape_du_ticket > 3), 'loading':(etape_du_ticket == 3)}">
				@traduction('module_sur_fiche.suivi_recette_easydev.suivi_avancement.besoin_de_precision')
			</div>
			<div class="icon" 
				:class="{'completed':(etape_du_ticket > 3), 'in-progress':(etape_du_ticket == 3)}">
				<i class="far fa-question-circle"></i>
			</div>
		</div>
		<div class="line" 
			:class="{'completed':(etape_du_ticket > 4), 'next-step-in-progress':(etape_du_ticket == 4), 'prev-step-in-progress':(etape_du_ticket == 3)}"></div>
		<div class="step" style="cursor: pointer;" @click="changer_etape_du_ticket(4)"
			:class="{'completed':(etape_du_ticket > 4), 'in-progress':(etape_du_ticket == 4)}">
				<svg v-if="etape_du_ticket > 4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
				</svg>
				<div v-if="etape_du_ticket == 4" class="preloader"></div>
			<div class="label" 
				:class="{'completed':(etape_du_ticket > 4), 'loading':(etape_du_ticket == 4)}">
				@traduction('module_sur_fiche.suivi_recette_easydev.suivi_avancement.relecture_easydev')
			</div>
			<div class="icon" 
				:class="{'completed':(etape_du_ticket > 4), 'in-progress':(etape_du_ticket == 4)}">
				<i class="fas fa-chalkboard-teacher"></i>
			</div>
		</div>
		<div class="line" 
			:class="{'completed':(etape_du_ticket > 5), 'next-step-in-progress':(etape_du_ticket == 5), 'prev-step-in-progress':(etape_du_ticket == 4)}"></div>
		<div class="step" style="cursor: pointer;" @click="changer_etape_du_ticket(5)"
			:class="{'completed':(etape_du_ticket > 5), 'in-progress':(etape_du_ticket == 5)}">
				<svg v-if="etape_du_ticket > 5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
				</svg>
				<div v-if="etape_du_ticket == 5" class="preloader"></div>
			<div class="label" 
				:class="{'completed':(etape_du_ticket > 5), 'loading':(etape_du_ticket == 5)}">
				@traduction('module_sur_fiche.suivi_recette_easydev.suivi_avancement.validation_client')
			</div>
			<div class="icon" 
				:class="{'completed':(etape_du_ticket > 5), 'in-progress':(etape_du_ticket == 5)}">
				<i class="far fa-thumbs-up"></i>
			</div>
		</div>
		<div class="line" 
			:class="{'completed':(etape_du_ticket > 6), 'next-step-in-progress':(etape_du_ticket == 6), 'prev-step-in-progress':(etape_du_ticket == 5)}"></div>
		<div class="step" style="cursor: pointer;" @click="changer_etape_du_ticket(6)"
			:class="{'completed':(etape_du_ticket > 6), 'completed':(etape_du_ticket == 6)}">
			<svg v-if="etape_du_ticket >= 6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
				<path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
			</svg>
			<div class="label" 
			:class="{'completed':(etape_du_ticket == 6)}">
				@traduction('module_sur_fiche.suivi_recette_easydev.suivi_avancement.cloture')
			</div>
			<div class="icon" 
			:class="{'completed':(etape_du_ticket == 6)}">
				<i class="fas fa-clipboard-check"></i>
			</div>
		</div>
	</div>
</div>

