<?php
	function fxGuardarAsistencia($msModulo, $mdFechaClase)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(ASISTENCIA_REL), 3), 0) as Ultimo from KDSA140A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "AS" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA140A (ASISTENCIA_REL, MODULO_REL, FECHA_140) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msModulo, $mdFechaClase]);

		return ($msCodigo);
	}
	
	function fxModificarAsistencia($msCodigo, $msModulo, $mdFechaClase)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA140A set MODULO_REL = ?, FECHA_140 = ? where ASISTENCIA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo, $mdFechaClase, $msCodigo]);
	}
	
	function fxBorrarAsistencia($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select MODULO_REL, FECHA_140 from KDSA140A where ASISTENCIA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$Fila = $mDatos->fetch();
		$msModulo = trim($Fila["MODULO_REL"]);
		$mdFechaClase = trim($Fila["FECHA_140"]);

		$msConsulta = "delete from KDSA141A where ASISTENCIA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$msConsulta = "delete from KDSA140A where ASISTENCIA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveAsistencia($mbLlenaGrid, $msCodigo = "")
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
				$msConsulta = "select ASISTENCIA_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ') - ', NOMBRE_021) as NOMBRE_021, FECHA_140 from KDSA140A, KDSA021A, KDSA020A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL order by ASISTENCIA_REL desc";	
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute();
			}
			else
			{
				$msConsulta = "select ASISTENCIA_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ') - ', NOMBRE_021) as NOMBRE_021, FECHA_140 from KDSA140A, KDSA021A, KDSA020A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = ? order by ASISTENCIA_REL desc";	
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msDocente]);
			}
		}
		else
		{
			$msConsulta = "select ASISTENCIA_REL, MODULO_REL, FECHA_140 from KDSA140A where ASISTENCIA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	/*****Detalle Estudiantes (KDSA141A)**************/
	
	function fxGuardarDetEstudiantes($msCodigo, $msMatricula, $mnEstado, $msJustificacion)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA141A (ASISTENCIA_REL, MATRICULA_REL, ESTADO_141, JUSTIFICACION_141) values (?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msMatricula, $mnEstado, $msJustificacion]);
	}
	
	function fxBorrarDetEstudiantes($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA141A where ASISTENCIA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetEstudiantes($msCodigo, $msModulo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		if ($msCodigo == "")
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
			$mDatos->execute([$msCodigo]);
		}
		return $mDatos;
	}
?>