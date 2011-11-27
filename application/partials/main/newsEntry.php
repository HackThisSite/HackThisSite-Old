<tr>
  <td class="normal-td" style="font-size: 16px;">
    <b>
      <span style="font-size: 12px">
        <img src="<?php echo Config::get("static:cdn"); ?>/images/tick.gif" alt="#">
        <?php echo Date::dayFormat("j M, Y", $date); ?>:&nbsp;&nbsp;
      </span>
      <?php echo $title; ?>
    </b>
    <span style="display:none; font-size: 9px;"><br /></span>
  </td>
</tr>
<tr>
  <td class="normal-td" style="font-size: 10px;">
    <br /><div class="news"><div align="left"><?php echo $body; ?></div></div>
    <br />
    <br />
    <span style="font-size: 10px;">
      <a href="/news/view/<?php echo Id::create(array('date' => $date, 'title' => $title), 'news'); ?>">read more...</a>
      <br />
      <br />
    </span>
  </td>
</tr>
