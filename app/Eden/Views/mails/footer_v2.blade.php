<?php

	if(isset($compte_email))
		$signature = $compte_email->image_signature;

?>

 <!--[if mso | IE]>
<table
		align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600"
>
	<tr>
		<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
		<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
			<tbody>
			<tr>
				<td style="direction:ltr;font-size:0px;padding:20px 0px 20px 0px;padding-bottom:0px;padding-top:20px;text-align:center;vertical-align:top;">
					<!--[if mso | IE]>
					<table role="presentation" border="0" cellpadding="0" cellspacing="0">

						<tr>

							<td
									class="" style="vertical-align:top;width:600px;"
							>
					<![endif]-->
					<div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
							<tr>
								<td align="center" style="font-size:0px;padding:10px 25px;padding-bottom:0px;word-break:break-word;">
									<!--[if mso | IE]>
									<table
											align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
									>
										<tr>

											<td>
									<![endif]-->
									<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
										<tr>
											<td style="padding:4px;">
												@if(!empty($signature))
													<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#66224D;border-radius:6px;">
														<tr>
															<td style="font-size:0;height:30%;vertical-align:middle;width:30%;">
																<img src="{{ asset("storage/$signature") }}" alt="" width="100%">
															</td>
														</tr>
													</table>
												@endif
											</td>
										</tr>
									</table>
							</tr>
						</table>
					</div>
					<!--[if mso | IE]>
					</td>

					</tr>

					</table>
					<![endif]-->
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<!--[if mso | IE]>
	</td>
	</tr>
	</table>
	<![endif]-->
