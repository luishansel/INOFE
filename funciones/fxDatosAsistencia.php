<?php
require_once ("fxGeneral.php");

if (isset($_POST["modulosCurso"]) and isset($_POST["modulosDocente"]) and isset($_POST["mbAdministrador"]) and isset($_POST["mnTipo"])) //Devuelve los Módulos de un Curso
{
	$m_cnx_MySQL = fxAbrirConexion();
	$codCurso = $_POST["modulosCurso"];
	$codDocente = $_POST["modulosDocente"];
	$Administrador = intval($_POST["mbAdministrador"]);
	$mnTipo = intval($_POST["mnTipo"]);
	$msResultado = "";

	if ($Administrador == 1 or $codDocente == "")
	{
		$msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? order by NUMERO_021";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$codCurso]);
	}
	else
	{
		$msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? and DOCENTE_REL = ? order by NUMERO_021";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$codCurso, $codDocente]);
	}

	while ($Fila = $mDatos->fetch())
	{
		$Valor = $Fila["MODULO_REL"];
		$Texto = $Fila["NOMBRE_021"];

		if ($mnTipo == 2) //Calificaciones
		{
			$msConsulta = "select CALIFICACION_REL from KDSA150A where MODULO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$Valor]);
			$mnRegistros = $mAuxiliar->rowCount();
			if ($mnRegistros == 0)
				$msResultado .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
		}
		else
		{
			$msResultado .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
		}
	}
	echo($msResultado);
}

if (isset($_POST["asistenciaClase"]) and isset($_POST["moduloClase"]) and isset($_POST["usuarioClase"]) and isset($_POST["docenteClase"])) //Pantalla de Asistencia (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msModulo = $_POST["moduloClase"];
	$msAsistencia = $_POST["asistenciaClase"];
	$msUsuario = $_POST["usuarioClase"];
	$msDocente = $_POST["docenteClase"];
	$msResultado = "";

	$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and SUPERVISOR_002 = 1";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msUsuario]);
	$mbAdministrador = $mDatos->rowCount();

	$msConsulta = "select HORAFIN_020 from KDSA020A, KDSA021A where KDSA020A.CURSO_REL = KDSA021A.CURSO_REL and KDSA021A.MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$Fila = $mDatos->fetch();
	$mdHoraFin = $Fila["HORAFIN_020"];
	
	$msConsulta = "select CLASE_REL, FECHACLASE_130 as FECHA from KDSA130A where not exists(select ASISTENCIA_REL from KDSA140A where FECHA_140 = FECHACLASE_130 and KDSA140A.MODULO_REL = KDSA130A.MODULO_REL) and MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$primeraFecha = "";
	while ($Fila = $mDatos->fetch())
	{
		$fechaBD = date_create_from_format('Y-m-d', $Fila["FECHA"]);
		$Valor = date_format($fechaBD, 'Y-m-d');
		$Texto = date_format($fechaBD, 'd / m / Y');
		$msResultado .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
		if ($primeraFecha == "")
			$primeraFecha = $Valor;
	}

	if ($msAsistencia == "")
	{
		$msConsulta = "select '' as ASISTENCIA_REL, KDSA030A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, ";
		$msConsulta .= "1 as ESTADO_141, '' as JUSTIFICACION_141 from KDSA021A, KDSA030A, KDSA010A ";
		$msConsulta .= "where KDSA021A.CURSO_REL = KDSA030A.CURSO_REL and ESTADO_030 in (0, 3) and ";
		$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA021A.MODULO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo]);
	}
	else
	{
		$msConsulta = "select KDSA141A.ASISTENCIA_REL, KDSA141A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, ESTADO_141, JUSTIFICACION_141 ";
		$msConsulta .= "from KDSA141A, KDSA030A, KDSA010A where KDSA141A.ASISTENCIA_REL = ? and ";
		$msConsulta .= "KDSA141A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msAsistencia]);
	}

	$mnRegistros = $mDatos->rowCount();
	$msGrid = '[';
	$i = 1;

	if ($mnRegistros == 0)
		$msGrid .= '{"matricula":"","estudiante":"","estado":"Ausente"}';
	else
	{
		while($mFila = $mDatos->fetch())
		{
			switch (intval($mFila['ESTADO_141']))
			{
				case 0:
					$msEstado = 'Presente';
					break;

				case 1:
					$msEstado = 'Ausente';
					break;

				default:
					$msEstado = 'Justificado';
			}

			$msEstudiante = html_entity_decode($mFila["ESTUDIANTE"]);
			$msGrid .= '{"matricula":"' . $mFila["MATRICULA_REL"] . '","estudiante":"' . $msEstudiante . '","estado":"' . $msEstado;

			if ($i == $mnRegistros)
				$msGrid .= '"}';
			else
				$msGrid .= '"},';

			$i++;
		}
	}
	$msGrid .= "]";

	echo($msDocente . "$" . $mbAdministrador . "?" . $mdHoraFin . "%" . $primeraFecha . "&" . $msResultado . "@" . $msGrid . "#");
}

if (isset($_POST["modulo"]) and isset($_POST["fechaClase"])) //Pantalla de Asistencia (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msModulo = $_POST["modulo"];
	$mdFechaClase = $_POST["fechaClase"];
	$msConsulta = "Select CLASE_REL from KDSA130A where MODULO_REL = ? and FECHACLASE_130 = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo, $mdFechaClase]);
	$mFila = $mDatos->fetch();
	$msPlanClase = $mFila["CLASE_REL"];

	$msConsulta = "Select count(CLASE_REL) as CONTEO from KDSA134A where CLASE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msPlanClase]);
	$mFila = $mDatos->fetch();
	$mnArch = $mFila["CONTEO"];

	$msConsulta = "Select count(CLASE_REL) as CONTEO from KDSA135A where CLASE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msPlanClase]);
	$mFila = $mDatos->fetch();
	$mnWeb = $mFila["CONTEO"];

	$msRespuesta = array('arch'=>$mnArch, 'web'=>$mnWeb);
	echo json_encode($msRespuesta);
}
?>