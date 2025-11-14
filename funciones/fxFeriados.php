<?php
	function fxGuardarFeriado($mdFecha, $msDescripcion, $msDia)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "insert into KDSA001A (FECHA_001, DESC_001, DIA_001) values (?, ?, ?)";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFecha, $msArchivo, $msDescripcion, $msDia]);

		//Ingresa el Feriado en los Cursos afectados
		$msConsulta = "select CURSO_REL, DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020 from KDSA020A where FECHAINI_020 <= ? and FECHAFIN_020 >= ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFecha, $mdFecha]);

		while ($Fila = $mDatos->fetch())
		{
			$mbingresarFecha = 0;
			$msCurso = $Fila["CURSO_REL"];
			$mbDomingo = $Fila["DOMINGO_020"];
			$mbLunes = $Fila["LUNES_020"];
			$mbMartes = $Fila["MARTES_020"];
			$mbMiercoles = $Fila["MIERCOLES_020"];
			$mbJueves = $Fila["JUEVES_020"];
			$mbViernes = $Fila["VIERNES_020"];
			$mbSabado = $Fila["SABADO_020"];

			if ($msDia == "Domingo" and $mbDomingo == 1)
				$mbingresarFecha = 1;

			if ($msDia == "Lunes" and $mbLunes == 1)
				$mbingresarFecha = 1;

			if ($msDia == "Martes" and $mbMartes == 1)
				$mbingresarFecha = 1;

			if ($msDia == "Miércoles" and $mbMiercoles == 1)
				$mbingresarFecha = 1;

			if ($msDia == "Jueves" and $mbJueves == 1)
				$mbingresarFecha = 1;

			if ($msDia == "Viernes" and $mbViernes == 1)
				$mbingresarFecha = 1;

			if ($msDia == "Sábado" and $mbSabado == 1)
				$mbingresarFecha = 1;

			if ($mbingresarFecha == 1)
			{
				$msConsulta = "select * from KDSA022A where FECHA_022 = ? and CURSO_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$mdFecha, $msCurso]);
				$mnRegistros = $mAuxiliar-rowCount();

				if ($mnRegistros == 0)
				{
					$msConsulta = "Select ifnull(max(DETFECHA_REL), 0) as Ultimo from KDSA022A where CURSO_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$msCurso]);
					$auxFila = $mAuxiliar->fetch();
					$Numero = intval($auxFila["Ultimo"]);
					$Numero += 1;
					$msConsulta = "insert into KDSA022A (CURSO_REL, DETFECHA_REL, FECHA_022, DIA_022, MOTIVO_022) values (?, ?, ?, ?, ?)";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$msCurso, Numero, $mdFecha, $msDia, $msDescripcion]);

					//Modifica las fechas de la Planificación Programática
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

					$msConsulta = "update KDSA021A set FECHAINI_021 = ?, FECHAFIN_021 = ? where MODULO_REL = ?";
					$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
					while ($auxFila = $mAuxiliar->fetch())
					{
						$msModulo = $auxFila["MODULO_REL"];
						$mdFechaIni = $auxFila["INI"];
						$mdFechaFin = $auxFila["FIN"];
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
		}
	}
	
	function fxModificarFeriado($mdFecha, $msDescripcion, $msDia)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "update KDSA001A set DESC_001 = ?, DIA_001 = ? where FECHA_001 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msDescripcion, $msDia, $mdFecha]);
	}
	
	function fxBorrarFeriado($mdFechaApp)
	{
		$FechaDividida = explode("-", $mdFechaApp);
		$Anno = $FechaDividida[2];
		$Mes = $FechaDividida[1];
		$Dia = $FechaDividida[0];
		$mdFecha = $Anno . "-" . $Mes . "-" . $Dia;
		
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "delete from KDSA001A where FECHA_001 = '" . trim($mdFecha) . "'";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFecha]);

		//Modifica las fechas de la Planificación Programática
		$msConsulta = "select CURSO_REL from KDSA022A where FECHA_022 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mdFecha]);
		while ($Fila = $mDatos->fetch())
		{
			$msCurso = $Fila["CURSO_REL"];

			$msConsulta = "select DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020 from KDSA020A where CURSO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso]);
			$auxFila = $mAuxiliar->fetch();
			$mbDomingo = $auxFila["DOMINGO_020"];
			$mbLunes = $auxFila["LUNES_020"];
			$mbMartes = $auxFila["MARTES_020"];
			$mbMiercoles = $auxFila["MIERCOLES_020"];
			$mbJueves = $auxFila["JUEVES_020"];
			$mbViernes = $auxFila["VIERNES_020"];
			$mbSabado = $auxFila["SABADO_020"];

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
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$mdFechaIni, $mdFechaFin, $msCurso]);
		
		//Borra el Feriado en los Cursos afectados
		$msConsulta = "delete from KDSA022A where FECHA_022 = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$mdFecha]);
	}
	
	function fxDevuelveFeriado($mbLlenaGrid, $mdFecha = "1900-01-01")
	{
		$m_cnx_MySQL = fxAbrirConexion();
		if ($mbLlenaGrid == 1)
		{
			$msConsulta = "select FECHA_001, DESC_001, DIA_001 from KDSA001A order by FECHA_001 desc";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute();
		}
		else
		{
			$msConsulta = "select FECHA_001, DESC_001, DIA_001 from KDSA001A where FECHA_001 = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$mdFecha]);
		}

		return $mDatos;
	}

	function fxExisteFeriado($msCodigo)
	{
		$m_cnx_MySQL = fxAbrirConexion();
		$msConsulta = "select DESC_001 from KDSA001A where FECHA_001 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCodigo]);
		$mnRegistros = $mDatos->rowCount();
		return $mnRegistros;
	}
?>