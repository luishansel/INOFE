<?php
	function fxGuardarCobroIndividual($msCobro, $msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select MONTO_050 from KDSA050A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
		$Fila = $mDatos->fetch();
		$mnMonto = $Fila["MONTO_050"];
		$msConsulta = "insert into KDSA051A (COBRO_REL, MATRICULA_REL, ADEUDADO_051, ABONADO_051, PAGADO_051, EXONERADO_051, ANULADO_051) ";
		$msConsulta .= "values(?, ?, ?, 0, 0, 0, 0)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula, $mnMonto]);
	}
	
	function fxPagarCobroIndividual($msCobro, $msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA051A set PAGADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula]);
	}
	
	function fxExonerarCobroIndividual($msCobro, $msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA051A set EXONERADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula]);
	}
	
	function fxAnularCobroIndividual($msCobro, $msMatricula)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA051A set ANULADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula]);
	}
	
	function fxDevuelveCobroIndividual($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select * from (select KDSA051A.COBRO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as NOMBRE_020, CONCEPTO_050, FECHAPREVISTA_050, MONTO_050 as MONTO, ADEUDADO_051, (case PAGADO_051 when 1 then 'x' else '' end) as PAGADO_051, ";
		$msConsulta .= "(case EXONERADO_051 when 1 then 'x' else '' end) as EXONERADO_051, (case ANULADO_051 when 1 then 'x' else '' end) as ANULADO_051 ";
		$msConsulta .= "from KDSA051A, KDSA050A, KDSA020A, KDSA030A where KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and KDSA050A.CURSO_REL = KDSA020A.CURSO_REL ";
		$msConsulta .= "and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.MATRICULA_REL = ? and TIPO_050 <> 1 ";
		$msConsulta .= "union ";
		$msConsulta .= "select KDSA051A.COBRO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as NOMBRE_020, CONCEPTO_050, FECHAPREVISTA_050, ADEUDADO_051 + ABONADO_051 as MONTO, ADEUDADO_051, (case PAGADO_051 when 1 then 'x' else '' end) as PAGADO_051, ";
		$msConsulta .= "(case EXONERADO_051 when 1 then 'x' else '' end) as EXONERADO_051, (case ANULADO_051 when 1 then 'x' else '' end) as ANULADO_051 ";
		$msConsulta .= "from KDSA051A, KDSA050A, KDSA020A, KDSA030A where KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and KDSA050A.CURSO_REL = KDSA020A.CURSO_REL ";
		$msConsulta .= "and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.MATRICULA_REL = ? and TIPO_050 = 1) as A order by A.COBRO_REL desc";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCodigo]);
		return $mDatos;
	}
?>