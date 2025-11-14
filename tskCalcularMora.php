<?php
	require_once ("funciones/fxGeneral.php");
	$m_cnx_MySQL = fxAbrirConexion();
	$mFechHoy = date("Y-m-d");
	$msConsulta = "select KDSA051A.COBRO_REL, MATRICULA_REL, FECHAPREVISTA_050 from KDSA051A, KDSA050A ";
	$msConsulta .= "where ABONADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ";
	$msConsulta .= "KDSA050A.TIPO_050 in (0, 6) and KDSA050A.ANULADO_050 = 0 and KDSA050A.ACTIVO_050 = 1 and FECHAPREVISTA_050 < ?";
	
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mFechHoy]);
	while ($Fila = $mDatos->fetch())
	{
		$Cobro = $Fila["COBRO_REL"];
		$Matricula = $Fila["MATRICULA_REL"];
		$Fecha = date_create($Fila["FECHAPREVISTA_050"]);
		$FechaHoy = date_create(date("Y-m-d"));
		$arrDiferencia = date_diff($Fecha, $FechaHoy);
		$dias = $arrDiferencia->days;
		
		$msConsulta = "select COBRO_REL, MONTO_050 from KDSA050A where KDS_COBRO_REL = ? and ANULADO_050 = 0 ";
		$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
		$mDatosAux->execute([$Cobro]);
		$mnRegistros = $mDatosAux->rowCount();

		if ($mnRegistros > 0)
		{
			$FilaAux = $mDatosAux->fetch();
			$Mora = $FilaAux["COBRO_REL"];
			$Monto = floatval($FilaAux["MONTO_050"]);
			$MontoDia = floatval($FilaAux["MONTO_050"]) / 30; //Mora de un día
			$MontoMora = $MontoDia * $dias;
		}
		else
		{
			$Mora = "";
			$Monto = 0;
			$MontoDia = 0; //Mora de un día
			$MontoMora = 0;
		}

		$msConsulta = "select MATRICULA_REL, ANULADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ?";
		$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
		$mDatosAux->execute([$Mora, $Matricula]);
		$mnRegistros = $mDatosAux->rowCount();
		
		if ($mnRegistros == 0)
		{
			if ($Mora != "")
			{
				$msConsulta = "insert into KDSA051A (COBRO_REL, MATRICULA_REL, ADEUDADO_051, ABONADO_051, PAGADO_051, EXONERADO_051, ANULADO_051) ";
				$msConsulta .= "values(?, ?, ?, 0, 0, 0, 0)";
				$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
				$mDatosAux->execute([$Mora, $Matricula, $MontoMora]);
			
				fxAgregarBitacora ("admon", "KDSA051A", $Mora, $Matricula, "Agregar Mora");
			}
		}
		else
		{
			$FilaAux = $mDatosAux->fetch();
			$mbAnulado = $FilaAux["ANULADO_051"];
		
			if ($mbAnulado == 0)
			{
				$msConsulta = "select ADEUDADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ?";
				$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
				$mDatosAux->execute([$Mora, $Matricula]);
				$FilaAux = $mDatosAux->fetch();
				$mnAdeudado = floatval($FilaAux["ADEUDADO_051"]);
				
				if ($dias < 30)
				{
					$msConsulta = "update KDSA051A set ADEUDADO_051 = ? where COBRO_REL = ? and MATRICULA_REL = ?";
					$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
					$mDatosAux->execute([$MontoMora, $Mora, $Matricula]);
				}
                else
				{
                    $msConsulta = "update KDSA051A set ADEUDADO_051 = ? where COBRO_REL = ? and MATRICULA_REL = ?";
					$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
					$mDatosAux->execute([$Monto, $Mora, $Matricula]);
				}
			}
			else
			{
                if ($dias < 30)
				{
				    $msConsulta = "update KDSA051A set ADEUDADO_051 = ?, ANULADO_051 = 0 where COBRO_REL = ? and MATRICULA_REL = ?";
					$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
					$mDatosAux->execute([$MontoMora, $Mora, $Matricula]);
				}
                else
				{
                    $msConsulta = "update KDSA051A set ADEUDADO_051 = ?, ANULADO_051 = 0 where COBRO_REL = ? and MATRICULA_REL = ?";
					$mDatosAux = $m_cnx_MySQL->prepare($msConsulta);
					$mDatosAux->execute([$Monto, $Mora, $Matricula]);
				}  
			}

			fxAgregarBitacora ("admon", "KDSA051A", $Mora, $Matricula, "Modificar Mora");
		}
	}
?>