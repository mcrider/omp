{**
 * templates/controllers/grid/user/reviewer/form/advancedSearchReviewerFilterForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the widgets that generate the filter sent to the reviewerSelect grid.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Handle filter form submission
		$('#reviewerFilterForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler');
	{rdelim});
</script>

{** This form contains the inputs that will be used to filter the list of reviewers in the grid below **}
<form id="reviewerFilterForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="fetchGrid" monographId=$monographId}" method="post" class="pkp_controllers_reviewerSelector">
	<input type="hidden" id="monographId" name="monographId" value="{$monographId}" />
	{fbvFormArea id="reviewerSearchForm"}
		{fbvFormSection float=$fbvStyles.float.LEFT}
			{fbvElement type="rangeSlider" id="done" label="manager.reviewerSearch.doneAmount" min=$reviewerValues.doneMin max=$reviewerValues.doneMax}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.RIGHT}
			{fbvElement type="rangeSlider" id="avg" label="manager.reviewerSearch.avgAmount" min=$reviewerValues.avgMin max=$reviewerValues.avgMax}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.LEFT}
			{fbvElement type="rangeSlider" id="last" label="manager.reviewerSearch.lastAmount" min=$reviewerValues.lastMin max=$reviewerValues.lastMax}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.RIGHT}
			{fbvElement type="rangeSlider" id="active" label="manager.reviewerSearch.activeAmount" min=$reviewerValues.activeMin max=$reviewerValues.activeMax}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvKeywordInput id="interestSearch" available=$existingInterests label="manager.reviewerSearch.interests"}
		{/fbvFormSection}
		{fbvFormSection}
			<input type="submit" class="button" id="submitFilter" value="{translate key="common.refresh"}" />
		{/fbvFormSection}
	{/fbvFormArea}
</form>
