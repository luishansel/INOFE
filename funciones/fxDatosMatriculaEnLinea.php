<?php
require_once ("fxGeneral.php");
require_once ("fxMatriculaEnLinea.php");

if (isset($_POST["Enlace"])) {
	$msEnlace = $_POST["Enlace"];

	$m_cnx_MySQL = fxAbrirConexion();
	$msConsulta = "select ESTADO_007 from KDSA007A where ENLACE_REL = '" .trim($msEnlace). "'";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msEnlace]);
	$Registros = $mDatos->rowCount();

	if ($Registros == 0)
	{
		$mnEstado = -1;
	}
	else
	{
		$fila = $mDatos->fetch();
		$mnEstado = $fila["ESTADO_007"];
	}
	echo($mnEstado);
}

if (isset($_POST["Cedula"])) {
	$msCedula = $_POST["Cedula"];
	$m_cnx_MySQL = fxAbrirConexion();
	$msConsulta = "select ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010, SEXO_010, FECHANAC_010, DOMICILIO_010, DIRECCION_010, TELEFONO_010, ";
	$msConsulta .= "CELULAR_010, CORREO_010, EMERGENCIA_010, PARENTESCO_010, NIVELACADEMICO_010, POSTGRADO_010, MAESTRIA_010, LUGARTRABAJO_010, PUESTO_010, ";
	$msConsulta .= "TELEFONOEMPRESA_010 from KDSA010A where CEDULA_010 = ? limit 1";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCedula]);
	$Fila = $mDatos->fetch();
	$msResultado = trim($Fila["ESTUDIANTE_REL"]) . '~' . trim($Fila["NOMBRES_010"]) . '~' . trim($Fila["APELLIDOS_010"]) . '~' . trim($Fila["SEXO_010"]) . '~';
	$msResultado .= trim($Fila["FECHANAC_010"]) . '~' . trim($Fila["DOMICILIO_010"]) . '~' . trim($Fila["DIRECCION_010"]) . '~' . trim($Fila["CELULAR_010"]) . '~';
	$msResultado .= trim($Fila["CORREO_010"]) . '~' . trim($Fila["NIVELACADEMICO_010"]) . '~' . trim($Fila["POSTGRADO_010"]) . '~' . trim($Fila["MAESTRIA_010"]) . '~';
	$msResultado .= trim($Fila["LUGARTRABAJO_010"]) . '~' . trim($Fila["PUESTO_010"]) . '~' . trim($Fila["TELEFONOEMPRESA_010"]) . '~' . trim($Fila["EMERGENCIA_010"]) . '~';
	$msResultado .= trim($Fila["PARENTESCO_010"]) . '~' . trim($Fila["TELEFONO_010"]) . '~';
	echo($msResultado);
}

if (isset($_POST["Curso"])) {
	$msCurso = $_POST["Curso"];
	$m_cnx_MySQL = fxAbrirConexion();
	$msConsulta = 'select CONCAT("Convocatoria ", CONVOCATORIA_020, " / Grupo ", GRUPO_020) as CONVOCATORIA, ';
	$msConsulta .= 'CONCAT((case TURNO_020 when 0 then "Nocturno" when 1 then "Sabatino" when 2 then "Dominical" when 3 then "Matutino" when 4 then "Vespertino" end), ';
	$msConsulta .= '". De ", TIME_FORMAT(HORAINI_020, "%h:%i %p"), " hasta ", TIME_FORMAT(HORAFIN_020, "%h:%i %p")) as HORARIO, ';
	$msConsulta .= 'fxDevuelveDias(CURSO_REL) as DIAS, CONCAT("Inicia el ", day(FECHAINI_020), " de ", (case month(FECHAINI_020) when 1 then "Enero" when 2 then ';
	$msConsulta .= '"Febrero" when 3 then "Marzo" when 4 then "Abril" when 5 then "Mayo" when 6 then "Junio" when 7 then "Julio" when 8 then "Agosto" when 9 then ';
	$msConsulta .= '"Septiembre" when 10 then "Octubre" when 11 then "Noviembre" when 12 then "Diciembre" end), " de ", year(FECHAINI_020), " - Finaliza el ", ';
	$msConsulta .= 'day(FECHAFIN_020), " de ", (case month(FECHAFIN_020) when 1 then "Enero" when 2 then "Febrero" when 3 then "Marzo" when 4 then "Abril" when 5 ';
	$msConsulta .= 'then "Mayo" when 6 then "Junio" when 7 then "Julio" when 8 then "Agosto" when 9 then "Septiembre" when 10 then "Octubre" when 11 then "Noviembre" ';
	$msConsulta .= 'when 12 then "Diciembre" end), " de ", year(FECHAFIN_020)) as PERIODO, TIPOASISTENCIA_020 from KDSA020A where CURSO_REL = ?';
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$Fila = $mDatos->fetch();
	$msResultado = trim($Fila["CONVOCATORIA"]) . '~' . trim($Fila["HORARIO"]) . '~' . trim($Fila["DIAS"]) . '~' . trim($Fila["PERIODO"]) . '~' . trim($Fila["TIPOASISTENCIA_020"]) . '~';
	echo($msResultado);
}
?>