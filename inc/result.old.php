<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Lynda Subtitle Generator v<?php echo $version; ?></title>
	<style>
		.err { color: #F30; }
		.success { color: #0B0; }
	</style>
</head>
<body>
	<section>
		<h1>Lynda Subtitle Generator v<?php echo $version; ?></h1>
		<?php if( $err == TRUE ): ?>
			<p class="err"><strong>Error:</strong> <?php echo $msg; ?></p>
		<?php ; else: ?>
			<p class="success"><?php echo $msg; ?></p>
		<?php endif; ?>
	</section>
	
	<footer>Copyright &copy; 2013 Hashem Qolami &lt;hashem@qolami.com&gt;.
		<a href="https://github.com/qolami/Lynda-Subtitle-Generator">Source Code</a>
	</footer>
</body>
</html>