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
{assign var="pageTitle" value="notification.settings"}
{include file="common/header.tpl"}
{/strip}

<p>{translate key="notification.settingsDescription"}</p>

<form class="pkp_form" id="notificationSettings" method="post" action="{url op="saveSettings"}">

<!-- Submission events -->
{if !$canOnlyRead && !$canOnlyReview}
	<h4>{translate key="notification.type.submissions"}</h4>

	<ul>
		<li>{translate key="notification.type.monographSubmitted" param=$titleVar}
		<ul class="plain">
			<li><span>
				<input id="notificationMonographSubmitted" type="checkbox" name="notificationMonographSubmitted"{if !$smarty.const.NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED|in_array:$notificationSettings} checked="checked"{/if} />
				{fieldLabel name="notificationMonographSubmitted" key="notification.allow"}
			</span></li>
			<li><span>
				<input id="emailNotificationMonographSubmitted"type="checkbox" name="emailNotificationMonographSubmitted"{if $smarty.const.NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED|in_array:$emailSettings} checked="checked"{/if} />
				{fieldLabel name="emailNotificationMonographSubmitted" key="notification.email"}
			</span></li>
		</ul>
		</li>
	</ul>

	<ul>
		<li>{translate key="notification.type.metadataModified" param=$titleVar}
		<ul class="plain">
			<li><span>
				<input id="notificationMetadataModified" type="checkbox" name="notificationMetadataModified"{if !$smarty.const.NOTIFICATION_TYPE_METADATA_MODIFIED|in_array:$notificationSettings} checked="checked"{/if} />
				{fieldLabel name="notificationMetadataModified" key="notification.allow"}
			</span></li>
			<li><span>
				<input id="emailNotificationModified" type="checkbox" name="emailNotificationMetadataModified"{if $smarty.const.NOTIFICATION_TYPE_METADATA_MODIFIED|in_array:$emailSettings} checked="checked"{/if} />
				{fieldLabel name="emailNotificationMetadataModified" key="notification.email"}
			</span></li>
		</ul>
		</li>
	</ul>
{/if}

<br />

{if !$canOnlyRead}
	<!-- Reviewing events -->
	<h4>{translate key="notification.type.reviewing"}</h4>


	<ul>
		<li>{translate key="notification.type.reviewerComment" param=$titleVar}
		<ul class="plain">
			<li><span>
				<input id="notificationReviewerComment" type="checkbox" name="notificationReviewerComment"{if !$smarty.const.NOTIFICATION_TYPE_REVIEWER_COMMENT|in_array:$notificationSettings} checked="checked"{/if} />
				{fieldLabel name="notificationReviewerComment" key="notification.allow"}
			</span></li>
			<li><span>
				<input id="emailNotificationReviewerComment" type="checkbox" name="emailNotificationReviewerComment"{if $smarty.const.NOTIFICATION_TYPE_REVIEWER_COMMENT|in_array:$emailSettings} checked="checked"{/if} />
				{fieldLabel name="emailNotificationReviewerComment" key="notification.email"}
			</span></li>
		</ul>
		</li>
	</ul>

	<br />
{/if}

<br />

<p><input type="submit" value="{translate key="form.submit"}" class="button defaultButton" />  <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="notification" escape=false}'" /></p>

</form>

{include file="common/footer.tpl"}

