{**
 * regretMessage.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a field for reviewers to enter regret messages
 *
 *}
<script type="text/javascript">
	<!--
	{literal}
	$(function() {
		$('.button').button();
		$('#declineReview').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
		$("#declineReviewSubmit").click(function() {
			$('#declineReview').submit();
			//$('#declineReview').parent().dialog('close');
			return false;
		});
	});
	{/literal}
	// -->
</script>

<form class="pkp_form" id="declineReview" method="post" action="{url op="saveDeclineReview"}">
<h3>{translate key="reviewer.monograph.declineReview"}</h3>

<p>{translate key="reviewer.monograph.declineReviewMessage"}</p>

<textarea name="declineReviewMessage" id="declineReviewMessage" rows="8" cols="40" class="textArea" style="margin-left:10px;"></textarea>
<br />
<br />
<input type="submit" id="declineReviewSubmit" value="{translate key='form.submit'}" class="button" style="margin-left:10px;" />

</form>
</div>

