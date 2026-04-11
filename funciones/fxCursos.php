<?php
	function fxGuardarCursos($msCursoInatec, $msNombre, $mdFechaIni, $mdFechaFin, $mdHoraIni, $mdHoraFin, $mbDomingo, $mbLunes, $mbMartes, $mbMiercoles, $mbJueves, $mbViernes, $mbSabado, $mnTipo, $mnTipoAsistencia, $mnTurno, $msConvocatoria, $mnGrupo, $mnMoneda, $mnValor, $mnMatricula, $mnCuota, $mnCertificacion, $mnMora, $mnMaximo, $mbActivo, $mbCertificar, $mbCertDigital)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(CURSO_REL), 4), 0) as Ultimo from KDSA020A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msCodigo = "CUR" . str_repeat("0", 7 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA020A (CURSO_REL, CURSOINATEC_REL, NOMBRE_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020, TIPO_020, TIPOASISTENCIA_020, TURNO_020, CONVOCATORIA_020, GRUPO_020, MONEDA_020, VALOR_020, MATRICULA_020, CUOTA_020, CERTIFICACION_020, MORA_020, MAXIMO_020, ACTIVO_020, CERTIFICAR_020, CERTDIGITAL_020, CERRADOANTES_020) ";
		$msConsulta .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo, $msCursoInatec, $msNombre, $mdFechaIni, $mdFechaFin, $mdHoraIni, $mdHoraFin, $mbDomingo, $mbLunes, $mbMartes, $mbMiercoles, $mbJueves, $mbViernes, $mbSabado, $mnTipo, $mnTipoAsistencia, $mnTurno, $msConvocatoria, $mnGrupo, $mnMoneda, $mnValor, $mnMatricula, $mnCuota, $mnCertificacion, $mnMora, $mnMaximo, $mbActivo, $mbCertificar, $mbCertDigital, 0]);
		return ($msCodigo);
	}
	
	function fxModificarCursos($msCodigo, $msCursoInatec, $msNombre, $mdFechaIni, $mdFechaFin, $mdHoraIni, $mdHoraFin, $mbDomingo, $mbLunes, $mbMartes, $mbMiercoles, $mbJueves, $mbViernes, $mbSabado, $mnTipo, $mnTipoAsistencia, $mnTurno, $msConvocatoria, $mnGrupo, $mnMoneda, $mnValor, $mnMatricula, $mnCuota, $mnCertificacion, $mnMora, $mnMaximo, $mbActivo, $mbCertificar, $mbCertDigital, $mbCerradoAntes)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA020A set CURSOINATEC_REL = ?, NOMBRE_020 = ?, FECHAINI_020 = ?, FECHAFIN_020 = ?, HORAINI_020 = ?, HORAFIN_020 = ?";
		$msConsulta .= ", LUNES_020 = ?, MARTES_020 = ?, MIERCOLES_020 = ?, JUEVES_020 = ?, VIERNES_020 = ?, SABADO_020 = ?, DOMINGO_020 = ?";
		$msConsulta .= ", TIPO_020 = ?, TIPOASISTENCIA_020 = ?, TURNO_020 = ?, CONVOCATORIA_020 = ?, GRUPO_020 = ?, MONEDA_020 = ?, VALOR_020 = ?, MATRICULA_020 = ?, CUOTA_020 = ?";
		$msConsulta .= ", CERTIFICACION_020 = ?, MORA_020 = ?, MAXIMO_020 = ?, ACTIVO_020 = ?, CERTIFICAR_020 = ?, CERTDIGITAL_020 = ?, CERRADOANTES_020 = ? where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCursoInatec, $msNombre, $mdFechaIni, $mdFechaFin, $mdHoraIni, $mdHoraFin, $mbLunes, $mbMartes, $mbMiercoles, $mbJueves, $mbViernes, $mbSabado, $mbDomingo, $mnTipo, $mnTipoAsistencia, $mnTurno, $msConvocatoria, $mnGrupo, $mnMoneda, $mnValor, $mnMatricula, $mnCuota, $mnCertificacion, $mnMora, $mnMaximo, $mbActivo, $mbCertificar, $mbCertDigital, $mbCerradoAntes, $msCodigo]);
	}
	
	function fxBorrarCursos($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA020A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);

		//Borra los Módulos
		$msConsulta = "delete from KDSA021A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);

		//Borra los Feriados
		$msConsulta = "delete from KDSA022A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);

		//Borra los Cobros
		$msConsulta = "delete from KDSA050A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);

		//Borra el Curso
		$msConsulta = "delete from KDSA020A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
	}
	
	function fxDevuelveCursos($mbLlenaGrid, $msCodigo = "")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, GRUPO_020, VALOR_020, (case ACTIVO_020 when 1 then 'x' else '' end) as ACTIVO_020, CONVOCATORIA_020 from KDSA020A order by CURSO_REL desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select CURSO_REL, CURSOINATEC_REL, NOMBRE_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020, DOMINGO_020, TIPO_020, TIPOASISTENCIA_020, TURNO_020, CONVOCATORIA_020, GRUPO_020, MONEDA_020, VALOR_020, MATRICULA_020, CUOTA_020, CERTIFICACION_020, MORA_020, MAXIMO_020, ACTIVO_020, CERTIFICAR_020, CERTDIGITAL_020, CERRADOANTES_020 from KDSA020A where CURSO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCodigo]);
		}

		return $mDatos;
	}

	/*****Detalle Módulos (KDSA021A)**************/
	
	function fxGuardarDetModulo($msCurso, $msDocente, $mnNumero, $msNombre, $mdFechaIni, $mdFechaFin, $mnValor)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "Select ifnull(mid(max(MODULO_REL), 2), 0) as Ultimo from KDSA021A";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
		$Fila = $mDatos->fetch();
		$Numero = intval($Fila["Ultimo"]);
		$Numero += 1;
		$Longitud = strlen($Numero);
		$msModulo = "M" . str_repeat("0", 9 - $Longitud) . trim($Numero);
		$msConsulta = "insert into KDSA021A (MODULO_REL, CURSO_REL, DOCENTE_REL, NUMERO_021, NOMBRE_021, FECHAINI_021, FECHAFIN_021, VALOR_021) ";
		$msConsulta .= "values (?, ?, ?, ?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo, $msCurso, $msDocente, $mnNumero, $msNombre, $mdFechaIni, $mdFechaFin, $mnValor]);
	}
	
	function fxModificarDetModulo($msModulo, $msCurso, $msDocente, $mnNumero, $msNombre, $mdFechaIni, $mdFechaFin, $mnValor)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA021A set CURSO_REL = ?, DOCENTE_REL = ?, NUMERO_021 = ?";
		$msConsulta .= ", NOMBRE_021 = ?, FECHAINI_021 = ?, FECHAFIN_021 = ?";
		$msConsulta .= ", VALOR_021 = ? where MODULO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $msDocente, $mnNumero, $msNombre, $mdFechaIni, $mdFechaFin, $mnValor, $msModulo]);
	}

	function fxBorrarDetModulo($msModulo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA021A where MODULO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msModulo]);
	}
	
	function fxDevuelveDetModulo($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select KDSA021A.MODULO_REL, CURSO_REL, KDSA021A.DOCENTE_REL, NUMERO_021, NOMBRE_021, NOMBRE_100, FECHAINI_021, FECHAFIN_021, VALOR_021, IFNULL((select PLANIFICACION_REL from KDSA120A where KDSA021A.MODULO_REL = KDSA120A.MODULO_REL limit 1), '') as PLAN from KDSA021A, KDSA100A where KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL and CURSO_REL = ? order by NUMERO_021, MODULO_REL";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
		return $mDatos;
	}
	
	/*****Detalle Feriados (KDSA022A)**************/
	
	function fxGuardarDetFeriado($msCurso, $mnFeriado, $mdFecha, $msDia, $msMotivo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA022A (CURSO_REL, DETFECHA_REL, FECHA_022, DIA_022, MOTIVO_022) values (?, ?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $mnFeriado, $mdFecha, $msDia, $msMotivo]);

		//Modifica las fechas de la Planificación Programática en caso de necesitarlo
		$msConsulta = "select distinct KDSA120A.PLANIFICACION_REL from KDSA121A, KDSA120A, KDSA021A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? and FECHA_121 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $mdFecha]);
		$mnRegistros = $mDatos->rowCount();

		if ($mnRegistros > 0)
		{
			//Encuentra el registro. La fecha existe en la Planificacion.
			$msConsulta = "select DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020 from KDSA020A where CURSO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			$Fila = $mAuxiliar->fetch();
			$mbDomingo = $Fila["DOMINGO_020"];
			$mbLunes = $Fila["LUNES_020"];
			$mbMartes = $Fila["MARTES_020"];
			$mbMiercoles = $Fila["MIERCOLES_020"];
			$mbJueves = $Fila["JUEVES_020"];
			$mbViernes = $Fila["VIERNES_020"];
			$mbSabado = $Fila["SABADO_020"];

			$msConsulta = "Select KDSA121A.PLANIFICACION_REL, DETPLANIFICACION_REL, FECHA_121 from KDSA121A, KDSA120A, KDSA021A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? order by FECHA_121";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			while ($auxFila = $mAuxiliar->fetch())
			{
				$msPlanificacion = $auxFila["PLANIFICACION_REL"];
				$mnDetPlanificacion = $auxFila["DETPLANIFICACION_REL"];
				$mdFechaPlan = $auxFila["FECHA_121"];
				$escribirFecha = false;

				if ($mdFechaPlan >= $mdFecha)
				{
					while (!$escribirFecha)
					{
						$mdFechaPlan = date("Y-m-d", strtotime($mdFechaPlan . "+ 1 days"));
						$msDiaSemana = date("l", strtotime($mdFechaPlan));

						if ($msDiaSemana == "Sunday" and $mbDomingo == 1)
							$escribirFecha = true;
		
						if ($msDiaSemana == "Monday" and $mbLunes == 1)
							$escribirFecha = true;
			
						if ($msDiaSemana == "Tuesday" and $mbMartes == 1)
							$escribirFecha = true;
			
						if ($msDiaSemana == "Wednesday" and $mbMiercoles == 1)
							$escribirFecha = true;
			
						if ($msDiaSemana == "Thursday" and $mbJueves == 1)
							$escribirFecha = true;
			
						if ($msDiaSemana == "Friday" and $mbViernes == 1)
							$escribirFecha = true;
			
						if ($msDiaSemana == "Saturday" and $mbSabado == 1)
							$escribirFecha = true;

						if ($escribirFecha)
						{
							$msConsulta = "update KDSA121A set FECHA_121 = ? where PLANIFICACION_REL = ? and DETPLANIFICACION_REL = ?";
							$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
							$mAuxiliar2->execute([$mdFechaPlan, $msPlanificacion, $mnDetPlanificacion]);
						}
					}
				}
			}

			//Modifica las fechas de Inicio y Fin de los Módulos usando las fechas de la Planificación
			$msConsulta = "Select KDSA121A.PLANIFICACION_REL, KDSA120A.MODULO_REL, min(FECHA_121) as INI, max(FECHA_121) as FIN from KDSA121A, KDSA120A, KDSA021A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? group by KDSA120A.MODULO_REL order by FECHA_121";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			while ($auxFila = $mAuxiliar->fetch())
			{
				$msModulo = $auxFila["MODULO_REL"];
				$mdFechaIni = $auxFila["INI"];
				$mdFechaFin = $auxFila["FIN"];
				$msConsulta = "update KDSA021A set FECHAINI_021 = ?, FECHAFIN_021 = ? where MODULO_REL = ?";
				$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar2->execute([$mdFechaIni, $mdFechaFin, $msModulo]);
			}

			//Modifica las fechas de Inicio y Fin del Curso usando las fechas de los Módulos
			$msConsulta = "select CURSO_REL, min(FECHAINI_021) as INI, max(FECHAFIN_021) as FIN from KDSA021A where CURSO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			$auxFila = $mAuxiliar->fetch();
			$mdFechaIni = $auxFila["INI"];
			$mdFechaFin = $auxFila["FIN"];
			$msConsulta = "update KDSA020A set FECHAINI_020 = ?, FECHAFIN_020 = ? where CURSO_REL = ?";
			$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar2->execute([$mdFechaIni, $mdFechaFin, $msCurso]);
		}
	}

	function fxBorrarDetFeriado($msCurso, $mdFecha)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA022A where CURSO_REL = ? and FECHA_022 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $mdFecha]);
	}

	function fxVerificarDetFeriado($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select FECHA_022 from KDSA022A where CURSO_REL = ?";
		$mFechas = $m_cnx_MySQL->prepare($msConsulta);
		$mFechas->execute([$msCurso]);

		while ($auxFechas = $mFechas->fetch())
		{
			$mdFecha = $auxFechas["FECHA_022"];
			//Modifica las fechas de la Planificación Programática en caso de necesitarlo
			$msConsulta = "select PLANIFICACION_REL from KDSA121A, KDSA120A, KDSA021A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? and FECHA_121 = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso, $mdFecha]);
			$mnRegistros = $mDatos->rowCount();

			if ($mnRegistros == 0)
			{
				//No encuentra el registro. La fecha no existe en la Planificacion.
				$msConsulta = "select DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020 from KDSA020A where CURSO_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msCurso]);
				$Fila = $mAuxiliar->fetch();
				$mbDomingo = $Fila["DOMINGO_020"];
				$mbLunes = $Fila["LUNES_020"];
				$mbMartes = $Fila["MARTES_020"];
				$mbMiercoles = $Fila["MIERCOLES_020"];
				$mbJueves = $Fila["JUEVES_020"];
				$mbViernes = $Fila["VIERNES_020"];
				$mbSabado = $Fila["SABADO_020"];

				$msConsulta = "Select KDSA121A.PLANIFICACION_REL, DETPLANIFICACION_REL, FECHA_121 from KDSA121A, KDSA120A, KDSA021A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? order by FECHA_121";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msCurso]);
				while ($auxFila = $mAuxiliar->fetch())
				{
					$msPlanificacion = $auxFila["PLANIFICACION_REL"];
					$mnDetPlanificacion = $auxFila["DETPLANIFICACION_REL"];
					$mdFechaPlan = $auxFila["FECHA_121"];
					$escribirFecha = false;

					if ($mdFechaPlan > $mdFecha)
					{
						while (!$escribirFecha)
						{
							$mdFechaPlan = date("Y-m-d", strtotime($mdFechaPlan . "- 1 days"));
							$msDiaSemana = date("l", strtotime($mdFechaPlan));

							if ($msDiaSemana == "Sunday" and $mbDomingo == 1)
								$escribirFecha = true;
			
							if ($msDiaSemana == "Monday" and $mbLunes == 1)
								$escribirFecha = true;
				
							if ($msDiaSemana == "Tuesday" and $mbMartes == 1)
								$escribirFecha = true;
				
							if ($msDiaSemana == "Wednesday" and $mbMiercoles == 1)
								$escribirFecha = true;
				
							if ($msDiaSemana == "Thursday" and $mbJueves == 1)
								$escribirFecha = true;
				
							if ($msDiaSemana == "Friday" and $mbViernes == 1)
								$escribirFecha = true;
				
							if ($msDiaSemana == "Saturday" and $mbSabado == 1)
								$escribirFecha = true;

							if ($escribirFecha)
							{
								$msConsulta = "update KDSA121A set FECHA_121 = ? where PLANIFICACION_REL = ? and DETPLANIFICACION_REL = ?";
								$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
								$mAuxiliar2->execute([$mdFechaPlan, $msPlanificacion, $mnDetPlanificacion]);
							}
						}
					}
				}

				//Modifica las fechas de Inicio y Fin de los Módulos usando las fechas de la Planificación
				$msConsulta = "Select KDSA121A.PLANIFICACION_REL, KDSA120A.MODULO_REL, min(FECHA_121) as INI, max(FECHA_121) as FIN from KDSA121A, KDSA120A, KDSA021A where KDSA121A.PLANIFICACION_REL = KDSA120A.PLANIFICACION_REL and KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? group by KDSA120A.MODULO_REL order by FECHA_121";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msCurso]);
				while ($auxFila = $mAuxiliar->fetch())
				{
					$msModulo = $auxFila["MODULO_REL"];
					$mdFechaIni = $auxFila["INI"];
					$mdFechaFin = $auxFila["FIN"];
					$msConsulta = "update KDSA021A set FECHAINI_021 = ?, FECHAFIN_021 = ? where MODULO_REL = ?";
					$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar2->execute([$mdFechaIni, $mdFechaFin, $msModulo]);
				}

				//Modifica las fechas de Inicio y Fin del Curso usando las fechas de los Módulos
				$msConsulta = "select CURSO_REL, min(FECHAINI_021) as INI, max(FECHAFIN_021) as FIN from KDSA021A where CURSO_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msCurso]);
				$auxFila = $mAuxiliar->fetch();
				$mdFechaIni = $auxFila["INI"];
				$mdFechaFin = $auxFila["FIN"];
				$msConsulta = "update KDSA020A set FECHAINI_020 = ?, FECHAFIN_020 = ? where CURSO_REL = ?";
				$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar2->execute([$mdFechaIni, $mdFechaFin, $msCurso]);
			}

			$msConsulta = "delete from KDSA022A where CURSO_REL = ? and FECHA_022 = ?";
			$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar2->execute([$msCurso, $mdFecha]);
		}
	}
	
	function fxDevuelveDetFeriado($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CURSO_REL, DETFECHA_REL, FECHA_022, DIA_022, MOTIVO_022 from KDSA022A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
		return $mDatos;
	}

	/**Documentos obligatorios de los cursos (KDSA024A)**/
	function fxGuardarDetDocObligatorio($msCurso, $mnDocumento, $msArchivo, $msRuta)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA024A (CURSO_REL, DOCUMENTO_REL, ARCHIVO_024, RUTA_024) ";
		$msConsulta .= "values (?, ?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso, $mnDocumento, $msArchivo, $msRuta]);
	}

	function fxBorrarDetDocObligatorio($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA024A where CURSO_REL = ? ";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
	}

	function fxDevuelveDetDocObligatorio($msCurso)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select CURSO_REL, DOCUMENTO_REL, ARCHIVO_024, RUTA_024 from KDSA024A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
		return $mDatos;
	}
?>