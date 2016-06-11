<div>
	<?php 
	while ($obj = $rh->fetch()):
	?>
	<p><?php echo $obj->question_id;?></p>
	<?php
	endwhile;
	?>
</div>