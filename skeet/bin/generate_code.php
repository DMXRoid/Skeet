<?
	namespace Skeet;
	require_once("../load.php");
	$codeGenerator = new Generator\MysqlCodeGenerator();
	$codeGenerator->doGenerate();
?>
