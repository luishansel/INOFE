<?php
	function fxGuardarRegulacion($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(REGULACION_REL), 3), 0) as Ultimo from KDSA160A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "RG" . str_repeat("0", 8 - $Longitud) . trim($Numero);

		$msConsulta = "insert into KDSA160A (REGULACION_REL, CURSO_REL, FECHAELABORACION_160, FECHAACTUALIZACION_160) ";
		$msConsulta .= "values (?, ?, NOW(), NOW())";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso]);
		return $msCodigo;
	}

	function fxModificarRegulacion($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA160A set FECHAACTUALIZACION_160 = NOW() where REGULACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}

	function fxBorrarRegulacion($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA161A where REGULACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$msConsulta = "delete from KDSA160A where REGULACION_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}

	function fxExisteDetRegulacion($msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select * from KDSA161A where MATRICULA_REL = '" . trim($msMatricula) . "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msMatricula]);
		$mnRegistros = $mDatos->rowCount();
		return $mnRegistros;
	}

	function fxGuardarDetRegulacion($msMatricula, $msRegulacion, $mnAusencias, $msRetirado, $msRazonRetiro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA161A (MATRICULA_REL, REGULACION_REL, AUSENCIAS_161, RETIRADO_161, RAZONRETIRO_161) values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msMatricula, $msRegulacion, $mnAusencias, $msRetirado, $msRazonRetiro]);
	}
	
	function fxModificarDetRegulacion($msMatricula, $mnAusencias, $msRetirado, $msRazonRetiro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA161A set AUSENCIAS_161 = ?, RETIRADO_161 = ?, RAZONRETIRO_161 = ? where MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mnAusencias, $msRetirado, $msRazonRetiro, $msMatricula]);
	}

	function fxGuardarDetAusencia($msMatricula, $mnDetAusencia, $msObservacion, $msFecha, $msRazonAusencia)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA162A (MATRICULA_REL, DETAUSENCIA_REL, OBSERVACION_162, FECHA_162, RAZONAUSENCIA_162) values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msMatricula, $mnDetAusencia, $msObservacion, $msFecha, $msRazonAusencia]);
	}
	
	function fxBorrarDetAusencia($msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA162A where MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msMatricula]);
	}
?>