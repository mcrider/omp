{**
 * production.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="workflow/header.tpl"}

<div class="ui-widget ui-widget-content ui-corner-all">

{url|assign:galleyGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.galley.GalleyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() canUpload=true canAddAuthor=true escape=false}
{load_url_in_div id="galleyGrid" url=$galleyGridUrl}

<br />

{* url|assign:copyeditingGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.CopyeditingFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() canUpload=true canAddAuthor=true escape=false}
{load_url_in_div id="copyeditingGrid" url=$copyeditingGridUrl *}

<br />

{* url|assign:fairCopyGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.fairCopy.FairCopyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() canUpload=true escape=false}
{load_url_in_div id="fairCopyGrid" url=$fairCopyGridUrl *}

<br />

{* include file="linkAction/legacyLinkAction.tpl" action=$promoteAction id="promoteAction" *}

</div>
{include file="common/footer.tpl"}

