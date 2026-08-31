{if $rcmthemestyle eq "twenty-one" || $rcmthemestyle eq "six" || $rcmthemestyle eq "nexus"}
	{include file="$template/includes/alert.tpl" type="info" msg=$RCMLANG.suggestdomaindesc}

	{if $suggestresults}
		<h3>{$RCMLANG.searchresulttitle} "{$smarty.post.keyword}"</h3>
		{if $noresults}
			<div class="alert alert-warning">
				<p>{$noresults}</p>
			</div>
		{else}
			<div class="well">
				<form name="domain" action="cart.php" method="post">
				<input type="hidden" name="a" value="add" />
				<input type="hidden" name="domain" value="register" />
				<table cellpadding="10" cellspacing="0" width="100%" class="table table-striped table-framed">
					<tr>
						<td style="width:25%"><strong>{$RCMLANG.suggestions}</strong></td>
						{foreach key=num item=tld from=$suggestarray.0}
						<td><strong>{$tld}</strong></td>
						{/foreach}
					</tr>
					{foreach key=keydom item=valdom from=$suggestarray.1}
					<tr>
						<td>{$keydom}</td>
						{foreach key=extension item=avail from=$valdom}
						{if $avail eq "available"}
						<td style="background-color:#DFF0D8;">
							<input type="checkbox" name="domains[]" value="{$keydom}.{$extension}">
							{if $domainbot_yrs eq "true"}
							<select name="domainsregperiod[{$keydom}.{$extension}]" class="form-control" style="min-width:55px">
								{if $extension eq "co" || $extension eq ".com.co" || $extension eq "net.co" || $extension eq "nom.co"}
								<option value="1">1 {$LANG.orderyears}</option>
								<option value="2">2 {$LANG.orderyears}</option>
								<option value="3">3 {$LANG.orderyears}</option>
								<option value="4">4 {$LANG.orderyears}</option>
								<option value="5">5 {$LANG.orderyears}</option>
								{else} 
								<option value="1">1 {$LANG.orderyears}</option>
								<option value="2">2 {$LANG.orderyears}</option>
								<option value="3">3 {$LANG.orderyears}</option>
								<option value="4">4 {$LANG.orderyears}</option>
								<option value="5">5 {$LANG.orderyears}</option>
								<option value="6">6 {$LANG.orderyears}</option>
								<option value="7">7 {$LANG.orderyears}</option>
								<option value="8">8 {$LANG.orderyears}</option>
								<option value="9">9 {$LANG.orderyears}</option>
								<option value="10">10 {$LANG.orderyears}</option>
								{/if}
							</select>
							{else}
								<input type="hidden" name="regperiod[{$keydom}.{$extension}]" value="1" />
							{/if}
						</td>
						{else}
						<td style="background-color:#F2DEDE;">{$RCMLANG.notavailable}</td>
						{/if}
						{/foreach}
					</tr>
					{/foreach} 
				</table>
				<p style="text-align:center;padding:10px;"><input type="submit" value="{$LANG.ordercontinuebutton}" class="btn btn-primary" /></p>
				<p style="text-align:center;">{$RCMLANG.selectandgo}</p>
				</form>
			</div>
		{/if}
		<h3>{$RCMLANG.tryothersearch}</h3>
	{/if}
	
	<div class="well">
		<table cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td>
					<form action="{$smarty.server.REQUEST_URI}" method="post" >
					<table cellpadding="10" cellspacing="0" width="100%">					
						<tr>
							<td><strong>{$RCMLANG.searchdomterms}</strong></td>
							<td><input class="form-control" type="text" name="keyword" /><br /></td>
						</tr>
						<tr>
							<td><strong>{$RCMLANG.selecttlds}</strong><br />{$RCMLANG.maxtlds} = {$domainbot_extqty}</td>
							<td>
								<table style="width: 100%; border: 1px solid #DDDDDD; border-collapse: separate; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px; padding: 10px;" cellspacing="0" cellpadding="10">
									{foreach key=num item=val from=$domainbot_tlds}
									<tr>
										{foreach key=tldnum item=tldval from=$val}
										<td style="border: none; text-align: right; padding:5px;">.{$tldval}</td>
										<td style="border: none; text-align: left;"><input name="tlds[]" type="checkbox" value="{$tldval}" /></td>
										{foreachelse}
										<td style="border: none; text-align: left;">&nbsp;</td>
										{/foreach}
									{/foreach}
									</tr>
								</table>
								<p style="text-align:center;padding:10px;"><input name="suggest" type="submit" value="{$RCMLANG.suggestbutton}" class="btn btn-primary" /></p>
							</td>
						</tr>
					</table>
					</form>
				</td>
			</tr>
		</table>
	</div>
	<form method="post" action="cart.php">
		<p><input type="submit" value="{$LANG.clientareabacklink}" class="btn" /></p>
	</form>
	
