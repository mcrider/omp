{**
 * step1.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 1 of author monograph submission.
 *}
{assign var="pageTitle" value="submission.submit.step1"}
{include file="submission/form/submitStepHeader.tpl"}

<form id="submitStepForm" method="post" action="{url op="saveStep" path=$submitStep}">
	{if $monographId}<input type="hidden" name="monographId" value="{$monographId|escape}" />{/if}
	<input type="hidden" name="submissionChecklist" value="1" />

	{include file="common/formErrors.tpl"}


	<!-- Submission Type -->
	<h3>{translate key="submission.workType"}</h3>

	{fbvFormArea id="submissionType"}
		{fbvFormSection}
		<p>{translate key="submission.workType.description"}</p>
		{fbvElement type="radio" name="isEditedVolume" id="isEditedVolume-0" value="1" checked=$isEditedVolume label="submission.workType.editedVolume"}
		{if $isEditedVolume}{assign var=notIsEditedVolume value=0}{else}{assign var=notIsEditedVolume value=1}{/if}
		{fbvElement type="radio" name="isEditedVolume" id="isEditedVolume-1" value="0" checked=$notIsEditedVolume label="submission.workType.authoredWork"}
		{/fbvFormSection}
	{/fbvFormArea}
	<div class="separator"></div>

	{if count($supportedSubmissionLocaleNames) == 1}
		{* There is only one supported submission locale; choose it invisibly *}
		{foreach from=$supportedSubmissionLocaleNames item=localeName key=locale}
			<input type="hidden" name="locale" value="{$locale|escape}" />
		{/foreach}
	{else}
		{* There are several submission locales available; allow choice *}
		<h3>{translate key="submission.submit.submissionLocale"}</h3>
		<p>{translate key="submission.submit.submissionLocaleDescription"}</p>

		{fbvFormArea id="submissionLocale"}
			{fbvFormSection}
				{fbvElement type="select" id="locale" from=$supportedSubmissionLocaleNames selected=$locale translate=false}
			{/fbvFormSection}
		{/fbvFormArea}

		<div class="separator"></div>
	{/if}{* count($supportedSubmissionLocaleNames) == 1 *}

	<!-- Submission Placement -->
	<h3>{translate key="submission.submit.placement"}</h3>

	{fbvFormArea id="placement"}
		{fbvFormSection}
			{fbvElement type="select" id="seriesId" from=$seriesOptions selected=$seriesId translate=false}
		{/fbvFormSection}
	{/fbvFormArea}


	<!-- Submission checklist -->
	{if $currentPress->getLocalizedSetting('submissionChecklist')}
	
	<h3>{translate key="submission.submit.submissionChecklist"}</h3>
		<div id="messageBox"></div>

		<div class="pkp_submissionChecklist">
		{fbvFormSection}
		<p>{translate key="submission.submit.submissionChecklistDescription"}</p>
		{foreach name=checklist from=$currentPress->getLocalizedSetting('submissionChecklist') key=checklistId item=checklistItem}
			{if $checklistItem.content}
				{fbvCheckbox id="checklist-$checklistId" required=true value=1 label=$checklistItem.content translate=false checked=$monographId}
			{/if}
		{/foreach}
		{/fbvFormSection}
		</div>
		<div class="separator"></div>
	{/if}


	<!-- Cover Note To Editor-->
	<h3>{translate key="submission.submit.coverNote"}</h3>

	{fbvFormArea id="commentsToEditorContainer"}
		{fbvFormSection for="commentsToEditor"}
		{fbvElement type="textarea" name="commentsToEditor" id="commentsToEditor" value=$commentsToEditor size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
		{/fbvFormSection}
	{/fbvFormArea}


	<!-- Privacy Statement -->
	<h3>{translate key="submission.submit.privacyStatement"}</h3>

	{fbvFormArea id="privacyStatement"}
		{fbvFormSection for="privacyStatement"}
		{fbvElement type="textarea" name="privacyStatement" id="privacyStatement" disabled=true value=$currentPress->getLocalizedSetting('privacyStatement') size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>


	<!-- Continue -->
	<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="{if $monographId}confirmAction('{url page="dashboard" op="status"}', '{translate|escape:"jsparam" key="submission.submit.cancelSubmission"}'){else}document.location.href='{url page="dashboard" op="status" escape=false}'{/if}" /></p>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>
{include file="common/footer.tpl"}

