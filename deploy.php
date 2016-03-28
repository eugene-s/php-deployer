<?php
	$commands = array(
		'echo $PWD',
		'whoami',
		'sh bin/deploy.sh'
	);
	// Run the commands for output
	$output = '';
	foreach($commands AS $command){
		// Run it
		$tmp = shell_exec($command);
		// Output
		$output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
		$output .= htmlentities(trim($tmp)) . "\n";
	}
?>
<?php ob_start() ?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>GIT DEPLOYMENT SCRIPT</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
 .  ____  .    ____________________________
 |/      \|   |                            |
[| <span style="color: #FF0000;">&hearts;    &hearts;</span> |]  | Git Deployment Script v0.5a |
 |___==___|  /        &copy; eugene-s 2015 |
              |____________________________|

<?php echo $output; ?>
</pre>
</body>
</html>
<?php 
	$content = ob_get_clean();

	echo $content;

	$filename = 'deploy_' . date('Ymdhis', time()) . '.html';
	file_put_contents("var/log/deploy/{$filename}", $content);
?>
