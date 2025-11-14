<?php
	function fxGuardarPlanClase($msModulo, $mdFechaPlan, $mdFechaClase, $msContenidos, $msAsignaciones)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(CLASE_REL), 3), 0) as Ultimo from KDSA130A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "PC" . str_repeat("0", 8 - $Longitud) . trim($Numero);

		if (strlen($msContenidos)>500)
			$msContenidos = substr($msContenidos, 0, 499);

		$msConsulta = "insert into KDSA130A (CLASE_REL, MODULO_REL, FECHA_130, FECHACLASE_130, CONTENIDOS_130, ASIGNACIONES_130) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msModulo, $mdFechaPlan, $mdFechaClase, $msContenidos, $msAsignaciones]);

		//Actualiza el Estado de la Planificación
		$msConsulta = "select PLANIFICACION_REL from KDSA120A where MODULO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo]);
		$Fila = $mDatos->fetch();
		$msPlanificacion = $Fila["PLANIFICACION_REL"];
		$msConsulta = "update KDSA121A set ESTADO_121 = 1 where PLANIFICACION_REL = ? and FECHA_121 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msPlanificacion, $mdFechaClase]);
		return ($msCodigo);
	}
	
	function fxModificarPlanClase($msCodigo, $msModulo, $mdFechaPlan, $mdFechaClase, $msContenidos, $msAsignaciones)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA130A set MODULO_REL = ?, FECHA_130 = ?, FECHACLASE_130 = ?";
		$msConsulta .= ", CONTENIDOS_130 = ?, ASIGNACIONES_130 = ? where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo, $mdFechaPlan, $mdFechaClase, $msContenidos, $msAsignaciones, $msCodigo]);
	}
	
	function fxBorrarPlanClase($msCodigo)
	{
		$mnResultado = 0;
		$m_cnx_MySQL = fxAbrirConexion();

		$msConsulta = "select MODULO_REL, FECHACLASE_130 from KDSA130A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$Fila = $mDatos->fetch();
		$msModulo = trim($Fila["MODULO_REL"]);
		$mdFechaClase = trim($Fila["FECHACLASE_130"]);

		//Verifica que no existan Asistencias
		$msConsulta = "select ASISTENCIA_REL from KDSA140A where MODULO_REL = ? and FECHA_140 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo, $mdFechaClase]);
		$mnRegistros = $mDatos->rowCount();
		if ($mnRegistros == 0)
		{
			$msConsulta = "delete from KDSA131A where CLASE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
			$msConsulta = "delete from KDSA132A where CLASE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
			$msConsulta = "delete from KDSA133A where CLASE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
			$msConsulta = "delete from KDSA130A where CLASE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);

			//Actualiza el Estado de la Planificación
			$msConsulta = "select PLANIFICACION_REL from KDSA120A where MODULO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msModulo]);
			$Fila = $mDatos->fetch();
			$msPlanificacion = $Fila["PLANIFICACION_REL"];
			$msConsulta = "update KDSA121A set ESTADO_121 = 0 where PLANIFICACION_REL = ? and FECHA_121 = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msPlanificacion, $mdFechaClase]);
			$mnResultado = 1;
		}

		return $mnResultado;
	}
	
	function fxDevuelvePlanClase($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msDocente = $_SESSION["gsDocente"];
		$msUsuario = $_SESSION["gsUsuario"];
		$msConsulta = "Select * from KDSA002A where USUARIO_REL =? and SUPERVISOR_002 = 1";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msUsuario]);
		$Administrador = $mDatos->rowCount();

		if ($mbLlenaGrid == 1)
		{
			if ($Administrador == 1 or $_SESSION["gsDocente"] == "")
			{
				$msConsulta = "select CLASE_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ') - ', NOMBRE_021) as NOMBRE_021, FECHA_130, FECHACLASE_130 from KDSA130A, KDSA021A, KDSA020A where KDSA130A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL order by CLASE_REL desc";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute();
			}
			else
			{
				$msConsulta = "select CLASE_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ') - ', NOMBRE_021) as NOMBRE_021, FECHA_130, FECHACLASE_130 from KDSA130A, KDSA021A, KDSA020A where KDSA130A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = ? order by CLASE_REL desc";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msDocente]);
			}
		}
		else
		{
			$msConsulta = "select CLASE_REL, MODULO_REL, FECHA_130, FECHACLASE_130, CONTENIDOS_130, ASIGNACIONES_130 from KDSA130A where CLASE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	/*****Detalle Objetivos (KDSA131A)**************/
	
	function fxGuardarDetObjetivos($msCodigo, $mnDetObjetivo, $msObjetivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA131A (CLASE_REL, DETOBJETIVOS_REL, DESC_131) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetObjetivo, $msObjetivo]);
	}
	
	function fxBorrarDetObjetivos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA131A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	function fxDevuelveDetObjetivos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CLASE_REL, DETOBJETIVOS_REL, DESC_131 from KDSA131A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Actividades (KDSA132A)***********/
	
	function fxGuardarDetActividades($msCodigo, $mnDetActividad, $msActividad)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA132A (CLASE_REL, DETACTIVIDADES_REL, DESC_132) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetActividad, $msActividad]);
	}
	
	function fxBorrarDetActividades($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA132A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetActividades($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CLASE_REL, DETACTIVIDADES_REL, DESC_132 from KDSA132A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	/*****Detalle Materiales (KDSA133A)*****/
	
	function fxGuardarDetMateriales($msCodigo, $mnDetMaterial, $msMaterial)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA133A (CLASE_REL, DETMATERIALES_REL, DESC_133) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetMaterial, $msMaterial]);
	}
	
	function fxBorrarDetMateriales($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA133A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetMateriales($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CLASE_REL, DETMATERIALES_REL, DESC_133 from KDSA133A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}

	/*****Detalle Archivos (KDSA134A)*****/
	
	function fxGuardarDetApoyo($msCodigo, $mnTipo, $msDescripcion, $msRutaArchivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(max(APOYO_REL), 0) as Ultimo from KDSA134A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$mFila = $mDatos->fetch();
		$mnApoyo = intval($mFila["Ultimo"]);
		$mnApoyo += 1;

		$msConsulta = "insert into KDSA134A (CLASE_REL, APOYO_REL, TIPO_134, DESC_134, RUTA_134) values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnApoyo, $mnTipo, $msDescripcion, $msRutaArchivo]);
	}
	
	function fxBorrarDetApoyo($msCodigo, $mnApoyo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA134A where CLASE_REL = ? and APOYO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnApoyo]);
	}
	
	function fxDevuelveDetApoyo($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CLASE_REL, APOYO_REL, TIPO_134, DESC_134, RUTA_134 from KDSA134A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}

	/*****Detalle Sitios web (KDSA135A)*****/
	
	function fxGuardarDetSitio($msCodigo, $mnDetSitio, $msDescripcion, $msURL)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA135A (CLASE_REL, SITIO_REL, DESC_135, URL_135) values (?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetSitio, $msDescripcion, $msURL]);
	}
	
	function fxBorrarDetSitio($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA135A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetSitio($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CLASE_REL, SITIO_REL, DESC_135, URL_135 from KDSA135A where CLASE_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>