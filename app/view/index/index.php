<div>
	<?php 
	while ($obj = $rh->fetch()):
	?>
	<p><?php echo $obj->sn;?></p>
	<?php
	endwhile;
	?>
</div>