<!-- templates/controllers/grid/files/reviewFiles/manageReviewFiles.tpl -->

{**
 * manageReviewFiles.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Allows editor to add more file to the review (that weren't added when the submission was sent to review)
 *}

<script type="text/javascript">{literal}
	$(function() {
		getAutocompleteSource("{/literal}{url op="getCopyeditUserAutocomplete" monographId=$monographId}{literal}", "");
	});
{/literal}</script>

{modal_title id="#addUserContainer" key='editor.monograph.copyediting.addUser' iconClass="fileManagement" canClose=1}

<div id="addUserContainer">
	<form name="manageReviewFilesForm" id="manageReviewFilesForm" action="{url component="grid.files.reviewFiles.EditorReviewFilesGridHandler" op="updateReviewFiles" monographId=$monographId|escape reviewType=$reviewType|escape round=$round|escape}" method="post">
		<input type="hidden" name="monographId" value="{$monographId|escape}" />

		<!-- User autocomplete -->
		<div id="userAutocomplete">
			{fbvFormSection}
				{fbvElement type="text" id="sourceTitle-" name="copyeditUserAutocomplete" label="user.role.reviewer" class="required" value=$userNameString|escape }
				<input type="hidden" id="sourceId-" name="reviewerId" />
			{/fbvFormSection}
		</div>

		<!-- Available copyediting files listbuilder -->
		{url|assign:copyeditingFilesListbuilderUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.files.CopyeditingFilesListbuilderHandler" op="fetch" monographId=$monographId}
		{load_url_in_div id="copyeditingFilesListbuilder" url=$copyeditingFilesListbuilderUrl}

		<!-- Message to user -->
		{fbvFormSection}
			{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.monograph.copyediting.personalMessageTouser" value=$personalMessage measure=$fbvStyles.measure.1OF1 size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	</form>
</div>

{init_button_bar id="#addUserContainer"}

<!-- / templates/controllers/grid/files/reviewFiles/manageReviewFiles.tpl -->

