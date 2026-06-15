<?php
require_once ("fxGeneral.php");

if (isset($_POST["cedulaEstudiante"]) and isset($_POST["codEstudiante"]))
{
	$m_cnx_MySQL = fxAbrirConexion();
	$Cedula = $_POST["cedulaEstudiante"];
	$Codigo = $_POST["codEstudiante"];
	$msConsulta = "Select concat(ESTUDIANTE_REL, ' (', NOMBRES_010, ' ', APELLIDOS_010, ')') as ESTUDIANTE from KDSA010A where CEDULA_010 = ? and ESTUDIANTE_REL <> ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$Cedula, $Codigo]);
	$mnRegistros = $mDatos->rowCount();
	
	if ($mnRegistros > 0)
	{
		$Fila = $mDatos->fetch();
		$msResultado = $Fila["ESTUDIANTE"];
	}
	else
	{
		$msResultado = "";
	}
	
	echo $msResultado;
}

if (isset($_POST["cedulaProspecto"]) and isset($_POST["codProspecto"]))
{
	$m_cnx_MySQL = fxAbrirConexion();
	$Cedula = $_POST["cedulaProspecto"];
	$Codigo = $_POST["codProspecto"];
	
	if ($Codigo == "")
	{
		$msConsulta = "Select PROSPECTO_REL from KDSA060A where CEDULARUC_060 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$Cedula]);
	}
	else
	{
		$msConsulta = "Select PROSPECTO_REL from KDSA060A where CEDULARUC_060 = ? and PROSPECTO_REL <> ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$Cedula, $Codigo]);
	}

	$mnRegistros = $mDatos->rowCount();
	
	if ($mnRegistros > 0)
	{
		$Fila = $mDatos->fetch();
		$msResultado = $Fila["PROSPECTO_REL"];
	}
	else
	{
		$msResultado = "";
	}
	
	echo $msResultado;
}

if (isset($_POST["nombreProspecto"]) and isset($_POST["codProspecto"]))
{
	$m_cnx_MySQL = fxAbrirConexion();
	$Nombre = $_POST["nombreProspecto"];
	$Codigo = $_POST["codProspecto"];
	
	if ($Codigo == "")
	{
		$msConsulta = "Select PROSPECTO_REL from KDSA060A where NOMBRE_060 like '%?%'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$Nombre]);
	}
	else
	{
		$msConsulta = "Select PROSPECTO_REL from KDSA060A where NOMBRE_060 like '%?%' and PROSPECTO_REL <> ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$Nombre, $Codigo]);
	}

	$mnRegistros = $mDatos->rowCount();
	
	if ($mnRegistros > 0)
	{
		$Fila = $mDatos->fetch();
		$msResultado = $Fila["PROSPECTO_REL"];
	}
	else
	{
		$msResultado = "";
	}
	
	echo $msResultado;
}

if (isset($_POST["txtProspecto"]))
{
	$m_cnx_MySQL = fxAbrirConexion();
	$Codigo = $_POST["txtProspecto"];
	
	$msConsulta = "Select NOMBRE_060 from KDSA060A where PROSPECTO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$Codigo]);
	$mnRegistros = $mDatos->rowCount();
	
	if ($mnRegistros > 0)
	{
		$Fila = $mDatos->fetch();
		$msResultado = $Fila["NOMBRE_060"];
	}
	else
	{
		$msResultado = "";
	}
	
	echo $msResultado;
}

if (isset($_POST["cboPreCurso"])) //Pantalla de Cursos (Detalle de módulos pre-configurados)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$Codigo = $_POST["cboPreCurso"];
	$msResultado = "";
	
	$msConsulta = "Select DETCFGMODULO_REL, DESC_111 from KDSA111A where CFGMODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$Codigo]);
	$mnRegistros = $mDatos->rowCount();
	
	if ($mnRegistros > 0)
	{
		$mDatos->execute([$Codigo]);

		$msResultado .= "<select class='form-control' id='cboPreModulos' name='cboPreModulos' style='width:100%'>";
		while ($Fila = $mDatos->fetch())
		{
			$Valor = rtrim($Fila["DETCFGMODULO_REL"]);
			$Texto = rtrim($Fila["DESC_111"]);
			$msResultado .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
		}
		$msResultado .= "</select>";
	}
	
	echo $msResultado;
}

