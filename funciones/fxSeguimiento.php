<?php
	function fxGuardarSeguimiento($msProspecto, $mdFecha, $mdProximaFecha, $msObservaciones, $msUsuario)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(SEGUIMIENTO_REL), 3), 0) as Ultimo from KDSA080A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "SG" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA080A (SEGUIMIENTO_REL, PROSPECTO_REL, FECHA_080, PROXIMOCONTACTO_080, OBSERVACIONES_080, USUARIO_080) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msProspecto, $mdFecha, $mdProximaFecha, $msObservaciones, $msUsuario]);
		return ($msCodigo);
	}
	
	function fxModificarSeguimiento($msCodigo, $msProspecto, $mdFecha, $mdProximaFecha, $msObservaciones, $msUsuario)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA080A set PROSPECTO_REL = ?, FECHA_080 = ?";
		$msConsulta .= ", PROXIMOCONTACTO_080 = ?, OBSERVACIONES_080 =?";
		$msConsulta .= ", USUARIO_080 = ? where SEGUIMIENTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msProspecto, $mdFecha, $mdProximaFecha, $msObservaciones, $msUsuario, $msCodigo]);
	}
	
	function fxBorrarSeguimiento($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA080A where SEGUIMIENTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveSeguimiento($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select SEGUIMIENTO_REL, NOMBRE_060, FECHA_080, PROXIMOCONTACTO_080 from KDSA080A join KDSA060A on KDSA080A.PROSPECTO_REL = KDSA060A.PROSPECTO_REL order by SEGUIMIENTO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select SEGUIMIENTO_REL, PROSPECTO_REL, FECHA_080, PROXIMOCONTACTO_080, OBSERVACIONES_080, USUARIO_080 from KDSA080A where SEGUIMIENTO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxGuardarDetSeguimiento($msCodigo, $msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA081A (SEGUIMIENTO_REL, CURSO_REL) values (?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso]);
	}
	
	function fxBorrarDetSeguimiento($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA081A where SEGUIMIENTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetSeguimiento($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select SEGUIMIENTO_REL, KDSA081A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020 from KDSA081A, KDSA020A where KDSA081A.CURSO_REL = KDSA020A.CURSO_REL and KDSA081A.SEGUIMIENTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	function fxGuardarDetSeguimiento2($msCodigo, $mnDetalle, $msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA082A (SEGUIMIENTO_REL, DETSEGUIMIENTO_REL, CURSO_082) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnDetalle, $msCurso]);
	}
	
	function fxBorrarDetSeguimiento2($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA082A where SEGUIMIENTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetSeguimiento2($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select SEGUIMIENTO_REL, DETSEGUIMIENTO_REL, CURSO_082 from KDSA082A where SEGUIMIENTO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>