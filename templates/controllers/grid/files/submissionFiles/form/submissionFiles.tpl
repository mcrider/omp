{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *
 * $Id$
 *}

{assign var='randomId' value=1|rand:99999}

{if $isRevision}
	{assign var=titleKey value="upload revision"}
{else}
	{assign var=titleKey value="submission.submit.uploadSubmissionFile"}
{/if}
{modal_title id="#fileUploadTabs-$fileId" key=$titleKey iconClass="fileManagement" canClose=1}

{init_tabs id="div#fileUploadTabs-$fileId"}
<script type="text/javascript">
	{literal}
	$(function() {
		$(".ui-dialog-titlebar-close").remove();  // Hide 'X' close button in dialog
		$('#fileUploadTabs-{/literal}{$fileId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
	});
	{/literal}
</script>
<div id="fileUploadTabs-{$fileId}" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url op="displayFileForm" monographId=$monographId fileId=$fileId isRevision=$isRevision}">1. {translate key="submission.submit.upload"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url op="editMetadata" monographId=$monographId fileId=$fileId}">2. {translate key="submission.submit.metadata"}</a></li>
		{if !$fileId}<li class="ui-state-default ui-corner-top"><a href="{url op="finishFileSubmissions" monographId=$monographId fileId=$fileId}">3. {translate key="submission.submit.finishingUp"}</a></li>{/if}
	</ul>
</div>