{else}

	{include file="$template/pageheader.tpl" title=$RCMLANG.suggestdomaintitle desc=$RCMLANG.suggestdomainintro}
	<div class="row">
		<div class="col30">
			<div class="internalpadding">
				<div class="styled_title">
					<p>{$RCMLANG.suggestdomaindesc}</p>
				</div>
			</div>
			<form method="post" action="cart.php">
				<p><input type="submit" value="{$LANG.clientareabacklink}" class="btn" /></p>
			</form>
		</div>
		
		<div class="col70">
			<div class="internalpadding">
				{if $suggestresults}
				<h3>{$RCMLANG.searchresulttitle} "{$smarty.post.keyword}"</h3>
				{if $noresults}
				<div class="alert alert-error alert-message error errorbox">
					<p>{$noresults}</p>
				</div>
				{else}
				<div class="well">
					<form name="domain" action="cart.php" method="post">
					<input type="hidden" name="a" value="add" />
					<input type="hidden" name="domain" value="register" />
					<table class="frame" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>
								<table border="0" cellpadding="10" cellspacing="0" width="100%">
									<tr>
										<td width="150" class="fieldarea"><strong>{$RCMLANG.suggestions}</strong></td>
										{foreach key=num item=tld from=$suggestarray.0}
										<td><strong>{$tld}</strong></td>
										{/foreach}
									</tr>
									{foreach key=keydom item=valdom from=$suggestarray.1}
									<tr>
										<td width="150" class="fieldarea">{$keydom}</td>
										{foreach key=extension item=avail from=$valdom}
										{if $avail eq "available"}
										<td style="background-color:#DFF0D8;">
											<input type="checkbox" name="domains[]" value="{$keydom}.{$extension}">
											{if $domainbot_yrs eq "true"}
											<select name="domainsregperiod[{$keydom}.{$extension}]" class="form-control" style="min-width:55px">
												{if $extension eq "co" || $extension eq ".com.co" || $extension eq "net.co" || $extension eq "nom.co"}
												<option value="1">1 {$LANG.orderyears}</option>
												<option value="2">2 {$LANG.orderyears}</option>
												<option value="3">3 {$LANG.orderyears}</option>
												<option value="4">4 {$LANG.orderyears}</option>
												<option value="5">5 {$LANG.orderyears}</option>
												{else} 
												<option value="1">1 {$LANG.orderyears}</option>
												<option value="2">2 {$LANG.orderyears}</option>
												<option value="3">3 {$LANG.orderyears}</option>
												<option value="4">4 {$LANG.orderyears}</option>
												<option value="5">5 {$LANG.orderyears}</option>
												<option value="6">6 {$LANG.orderyears}</option>
												<option value="7">7 {$LANG.orderyears}</option>
												<option value="8">8 {$LANG.orderyears}</option>
												<option value="9">9 {$LANG.orderyears}</option>
												<option value="10">10 {$LANG.orderyears}</option>
												{/if}
											</select>
											{else}
												<input type="hidden" name="regperiod[{$keydom}.{$extension}]" value="1" />
											{/if}
										</td>
										{else}
										<td style="background-color:#F2DEDE;">{$RCMLANG.notavailable}</td>
										{/if}
										{/foreach}
									</tr>
									{/foreach} 
								</table>
							</td>
						</tr>
					</table>
					<p style="text-align:center;padding:10px;"><input type="submit" value="{$LANG.ordercontinuebutton}" class="btn btn-info info" /></p>
					<p style="text-align:center;">{$RCMLANG.selectandgo}</p>
					</form>
				</div>
				{/if}
			
				<h3>{$RCMLANG.tryothersearch}</h3>
			
				{/if}
				<div class="well">
					<table class="frame" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>
								<form action="{$smarty.server.REQUEST_URI}" method="post" >
								<table border="0" cellpadding="10" cellspacing="0" width="100%">					
									<tr>
										<td class="fieldarea" width="150"><strong>{$RCMLANG.searchdomterms}</strong></td>
										<td><input class="inputbox" style="width: 300px;" type="text" name="keyword" /></td>
									</tr>
									<tr>
										<td class="fieldarea" width="150"><strong>{$RCMLANG.selecttlds}</strong><br />{$RCMLANG.maxtlds} = {$domainbot_extqty}</td>
										<td>
											<table style="width: 100%; border: 1px solid #DDDDDD; border-collapse: separate; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px; padding: 10px;" cellspacing="0" cellpadding="10">
												{foreach key=num item=val from=$domainbot_tlds}
												<tr>
													{foreach key=tldnum item=tldval from=$val}
													<td style="border: none; text-align: right; padding-right:5px;">.{$tldval}</td>
													<td style="border: none; text-align: left;"><input name="tlds[]" type="checkbox" value="{$tldval}" /></td>
													{foreachelse}
													<td style="border: none; text-align: left;">&nbsp;</td>
													{/foreach}
												{/foreach}
												</tr>
											</table>
											<p style="text-align:center;padding:10px;"><input name="suggest" type="submit" value="{$RCMLANG.suggestbutton}" class="btn btn-info info" /></p>
										</td>
									</tr>
								</table>
								</form>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
{/if}