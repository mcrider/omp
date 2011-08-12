{**
 * index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the notification settings page and unchecks
 *
 *}
{strip}
{assign var="pageTitle" value="notification.mailList"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#notificationMailList').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<p><span class="instruct">{translate key="notification.mailListDescription"}</span></p>

<form class="pkp_form" id="notificationMailListForm" method="post" action="{url op="saveSubscribeMailList"}">
	{include file="common/formErrors.tpl"}
	{if $success}
		<p><span class="pkp_form_success">{translate key="$success"}</span></p>
	{/if}

	{fbvFormArea id="notificationMailList"}
		{fbvFormSection title="user.email" for="email" required="true"}
			{fbvElement type="text" id="email" value=$email|escape size=$fbvStyles.size.MEDIUM} <br />
			{fbvElement type="text" label="user.confirmEmail" id="confirmEmail" value=$confirmEmail|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:cancelUrl page="notification"}
	{fbvFormButtons submitText="form.submit" cancelUrl=$cancelUrl}
</form>

</form>

{include file="common/footer.tpl"}

