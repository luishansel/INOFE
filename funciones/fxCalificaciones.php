<?php
	function fxGuardarCalificaciones($msModulo, $mdFecha)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(CALIFICACION_REL), 3), 0) as Ultimo from KDSA150A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "CA" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA150A (CALIFICACION_REL, MODULO_REL, FECHA_150) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msModulo, $mdFecha]);
		return ($msCodigo);
	}
	
	function fxModificarCalificaciones($msCodigo, $msModulo, $mdFecha)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA150A set MODULO_REL = ?, FECHA_150 = ? where CALIFICACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo, $mdFecha, $msCodigo]);
	}
	
	function fxBorrarCalificaciones($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA151A where CALIFICACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$msConsulta = "delete from KDSA150A where CALIFICACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveCalificaciones($mbLlenaGrid, $msCodigo = "")
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
				$msConsulta = "select CALIFICACION_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ') - ', NOMBRE_021) as NOMBRE_021, FECHA_150 from KDSA150A, KDSA021A, KDSA020A where KDSA150A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL order by CALIFICACION_REL desc";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute();
			}
			else
			{
				$msConsulta = "select CALIFICACION_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ') - ', NOMBRE_021) as NOMBRE_021, FECHA_150 from KDSA150A, KDSA021A, KDSA020A where KDSA150A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = ? order by CALIFICACION_REL desc";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msDocente]);
			}
		}
		else
		{
			$msConsulta = "select CALIFICACION_REL, MODULO_REL, FECHA_150 from KDSA150A where CALIFICACION_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	/*****Detalle Calificaciones (KDSA151A)**************/
	
	function fxGuardarDetCalificaciones($msCodigo, $msMatricula, $mnPuntaje)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA151A (CALIFICACION_REL, MATRICULA_REL, PUNTAJE_151) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msMatricula, $mnPuntaje]);
	}
	
	function fxBorrarDetCalificaciones($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA151A where CALIFICACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetCalificaciones($msCodigo, $msModulo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		if ($msCodigo == "")
		{
			$msConsulta = "select '' as CALIFICACION_REL, KDSA030A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, ";
			$msConsulta .= "-1 as PUNTAJE_151 from KDSA150A, KDSA021A, KDSA030A, KDSA010A ";
			$msConsulta .= "where KDSA150A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA030A.CURSO_REL and ESTADO_030 not in (4, 2) and ";
			$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA150A.MODULO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msModulo]);
		}
		else
		{
			$msConsulta = "select KDSA151A.CALIFICACION_REL, KDSA151A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, PUNTAJE_151 ";
			$msConsulta .= "from KDSA151A, KDSA030A, KDSA010A where KDSA151A.CALIFICACION_REL = ? and ";
			$msConsulta .= "KDSA151A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
?>