if (isset($_POST["cursoFechaIni"]) and isset($_POST["cursoFechaFin"])) //Pantalla de Cursos (Detalle de feriados pre-configurados)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$fechaIni = $_POST["cursoFechaIni"];
	$fechaFin = $_POST["cursoFechaFin"];
	
	$msConsulta = "select FECHA_001, DESC_001, DIA_001 from KDSA001A where FECHA_001 between ? and ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$fechaIni, $fechaFin]);
	$mnRegistros = $mDatos->rowCount();
	
	if ($mnRegistros > 0)
	{
		$mDatos->execute([$fechaIni, $fechaFin]);
		$msResultado = "";

		while ($Fila = $mDatos->fetch())
		{
			$fecha = trim($Fila["FECHA_001"]);
			$descripcion =trim($Fila["DESC_001"]);
			$dia = trim($Fila["DIA_001"]);
			$msResultado .= $fecha . "%" . $descripcion . "@" . $dia . "#";
		}
	}
	else
	{
		$msResultado = "0";
	}
	
	echo $msResultado;
}


if (isset($_POST["moduloPlan"])) //Pantalla de Planificación Programática (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$modulo = $_POST["moduloPlan"];

	$msConsulta = "select DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020, FECHAINI_021, FECHAFIN_021 from KDSA021A, KDSA020A where KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$modulo]);
	$Fila = $mDatos->fetch();
	$domingo = $Fila["DOMINGO_020"];
	$lunes = $Fila["LUNES_020"];
	$martes = $Fila["MARTES_020"];
	$miercoles = $Fila["MIERCOLES_020"];
	$jueves = $Fila["JUEVES_020"];
	$viernes = $Fila["VIERNES_020"];
	$sabado = $Fila["SABADO_020"];
	$fecha = trim($Fila["FECHAINI_021"]);
	$fechaFin = trim($Fila["FECHAFIN_021"]);
	$msResultado = "";

	while ($fecha <= $fechaFin)
	{
		$escribirFecha = false;
		$diaSemana = date("l", strtotime($fecha));

		if ($diaSemana == "Sunday" and $domingo == 1)
			$escribirFecha = true;

		if ($diaSemana == "Monday" and $lunes == 1)
			$escribirFecha = true;
		
		if ($diaSemana == "Tuesday" and $martes == 1)
			$escribirFecha = true;

		if ($diaSemana == "Wednesday" and $miercoles == 1)
			$escribirFecha = true;

		if ($diaSemana == "Thursday" and $jueves == 1)
			$escribirFecha = true;

		if ($diaSemana == "Friday" and $viernes == 1)
			$escribirFecha = true;

		if ($diaSemana == "Saturday" and $sabado == 1)
			$escribirFecha = true;

		if ($escribirFecha)
		{
			//Verifica que no sea un día Feriado
			$msConsulta = "select DETFECHA_REL from KDSA021A, KDSA022A where KDSA021A.CURSO_REL = KDSA022A.CURSO_REL and KDSA021A.MODULO_REL = ? and FECHA_022 = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$modulo, $fecha]);
			$mnAuxiliar = $mAuxiliar->rowCount();
			
			if ($mnAuxiliar == 0)
			{
				$fechaDividida = explode("-", $fecha);
				$anno = $fechaDividida[0];
				$mes = $fechaDividida[1];
				$dia = $fechaDividida[2];
				$msResultado .= $anno . "%" . $mes . "@" . $dia . "#";
			}
		}
		
		$fecha = date("Y-m-d", strtotime($fecha . "+ 1 days"));
	}
	echo $msResultado;
}

if (isset($_POST["existeModuloPlan"])) //Pantalla de Planificación Programática (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$modulo = $_POST["existeModuloPlan"];
	$msConsulta = "select PLANIFICACION_REL from KDSA120A where MODULO_REL = '" . trim($modulo) . "'";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute();
	$mnRegistros = $mDatos->rowCount();
	echo $mnRegistros;
}

