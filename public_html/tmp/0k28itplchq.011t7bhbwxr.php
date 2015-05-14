<?php echo $this->render('user.htm',$this->mime,get_defined_vars()); ?>
<div class="main">
	<h3><?php echo $page['title']; ?></h3>
	<div class="contents">
		<p><?php echo Base::instance()->raw(nl2br($page['contents'])); ?></p>
		<?php if ($page['updated']): ?>
		<p><small>Last updated <?php echo date($time_format,$page['updated']); ?></small></p>
		<?php endif; ?>
	</div>
	<div class="comments">
		<?php if ($comments): ?>
		<h5>Comments</h5>
		<?php endif; ?>
		<?php foreach (($comments?:array()) as $comment): ?>
		<div>
			<p class="identicon"><img src="<?php echo $comment['identicon']; ?>" title="<?php echo $comment['name']; ?>" /></p>
			<p><small>Posted by <?php echo $comment['name']; ?> on <?php echo date($time_format,$comment['posted']); ?></small><br />
			<?php echo nl2br($comment['contents']); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php if ($page['commentable']): ?>
	<form method="post" action="<?php echo $BASE; ?>/<?php echo $page['slug']; ?>" class="comment">
		<h5>Leave a Comment</h5>
		<?php if (isset($message)): ?>
		<p class="message"><?php echo $message; ?></p>
		<?php endif; ?>
		<p>
			<label for="name"><small>Name</small></label><br />
			<input id="name" name="name" type="text" <?php echo isset($POST['name'])?('value="'.$POST['name'].'"'):''; ?> />
		</p>
		<p>
			<label for="email"><small>E-mail</small></label><br />
			<input id="email" name="email" type="text" <?php echo isset($POST['email'])?('value="'.$POST['email'].'"'):''; ?> />
		</p>
		<p>
			<label for="contents"><small>Comment</small></label><br />
			<textarea id="contents" name="contents"><?php echo isset($POST['name'])?$POST['name']:''; ?></textarea>
		</p>
		<p>
			<input id="slug" name="slug" value="<?php echo $page['slug']; ?>" type="hidden" />
			<button id="save">Save</button>
		</p>
	</form>
	<?php endif; ?>
</div>