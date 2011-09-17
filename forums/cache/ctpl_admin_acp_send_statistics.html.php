<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<a name="maincontent"></a>

<h1><?php echo ((isset($this->_rootref['L_SEND_STATISTICS'])) ? $this->_rootref['L_SEND_STATISTICS'] : ((isset($user->lang['SEND_STATISTICS'])) ? $user->lang['SEND_STATISTICS'] : '{ SEND_STATISTICS }')); ?></h1>

<p><?php echo ((isset($this->_rootref['L_EXPLAIN_SEND_STATISTICS'])) ? $this->_rootref['L_EXPLAIN_SEND_STATISTICS'] : ((isset($user->lang['EXPLAIN_SEND_STATISTICS'])) ? $user->lang['EXPLAIN_SEND_STATISTICS'] : '{ EXPLAIN_SEND_STATISTICS }')); ?></p>

<script type="text/javascript">
//<![CDATA[
var iframect = 0;

function iframe_updated()
{
	if (iframect++ == 0)
	{
		return;
	}

	dE('questionnaire-form', -1);
	dE('questionnaire-thanks', 1);
}
//]]>
</script>

<iframe onload="iframe_updated();" name="questionaire_result" style="display: none;"></iframe>

<form action="<?php echo (isset($this->_rootref['U_COLLECT_STATS'])) ? $this->_rootref['U_COLLECT_STATS'] : ''; ?>" method="post" target="questionaire_result" id="questionnaire-form">

	<p><a href="<?php echo (isset($this->_rootref['U_ACP_MAIN'])) ? $this->_rootref['U_ACP_MAIN'] : ''; ?>"><?php echo ((isset($this->_rootref['L_DONT_SEND_STATISTICS'])) ? $this->_rootref['L_DONT_SEND_STATISTICS'] : ((isset($user->lang['DONT_SEND_STATISTICS'])) ? $user->lang['DONT_SEND_STATISTICS'] : '{ DONT_SEND_STATISTICS }')); ?></a></p>

	<p><?php echo ((isset($this->_rootref['L_EXPLAIN_SHOW_STATISTICS'])) ? $this->_rootref['L_EXPLAIN_SHOW_STATISTICS'] : ((isset($user->lang['EXPLAIN_SHOW_STATISTICS'])) ? $user->lang['EXPLAIN_SHOW_STATISTICS'] : '{ EXPLAIN_SHOW_STATISTICS }')); ?></p>

	<p id="show-button"><input type="button" class="button2" onclick="dE('configlist', 1); dE('show-button', -1);" value="<?php echo ((isset($this->_rootref['L_SHOW_STATISTICS'])) ? $this->_rootref['L_SHOW_STATISTICS'] : ((isset($user->lang['SHOW_STATISTICS'])) ? $user->lang['SHOW_STATISTICS'] : '{ SHOW_STATISTICS }')); ?>" /></p>

	<div id="configlist">
		<input type="button" class="button2" onclick="dE('show-button', 1); dE('configlist', -1);" value="<?php echo ((isset($this->_rootref['L_HIDE_STATISTICS'])) ? $this->_rootref['L_HIDE_STATISTICS'] : ((isset($user->lang['HIDE_STATISTICS'])) ? $user->lang['HIDE_STATISTICS'] : '{ HIDE_STATISTICS }')); ?>" />
		<p class="submit-buttons">
			<input class="button1" type="submit" id="submit" name="submit" value="<?php echo ((isset($this->_rootref['L_SEND_STATISTICS'])) ? $this->_rootref['L_SEND_STATISTICS'] : ((isset($user->lang['SEND_STATISTICS'])) ? $user->lang['SEND_STATISTICS'] : '{ SEND_STATISTICS }')); ?>" />
		</p>

		<?php $_providers_count = (isset($this->_tpldata['providers'])) ? sizeof($this->_tpldata['providers']) : 0;if ($_providers_count) {for ($_providers_i = 0; $_providers_i < $_providers_count; ++$_providers_i){$_providers_val = &$this->_tpldata['providers'][$_providers_i]; ?>

		<fieldset>
			<legend><?php echo $_providers_val['NAME']; ?></legend>
			<?php $_values_count = (isset($_providers_val['values'])) ? sizeof($_providers_val['values']) : 0;if ($_values_count) {for ($_values_i = 0; $_values_i < $_values_count; ++$_values_i){$_values_val = &$_providers_val['values'][$_values_i]; ?>

			<dl>
				<dt><?php echo $_values_val['KEY']; ?></dt>
				<dd><?php echo $_values_val['VALUE']; ?></dd>
			</dl>
			<?php }} ?>

		</fieldset>
		<?php }} ?>

	</div>
	<p class="submit-buttons">
		<input type="hidden" name="systemdata" value="<?php echo (isset($this->_rootref['RAW_DATA'])) ? $this->_rootref['RAW_DATA'] : ''; ?>" />
		<input class="button1" type="submit" id="submit" name="submit" value="<?php echo ((isset($this->_rootref['L_SEND_STATISTICS'])) ? $this->_rootref['L_SEND_STATISTICS'] : ((isset($user->lang['SEND_STATISTICS'])) ? $user->lang['SEND_STATISTICS'] : '{ SEND_STATISTICS }')); ?>" />
	</p>
</form>

<div id="questionnaire-thanks" class="successbox">
	<p><strong><?php echo ((isset($this->_rootref['L_THANKS_SEND_STATISTICS'])) ? $this->_rootref['L_THANKS_SEND_STATISTICS'] : ((isset($user->lang['THANKS_SEND_STATISTICS'])) ? $user->lang['THANKS_SEND_STATISTICS'] : '{ THANKS_SEND_STATISTICS }')); ?></strong><br /><br /><a href="<?php echo (isset($this->_rootref['U_ACP_MAIN'])) ? $this->_rootref['U_ACP_MAIN'] : ''; ?>">&laquo; <?php echo ((isset($this->_rootref['L_GO_ACP_MAIN'])) ? $this->_rootref['L_GO_ACP_MAIN'] : ((isset($user->lang['GO_ACP_MAIN'])) ? $user->lang['GO_ACP_MAIN'] : '{ GO_ACP_MAIN }')); ?></a></p>
</div>

<script type="text/javascript">
//<![CDATA[
	dE('configlist', -1);
	dE('questionnaire-thanks', -1);
//]]>
</script>

<?php $this->_tpl_include('overall_footer.html'); ?>