if (isset($_POST["insertarPlan"])) //Pantalla de Planificación Programática (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msPlanificacion = $_POST["insertarPlan"];
	$nombreArchivo = "../" . $msPlanificacion . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Inicia la escritura del Json
	$archivo = fopen($nombreArchivo, "a");
	fwrite($archivo, "[" . PHP_EOL);

	$msConsulta = "select MODULO_REL from KDSA120A where PLANIFICACION_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msPlanificacion]);
	$Fila = $mDatos->fetch();
	$msModulo = $Fila["MODULO_REL"];

	$msConsulta = "select DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020, FECHAINI_021, FECHAFIN_021 ";
	$msConsulta .= "from KDSA021A, KDSA020A where KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$Fila = $mDatos->fetch();
	$domingo = $Fila["DOMINGO_020"];
	$lunes = $Fila["LUNES_020"];
	$martes = $Fila["MARTES_020"];
	$miercoles = $Fila["MIERCOLES_020"];
	$jueves = $Fila["JUEVES_020"];
	$viernes = $Fila["VIERNES_020"];
	$sabado = $Fila["SABADO_020"];
	$fecha = trim($Fila["FECHAINI_021"]);
	$fechaFin = trim($Fila["FECHAFIN_021"]);
	$primeraFecha = true;
	$detalle = 0;

	while ($fecha <= $fechaFin)
	{
		$escribirFecha = false;
		$diaSemana = date("l", strtotime($fecha));

		if ($diaSemana == "Sunday" and $domingo == 1)
			$escribirFecha = true;

		if ($diaSemana == "Monday" and $lunes == 1)
			$escribirFecha = true;
		
		if ($diaSemana == "Tuesday" and $martes == 1)
			$escribirFecha = true;

		if ($diaSemana == "Wednesday" and $miercoles == 1)
			$escribirFecha = true;

		if ($diaSemana == "Thursday" and $jueves == 1)
			$escribirFecha = true;

		if ($diaSemana == "Friday" and $viernes == 1)
			$escribirFecha = true;

		if ($diaSemana == "Saturday" and $sabado == 1)
			$escribirFecha = true;
		
		if ($escribirFecha)
		{
			//Verifica que no sea un día Feriado
			$msConsulta = "select DETFECHA_REL from KDSA021A, KDSA022A where KDSA021A.CURSO_REL = KDSA022A.CURSO_REL and KDSA021A.MODULO_REL = ? and FECHA_022 = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msModulo, $fecha]);
			$mnAuxiliar = $mAuxiliar->rowCount();

			if ($mnAuxiliar == 0)
			{
				$msConsulta = "select * from KDSA121A where PLANIFICACION_REL = ? and FECHA_121 = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msPlanificacion, $fecha]);
				$mnRegistros = $mAuxiliar->rowCount();
				$repiteCiclo = 5 - $mnRegistros;

				for ($i = 1; $i <= $mnRegistros; $i++)
				{
					$detalle += 1;
					$dLocal = $detalle;
					$Fila = $mAuxiliar->fetch();
					if ($primeraFecha)
					{
						$texto = '{';
						$primeraFecha = false;
					}
					else
						$texto = ',{';
					
					$texto .= '"planificacion":"' . trim($Fila['PLANIFICACION_REL']) . '", ';
					$texto .= '"detalle":"' . trim($dLocal) . '", ';
					$fechaBD = date_create_from_format('Y-m-d', $Fila["FECHA_121"]);
					$texto .= '"fechaGrid":"' . date_format($fechaBD, 'd/m/Y') . '", ';
					$texto .= '"fecha":"' . trim($Fila['FECHA_121']) . '", ';
					$texto .= '"unidad":"' . trim($Fila['UNIDAD_121']) . '", ';
					$texto .= '"contenido":"' . trim($Fila['CONTENIDO_121']) . '", ';
					$texto .= '"objetivos":"' . trim($Fila['OBJETIVOS_121']) . '", ';
					$texto .= '"actividades":"' . trim($Fila['ACTIVIDADES_121']) . '", ';
					$texto .= '"recursos":"' . trim($Fila['RECURSOS_121']) . '", ';
					$texto .= '"evaluacion":"' . trim($Fila['EVALUACION_121']) . '", ';
					$texto .= '"estado":"' . trim($Fila['ESTADO_121']) . '"';
					$texto .= '}';
					fwrite($archivo, $texto . PHP_EOL);
				}

				for ($k = 1; $k <= $repiteCiclo; $k++)
				{
					$detalle += 1;
					$dLocal = $detalle;
					if ($primeraFecha)
					{
						$texto = '{';
						$primeraFecha = false;
					}
					else
						$texto = ',{';
					
					$texto .= '"planificacion":"' . trim($msPlanificacion) . '", ';
					$texto .= '"detalle":"' . trim($dLocal) . '", ';
					$fechaBD = date_create_from_format('Y-m-d', $fecha);
					$texto .= '"fechaGrid":"' . date_format($fechaBD, 'd/m/Y') . '", ';
					$texto .= '"fecha":"' . date_format($fechaBD, 'Y-m-d') . '", ';
					$texto .= '"unidad":"", ';
					$texto .= '"contenido":"", ';
					$texto .= '"objetivos":"", ';
					$texto .= '"actividades":"", ';
					$texto .= '"recursos":"", ';
					$texto .= '"evaluacion":"", ';
					if ($repiteCiclo == 5)
						$texto .= '"estado":"0"';
					else
						$texto .= '"estado":"' . rtrim($Fila['ESTADO_121']) . '"';
					$texto .= '}';
					fwrite($archivo, $texto . PHP_EOL);
				}			
			}
		}
		
		$fecha = date("Y-m-d", strtotime($fecha . "+ 1 days"));
	}
	
	fwrite($archivo, "]");
	fclose($archivo);
	
	echo $msPlanificacion . ".json";
}

