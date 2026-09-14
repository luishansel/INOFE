<?php
	function fxGuardarCobros($msCurso, $mdFecha, $msConcepto, $mnMonto, $mnMoneda, $mnTipo, $mbActivo, $msCodCuota = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(COBRO_REL), 3), 0) as Ultimo from KDSA050A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "CO" . str_repeat("0", 7 - $Longitud) . trim($Numero);
		
		if ($msCodCuota == "")
		{
			$msConsulta = "insert into KDSA050A (COBRO_REL, CURSO_REL, FECHAPREVISTA_050, CONCEPTO_050, MONTO_050, MONEDA_050, TIPO_050, ACTIVO_050, ANULADO_050) ";
			$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, 0)";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo, $msCurso, $mdFecha, $msConcepto, $mnMonto, $mnMoneda, $mnTipo, $mbActivo]);

			if ($mnTipo == 0 or $mnTipo == 5)
			{
				//Aplica retroactivamente el Cobro a los Alumnos que ya han sido matriculados en el Curso
				$msConsulta = "select MATRICULA_REL from KDSA030A where CURSO_REL = ? and ESTADO_030 = 0";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msCurso]);
				
				while ($Fila = $mDatos->fetch())
				{
					$msMatricula = $Fila["MATRICULA_REL"];
					$msConsulta = "insert into KDSA051A (COBRO_REL, MATRICULA_REL, ADEUDADO_051, ABONADO_051, PAGADO_051, EXONERADO_051, ANULADO_051) ";
					$msConsulta .= "values (?, ?, ?, 0, 0, 0, 0)";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$msCodigo, $msMatricula, $mnMonto]);
          fxAgregarBitacora('admon', 'KDSA051A', $msCodigo, $msMatricula, 'Agregar');
				}
			}
		}
		else
		{
			$msConsulta = "insert into KDSA050A (COBRO_REL, KDS_COBRO_REL, CURSO_REL, FECHAPREVISTA_050, CONCEPTO_050, MONTO_050, MONEDA_050, TIPO_050, ACTIVO_050, ANULADO_050) ";
			$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo, $msCodCuota, $msCurso, $mdFecha, $msConcepto, $mnMonto, $mnMoneda, $mnTipo, $mbActivo]);
		}

		return ($msCodigo);
	}
	
	function fxModificarCobros($msCodigo, $msCurso, $mdFecha, $msConcepto, $mnMonto, $mnMoneda, $mnTipo, $mbActivo, $msCodCuota = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		if ($msCodCuota == "")
		{
			$msConsulta = "update KDSA050A set CURSO_REL = ?, FECHAPREVISTA_050 = ?, CONCEPTO_050 = ?, MONTO_050 = ?, MONEDA_050 = ?, TIPO_050 = ?, ACTIVO_050 = ? where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso, $mdFecha, $msConcepto, $mnMonto, $mnMoneda, $mnTipo, $mbActivo, $msCodigo]);

			//Modifica el valor en los Cobros individuales
			$msConsulta = "select MATRICULA_REL, ABONADO_051 from KDSA051A where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
			while ($Fila = $mDatos->fetch())
			{
				$msMatricula = $Fila["MATRICULA_REL"];
				$mnAbonado = $Fila["ABONADO_051"];
				$mnAdeudado = $mnMonto - $mnAbonado;

				$msConsulta = "update KDSA051A set ADEUDADO_051 = ? where COBRO_REL = ? and MATRICULA_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$mnAdeudado, $msCodigo, $msMatricula]);

				if ($mnAdeudado > 0)
				{
					$msConsulta = "update KDSA051A set PAGADO_051 = 0 where COBRO_REL = ? and MATRICULA_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$msCodigo, $msMatricula]);
				}
			}

			if ($mnTipo == 0 or $mnTipo == 6)
			{
				//Modifica la fecha de la mora para las cuotas y las cuotas especiales
				$NvaFecha = date("Y-m-d", strtotime($mdFecha . "+1 days"));
				$msConsulta = "Update KDSA050A set FECHAPREVISTA_050 = ? where KDS_COBRO_REL = ?";
				$mAuxMora = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxMora->execute([$NvaFecha, $msCodigo]);
			}
		}
		else
		{
			$msConsulta = "update KDSA050A set KDS_COBRO_REL = ?, CURSO_REL = ?, FECHAPREVISTA_050 = ?, CONCEPTO_050 = ?, MONTO_050 = ?, MONEDA_050 = ?, TIPO_050 = ?, ACTIVO_050 = ? where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodCuota, $msCurso, $mdFecha, $msConcepto, $mnMonto, $mnMoneda, $mnTipo, $mbActivo, $msCodigo]);
		}
	}
	
	function fxBorrarCobros($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA050A where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxAnularCobros($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA050A set ANULADO_050 = 1 where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		//Anula los Cobros individuales relacionados
		$msConsulta = "update KDSA051A set ANULADO_051 = 1 where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		//Anula los Cobros por Mora relacionados
		$msConsulta = "update KDSA051A set ANULADO_051 = 1 where KDS_COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		
		//Anula los Cobros empresariales relacionados
		$msConsulta = "update KDSA052A set ANULADO_052 = 1 where COBRO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveCobros($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select COBRO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, CONCEPTO_050, (case TIPO_050 when 0 then 'Cuota' when 1 then 'Moratorio' when 2 then 'Matrícula' when 3 then 'Empresarial' when 4 then 'INATEC' when 5 then 'Certificado' when 6 then 'Cuota especial' end) as TIPO_050, FECHAPREVISTA_050, MONTO_050, (case ACTIVO_050 when 1 then 'x' else '' end) as ACTIVO_050, (case ANULADO_050 when 1 then 'x' else '' end) as ANULADO_050 from KDSA050A, KDSA020A where KDSA050A.CURSO_REL = KDSA020A.CURSO_REL order by COBRO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select COBRO_REL, KDS_COBRO_REL, CURSO_REL, FECHAPREVISTA_050, CONCEPTO_050, MONTO_050, MONEDA_050, TIPO_050, ACTIVO_050, ANULADO_050 from KDSA050A where COBRO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}
?>