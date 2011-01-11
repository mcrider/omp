{**
 * stageParticipantForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission Participant grid form
 *
 *}

{modal_title id="#addStageParticipant" key="submission.submit.addStageParticipant" iconClass="fileManagement" canClose=1}

{literal}
<script type='text/javascript'>
	// Handle the user group drop-down change event
	$(function(){
		$('#userGroupId').pkpHandler(
			'$.pkp.controllers.ListbuilderSwitcherHandler',
			{
				onChangeUrl: '{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.StageParticipantListbuilderHandler" op="fetch" monographId=$monographId stageId=$stageId escape=false}{literal}',
				defaultValue: '3',
				listbuilderContainer: '#submissionParticipantsContainer'
			}
		);
	});
</script>
{/literal}

<form id="addStageParticipant" method="post" action="{url op="saveStageParticipant" monographId=$monographId}">
	{include file="common/formErrors.tpl"}

	<p>{translate key="submission.submit.addStageParticipant.description"}</p>

	<span style="padding-left:10px;">{fbvSelect name="userGroupId" id="userGroupId" from=$userGroupOptions translate=false}</span>
	<div id="submissionParticipantsContainer"></div>
</form>

{init_button_bar id="#addStageParticipant"}
