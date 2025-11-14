<?php
	$mask = "*.json";
	array_map("unlink", glob($mask));
	$mask = "*.png";
	array_map("unlink", glob($mask));
	$mask = "*.xls";
	array_map("unlink", glob($mask));
	$mask = "*.xlsx";
	array_map("unlink", glob($mask));
	$mask = "*.csv";
	array_map("unlink", glob($mask));
?>