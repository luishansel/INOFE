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

if (isset($_POST["moduloCalificacion"]) and isset($_POST["codCalificacion"])) //Pantalla de Calificaciones (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msModulo = $_POST["moduloCalificacion"];
	$msCalificacion = $_POST["codCalificacion"];
	$msResultado = "";
	
	$msConsulta = "select DATEDIFF(CURRENT_DATE,FECHAFIN_021) as DIAS from KDSA021A where MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$Fila = $mDatos->fetch();
	$mnDias = $Fila["DIAS"];
	
	//Inicia la escritura del Json
	$msConsulta = "select '' as CALIFICACION_REL, KDSA030A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, ";
	$msConsulta .= "'' as PUNTAJE_151 from KDSA021A, KDSA030A, KDSA010A ";
	$msConsulta .= "where KDSA021A.CURSO_REL = KDSA030A.CURSO_REL and ESTADO_030 not in (4, 2) and ";
	$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA021A.MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$numRegistros = $mDatos->rowCount();

	$msGrid = "[";
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		$msGrid .= '{';
		$msGrid .= '"matricula":"' . trim($Fila['MATRICULA_REL']) . '", ';
		$msGrid .= '"estudiante":"' . trim($Fila['ESTUDIANTE']) . '", ';
        $msGrid .= '"puntaje":"' . trim($Fila['PUNTAJE_151']) . '"';
		
		if ($i == $numRegistros)
			$msGrid .= "}";
		else
			$msGrid .= "},";
	}
	$msGrid .= "]";

	$msConsulta = "select FECHAFIN_021 from KDSA021A where MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$Fila = $mDatos->fetch();
	$mdFecha = $Fila["FECHAFIN_021"];

	$msConsulta = "select FECHA_121 from KDSA121A join KDSA120A on KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL ";
	$msConsulta .= "where ESTADO_121 <> 2 and MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$numRegistros = $mDatos->rowCount();

	echo ($mnDias . "^" . $mdFecha . "@" . $numRegistros . "#" . $msGrid . "%");
}
?>