<?php

	if(isset($compte_email)) {
		
		$utilisateur = modele('utilisateur', $compte_email->utilisateur_id);
		$signature = $compte_email->signature;
	}

?>

@if(!empty($signature))
    <img src="<?php echo $message->embed(public_path("storage/$signature")); ?>" alt="" width="100%">
@endif
