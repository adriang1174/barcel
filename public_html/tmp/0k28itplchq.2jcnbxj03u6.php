<div class="menu">
	<ul>
	<?php foreach (($menu?:array()) as $link): ?>
		<?php if ($URI==$BASE.'/'.$link['slug']): ?>
		
		<li class="active"><?php echo $link['title']; ?></li>
		
		<?php else: ?>
		<li><a href="<?php echo $BASE; ?>/<?php echo $link['slug']; ?>"><?php echo $link['title']; ?></a></li>
		
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>
</div>