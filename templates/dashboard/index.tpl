{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard index.
 *
 * $Id$
 *}

{strip}
{assign var="pageTitle" value="navigation.dashboard"}
{include file="common/header.tpl"}
{/strip}

<script type='text/javascript'>
{literal}$(function(){
// Create a map of user group ids to role ids.

	$('.submissionList').click(function() {
		// Get the role's default user group
		var userGroupId = $(this).attr('id');

		// Change the selected user group on the server.
		$.post(
			{/literal}'{url router=$smarty.const.ROUTE_COMPONENT component="api.user.UserApiHandler" op="changeActingAsUserGroup"}'{literal},
			{'changedActingAsUserGroupId': userGroupId},
			function(jsonData) {
				// Display error message (if any)
				if (jsonData.status == false) {
					alert(jsonData.content);
					return false;
				} else {
					window.location.replace({/literal}"{url page="submission"}"{literal});
				}
			},
			"json"
		);
	});
});{/literal}
</script>

<h3>Temporary Dashboard</h3>

{if $atSiteLevel}
<!-- List Presses -->
<div id="presses">
	<br /></p>Please select a press to go to its dashboard:</p>
	{iterate from=presses item=press}
	<ul>
		<li><a class="action" href="{url press=$press->getPath() page="dashboard"}">{$press->getLocalizedName()|escape}</a></li>
	</ul>
	{/iterate}

</div>
{else}
<!-- Submission lists for each role -->
<div id="submissionLists" style="border: 1px solid gray; margin: 10px;">
	<h4>Submission lists</h4>
	<ul>
		{if $isManager}<li><a class="submissionList" id="{$managerId}" href="#">Manager/Editor Submissions</a></li>{/if}
		{if $isSeriesEditor}<li><a class="submissionList" id="{$seriesEditorId}" href="#">Series Editor Submissions</a></li>{/if}
		{if $isPressAssistant}<li><a class="submissionList" id="{$assistantId}" href="#">Press Assistant Submissions</a></li>{/if}
		{if $isReviewer}<li><a class="submissionList" id="{$reviewerId}" href="#">Reviewer Submissions</a></li>{/if}
		{if $isAuthor}<li><a class="submissionList" id="{$authorId}" href="#">Author Submissions</a>{/if}
	</ul>
</div>

{if $isManager}
<!-- Press manager functions -->
<div id="managerFunctions" style="border: 1px solid gray; margin: 10px;">
	<h4>Managerial functions</h4>
	<ul class="plain">
		{if $announcementsEnabled}
			<li>&#187; <a href="{url op="announcements"}">{translate key="manager.announcements"}</a></li>
		{/if}
		<li>&#187; <a href="{url page="manager" op="files"}">{translate key="manager.filesBrowser"}</a></li>
		<li>&#187; <a href="{url page="manager" op="languages"}">{translate key="common.languages"}</a></li>
		<li>&#187; <a href="{url page="manager" op="groups"}">{translate key="manager.groups"}</a></li>
		<li>&#187; <a href="{url page="manager" op="emails"}">{translate key="manager.emails"}</a></li>
		<li>&#187; <a href="{url page="manager" op="setup"}">{translate key="manager.setup"}</a></li>
		<li>&#187; <a href="{url page="manager" op="plugins"}">{translate key="manager.plugins"}</a></li>
		<li>&#187; <a href="{url page="manager" op="importexport"}">{translate key="manager.importExport"}</a></li>
		{call_hook name="Templates::Manager::Index::ManagementPages"}
	</ul>

	<h4>User management</h4>

	<ul class="plain">
		<li>&#187; <a href="{url page="manager" op="people" path="all"}">{translate key="manager.people.allEnrolledUsers"}</a></li>
		<li>&#187; <a href="{url page="manager" op="enrollSearch"}">{translate key="manager.people.allSiteUsers"}</a></li>
		<li>&#187; <a href="{url page="manager" op="showNoRole"}">{translate key="manager.people.showNoRole"}</a></li>
		{url|assign:"managementUrl" page="manager"}
		<li>&#187; <a href="{url page="manager" op="createUser" source=$managementUrl}">{translate key="manager.people.createUser"}</a></li>
		<li>&#187; <a href="{url page="manager" op="mergeUsers"}">{translate key="manager.people.mergeUsers"}</a></li>
		{call_hook name="Templates::Manager::Index::Users"}
	</ul>

</div>
{/if}
{/if}


<!-- Site admin functions -->
{if $isSiteAdmin}
<div id="adminFunctions" style="border: 1px solid gray; margin: 10px;">
	<h3>{translate key="admin.siteManagement"}</h3>
	<ul class="plain">
		<li>&#187; <a href="{url context="index" page="admin" op="settings"}">{translate key="admin.siteSettings"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="presses"}">{translate key="admin.hostedPresses"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="languages"}">{translate key="common.languages"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="auth"}">{translate key="admin.authSources"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="systemInfo"}">{translate key="admin.systemInformation"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="expireSessions"}" onclick="return confirm('{translate|escape:"jsparam" key="admin.confirmExpireSessions"}')">{translate key="admin.expireSessions"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="clearDataCache"}">{translate key="admin.clearDataCache"}</a></li>
		<li>&#187; <a href="{url context="index" page="admin" op="clearTemplateCache"}" onclick="return confirm('{translate|escape:"jsparam" key="admin.confirmClearTemplateCache"}')">{translate key="admin.clearTemplateCache"}</a></li>
		{call_hook name="Templates::Admin::Index::AdminFunctions"}
	</ul>
</div>
{/if}


{include file="common/footer.tpl"}

