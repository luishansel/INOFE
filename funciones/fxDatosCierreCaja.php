<?php
require_once ("fxGeneral.php");

if (isset($_POST["fechaCierre"])) //Devuelve 0 si la fecha no ha sido cerrada, 1 si ya se cerró.
{
	$m_cnx_MySQL = fxAbrirConexion();
	$mdFecha = $_POST["fechaCierre"];

	$msConsulta = "select FECHA_044 from KDSA044A where FECHA_044 = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFecha]);
	$mnRegistros = $mDatos->rowCount();
	echo ($mnRegistros);
}
?>