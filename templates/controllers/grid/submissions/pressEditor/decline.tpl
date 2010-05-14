{**
 * decline.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to decline the submission and send a message to the author
 *}
 
<form name="declineForm-{$monographId}" id="declineForm-{$monographId}" action="{url component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="saveDecline" monographId=$monographId}" method="post">
	<h4>{translate key="editor.monograph.decline"}</h4>

	<p>{translate key="common.personalMessage"}:</p>
	{fbvElement type="textarea" id="personalMessage" size=$fbvStyles.size.MEDIUM}<br/>
</form>