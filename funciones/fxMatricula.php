<?php
	function fxGuardarMatricula($msEstudiante, $msCurso, $mnTipoAsistencia, $mdFecha, $mnDescuento, $msMotivo, $msMedio, $mnFuenteIngreso, $mbPrimeraVez, $mbBecado, $msBecadoPor, $mbInatec, $mbDocIdentidad, $mbDocAcademico, $mnEstado, $mbCertDigital)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(MATRICULA_REL), 3), 0) as Ultimo from KDSA030A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "MT" . str_repeat("0", 8 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA030A (MATRICULA_REL, ESTUDIANTE_REL, CURSO_REL, TIPOASISTENCIA_030, FECHA_030, DESCUENTO_030, MOTIVO_030, MEDIO_030, FUENTEINGRESO_030, PRIMERAVEZ_030, BECADO_030, BECADOPOR_030, INATEC_030, DOCIDENTIDAD_030, DOCACADEMICO_030, ESTADO_030, CERTDIGITAL_030) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msEstudiante, $msCurso, $mnTipoAsistencia, $mdFecha, $mnDescuento, $msMotivo, $msMedio, $mnFuenteIngreso, $mbPrimeraVez, $mbBecado, $msBecadoPor, $mbInatec, $mbDocIdentidad, $mbDocAcademico, $mnEstado, $mbCertDigital]);
		return ($msCodigo);
	}
	
	function fxModificarMatricula($msCodigo, $msEstudiante, $msCurso, $mnTipoAsistencia, $mdFecha, $mnDescuento, $msMotivo, $msMedio, $mnFuenteIngreso, $mbPrimeraVez, $mbBecado, $msBecadoPor, $mbInatec, $mbDocIdentidad, $mbDocAcademico, $mbCertDigital)
	{
		$m_cnx_MySQL = fxAbrirConexion();

		$msConsulta = "update KDSA030A set ESTUDIANTE_REL = ?, CURSO_REL = ?, TIPOASISTENCIA_030 = ?, FECHA_030 = ?, DESCUENTO_030 = ?, MOTIVO_030 = ?, MEDIO_030 = ?, FUENTEINGRESO_030 = ?, PRIMERAVEZ_030 = ?";
		$msConsulta .= ", BECADO_030 = ?, BECADOPOR_030 = ?, INATEC_030 = ?, DOCIDENTIDAD_030 = ?, DOCACADEMICO_030 = ?, CERTDIGITAL_030 = ? where MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msEstudiante, $msCurso, $mnTipoAsistencia, $mdFecha, $mnDescuento, $msMotivo, $msMedio, $mnFuenteIngreso, $mbPrimeraVez, $mbBecado, $msBecadoPor, $mbInatec, $mbDocIdentidad, $mbDocAcademico, $mbCertDigital, $msCodigo]);
	}
	
	function fxDevuelveMatricula($mbLlenaGrid, $msCodigo = "", $mnAnno = 0)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			if ($mnAnno == 0)
			{
				$msConsulta = "select MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as NOMBRE_020, FECHA_030, (case ESTADO_030 when 0 then 'Activo' when 1 then 'Inactivo' when 2 then 'Deserción' when 3 then 'Certificado' when 4 then 'Anulado' else 'Baja' end) as ESTADO_030 from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL order by MATRICULA_REL desc";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute();
			}
			else
			{
				$msConsulta = "select MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as NOMBRE_020, FECHA_030, (case ESTADO_030 when 0 then 'Activo' when 1 then 'Inactivo' when 2 then 'Deserción' when 3 then 'Certificado' when 4 then 'Anulado' else 'Baja' end) as ESTADO_030 from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and year(FECHA_030) = ? order by MATRICULA_REL desc";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$mnAnno]);
			}
		}
		else
		{
			$msConsulta = "select MATRICULA_REL, ESTUDIANTE_REL, CURSO_REL, TIPOASISTENCIA_030, FECHA_030, DESCUENTO_030, MOTIVO_030, MEDIO_030, FUENTEINGRESO_030, PRIMERAVEZ_030, BECADO_030, BECADOPOR_030, INATEC_030, DOCIDENTIDAD_030, DOCACADEMICO_030, ESTADO_030, CERTDIGITAL_030 from KDSA030A where MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}
		
		return $mDatos;
	}

	function fxNotificacionMatricula ($msCodigo, $mnEstado)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msFechaHoy = date('Y-m-d H:i:s');
		$msConsulta = "insert into KDSA031A (MATRICULA_REL, FECHA_031, ESTADO_031) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msFechaHoy, $mnEstado]); //Ingresa el registro

		if ($mnEstado == 0)
		{
			$msConsulta = "update KDSA030A set NOTIFICADO_030 = 0 where MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]); //Marca la notificación
		}
		else
		{
			$msConsulta = "update KDSA030A set ESTADO_030 = 0 where MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]); //Activa la matrícula
		}
	}

	function fxCambiaEstadoMatricula ($msCodigo, $mnEstado)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select ESTADO_030 from KDSA030A where MATRICULA_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$Fila = $mDatos->fetch();
		$mnEstadoBD = $Fila["ESTADO_030"];

		if ($mnEstado != $mnEstadoBD)
		{
			$msConsulta = "update KDSA030A set ESTADO_030 = ? where MATRICULA_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$mnEstado, $msCodigo]);

			if ($mnEstado == 2 or $mnEstado == 4 or $mnEstado == 5) //Deserción, Anulado o Baja
			{
				//Anula los Cobros individuales que no están Cancelados o Exonerados
				$msConsulta = "update KDSA051A set ANULADO_051 = 1 where MATRICULA_REL = ? and EXONERADO_051 = 0 and PAGADO_051 = 0 and ABONADO_051 = 0";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msCodigo]);

				$msConsulta = "update KDSA051A set EXONERADO_051 = 1 where MATRICULA_REL = ? and EXONERADO_051 = 0 and PAGADO_051 = 0 and ABONADO_051 > 0";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msCodigo]);
			}

			if ($mnEstado == 0) //Activo
			{
				//Esta rutina es para reactivar los Cobros Individuales de las Matrículas
				if ($mnEstadoBD == 2 or $mnEstadoBD == 4 or $mnEstadoBD == 5) //Deserción, Anulado o Baja
				{
					$msConsulta = "update KDSA051A set ANULADO_051 = 0 where MATRICULA_REL = ? and ANULADO_051 = 1 and EXONERADO_051 = 0 and PAGADO_051 = 0";
					$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					$mDatos->execute([$msCodigo]);

					$msConsulta = "update KDSA051A set EXONERADO_051 = 0 where MATRICULA_REL = ? and EXONERADO_051 = 1 and PAGADO_051 = 0 and ABONADO_051 > 0";
					$mDatos = $m_cnx_MySQL->prepare($msConsulta);
					$mDatos->execute([$msCodigo]);
				}
			}

			return 1;
		}
		else
			return 0;
	}
?> 