<?php
require_once ("funciones/fxGeneral.php");
$m_cnx_MySQL = fxAbrirConexion();

//Obtiene los cursos activos
$msConsulta = "select CURSO_REL, MORA_020, CUOTA_020 from KDSA020A where ACTIVO_020 = 1";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute();

while ($mrFila = $mDatos->fetch())
{
	$msCodCurso = $mrFila["CURSO_REL"];
	$mnPorcentajeMora = intval($mrFila["MORA_020"]);
	$mnCuota = floatval($mrFila["CUOTA_020"]);
	$mnMoraMensual = round($mnCuota * ($mnPorcentajeMora / 100), 2);
	$mnMoraDiaria = round($mnMoraMensual / 30, 2);

	//Obtiene los estudiantes del curso
	$msConsulta = "select MATRICULA_REL from KDSA030A where CURSO_REL = ?";
	$mMatriculados = $m_cnx_MySQL->prepare($msConsulta);
	$mMatriculados->execute([$msCodCurso]);

	while ($mrMatricula = $mMatriculados->fetch())
	{
		$msMatricula = $mrMatricula["MATRICULA_REL"];

		//Obtiene los cobros del matriculado
		$msConsulta = "select COBRO_REL from KDSA051A where MATRICULA_REL = ? and ABONADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0";
		$mCobros = $m_cnx_MySQL->prepare($msConsulta);
		$mCobros->execute([$msMatricula]);

		//Verifica el tipo de cobro
		while ($mrCobros = $mCobros->fetch())
		{
			$msCobro = $mrCobros["COBRO_REL"];
			
			$msConsulta = "select FECHAPREVISTA_050, TIPO_050 from KDSA050A where COBRO_REL = ? and ACTIVO_050 = 1 and ANULADO_050 = 0";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCobro]);

			while ($mrAuxiliar = $mAuxiliar->fetch())
			{
				$msFechaPrevista = $mrAuxiliar["FECHAPREVISTA_050"];
				$mdFechaPrevista = new DateTime($msFechaPrevista);
				$mdFechaHoy = new DateTime("now");
				$mDiferencia = $mdFechaHoy->diff($mdFechaPrevista);
				$mnDias = $mDiferencia->days;
				$mnTipo = intval($mrAuxiliar["TIPO_050"]);

				//Sólo cobros de cuota
				if ($mnTipo == 0 or $mnTipo == 6)
				{
					//La Fecha Prevista ya pasó
					if ($mdFechaHoy > $mdFechaPrevista)
					{
						if ($mnDias >= 30)
							$mnMoraHoy = $mnMoraMensual;
						else
							$mnMoraHoy = $mnMoraDiaria * $mnDias;

						//Obtiene el código del cobro de la mora
						$msConsulta = "Select COBRO_REL from KDSA050A where KDS_COBRO_REL = ?";
						$mCodigoMora = $m_cnx_MySQL->prepare($msConsulta);
						$mCodigoMora->execute([$msCobro]);
						$mrCodigoMora = $mCodigoMora->fetch();
						$msCodMora = $mrCodigoMora["COBRO_REL"];

						//Obtiene el registro de la mora
						$msConsulta = "select COBRO_REL, ABONADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ? and PAGADO_051 = 0";
						$mExisteMora = $m_cnx_MySQL->prepare($msConsulta);
						$mExisteMora->execute([$msCodMora, $msMatricula]);
						$mnRegistros = $mExisteMora->rowCount();

						if ($mnRegistros == 0)
						{
							$msConsulta = "insert into KDSA051A (COBRO_REL, MATRICULA_REL, ADEUDADO_051, ABONADO_051, PAGADO_051, EXONERADO_051, ANULADO_051)";
							$msConsulta .= "values (?, ?, ?, ?, ?, ?, ?)";
							$mMoratorio = $m_cnx_MySQL->prepare($msConsulta);
							$mMoratorio->execute([$msCodMora, $msMatricula, $mnMoraHoy, 0, 0, 0, 0]);
						}
						else
						{
							$mrMoratorio = $mExisteMora->fetch();
							$mnAbonado = floatval($mrMoratorio["ABONADO_051"]);
							if ($mnAbonado > 0)
								$mnMoraHoy = $mnMoraHoy - $mnAbonado;

							$msConsulta = "update KDSA051A set ADEUDADO_051 = ? where COBRO_REL = ? and MATRICULA_REL = ?";
							$mMoratorio = $m_cnx_MySQL->prepare($msConsulta);
							$mMoratorio->execute([$mnMoraHoy, $msCodMora, $msMatricula]);
						}
					}
				}
			}
		}
	}
}
?>