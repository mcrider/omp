{**
 * memberships.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of groups in press management.
 *}

<ul class="menu">
	<li><a href="javascript:replaceModalWithUrl('{$baseUrl}/index.php/dev/$$$call$$$/grid/masthead/masthead-row/edit-group?rowId={$group->getId()}', '{translate key="manager.groups.editTitle"}')">{translate key="manager.groups.editTitle"}</a></li>
	<li class="current"><a href=""}">{translate key="manager.groups.membership"}</a></li>
</ul>

<br/>

<div id="membership">
<table width="100%" class="pkp_listing">
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="85%">{translate key="user.name"}</td>
		<td width="15%">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
{iterate from=memberships item=membership}
	{assign var=user value=$membership->getUser()}
	<tr valign="top">
		<td>{$user->getFullName()|escape}</td>
		<td>
			<a href="{url op="deleteMembership" path=$membership->getGroupId()|to_array:$membership->getUserId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.groups.membership.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveMembership" d=u groupId=$group->getId() userId=$user->getId()}">&uarr;</a>&nbsp;<a href="{url op="moveMembership" d=d groupId=$group->getId() userId=$user->getId()}">&darr;</a>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="{if $memberships->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $memberships->wasEmpty()}
	<tr>
		<td colspan="2" class="nodata">{translate key="manager.groups.membership.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="2" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$memberships}</td>
		<td align="right">{page_links anchor="membership" name="memberships" iterator=$memberships}</td>
	</tr>
{/if}
</table>

<a href="{url op="addMembership" path=$group->getId()}" class="action">{translate key="manager.groups.membership.addMember"}</a>
</div>

<form class="pkp_form" action="test">
	</form>