if (isset($_POST["consCurso"])) //Pantalla de Consulta de Cursos activos
{
	$m_cnx_MySQL = fxAbrirConexion();
	$codCurso = $_POST["consCurso"];
	$fechaHora = date("YmdHis");
	$nombreArchivo = $codCurso . $fechaHora . "A.json";

	if (file_exists("../" . $nombreArchivo))
		unlink("../" . $nombreArchivo);

	$msConsulta = "select KDSA021A.MODULO_REL, CURSO_REL, KDSA021A.DOCENTE_REL, NUMERO_021, NOMBRE_021, NOMBRE_100, FECHAINI_021, FECHAFIN_021, ";
	$msConsulta .= "IFNULL((select PLANIFICACION_REL from KDSA120A where KDSA021A.MODULO_REL = KDSA120A.MODULO_REL limit 1), '') as PLAN ";
	$msConsulta .= "from KDSA021A, KDSA100A where KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL and CURSO_REL = ? order by NUMERO_021, MODULO_REL";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codCurso]);
	$numRegistros = $mDatos->rowCount();
	$archivo = fopen("../" . $nombreArchivo, "a");
	fwrite($archivo, "[" . PHP_EOL);
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"modulo":"' . rtrim($Fila['MODULO_REL']) . '", ');
		fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
		fwrite($archivo, '"codDocente":"' . rtrim($Fila['DOCENTE_REL']) . '", ');
		fwrite($archivo, '"numero":"' . rtrim($Fila['NUMERO_021']) . '", ');
		fwrite($archivo, '"docente":"' . rtrim($Fila['NOMBRE_100']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_021']) . '", ');
		fwrite($archivo, '"fechaIni":"' . rtrim($Fila['FECHAINI_021']) . '", ');
		fwrite($archivo, '"fechaFin":"' . rtrim($Fila['FECHAFIN_021']) . '", ');
		fwrite($archivo, '"plan":"' . rtrim($Fila['PLAN']) . '"');

		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	$msConsulta = "select FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIASCLASE, ";
	$msConsulta .= "(case TIPO_020 when 0 then 'Seminario' when 1 then 'Curso' when 2 then 'Carrera' when 3 then 'Taller' when 4 then 'Diplomado' end) as TIPO_020, ";
	$msConsulta .= "(case TIPOASISTENCIA_020 when 0 then 'Presencial' when 1 then 'Virtual' when 2 then 'On-line' end) as TIPOASISTENCIA_020, ";
	$msConsulta .= "(case TURNO_020 when 0 then 'Nocturno' when 1 then 'Sabatino' when 2 then 'Dominical' when 3 then 'Matutino' when 4 then 'Vespertino' end) as TURNO_020, ";
	$msConsulta .= "VALOR_020, MATRICULA_020, CUOTA_020, CERTIFICACION_020, MORA_020 from KDSA020A where CURSO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codCurso]);
	$Fila = $mDatos->fetch();
	$msResultado = $Fila["FECHAINI_020"] . "~" . $Fila["FECHAFIN_020"] . "~" . $Fila["HORAINI_020"] . "~" . $Fila["HORAFIN_020"] . "~";
	$msResultado .= $Fila["TIPO_020"] . "~" . $Fila["TURNO_020"] . "~" . $Fila["TIPOASISTENCIA_020"] . "~" . $Fila["DIASCLASE"] . "~";
	$msResultado .= $Fila["VALOR_020"] . "~" . $Fila["MATRICULA_020"] . "~" . $Fila["CUOTA_020"] . "~" . $Fila["CERTIFICACION_020"] . "~";
	$msResultado .= $Fila["MORA_020"] . "~" . trim($nombreArchivo) . "~";

	echo $msResultado;
}
?>