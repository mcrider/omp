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

<p><span class="instruct">{translate key="notification.unsubscribeDescription"}</span></p>

<form class="pkp_form" id="notificationSettingsForm" method="post" action="{url op="unsubscribeMailList"}">
	{if $error}
		<p><span class="pkp_form_error">{translate key="$error"}</span></p>
	{/if}

	{if $success}
		<p>{translate key="$success"}</p>
	{/if}

	{fbvFormArea id="notificationMailList"}
		{fbvFormSection required="true"}
			{fbvElement type="text" label="user.email" id="email" size=$fbvStyles.size.MEDIUM} <br />
			{fbvElement type="text" label="user.password" id="password" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:cancelUrl page="notification"}
	{fbvFormButtons submitText="form.submit" cancelUrl=$cancelUrl}
</form>

{include file="common/footer.tpl"}

