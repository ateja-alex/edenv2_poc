<div class="row">
	<div class="form-row col-md-12">
		<div class="row">
			<label class="col-md-4 control-label" for="ccard-num">@traduction('formulaire.mise_a_jour_carte_payline.numero')*</label>
			<div class="col-md-6">
				<input type="text" name="ccard-num" id="ccard-num" class="form-control input-myaccount">
			</div>
		</div>
		<div class="row">
			<label class="col-md-4 control-label" for="ccard-exp">@traduction('formulaire.mise_a_jour_carte_payline.date_expiration')*</label>
			<div class="col-md-3">
				<select name="ccard-exp1" id="ccard-exp1" class="form-control" required="required" style="width:65px;display: inline;padding:6px;">
					<option disabled="disabled" selected="selected">{{ traduction('formulaire.mise_a_jour_carte_payline.mm') }}</option>
					@for ( $m=1;$m<=12;$m++ ) 
						<option value="{{sprintf('%1$02d', $m)}}">{{sprintf('%1$02d', $m)}}</option>
					@endfor
				</select>
				/
				<select name="ccard-exp2" id="ccard-exp2" class="form-control" required="required" style="width:65px;display: inline;padding:6px;">
					<option disabled="disabled" selected="selected">{{ traduction('formulaire.mise_a_jour_carte_payline.aa') }}</option>
					@for ( $a=date('Y');$a<=date('Y')+50;$a++ ) 
						<option value="{{substr($a,2)}}">{{$a}}</option>
					@endfor
				</select>
			</div>
			<label class="col-md-1 control-label" for="ccard-cvc">@traduction('formulaire.mise_a_jour_carte_payline.cvc')*</label>
			<div class="col-md-2">
				<input type="text" name="ccard-cvc" id="ccard-cvc" class="form-control input-myaccount">
			</div>
				
		</div>	
	</div>
</div>