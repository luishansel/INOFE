<?php
	//*****FIRMAS************************************************************//
	function fxGuardarFirma($msCodigo, $msNombre, $msCargo, $msSexo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(FIRMA_REL), 3), 0) as Ultimo from KDSA008A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "FI" . str_repeat("0", 3 - $Longitud) . trim($Numero);
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA008A (FIRMA_REL, NOMBRE_008, CARGO_008, SEXO_008) values(?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msNombre, $msCargo, $msSexo]);
	}
	
	function fxModificarFirma($msCodigo, $msNombre, $msCargo, $msSexo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA008A set NOMBRE_008 = ?, CARGO_008 = ?, SEXO_008 = ? where FIRMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msNombre, $msCargo, $msSexo, $msCodigo]);
	}
	
	function fxBorrarFirma($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA008A where FIRMA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveFirma()
	{
		$m_cnx_MySQL = fxAbrirConexion();

		$msConsulta = "select FIRMA_REL, NOMBRE_008, CARGO_008, SEXO_008 from KDSA008A order by FIRMA_REL desc";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		return $mDatos;
	}
?>