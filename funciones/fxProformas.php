<?php
	function fxGuardarProformas($msProspecto, $mdFecha, $mbInatec, $mnTipoCambio, $msObservaciones)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(PROFORMA_REL), 3), 0) as Ultimo from KDSA090A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "PF" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		
		$msConsulta = "insert into KDSA090A (PROFORMA_REL, PROSPECTO_REL, FECHA_090, INATEC_090, TIPOCAMBIO_090, OBSERVACIONES_090) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msProspecto, $mdFecha, $mbInatec, $mnTipoCambio, $msObservaciones]);
		return ($msCodigo);
	}
	
	function fxModificarProformas($msCodigo, $msProspecto, $mdFecha, $mbInatec, $mnTipoCambio, $msObservaciones)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA090A set PROSPECTO_REL = ?, FECHA_090 = ?, INATEC_090 = ?, ";
		$msConsulta .= "TIPOCAMBIO_090 = ?, OBSERVACIONES_090 = ? where PROFORMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msProspecto, $mdFecha, $mbInatec, $mnTipoCambio, $msObservaciones, $msCodigo]);
		return ($msCodigo);
	}
	
	function fxBorrarProformas($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA090A where PROFORMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}

	function fxDevuelveProformas($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select PROFORMA_REL, FECHA_090, NOMBRE_060, (case INATEC_090 when 0 then '' when 1 then 'X' end) as INATEC_090 from KDSA090A, KDSA060A where KDSA090A.PROSPECTO_REL = KDSA060A.PROSPECTO_REL order by PROFORMA_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}
		else
		{
			$msConsulta = "select PROFORMA_REL, PROSPECTO_REL, FECHA_090, INATEC_090, TIPOCAMBIO_090, OBSERVACIONES_090 from KDSA090A where PROFORMA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxGuardarDetProformas($msCodigo, $msCurso, $mnCantidad)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA091A (PROFORMA_REL, CURSO_REL, CANTIDAD_091) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso, $mnCantidad]);
	}
	
	function fxBorrarDetProformas($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA091A where PROFORMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetProformas($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select PROFORMA_REL, KDSA091A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, CANTIDAD_091 from KDSA091A, KDSA020A where KDSA091A.CURSO_REL = KDSA020A.CURSO_REL and KDSA091A.PROFORMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
	
	function fxGuardarOtroDetProformas($msCodigo, $mnConsecutivo, $msCursoKDSA, $msCursoInatec, $msDiasClase, $msHorario, $mdFechaIni, $mdFechaFin, $mnHorasClase, $msCodInatec, $msAcuerdo, $mnPrecio, $mnCupos)
	{
		if (trim($mnHorasClase) == '')
			$mnHorasClase = 0;

		if (trim($mdFechaIni) == '')
			$mdFechaIni = '1900-01-01';

		if (trim($mdFechaFin) == '')
			$mdFechaFin = '1900-01-01';

		if (trim($mnPrecio) == '')
			$mnPrecio = 0;

		if (trim($mnCupos) == '')
			$mnCupos = 1;
		
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA092A (PROFORMA_REL, CONSECUTIVO_REL, CURSOKDSA_092, CURSOINATEC_092, DIASCLASE_092, HORARIO_092, FECHAINI_092, FECHAFIN_092, HORASCLASE_092, ";
		$msConsulta .= "CODIGOINATEC_092, ACUERDO_092, PRECIO_092, CUPOS_092) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mnConsecutivo, $msCursoKDSA, $msCursoInatec, $msDiasClase, $msHorario, $mdFechaIni, $mdFechaFin, $mnHorasClase, $msCodInatec, $msAcuerdo, $mnPrecio, $mnCupos]);
	}
	
	function fxBorrarOtroDetProformas($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA092A where PROFORMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveOtroDetProformas($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select PROFORMA_REL, CURSOKDSA_092, CURSOINATEC_092, DIASCLASE_092, HORARIO_092, FECHAINI_092, FECHAFIN_092, CODIGOINATEC_092, ";
		$msConsulta .= "HORASCLASE_092, ACUERDO_092, PRECIO_092, CUPOS_092 from KDSA092A where PROFORMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}	
?>