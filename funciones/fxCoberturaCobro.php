<?php
	function fxGuardarCoberturaCobro($msCobro, $msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA053A (COBRO_REL, MATRICULA_REL) values(?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msMatricula, $msCobro]);
	}
	
	function fxBorrarCoberturaCobro($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA053A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
?>