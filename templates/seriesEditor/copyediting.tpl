<!-- templates/seriesEditor/showReviewers.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Series editor index.
 *
 * $Id$
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="submission/header.tpl"}

<div class="ui-widget ui-widget-content ui-corner-all">

{url|assign:finalDraftGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.finalDraftFiles.FinalDraftFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType canAdd=1 escape=false}
{load_url_in_div id="assign:finalDraftGrid" url=$finalDraftGridUrl}

<br />

{**url|assign:copyeditingGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.copyediting.CopyeditingGridHandler" op="fetchGrid" monographId=$monographId escape=false}
{load_url_in_div id="copyeditingGrid" url=$copyeditingGridUrl**}

<br />

{**url|assign:fairCopyGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.fairCopyFiles.FairCopyFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$selectedRound escape=false}
{load_url_in_div id="fairCopyGrid" url=$fairCopyGridUrl**}

<br />

{include file="linkAction/linkAction.tpl" action=$promoteAction id="promoteAction"}

</div>
{include file="common/footer.tpl"}
<!-- / templates/seriesEditor/showReviewers.tpl -->

