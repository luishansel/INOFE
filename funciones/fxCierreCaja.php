<?php
	function fxGuardarCierreCaja($mdFecha)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA044A (FECHA_044) values(?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFecha]);
	}
	
	function fxBorrarCierreCaja($mdFecha)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA044A where FECHA_044 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFecha]);
	}
?>