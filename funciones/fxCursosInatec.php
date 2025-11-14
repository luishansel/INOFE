<?php
	function fxGuardarCursosInatec($msNombre, $mnHorasClase, $msCodigoInatec, $msAcuerdo, $mdFechaVenc, $mbActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(CURSOINATEC_REL), 3), 0) as Ultimo from KDSA070A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "CI" . str_repeat("0", 4 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA070A (CURSOINATEC_REL, NOMBRE_070, HORASCLASE_070, CODIGO_070, ACUERDO_070, FECHAVENC_070, ACTIVO_070) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msNombre, $mnHorasClase, $msCodigoInatec, $msAcuerdo, $mdFechaVenc, $mbActivo]);
		return ($msCodigo);
	}
	
	function fxModificarCursosInatec($msCodigo, $msNombre, $mnHorasClase, $msCodigoInatec, $msAcuerdo, $mdFechaVenc, $mbActivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA070A set NOMBRE_070 = '" . trim($msNombre) . "', HORASCLASE_070 = " . trim($mnHorasClase) . ", CODIGO_070 = '" . trim($msCodigoInatec);
		$msConsulta .= "', ACUERDO_070 = '" . trim($msAcuerdo) . "', FECHAVENC_070 = '" . trim($mdFechaVenc) . "', ACTIVO_070 = " . trim($mbActivo) . " where CURSOINATEC_REL = '" .trim($msCodigo). "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msNombre, $mnHorasClase, $msCodigoInatec, $msAcuerdo, $mdFechaVenc, $mbActivo, $msCodigo]);
	}
	
	function fxBorrarCursosInatec($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA070A where CURSOINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveCursosInatec($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select CURSOINATEC_REL, NOMBRE_070, CODIGO_070, ACUERDO_070, FECHAVENC_070, (case ACTIVO_070 when 1 then 'x' else '' end) as ACTIVO_070 from KDSA070A order by CURSOINATEC_REL";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select CURSOINATEC_REL, NOMBRE_070, HORASCLASE_070, CODIGO_070, ACUERDO_070, FECHAVENC_070, ACTIVO_070 from KDSA070A where CURSOINATEC_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
?>