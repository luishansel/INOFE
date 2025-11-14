<?php
    function fxGuardarTomo($msTomo, $msDescripcion, $mdApertura, $mnNumero, $mnTipo, $mnUltimoFolio, $mnUltimaActa, $mbCerrado)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA180A (TOMO_REL, DESCRIPCION_180, APERTURA_180, NUMERO_180, TIPO_180, ULTIMOFOLIO_180, ULTIMAACTA_180, CERRADO_180) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msTomo, $msDescripcion, $mdApertura, $mnNumero, $mnTipo, $mnUltimoFolio, $mnUltimaActa, $mbCerrado]);
    }
    
    function fxModificarTomo($msTomo, $msDescripcion, $mdApertura, $mnNumero, $mnTipo, $mnUltimoFolio, $mnUltimaActa, $mbCerrado)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA180A set DESCRIPCION_180 = ?, APERTURA_180 = ?, NUMERO_180=?, TIPO_180=?, ULTIMOFOLIO_180 = ?, ULTIMAACTA_180 = ?, CERRADO_180 = ? where TOMO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msDescripcion, $mdApertura, $mnNumero, $mnTipo, $mnUltimoFolio, $mnUltimaActa, $mbCerrado, $msTomo]);
	}
	
	function fxModificarUltimos($msTomo, $mnUltimoFolio, $mnUltimaActa)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA180A set ULTIMOFOLIO_180 = ?, ULTIMAACTA_180 = ? where TOMO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mnUltimoFolio, $mnUltimaActa, $msTomo]);
    }
    
    function fxBorrarTomo($msTomo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA180A where TOMO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msTomo]);
    }
    
    function fxExisteTomo($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "select TOMO_REL from KDSA180A where TOMO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$mnRegistros = $mDatos->rowCount();

		return $mnRegistros;
	}

	function fxDevuelveTomo($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select TOMO_REL, DESCRIPCION_180, APERTURA_180, NUMERO_180, case TIPO_180 when 0 then 'Seminario' when 1 then 'Curso' ";
			$msConsulta .= "when 2 then 'Carrera' when 3 then 'Taller' when 4 then 'Diplomado' when 5 then 'Webinar' when 6 then 'Workshop' ";
			$msConsulta .= "when 7 then 'Teambuilding' end as TIPO_180, ULTIMOFOLIO_180, ULTIMAACTA_180, case CERRADO_180 when 0 then 'Abierto' ";
			$msConsulta .= "when 1 then 'Cerrado' end as ESTADO from KDSA180A";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select TOMO_REL, DESCRIPCION_180, APERTURA_180, NUMERO_180, TIPO_180, ULTIMOFOLIO_180, ULTIMAACTA_180, CERRADO_180 from KDSA180A where TOMO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
?>