{**
 * templates/controllers/grid/user/reviewer/form/reviewerFormFooter.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The non-searching part of the add reviewer form
 *
 *}
 <script type="text/javascript">
$("#responseDueDate").datepicker({ldelim} dateFormat: 'yy-mm-dd' {rdelim});
$("#reviewDueDate").datepicker({ldelim} dateFormat: 'yy-mm-dd' {rdelim});

$("#interests").tagit({ldelim}
	// This is the list of interests in the system used to populate the autocomplete
	availableTags: [{foreach name=existingInterests from=$existingInterests item=interest}"{$interest|escape|escape:'javascript'}"{if !$smarty.foreach.existingInterests.last}, {/if}{/foreach}],
	currentTags: []
{rdelim});
</script>

<!--  Message to reviewer textarea -->
{fbvFormSection}
	{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.3OF4 size=$fbvStyles.size.MEDIUM}
{/fbvFormSection}

<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->
{fbvFormSection}
	{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.responseDueDate" value=$responseDueDate }
	{fbvElement type="text" id="reviewDueDate" name="reviewDueDate" label="editor.review.reviewDueDate" value=$reviewDueDate }
{/fbvFormSection}

<!--  Ensuring a blind review for this reviewer -->
{if $reviewMethod == 1}
	{assign var='blindReview' value=true}
{elseif $reviewMethod == 2}
	{assign var='doubleBlindReview' value=true}
{elseif $reviewMethod == 3}
	{assign var='openReview' value=true}
{/if}
{fbvFormSection title="editor.submissionReview.reviewType"}
	{fbvElement type="radio" name="reviewMethod" id="blindReview" label="editor.submissionReview.blind" checked=$blindReview}
	{fbvElement type="radio" name="reviewMethod" id="doubleBlindReview" label="editor.submissionReview.doubleBlind" checked=$doubleBlindReview}
	{fbvElement type="radio" name="reviewMethod" id="openReview" label="editor.submissionReview.open" checked=$openReview}
{/fbvFormSection}

<!-- All of the hidden inputs -->
<input type="hidden" name="selectionType" value={$selectionType|escape} />
<input type="hidden" name="monographId" value={$monographId|escape} />
<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId|escape}" />
<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
<input type="hidden" name="round" value="{$round|escape}" />