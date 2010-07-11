<?
	$backtrace = $this->getBacktrace();
	Debug::jam($backtrace);
	
	foreach($backtrace as $key => $bt) {
		if($key && $key % 2 == 0) {
			$traceClass = "backtrace_even";
		}
		else {
			$traceClass = "backtrace_odd";
		}
?>
<div class="<?= $traceClass ?>">
	<h3><?= $key ?></h3>
	<span class="bold">File: </span> <?= $bt["file"] ?>
	<br>
	<span class="bold">Line: </span> <?= $bt["line"] ?>
	<br>
	<span class="bold">Call: </span> 
	<?
		$call = "";
		if($bt["class"]) {
			$call = $bt["class"] . "::";
		}
		$call .= $bt["function"] . "('" . implode("','",$bt["args"]) . "')";
		echo $call;
	?>
</div>
<?
	}
?>