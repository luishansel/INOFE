<?php
    function fxGuardarActaCertificacion($msCurso, $msTomo, $msFecha, $mnNumeroTomo, $mnNumeroActa, $mnFolioIni, $mnLineaIni)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(ACTA_REL), 3), 0) as Ultimo from KDSA190A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "AC" . str_repeat("0", 8 - $Longitud) . trim($Numero);

		$msConsulta = "insert into KDSA190A (ACTA_REL, CURSO_REL, TOMO_REL, FECHA_190, TOMO_190, ACTA_190, FOLIOINI_190, LINEAINI_190, LINEAFIN_190) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCurso, $msTomo, $msFecha, $mnNumeroTomo, $mnNumeroActa, $mnFolioIni, $mnLineaIni, 0]);
		return $msCodigo;
	}

	function fxActualizarLineaFinal($msActa, $mnLineaFin)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA190A set LINEAFIN_190 = ? where ACTA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mnLineaFin, $msActa]);
	}

	function fxGuardarDetalleActa($msActa, $msMatricula, $mnFolio, $mnRegistro, $msVerificacion)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA191A (ACTA_REL, MATRICULA_REL, FOLIO_191, REGISTRO_191, VERIFICACION_191) ";
		$msConsulta .= "values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msActa, $msMatricula, $mnFolio, $mnRegistro, $msVerificacion]);
	}
?>