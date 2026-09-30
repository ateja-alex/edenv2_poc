<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20220310_budgetinsight_debit_credit implements Script {
	
	public function execute() {
		
		$transactions = modele('budget_insight_transaction')->get();
		
		foreach($transactions as $transaction) {
			
			if($transaction->value < 0) {
				
				$transaction->debit = $transaction->value;
				$transaction->credit = 0;
				$transaction->save();
			}
			else {
				
				$transaction->debit = 0;
				$transaction->credit = $transaction->value;
				$transaction->save();
			}
		}
		
		return true;
	}
}