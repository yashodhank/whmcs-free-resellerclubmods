<script language="javascript" type="text/javascript">
	function confirmMove(){literal}{{/literal}return confirm("{$RCMLANG.clientareamovewarning}");{literal}}{/literal}
</script>

{if isset($subaccount_deny)}

	{include file="$template/contactaccessdenied.tpl"}

{else}

{if $rcmthemestyle eq "twenty-one" || $rcmthemestyle eq "six" || $rcmthemestyle eq "nexus"}
	{include file="$template/includes/alert.tpl" type="info" msg=$RCMLANG.movedomaindesc01|cat:"<p>"|cat:{$RCMLANG.movedomaindesc02}|cat:"</p>"}

	{if $validate_success}
		<div class="alert alert-success">
			<p>{$validate_success}</p>
		</div>
	{/if}
	{if $validate_error}
		<div class="alert alert-danger">
			<p>{$validate_error}</p>
		</div>
	{/if}
	{if $move_success}
		<div class="alert alert-success">
			<p>{$move_success}</p>
		</div>
	{/if}
	{if $move_error}
		<div class="alert alert-danger">
			<p>{$move_error}</p>
		</div>
	{/if}
	
	{if $datavalidated neq "true" && $smarty.post.domove neq "true"}
		<p>{$RCMLANG.movedomaindesc03} &quot;{$RCMLANG.validatebutton}&quot;</p>
	{/if}
	
	{if $accessdenied}
		<div class="alert alert-danger"><p>{$RCMLANG.accessdenied}</p></div>
	{elseif $registrarsupport neq "true"}
		<div class="alert alert-danger"><p>{$RCMLANG.notsupported}</p></div>
	{else}
		<div class="well">
			<div>
				{if $datavalidated neq "true" && $smarty.post.domove neq "true"}
					<form method="post" action="{$smarty.server.REQUEST_URI}">
						{$RCMLANG.newowneremail}: 
						<input type="text" name="newcustomer" size="50" value="" class="form-control" />
						<input type="hidden" name="domain" value="{$domain}" />
						<input type="hidden" name="domainid" value="{$domainid}" />
						<input type="hidden" name="validateemail" value="true" />
						<br /><p><input type="submit" value="{$RCMLANG.validatebutton}" class="btn btn-primary" /></p>
					</form>
				{else}
					<form method="post" action="{$smarty.server.REQUEST_URI}">
						<input type="hidden" name="domove" value="true" />
						<input type="hidden" name="datavalidated" value="{$datavalidated}" />
						<input type="hidden" name="domain" value="{$domain}" />
						<input type="hidden" name="domainid" value="{$domainid}" />
						<input type="hidden" name="contact" value="oldcontact" />
						<input type="hidden" name="newcustomer" value="{if $checked_email}{$checked_email}{else}{$smarty.post.newcustomer}{/if}" />
						<table cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td>
									<table class="table" border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td style="width:40%"><strong>{$RCMLANG.domaintomove}</strong></td>
											<td>{$domain}</td>
										</tr>
										{if $productnames}
											<tr>
												<td><strong>{$RCMLANG.assocproducts}</strong></td>
												<td>{$productnames}</td>
											</tr>
										{/if} 
										<tr>
											<td><strong>{$RCMLANG.newdomainowner}</strong></td>
											<td><span class="label label-danger">{if $checked_email}{$checked_email}{else}{$smarty.post.newcustomer}{/if}</span></td>
										</tr>
										<tr>
											<td style="text-align:right;"><input type="checkbox" name="confirmed" value="yes"  />&nbsp;</td>
											<td>{$RCMLANG.moveconfirmation}</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<p align="center">
							<input type="submit" value="{$RCMLANG.movebutton}" class="btn btn-danger" {if $move_success}disabled="disabled"{/if}  onclick="return confirmMove();" />
						</p>
					</form>					
				{/if}
			</div>
		</div>
	{/if}
{else}
	{include file="$template/pageheader.tpl" title=$RCMLANG.movedomaintitle desc=$RCMLANG.movedomaindesc01}

	{if $validate_success}
		<div class="alert alert-success alert-message success successbox">
			<p>{$validate_success}</p>
		</div>
	{/if}
	{if $validate_error}
		<div class="alert alert-error alert-message error errorbox">
			<p>{$validate_error}</p>
		</div>
	{/if}
	{if $move_success}
		<div class="alert alert-success alert-message success successbox">
			<p>{$move_success}</p>
		</div>
	{/if}
	{if $move_error}
		<div class="alert alert-error alert-message error errorbox">
			<p>{$move_error}</p>
		</div>
	{/if}

	<div class="row">
		<div class="col30">
			<div class="internalpadding">
    			<div class="styled_title">
					<p>{$RCMLANG.movedomaindesc02}</p>
					{if $datavalidated neq "true" && $smarty.post.domove neq "true"}
						<p>{$RCMLANG.movedomaindesc03} &quot;{$RCMLANG.validatebutton}&quot;</p>
					{/if}
				</div>
    		</div>
			<form method="post" action="{if $move_success}clientarea.php?action=domains{else}clientarea.php?action=domaindetails{/if}">
				<input type="hidden" name="id" value="{$domainid}" />
				<p><input type="submit" value="{$LANG.clientareabacklink}" class="btn" /></p>
			</form>
		</div>

		<div class="col70">
			<div class="internalpadding">
				{if $accessdenied}
					<div class="alert alert-error alert-message error errorbox">
						<p>{$RCMLANG.accessdenied}</p>
					</div>
				{elseif $registrarsupport neq "true"}
					<div class="alert alert-error alert-message error errorbox">
						<p>{$RCMLANG.notsupported}</p>
					</div>
				{else}
					<div class="well">
						<div>
							{if $datavalidated neq "true" && $smarty.post.domove neq "true"}
								<form method="post" action="{$smarty.server.REQUEST_URI}">
									{$RCMLANG.newowneremail}: 
									<input type="text" name="newcustomer" size="50" value="" />&nbsp;
									<input type="submit" value="{$RCMLANG.validatebutton}" class="btn btn-primary info" />
									<input type="hidden" name="domain" value="{$domain}" />
									<input type="hidden" name="domainid" value="{$domainid}" />
									<input type="hidden" name="validateemail" value="true" />
								</form>
							{else}
								<form method="post" action="{$smarty.server.REQUEST_URI}">
									<input type="hidden" name="domove" value="true" />
									<input type="hidden" name="datavalidated" value="{$datavalidated}" />
									<input type="hidden" name="domain" value="{$domain}" />
									<input type="hidden" name="domainid" value="{$domainid}" />
									<input type="hidden" name="contact" value="oldcontact" />
									<input type="hidden" name="newcustomer" value="{if $checked_email}{$checked_email}{else}{$smarty.post.newcustomer}{/if}" />
									<table class="frame" {if $template=="default"}style="border:none;"{/if} cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td {if $template=="default"}style="border:none;"{/if}>
												<table {if $template=="default"}style="border:none;"{/if} border="0" cellpadding="10" cellspacing="0" width="100%">
													<tr>
				  										<td {if $template=="default"}style="border:none;"{/if} class="fieldarea" width="200"><strong>{$RCMLANG.domaintomove}</strong></td>
														<td {if $template=="default"}style="border:none;"{/if}>{$domain}</td>
													</tr>
													{if $productnames}
														<tr>
															<td {if $template=="default"}style="border:none;"{/if} class="fieldarea"><strong>{$RCMLANG.assocproducts}</strong></td>
															<td {if $template=="default"}style="border:none;"{/if}>{$productnames}</td>
														</tr>
													{/if} 
													<tr>
														<td {if $template=="default"}style="border:none;"{/if} class="fieldarea"><strong>{$RCMLANG.newdomainowner}</strong></td>
														<td {if $template=="default"}style="border:none;"{/if}><span class="label terminated">{if $checked_email}{$checked_email}{else}{$smarty.post.newcustomer}{/if}</span></td>
													</tr>
													<tr>
														<td align="right" {if $template=="default"}style="border:none;"{/if} class="fieldarea"><input type="checkbox" name="confirmed" value="yes"  />&nbsp;</td>
														<td {if $template=="default"}style="border:none;"{/if}>{$RCMLANG.moveconfirmation}</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<p align="center">
										<input type="submit" value="{$RCMLANG.movebutton}" class="btn btn-danger error" {if $move_success}disabled="disabled"{/if}  onclick="return confirmMove();" />
									</p>
								</form>					
							{/if}
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
{/if}

{/if}