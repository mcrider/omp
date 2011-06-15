{**
 * installComplete.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display confirmation of successful installation.
 * If necessary, will also display new config file contents if config file could not be written.
 *}
{strip}
{assign var="pageTitle" value="installer.ompInstallation"}
{include file="common/header.tpl"}
{/strip}

{url|assign:"loginUrl" page="login"}
{translate key="installer.installationComplete" loginUrl=$loginUrl}

{if $writeConfigFailed}
{translate key="installer.overwriteConfigFileInstructions"}

<form class="pkp_form" action="#">
<p>
{translate key="installer.contentsOfConfigFile"}:<br />
<textarea name="config" cols="80" rows="20" class="textArea" style="font-family: Courier,'Courier New',fixed-width">{$configFileContents|escape}</textarea>
</p>
</form>
{/if}

{if $manualInstall}
{translate key="installer.manualSQLInstructions"}

<form class="pkp_form" action="#">
<p>
{translate key="installer.installerSQLStatements"}:<br />
<textarea name="sql" cols="80" rows="20" class="textArea" style="font-family: Courier,'Courier New',fixed-width">{foreach from=$installSql item=sqlStmt}{$sqlStmt|escape};


{/foreach}</textarea>
</p>
</form>
{/if}

{include file="common/footer.tpl"}

