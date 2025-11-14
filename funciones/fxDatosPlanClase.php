<?php
require_once ("fxGeneral.php");
require_once ("fxPlanClase.php");
    
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


if (isset($_POST["moduloPlanClase"])) //Pantalla de Planificación de Clase (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msModulo = $_POST["moduloPlanClase"];
	$mnResultado = 1;

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
			$mnRegistros = $mAuxiliar->rowCount();

			if ($mnRegistros == 0)
			{
				//Verifica que no exista una planificación en la fecha
				$msConsulta = "select CLASE_REL from KDSA130A where MODULO_REL = ? and FECHACLASE_130 = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msModulo, $fecha]);
				$mnRegistros = $mAuxiliar->rowCount();

				if ($mnRegistros == 0)
				{
					$fechaBD = date_create_from_format('Y-m-d', $fecha);
					$Valor = date_format($fechaBD, 'Y-m-d');
					$Texto = date_format($fechaBD, 'd / m / Y');

					//Agrega la fecha en el ComboBox
					echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
				}
			}
		}

		$fecha = date("Y-m-d", strtotime($fecha . "+ 1 days"));
	}

	echo($mnResultado);
}

if (isset($_POST["moduloPlanClaseFechas"])) //Pantalla de Planificación de Clase (Docencia)
{
	$m_cnx_MySQL = fxAbrirConexion();
	$msModulo = $_POST["moduloPlanClaseFechas"];
	$msResultado = "";

	$msConsulta = "select DISTINCT KDSA121A.PLANIFICACION_REL, FECHA_121 from KDSA121A, KDSA120A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	while ($Fila = $mDatos->fetch())
	{
		$msConsulta = "select CLASE_REL from KDSA130A where MODULO_REL = ? and FECHACLASE_130 = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msModulo, $Fila["FECHA_121"]]);
		$mnRegistros = $mAuxiliar->rowCount();
		
		if ($mnRegistros == 0)
		{
			$fechaBD = date_create_from_format('Y-m-d', $Fila["FECHA_121"]);
			$Valor = date_format($fechaBD, 'Y-m-d');
			$Texto = date_format($fechaBD, 'd / m / Y');
			$msResultado .= "<option value='" . $Valor . "'>" . $Texto . "</option>";
		}
	}
	echo($msResultado);
}

/*Evita que se haga una planificación sin las calificaciones del módulo anterior*/
if (isset($_POST["msModulo"]) and isset($_POST["mbAdministrador"]) and isset($_POST["mbAcademico"]))
{
	$m_cnx_MySQL = fxAbrirConexion();
	$mnResultado = 0;
	$msModulo = $_POST["msModulo"];
	$msConsulta = "select CURSO_REL, DOCENTE_REL, FECHAINI_021 from KDSA021A where MODULO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msModulo]);
	$mFila = $mDatos->fetch();
	$msCurso = $mFila["CURSO_REL"];
	$msDocente = $mFila["DOCENTE_REL"];
	$mdFechaIni = $mFila["FECHAINI_021"];

	if ($_POST["mbAdministrador"] != 1 and $_POST["mbAcademico"] != 1)
	{
		$msConsulta = "select MODULO_REL from KDSA021A where FECHAINI_021 < ? and CURSO_REL = ? and DOCENTE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFechaIni, $msCurso, $msDocente]);

		while($mFila = $mDatos->fetch())
		{
			$mModulo = $mFila["MODULO_REL"];
			$msConsulta = "select CALIFICACION_REL from KDSA150A where MODULO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mModulo]);
			$mnRegistros = $mAuxiliar->rowCount();
			if ($mnRegistros == 0)
				$mnResultado++;
		}
	}

	echo($mnResultado);
}

/***MANEJO DE DOCUMENTOS DE APOYO***/

if (is_array($_FILES) && count($_FILES) > 0) {
	$msPlanClase = $_POST["txtPlanClase"];
	$mnTipo = $_POST["optTipo"];
	$msArchivo = $_FILES['archivo']['name'];
	//$msRuta = "https://appadmin.capacitacionkdsa.com/planClase/".$msPlanClase."/".$_FILES['archivo']['name'];
	$msRuta = "https://appdocente.capacitacionkdsa.com/planClase/".$msPlanClase."/".$_FILES['archivo']['name'];
	$miCarpeta = '../planClase/'.$msPlanClase;
	if (!file_exists($miCarpeta)) {
		mkdir($miCarpeta, 0777, true);
	}

	if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $miCarpeta."/".$_FILES['archivo']['name'])) {
		fxGuardarDetApoyo ($msPlanClase, $mnTipo, $msArchivo, $msRuta);
		
		//Construye el contenido del grid
		$mDatos = fxDevuelveDetApoyo($msPlanClase);
		$mnRegistros = $mDatos->rowCount();
		$msResultado = "[";
		$i = 1;

		while ($mFila = $mDatos->fetch()){
			$mnApoyo = $mFila["APOYO_REL"];
			$mnTipo = $mFila["TIPO_134"];
			$msArchivo = $mFila["DESC_134"];
			$msRuta = $mFila["RUTA_134"];

			if ($mnTipo == 0)
				$msTipo = "Teoría";
			else
				$msTipo = "Ejercicio";

			$msResultado .= '{"clase":"' . $msPlanClase . '","apoyo":"' . $mnApoyo . '","ruta":"' . $msRuta . '","tipo":"' . $msTipo . '","descripcion":"' . $msArchivo . '"}';
			
			if ($i != $mnRegistros)
            	$msResultado .= ',';

        	$i++;
		}
		$msResultado .= "]";
		echo $msResultado;
	} else {
		echo "";
	}
} else {
    echo "";
}

if (isset($_POST["CodPlanClase"]) and isset($_POST["CodApoyo"]) and isset($_POST["Ruta"])) {
	$m_cnx_MySQL = fxAbrirConexion();
	$msPlanClase = $_POST["CodPlanClase"];
	$mnApoyo = intval($_POST["CodApoyo"]);
	$pRuta = $_POST["Ruta"];

	$msConsulta = "select DESC_134 from KDSA134A where CLASE_REL = ? and APOYO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msPlanClase, $mnApoyo]);
	$mFila = $mDatos->fetch();
	$msArchivo = $mFila["DESC_134"];
	$msRuta = '../planClase/' . $msPlanClase . "/" . $msArchivo;

	if (array_map('unlink', glob($msRuta))) {
		fxBorrarDetApoyo ($msPlanClase, $mnApoyo);
		
		//Construye el contenido del grid
		$mDatos = fxDevuelveDetApoyo($msPlanClase);
		$mnRegistros = $mDatos->rowCount();
		$msResultado = "[";
		$i = 1;

		while ($mFila = $mDatos->fetch()){
			$mnApoyo = $mFila["APOYO_REL"];
			$mnTipo = $mFila["TIPO_134"];
			$msArchivo = $mFila["DESC_134"];
			$msRuta = $mFila["RUTA_134"];

			if ($mnTipo == 0)
				$msTipo = "Teoría";
			else
				$msTipo = "Ejercicio";

			$msResultado .= '{"clase":"' . $msPlanClase . '","apoyo":"' . $mnApoyo . '","ruta":"' . $msRuta . '","tipo":"' . $msTipo . '","descripcion":"' . $msArchivo . '"}';
			
			if ($i != $mnRegistros)
            	$msResultado .= ',';

        	$i++;
		}
		$msResultado .= "]";
		echo $msResultado;
	} else {
		echo "";
	};
} else {
	echo "";
}
?>