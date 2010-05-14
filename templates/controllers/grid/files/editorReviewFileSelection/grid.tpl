{**
 * grid.tpl
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * grid HTML markup and construction
 *
 * FIXME: Re-introduce "partial width" when needed without
 *  configuring an actual pixel width in the controller.
 *}
<script type="text/javascript">
	{literal}
        $(function(){
		$('.editorReviewFileSelect').live("click", (function() {
			$(this).parent().parent().toggleClass('selected');
			if($(this).is(':checked')) {
				$(this).attr('checked', true);
			} else {
				$(this).attr('checked', false);
			}
		}));
	});
	{/literal}
</script>

{include file="controllers/grid/grid.tpl"}