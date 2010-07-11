<?
	$exception = $this->getException();
?>
<div id="exception">
	<h2><?= get_class($exception) ?> was thrown!</h2>
	<a href="#" onclick="$('exception_info').appear({duration: .3}); return false;">click here</a> to view the entire exception.
	<div id="exception_info" style="display: none;">
		Exception message: <?= nl2br($exception->getMessage()) ?>
		
		<br><br>
		
		<?
			$backtraceComponent = ComponentFactory::getComponent("backtrace",PageFactory::getCurrentPage());
			$backtraceComponent->setBacktrace($exception->getTrace());
			$backtraceComponent->render();
		?>
	</div>
	
	
</div>