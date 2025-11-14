<?php
	function fxGuardarCobroInatec($msDescripcion, $mnRetDgi, $mnRetAlcaldia)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(COBROINATEC_REL), 3), 0) as Ultimo from KDSA054A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "CI" . str_repeat("0", 7 - $Longitud) . trim($Numero);
		
		$msConsulta = "insert into KDSA054A (COBROINATEC_REL, DESC_054, RETENCION_DGI_054, RETENCION_ALCALDIA_054, ABONADO_054, PAGADO_054, EXONERADO_054, ANULADO_054) ";
		$msConsulta .= "values(?, ?, ?, ?, 0, 0, 0, 0)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msDescripcion, $mnRetDgi, $mnRetAlcaldia]);
		return ($msCodigo);
	}
	
	function fxModificarCobroInatec($msCobro, $msDescripcion, $mnRetDgi, $mnRetAlcaldia)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA054A set DESC_054 = ?, RETENCION_DGI_054 = ?, RETENCION_ALCALDIA_054 = ? where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msDescripcion, $mnRetDgi, $mnRetAlcaldia, $msCodigo]);
	}
	
	function fxPagarCobroInatec($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA054A set PAGADO_054 = 1 where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxExonerarCobroInatec($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA054A set EXONERADO_054 = 1 where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxAnularCobroInatec($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA054A set ANULADO_054 = 1 where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxDevuelveCobroInatec($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select COBROINATEC_REL, DESC_054, ABONADO_054, ";
			$msConsulta .= "(case PAGADO_054 when 1 then 'x' else '' end) as PAGADO_054, (case EXONERADO_054 when 1 then 'x' else '' end) as EXONERADO_054, ";
			$msConsulta .= "(case ANULADO_054 when 1 then 'x' else '' end) as ANULADO_054 ";
			$msConsulta .= "from KDSA054A order by COBROINATEC_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select COBROINATEC_REL, DESC_054, RETENCION_DGI_054, RETENCION_ALCALDIA_054, ABONADO_054, PAGADO_054, EXONERADO_054, ANULADO_054 from KDSA054A ";
			$msConsulta .= "where COBROINATEC_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxGuardarDetCobroInatec($msCodigo, $msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA055A (COBROINATEC_REL, COBRO_REL) values (?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCobro]);
	}
	
	function fxBorrarDetCobroInatec($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA055A where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveDetCobroInatec($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select KDSA050A.COBRO_REL, CONCEPTO_050 from KDSA055A, KDSA050A ";
		$msConsulta .= "where KDSA055A.COBRO_REL = KDSA050A.COBRO_REL and KDSA055A.COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		return $mDatos;
	}
?>