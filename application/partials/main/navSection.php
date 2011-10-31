<h4 class="header"><?php echo $title ;?></h4>
<ul class="navigation">
  <?php foreach($links as $name => $link): ?>
    <li>
      <a class="nav" href="<?php echo $link; ?>"><?php echo $name; ?></a>
    </li>
  <?php endforeach; ?>
</ul>