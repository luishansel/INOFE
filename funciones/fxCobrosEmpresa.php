<?php
	function fxGuardarCobroEmpresa($msCobro, $msDeudor)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA052A (COBRO_REL, DEUDOR_052, ABONADO_052, PAGADO_052, EXONERADO_052, ANULADO_052) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msDeudor, 0, 0, 0, 0]);
	}
	
	function fxModificarCobroEmpresa($msCobro, $msDeudor)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA052A set DEUDOR_052 = ? where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msDeudor, $msCobro]);
	}
	
	function fxPagarCobroEmpresa($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA052A set PAGADO_052 = 1 where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxExonerarCobroEmpresa($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA052A set EXONERADO_052 = 1 where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxAnularCobroEmpresa($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA052A set ANULADO_052 = 1 where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxDevuelveCobroEmpresa($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();

		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select KDSA052A.COBRO_REL, DEUDOR_052, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ')') as NOMBRE_020, MONTO_050, ";
			$msConsulta .= "(case PAGADO_052 when 1 then 'x' else '' end) as PAGADO_052, (case EXONERADO_052 when 1 then 'x' else '' end) as EXONERADO_052, ";
			$msConsulta .= "(case ANULADO_052 when 1 then 'x' else '' end) as ANULADO_052 ";
			$msConsulta .= "from KDSA052A, KDSA050A, KDSA020A where KDSA052A.COBRO_REL = KDSA050A.COBRO_REL and KDSA050A.CURSO_REL = KDSA020A.CURSO_REL ";
			$msConsulta .= "order by KDSA052A.COBRO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select COBRO_REL, DEUDOR_052, ABONADO_052, PAGADO_052, EXONERADO_052, ANULADO_052 from KDSA052A where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxGuardarDetCobroEmpresa($msCobro, $msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA053A (COBRO_REL, MATRICULA_REL) values (?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula]);
	}
	
	function fxBorrarDetCobroEmpresa($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA053A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
	}
	
	function fxDevuelveDetCobroEmpresa($msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select KDSA053A.MATRICULA_REL, concat(trim(APELLIDOS_010), ', ', trim(NOMBRES_010)) as ESTUDIANTE from KDSA053A, KDSA030A, KDSA010A ";
		$msConsulta .= "where KDSA053A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA053A.COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
		return $mDatos;
	}
?>