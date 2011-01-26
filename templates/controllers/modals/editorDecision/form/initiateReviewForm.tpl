{**
 * initiateReviewForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to initiate the first review round.
 *
 *}
{assign var='uniqueId' value=""|uniqid}
{modal_title id="#initiateReview" key='editor.monograph.initiateReview' iconClass="fileManagement" canClose=1}

<p>{translate key="editor.monograph.initiateReviewDescription"}</p>
<form id="initiateReview" method="post" action="{url op="saveInitiateReview"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />

	<!-- Available submission files -->
	{url|assign:filesForReviewUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.SelectableSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$round isSelectable=1 escape=false}
	{load_url_in_div id="filesForReviewGrid" url=$filesForReviewUrl}
</form>

{init_button_bar id="#initiateReview" submitText="editor.monograph.createNewRound"}

