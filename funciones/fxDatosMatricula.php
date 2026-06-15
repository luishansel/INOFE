<?php
require_once ("fxGeneral.php");

if (isset($_POST["maximoCurso"])) //Pantalla de Matrícula. Evita sobrepasar el límite de matriculados.
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msCurso = $_POST["maximoCurso"];
	$msResultado = "";

	$msConsulta = "select MAXIMO_020 from KDSA020A where CURSO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$Fila = $mDatos->fetch();
	$mnMaximo = $Fila["MAXIMO_020"];

	$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A where CURSO_REL = ? and ESTADO_030 not in (4, 5)";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$Fila = $mDatos->fetch();
	$mnConteo = $Fila["CONTEO"];

	$msResultado = $mnMaximo - $mnConteo;
	echo($msResultado);
}
?>