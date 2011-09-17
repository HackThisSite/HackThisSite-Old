<?php if (!defined('IN_PHPBB')) exit; ?></div>
				</div>
			<span class="corners-bottom"><span></span></span>
			<div class="clear"></div>
		</div>
		</div>
	</div>

	<div id="page-footer">
		<?php if ($this->_rootref['S_COPYRIGHT_HTML']) {  ?>

			Powered by <a href="http://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Group
			<?php if ($this->_rootref['TRANSLATION_INFO']) {  ?><br /><?php echo (isset($this->_rootref['TRANSLATION_INFO'])) ? $this->_rootref['TRANSLATION_INFO'] : ''; } } if ($this->_rootref['DEBUG_OUTPUT']) {  if ($this->_rootref['S_COPYRIGHT_HTML']) {  ?><br /><?php } ?>

			<?php echo (isset($this->_rootref['DEBUG_OUTPUT'])) ? $this->_rootref['DEBUG_OUTPUT'] : ''; ?>

		<?php } ?>

	</div>
</div>

</body>
</html>