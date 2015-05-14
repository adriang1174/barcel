<!DOCTYPE html>
<html lang="en">
	<head>
		<base href="<?php echo $SCHEME.'://'.$HOST.$BASE.'/'.$UI; ?>" />
		<meta charset="<?php echo $ENCODING; ?>" />
		<title><?php echo $site; ?></title>
		<link rel="stylesheet" href="css/reset.css" type="text/css" />
		<link rel="stylesheet" href="css/typography.css" type="text/css" />
		<link rel="stylesheet" href="css/theme.css" type="text/css" />
	</head>
	<body>
		<div class="header">
			<h1><?php echo $site; ?></h1>
		</div>
		<?php echo $this->render($inc,$this->mime,get_defined_vars()); ?>
		<div class="footer">
			<p><a href="http://fatfree.sf.net/"><img src="logo.png" title="Fat-Free Framework" /></a><br />Licensed under the terms of the GPL v3. Copyright © 2009-2012 F3::Factory</p>
		</div>
	</body>
</html>
