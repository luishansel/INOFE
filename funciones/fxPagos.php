<?php
	function fxGuardarPagos($mdFecha, $msNombre, $msRecibo, $msSerie, $mnMonto, $mnRetDgi, $mnRetAlcaldia, $mnMoneda, $mnTipoCambio, $msConcepto, $mnTipoPago, $msNumeroCk, $msBancoCk, $mbOtroIngreso, $mbEmpresarial, $mbInatec)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(PAGO_REL), 3), 0) as Ultimo from KDSA040A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "PG" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA040A (PAGO_REL, FECHA_040, NOMBRE_040, RECIBO_040, SERIE_040, MONTO_040, RETENCION_DGI_040, RETENCION_ALCALDIA_040, MONEDA_040, TIPOCAMBIO_040, ";
		$msConsulta .= "CONCEPTO_040, TIPOPAGO_040, NUMEROCK_040, BANCOCK_040, OTROINGRESO_040, EMPRESARIAL_040, INATEC_040, ANULADO_040) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $mdFecha, $msNombre, $msRecibo, $msSerie, $mnMonto, $mnRetDgi, $mnRetAlcaldia, $mnMoneda, $mnTipoCambio, $msConcepto, $mnTipoPago, $msNumeroCk, $msBancoCk, $mbOtroIngreso, $mbEmpresarial, $mbInatec]);
		return ($msCodigo);
	}
	
	function fxAnularPagos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "update KDSA040A set ANULADO_040 = 1 where PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		$msConsulta = "select COBRO_REL, MATRICULA_REL, MONTO_041, DESCUENTO_041, MONEDA_040, TIPOCAMBIO_040 from KDSA041A, KDSA040A ";
		$msConsulta .= "where KDSA041A.PAGO_REL = KDSA040A.PAGO_REL and KDSA041A.PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		//Quita la Cancelación del Cobro Individual
		while ($Fila = $mDatos->fetch())
		{
			$msCobro = $Fila["COBRO_REL"];
			$msMatricula = $Fila["MATRICULA_REL"];
			$mnMonedaPago = intval($Fila["MONEDA_040"]);
			$mnTipoCambio = floatval($Fila["TIPOCAMBIO_040"]);
			$mnMonto = floatval($Fila["MONTO_041"]);
			$mnDescuento = floatval($Fila["DESCUENTO_041"]);
			$mnValor = $mnMonto + $mnDescuento;
			
			$msConsulta = "select ABONADO_051, ADEUDADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCobro, $msMatricula]);

			$Auxiliar = $mAuxiliar->fetch();
			$mnAbonado = floatval($Auxiliar["ABONADO_051"]);
			$mnAdeudado = floatval($Auxiliar["ADEUDADO_051"]);

			$msConsulta = "select MONEDA_050 from KDSA050A where COBRO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCobro]);
			$Auxiliar = $mAuxiliar->fetch();
			$mnMonedaCobro = intval($Auxiliar["MONEDA_050"]);

			if ($mnMonedaPago != $mnMonedaCobro)
			{
				if ($mnMonedaPago == 0) //El pago es en córdobas y el cobro en dólares
					$mnValor = round($mnValor / $mnTipoCambio, 2);
				else //El pago es en dólares y el cobro en córdobas
					$mnValor = round($mnValor * $mnTipoCambio, 2);
			}

			$mnAbonado = $mnAbonado - $mnValor;
			$mnAdeudado = $mnAdeudado + $mnValor;

			$msConsulta = "update KDSA051A set PAGADO_051 = 0, ABONADO_051 = ?, ADEUDADO_051 = ? where COBRO_REL = ? and MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mnAbonado, $mnAdeudado, $msCobro, $msMatricula]);
		}
	}
	
	function fxDevuelvePagos($mbLlenaGrid, $msEstudiante = "", $msCodigo = "", $msCurso = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select KDSA040A.PAGO_REL, RECIBO_040, FECHA_040, CONCEPTO_040, MONTO_040, (case MONEDA_040 when 0 then 'Córdobas' else 'Dólares' end) as MONEDA_040, ";
			$msConsulta .= "(case ANULADO_040 when 1 then 'x' else '' end) as ANULADO_040 from KDSA040A where EXISTS(select KDSA030A.MATRICULA_REL ";
			$msConsulta .= "from KDSA030A join KDSA041A on KDSA030A.MATRICULA_REL = KDSA041A.MATRICULA_REL where KDSA041A.PAGO_REL = KDSA040A.PAGO_REL and ";
			$msConsulta .= "KDSA030A.ESTUDIANTE_REL = ? and CURSO_REL = ?) order by KDSA040A.PAGO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msEstudiante, $msCurso]);
		}
		else
		{
			$msConsulta = "select PAGO_REL, FECHA_040, NOMBRE_040, RECIBO_040, SERIE_040, MONTO_040, RETENCION_DGI_040, RETENCION_ALCALDIA_040, MONEDA_040, ";
			$msConsulta .= "TIPOCAMBIO_040, CONCEPTO_040, TIPOPAGO_040, NUMEROCK_040, BANCOCK_040, ANULADO_040 from KDSA040A where PAGO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxGuardarDetPagos($msCodigo, $msCobro, $msMatricula, $mnMonto, $mnDescuento)
	{
		$mnSumaPagosDol = 0;
		$mnSumaPagosCor = 0;
		$mnSumaDescuentosDol = 0;
		$mnSumaDescuentosCor = 0;
		
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA041A (PAGO_REL, COBRO_REL, MATRICULA_REL, MONTO_041, DESCUENTO_041) values(?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCobro, $msMatricula, $mnMonto, $mnDescuento]);
		
		//Obtiene los Pagos realizados del Cobro
		$msConsulta = "select MONEDA_040, TIPOCAMBIO_040, MONTO_041, DESCUENTO_041 from KDSA041A, KDSA040A where KDSA041A.PAGO_REL = KDSA040A.PAGO_REL ";
		$msConsulta .= "and KDSA041A.COBRO_REL = ? and KDSA041A.MATRICULA_REL = ? and ANULADO_040 = 0";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula]);
		
		while ($Fila = $mDatos->fetch())
		{
			$mnMoneda040 = intval($Fila["MONEDA_040"]);
			$mnTipoCambio040 = floatval($Fila["TIPOCAMBIO_040"]);
			
			if ($mnMoneda040 == 0) //Convierte los pagos en córdobas a dólares
			{
				$mnMontoDol = floatval($Fila["MONTO_041"]) / $mnTipoCambio040;
				$mnDescuentoDol = floatval($Fila["DESCUENTO_041"]) / $mnTipoCambio040;

				$mnMontoDol = round($mnMontoDol, 2, PHP_ROUND_HALF_UP);
				$mnDescuentoDol = round($mnDescuentoDol, 2, PHP_ROUND_HALF_UP);

				$mnSumaPagosDol += $mnMontoDol;
				$mnSumaDescuentosDol += $mnDescuentoDol;
			}
			else //Suma los pagos en dólares
			{
				$mnSumaPagosDol += floatval($Fila["MONTO_041"]);
				$mnSumaDescuentosDol += floatval($Fila["DESCUENTO_041"]);
			}

			if ($mnMoneda040 == 1) //Convierte los pagos en dólares a córdobas
			{
				$mnMontoCor = floatval($Fila["MONTO_041"]) * $mnTipoCambio040;
				$mnDescuentoCor = floatval($Fila["DESCUENTO_041"]) * $mnTipoCambio040;

				$mnMontoCor = round($mnMonto041, 2, PHP_ROUND_HALF_UP);
				$mnDescuentoCor = round($mnDescuentoCor, 2, PHP_ROUND_HALF_UP);

				$mnSumaPagosCor += $mnMontoCor;
				$mnSumaDescuentosCor += $mnDescuentoCor;
			}
			else //Suma los pagos en córdobas
			{
				$mnSumaPagosCor += floatval($Fila["MONTO_041"]);
				$mnSumaDescuentosCor += floatval($Fila["DESCUENTO_041"]);
			}
		}

		$TotalPagosDol = $mnSumaPagosDol + $mnSumaDescuentosDol;
		$TotalPagosCor = $mnSumaPagosCor + $mnSumaDescuentosCor;
		
		//Obtiene el Monto original del Cobro
		$msConsulta = "select MONTO_050, TIPO_050, MONEDA_050 from KDSA050A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
		$Fila = $mDatos->fetch();
		$mnTipo = intval($Fila["TIPO_050"]);

		if ($mnTipo == 1) //Cobro moratorio
		{
			$msConsulta = "select ADEUDADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ?";
			$mAux = $m_cnx_MySQL->prepare($msConsulta);
			$mAux->execute([$msCobro, $msMatricula]);
			$FilaAux = $mAux->fetch();
		}

		if ($mnTipo == 1)
			$mnMontoCobro = floatval($FilaAux["ADEUDADO_051"]);
		else
			$mnMontoCobro = floatval($Fila["MONTO_050"]);

		if (intval($Fila["MONEDA_050"]) == 0) //El cobro está en córdobas
			$mnDeuda = $mnMontoCobro - $TotalPagosCor;
		else //El cobro está en dólares
			$mnDeuda = $mnMontoCobro - $TotalPagosDol;
		
		//Abona al Cobro Individual
		$msConsulta = "update KDSA051A set ABONADO_051 = ?, ADEUDADO_051 = ? where COBRO_REL = ? and MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		if (intval($Fila["MONEDA_050"]) == 0)
			$mDatos->execute([$TotalPagosCor, $mnDeuda, $msCobro, $msMatricula]);
		else
			$mDatos->execute([$TotalPagosDol, $mnDeuda, $msCobro, $msMatricula]);
		
		//Verifica la Cancelación del Cobro
		$msConsulta = "select ADEUDADO_051 from KDSA051A where COBRO_REL = ? and KDSA051A.MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula]);
		$Fila = $mDatos->fetch();
		$mnAdeudado = floatval($Fila["ADEUDADO_051"]);
		if ($mnAdeudado <= 0)
		{
			$msConsulta = "update KDSA051A set PAGADO_051 = 1 where COBRO_REL = ? and MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCobro, $msMatricula]);
		}
	}
	
	function fxDevuelveDetPagos($msCodigo, $msEstudiante)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($msCodigo == "")
		{
			$msConsulta = "select CONCEPTO_050, ADEUDADO_051 as DEUDA, 0 as PAGO, 0 as DESCUENTO, KDSA051A.COBRO_REL, FECHAPREVISTA_050, KDSA051A.MATRICULA_REL from KDSA051A, KDSA050A, KDSA030A where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ESTADO_030 <> 4 and KDSA030A.ESTUDIANTE_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msEstudiante]);
		}
		else
		{
			$msConsulta = "select CONCEPTO_050, 0 as DEUDA, MONTO_041 as PAGO, DESCUENTO_041 as DESCUENTO, KDSA041A.COBRO_REL, '1900-01-01' as FECHAPREVISTA_050, KDSA041A.MATRICULA_REL from KDSA041A, KDSA050A, KDSA030A where KDSA041A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA041A.COBRO_REL = KDSA050A.COBRO_REL and KDSA030A.ESTUDIANTE_REL = ? and KDSA041A.PAGO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msEstudiante, $msCodigo]);
		}

		return $mDatos;
	}
	
	function fxAnularOtrosIngresos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA040A set ANULADO_040 = 1 where PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveOtrosIngresos()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "select KDSA040A.PAGO_REL, RECIBO_040, FECHA_040, NOMBRE_040, CONCEPTO_040, MONTO_040, (case MONEDA_040 when 0 then 'Córdobas' else 'Dólares' end) as MONEDA_040, (case ANULADO_040 when 1 then 'x' else '' end) as ANULADO_040 from KDSA040A where OTROINGRESO_040 = 1 order by KDSA040A.PAGO_REL desc";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		return $mDatos;
	}
	
	function fxDevuelvePagosEmpresa()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "select KDSA040A.PAGO_REL, FECHA_040, NOMBRE_040, CONCEPTO_040, MONTO_040, (case MONEDA_040 when 0 then 'Córdobas' else 'Dólares' end) as MONEDA_040, (case ANULADO_040 when 1 then 'x' else '' end) as ANULADO_040 from KDSA040A where EMPRESARIAL_040 = 1 order by KDSA040A.PAGO_REL desc";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		return $mDatos;
	}
	
	function fxAnularPagosEmpresa($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA040A set ANULADO_040 = 1 where PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		$msConsulta = "select COBRO_REL, MONTO_042, RETENCION_DGI_042, RETENCION_ALCALDIA_042 from KDSA042A where PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		//Quita la Cancelación del Cobro Empresarial
		while ($Fila = $mDatos->fetch())
		{
			$msCobro = $Fila["COBRO_REL"];
			$mnMonto = $Fila["MONTO_042"];
			$mnRetDgi = $Fila["RETENCION_DGI_042"];
			$mnRetAlcaldia = $Fila["RETENCION_ALCALDIA_042"];
			$mnValor = $mnMonto + $mnRetDgi + $mnRetAlcaldia;
			
			$msConsulta = "update KDSA052A set PAGADO_052 = 0, ABONADO_052 = ABONADO_052 - ? where COBRO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mnValor, $msCobro]);
		}
	}
	
	function fxGuardarDetPagosEmp($msCodigo, $msCobro, $mnMonto, $mnRetDgi, $mnRetAlcaldia)
	{
		$mnSumaPagosDol = 0;
		$mnSumaPagosCor = 0;
		$mnSumaRetDgiDol = 0;
		$mnSumaRetDgiCor = 0;
		$mnSumaRetAlcaldiaDol = 0;
		$mnSumaRetAlcaldiaCor = 0;
		
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA042A (PAGO_REL, COBRO_REL, MONTO_042, RETENCION_DGI_042, RETENCION_ALCALDIA_042) values(?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCobro, $mnMonto, $mnRetDgi, $mnRetAlcaldia]);
		
		//Obtiene los Pagos realizados del Cobro
		$msConsulta = "select MONEDA_040, TIPOCAMBIO_040, MONTO_042, RETENCION_DGI_042, RETENCION_ALCALDIA_042 from KDSA042A, KDSA040A where KDSA042A.PAGO_REL = KDSA040A.PAGO_REL and KDSA042A.COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);

		while ($Fila = $mDatos->fetch())
		{
			$mnMoneda040 = intval($Fila["MONEDA_040"]);
			$mnTipoCambio040 = floatval($Fila["TIPOCAMBIO_040"]);

			if ($mnMoneda040 == 0) //Convierte los pagos en córdobas a dólares
			{
				$mnMontoDol = floatval($Fila["MONTO_042"]) / $mnTipoCambio040;
				$mnRetDgiDol = floatval($Fila["RETENCION_DGI_042"]) / $mnTipoCambio040;
				$mnRetAlcaldiaDol = floatval($Fila["RETENCION_ALCALDIA_042"]) / $mnTipoCambio040;

				$mnMontoDol = round($mnMontoDol, 2, PHP_ROUND_HALF_UP);
				$mnRetDgiDol = round($mnRetDgiDol, 2, PHP_ROUND_HALF_UP);
				$mnRetAlcaldiaDol = round($mnRetAlcaldiaDol, 2, PHP_ROUND_HALF_UP);

				$mnSumaPagosDol += $mnMontoDol;
				$mnSumaRetDgiDol += $mnRetDgiDol;
				$mnSumaRetAlcaldiaDol += $mnRetAlcaldiaDol;
			}
			else //Suma los pagos en dólares
			{
				$mnSumaPagosDol += floatval($Fila["MONTO_042"]);
				$mnSumaRetDgiDol += floatval($Fila["RETENCION_DGI_042"]);
				$mnSumaRetAlcaldiaDol += floatval($Fila["RETENCION_ALCALDIA_042"]);
			}

			if ($mnMoneda040 == 1) //Convierte los pagos en dólares a córdobas
			{
				$mnMontoCor = floatval($Fila["MONTO_042"]) * $mnTipoCambio040;
				$mnRetDgiCor = floatval($Fila["RETENCION_DGI_042"]) * $mnTipoCambio040;
				$mnRetAlcaldiaCor = floatval($Fila["RETENCION_ALCALDIA_042"]) * $mnTipoCambio040;

				$mnMontoCor = round($mnMontoCor, 2, PHP_ROUND_HALF_UP);
				$mnRetDgiCor = round($mnRetDgiCor, 2, PHP_ROUND_HALF_UP);
				$mnRetAlcaldiaCor = round($mnRetAlcaldiaCor, 2, PHP_ROUND_HALF_UP);

				$mnSumaPagosCor += $mnMontoCor;
				$mnSumaRetDgiCor += $mnRetDgiCor;
				$mnSumaRetAlcaldiaCor += $mnRetAlcaldiaCor;
			}
			else //Suma los pagos en córdobas
			{
				$mnSumaPagosCor += floatval($Fila["MONTO_042"]);
				$mnSumaRetDgiCor += floatval($Fila["RETENCION_DGI_042"]);
				$mnSumaRetAlcaldiaCor += floatval($Fila["RETENCION_ALCALDIA_042"]);
			}
		}

		$TotalPagosDol = $mnSumaPagosDol + $mnSumaRetDgiDol + $mnSumaRetAlcaldiaDol;
		$TotalPagosCor = $mnSumaPagosCor + $mnSumaRetDgiCor + $mnSumaRetAlcaldiaCor;
		
		//Obtiene el Monto original del Cobro
		$msConsulta = "select MONTO_050, MONEDA_050 from KDSA050A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
		$Fila = $mDatos->fetch();
		$mnMontoCobro = $Fila["MONTO_050"];
		
		if (intval($Fila["MONEDA_050"]) == 0) //El cobro está en córdobas
			$mnDeuda = $mnMontoCobro - $TotalPagosCor;
		else //El cobro está en dólares
			$mnDeuda = $mnMontoCobro - $TotalPagosDol;
		
		//Abona al Cobro Empresarial
		$msConsulta = "update KDSA052A set ABONADO_052 = ? where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		if (intval($Fila["MONEDA_050"]) == 0)
			$mDatos->execute([$TotalPagosCor, $msCobro]);
		else
			$mDatos->execute([$TotalPagosDol, $msCobro]);

		//Verifica la Cancelación del Cobro
		if ($mnDeuda <= 0)
		{
			$msConsulta = "update KDSA052A set PAGADO_052 = 1 where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCobro]);
		}
	}
	
	function fxDevuelveDetPagosEmpresa($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($msCodigo == "")
		{
			$msConsulta = "select CONCEPTO_050, MONTO_050 - ifnull(ABONADO_052,0) as DEUDA, 0 as PAGO, 0 as RET_DGI, 0 as RET_ALCADIA, DEUDOR_052, KDSA052A.COBRO_REL from KDSA052A, KDSA050A where PAGADO_052 = 0 and EXONERADO_052 = 0 and ANULADO_052 = 0 and KDSA052A.COBRO_REL = KDSA050A.COBRO_REL";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select CONCEPTO_050, 0 as DEUDA, MONTO_042 as PAGO, RETENCION_DGI_042 as RET_DGI, RETENCION_ALCALDIA_042 as RET_ALCADIA, '' as DEUDOR_052, KDSA042A.COBRO_REL from KDSA042A, KDSA050A where KDSA042A.COBRO_REL = KDSA050A.COBRO_REL and KDSA042A.PAGO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
	
	function fxAnularPagosInatec($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA040A set ANULADO_040 = 1 where PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);

		$msConsulta = "select COBRO_REL, COBROINATEC_REL, MATRICULA_REL, MONTO_043, MONEDA_040, TIPOCAMBIO_040 from KDSA043A, KDSA040A where KDSA043A.PAGO_REL = KDSA040A.PAGO_REL and KDSA043A.PAGO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		//Quita la Cancelación del Cobro Individual y el Cobro INATEC
		while ($Fila = $mDatos->fetch())
		{
			$msCobro = $Fila["COBRO_REL"];
			$msCobroInatec = $Fila["COBROINATEC_REL"];
			$msMatricula = $Fila["MATRICULA_REL"];
			$mnMoneda = $Fila["MONEDA_040"];
			$mnTipoCambio = $Fila["TIPOCAMBIO_040"];
			$mnMonto = $Fila["MONTO_041"];
			
			if ($mnMoneda == 0)
				$mnValor = $mnMonto / $mnTipoCambio;
			
			$msConsulta = "update KDSA051A set PAGADO_051 = 0, ABONADO_051 = ABONADO_051 - ?, ADEUDADO_051 = ADEUDADO_051 + ? where COBRO_REL = ? and MATRICULA_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mnValor, $mnValor, $msCobro, $msMatricula]);

			$msConsulta = "update KDSA054A set PAGADO_054 = 0, ABONADO_054 = ABONADO_054 - ?, ADEUDADO_054 = ADEUDADO_054 + ? where COBROINATEC_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mnMonto, $mnMonto, $msCobroInatec]);
		}
	}

	function fxGuardarDetPagosInatec($msCodigo, $msCobroInatec, $msMatricula, $msCobro, $mnMonto, $mnRetDgi, $mnRetAlcaldia)
	{
		//Los Pagos de INATEC cancelan Cobros Individuales y Cobros de INATEC
		$mnSumaPagos = 0;
		$mnSumaRetencion = 0;
		
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA043A (COBRO_REL, MATRICULA_REL, COBROINATEC_REL, PAGO_REL, MONTO_043, RETENCION_DGI_043, RETENCION_ALCALDIA_043) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro, $msMatricula, $msCobroInatec, $msCodigo, $mnMonto, $mnRetDgi, $mnRetAlcaldia]);
		
		//Obtiene los Pagos realizados del Cobro Individual
		$msConsulta = "select MONEDA_040, TIPOCAMBIO_040, MONTO_043, RETENCION_DGI_043, RETENCION_ALCALDIA_043 from KDSA043A, KDSA040A where KDSA043A.PAGO_REL = KDSA040A.PAGO_REL and KDSA043A.COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);

		while ($Fila = $mDatos->fetch())
		{
			$mnMoneda040 = $Fila["MONEDA_040"];
			$mnTipoCambio040 = $Fila["TIPOCAMBIO_040"];
			$mnMonto043 = $Fila["MONTO_043"];
			$mnRetDgi043 = $Fila["RETENCION_DGI_043"];
			$mnRetAlcaldia043 = $Fila["RETENCION_ALCALDIA_043"];
			
			if ($mnMoneda040 == 0)
			{
				$mnMonto043 = $Fila["MONTO_043"] / $mnTipoCambio040;
				$mnRetDgi043 = $Fila["RETENCION_DGI_043"] / $mnTipoCambio040;
				$mnRetAlcaldia043 = $Fila["RETENCION_ALCALDIA_043"] / $mnTipoCambio040;
			}

			$mnSumaPagos += $mnMonto043;
			$mnSumaRetencion += $mnRetDgi043;
			$mnSumaRetencion += $mnRetAlcaldia043;
		}
		$TotalPagos = $mnSumaPagos + $mnSumaRetencion;
		
		//Obtiene el Monto original del Cobro Individual
		$msConsulta = "select MONTO_050 from KDSA050A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobro]);
		$Fila = $mDatos->fetch();
		$mnMontoCobro = $Fila["MONTO_050"];
		
		//Abona al Cobro Individual
		$msConsulta = "update KDSA051A set ABONADO_051 = ? where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$TotalPagos, $msCobro]);

		//Verifica la Cancelación del Cobro Individual
		if ($TotalPagos >= $mnMontoCobro)
		{
			$msConsulta = "update KDSA051A set PAGADO_051 = 1 where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCobro]);
		}
		
		//Obtiene el Monto original del Cobro INATEC
		$msConsulta = "select MONTO_050 from KDSA050A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobroInatec]);
		$Fila = $mDatos->fetch();
		$mnMontoCobro = $Fila["MONTO_050"];
		
		//Abona al Cobro INATEC
		$msConsulta = "update KDSA054A set ABONADO_054 = ABONADO_054 + ? where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$TotalPagos, $msCobroInatec]);

		//Verifica la Cancelación del Cobro INATEC
		$msConsulta = "select ABONADO_054 from KDSA054A where COBROINATEC_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCobroInatec]);
		$Fila = $mDatos->fetch();
		$TotalPagos = $Fila["ABONADO_054"];
		
		if ($TotalPagos >= $mnMontoCobro)
		{
			$msConsulta = "update KDSA054A set PAGADO_054 = 1 where COBROINATEC_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCobroInatec]);
		}
	}
	
	function fxDevuelvePagosInatec()
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		$msConsulta = "select KDSA040A.PAGO_REL, RECIBO_040, FECHA_040, NOMBRE_040, CONCEPTO_040, MONTO_040, (case MONEDA_040 when 0 then 'Córdobas' else 'Dólares' end) as MONEDA_040, (case ANULADO_040 when 1 then 'x' else '' end) as ANULADO_040 from KDSA040A where INATEC_040 = 1 order by KDSA040A.PAGO_REL desc";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		return $mDatos;
	}
	
	function fxDevuelveDetPagosInatec($msPago, $msCobro)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($msPago == "")
		{
			$msConsulta = "select KDSA051A.COBRO_REL, KDSA051A.MATRICULA_REL, KDSA051A.ADEUDADO_051 as DEUDA, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, NOMBRE_020, B.CONCEPTO_050, 0 as MONTO, 0 as RET_DGI, 0 as RET_ALCALDIA ";
			$msConsulta .= "from KDSA051A, KDSA030A, KDSA010A, KDSA020A, KDSA050A A, KDSA050A B, KDSA055A where A.COBRO_REL = KDSA055A.COBRO_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
			$msConsulta .= "and KDSA020A.CURSO_REL = A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and ANULADO_051 = 0 ";
			$msConsulta .= "and PAGADO_051 = 0 and EXONERADO_051 = 0 and KDSA051A.COBRO_REL = B.COBRO_REL and INATEC_030 = 1 and COBROINATEC_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCobro]);
		}
		else
		{
			$msConsulta = "select KDSA043A.COBRO_REL, KDSA043A.MATRICULA_REL, 0 as DEUDA, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, NOMBRE_020, CONCEPTO_050, MONTO_043 as MONTO, RETENCION_DGI_043 as RET_DGI, RETENCION_ALCALDIA_043 as RET_ALCALDIA ";
			$msConsulta .= "from KDSA043A, KDSA050A, KDSA030A, KDSA010A, KDSA020A where KDSA043A.COBRO_REL = KDSA050A.COBRO_REL and KDSA043A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA043A.PAGO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msPago]);
		}

		return $mDatos;
	}
?>