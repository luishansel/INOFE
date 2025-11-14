<?php
    function fxModificarRegistro($mnUltimoRegistro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "UPDATE KDSA181A SET CONSECUTIVO_181 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mnUltimoRegistro]);
    }

    function fxObtenerRegistro()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "SELECT CONSECUTIVO_181 FROM KDSA181A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
        $mDatos->execute();
        $mFila = $mDatos->fetch();
        $mnRegisto = $mFila["CONSECUTIVO_181"];
        return $mnRegisto;
    }
?>