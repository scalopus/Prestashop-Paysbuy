<p class="payment_module">
	<a href="javascript:$('#paysbuy_form').submit();" title="{l s='Pay with PaySbuy' mod='paysbuy'}">
		<img src="{$module_template_dir}paysbuy.gif" alt="{l s='Pay with PaySbuy' mod='paysbuy'}" align="left" />
		<b>{l s='Make a secure payment over Paysbuy gateway' mod='paysbuy'}</b> <br /><br />
		
		{l s='You must pay ' mod='paysbuy'}<b>{$total}</b> {l s='THB via this gateway.' mod='paysbuy'}
		({$Charge}% {l s='extra charges.' mod='paysbuy'})
		
		   <br />
		{l s='- Accepts money from Paysbuy account.' mod='paysbuy'}<br />
		{if $accept_visa}
			{l s='- Credits cards of VISA and MasterCard are accepted' mod='paysbuy'}<br />
		{/if}
		{if $accept_amex}
			{l s='- Credits cards of AmericanExpress (AMEX) is accepted' mod='paysbuy'}<br />
		{/if}
		{if !$accept_visa && !$accept_amex}
			{l s='- Not accepts any credits cards.' mod='paysbuy'}<br />
		{/if}<br />
	</a>
</p>

<form action="{$paysbuyUrl}" method="post" id="paysbuy_form" class="hidden">
	<input type="hidden" name="psb" value="psb" />
	<input type="hidden" name="inv" value="{$id_cart}" />
	<input type="hidden" name="biz" value="{$business}" />
	<input type="hidden" name="reqURL" value="{$reqUrl}" />
	<input type="hidden" name="postURL" value="{$postUrl}" />
	<input type="hidden" name="currencyCode" value="{$currency->iso_code}" />	
	<input type="hidden" name="email" value="{$customer->email}" />
	<input type="hidden" name="itm" value="{l s='Purchase Number' mod='paysbuy'} {$id_cart}" />
	<input type="hidden" name="amt" value="{$total}" />
</form>