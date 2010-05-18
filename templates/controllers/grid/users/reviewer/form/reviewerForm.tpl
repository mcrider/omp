{**
 * reviewerForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review assignment form
 *
 *}
{assign var='randomId' value=1|rand:99999}
<script type='text/javascript'>
	{literal}
	getAutocompleteSource("{/literal}{url op="getReviewerAutocomplete" monographId="$monographId"}{literal}", "{/literal}{$randomId}{literal}");
	{/literal}
</script>

<form name="addReviewerForm" id="addReviewer-{$randomId}" method="post" action="{url op="addReviewer" monographId=$monographId}">
	<!--  Reviewer autosuggest selector -->
	<input type="text" class="textField" id="sourceTitle-{$randomId}" name="reviewerSelectAutocomplete" value="" /> <br />
	<input type="hidden" id="sourceId-{$randomId}" name="reviewerSelectValue">

	<!--  Message to reviewer textarea -->
	
	
	<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->


	<!--  File selection grid -->

d
</form>