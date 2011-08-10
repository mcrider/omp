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

<ul>
{if $status == 'subscribeError'}
	<li class="pkp_form"><span class="pkp_form_error">{translate key="notification.subscribeError"}</span></li>
{elseif $status == 'subscribeSuccess'}
	<li>{translate key="notification.subscribeSuccess"}</li>
{elseif $status == 'confirmError'}
	<li class="pkp_form"><span class="pkp_form_error">{translate key="notification.confirmError"}</span></li>
{elseif $status == 'confirmSuccess'}
	<li>{translate key="notification.confirmSuccess"}</li>
{elseif $status == 'unsubscribeSuccess'}
	<li class="pkp_form"><span class="pkp_form_error">{translate key="notification.unsubscribeSuccess"}</span></li>
{elseif $status == 'unsubscribeError'}
	<li class="pkp_form"><span class="pkp_form_error">{translate key="notification.unsubscribeError"}</span></li>
{/if}
<ul>

{include file="common/footer.tpl"}

