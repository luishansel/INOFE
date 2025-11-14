<?php
	function fxReprogramar($msCurso, $mdFecha)
	{
		$m_cnx_MySQL = fxAbrirConexion();

		//Obtiene la información necesaria del Curso
		$msConsulta = "select FECHAINI_020, DOMINGO_020, LUNES_020, MARTES_020, MIERCOLES_020, JUEVES_020, VIERNES_020, SABADO_020 from KDSA020A where CURSO_REL = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
		$Fila = $mDatos->fetch();
		$mdFechaIni = $Fila["FECHAINI_020"];
		$mbDomingo = $Fila["DOMINGO_020"];
		$mbLunes = $Fila["LUNES_020"];
		$mbMartes = $Fila["MARTES_020"];
		$mbMiercoles = $Fila["MIERCOLES_020"];
		$mbJueves = $Fila["JUEVES_020"];
		$mbViernes = $Fila["VIERNES_020"];
		$mbSabado = $Fila["SABADO_020"];

		//Calcula la cantidad de SESIONES que se moverá el Curso
		$mdFechaControl = $mdFechaIni;
		$mdFechaIniCur = $mdFecha;
		$mnSesionesCur = 0;
		while ($mdFechaControl <= $mdFechaIniCur)
		{
			$msConsulta = "select DETFECHA_REL from KDSA022A where CURSO_REL = ? and FECHA_022 = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msCurso, $mdFechaControl]);
			$mnRegistros = $mAuxiliar->rowCount();
			$mdFechaControl = date("Y-m-d", strtotime($mdFechaControl . "+ 1 days"));

			if ($mnRegistros == 0)
			{
				$msDiaSemana = date("l", strtotime($mdFechaControl));
				if ($msDiaSemana == "Sunday" and $mbDomingo == 1)
					$mnSesionesCur++;

				if ($msDiaSemana == "Monday" and $mbLunes == 1)
					$mnSesionesCur++;

				if ($msDiaSemana == "Tuesday" and $mbMartes == 1)
					$mnSesionesCur++;

				if ($msDiaSemana == "Wednesday" and $mbMiercoles == 1)
					$mnSesionesCur++;

				if ($msDiaSemana == "Thursday" and $mbJueves == 1)
					$mnSesionesCur++;

				if ($msDiaSemana == "Friday" and $mbViernes == 1)
					$mnSesionesCur++;

				if ($msDiaSemana == "Saturday" and $mbSabado == 1)
					$mnSesionesCur++;
			}
		}

		//Modifica las fechas de Inicio y Fin de los Módulos
		$msConsulta = "select MODULO_REL, FECHAINI_021, FECHAFIN_021 from KDSA021A where CURSO_REL = ? order by NUMERO_021, MODULO_REL";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$msCurso]);
		while ($Fila = $mDatos->fetch())
		{
			$msModulo = $Fila["MODULO_REL"];
			$mdFechaIniMod = $Fila["FECHAINI_021"];
			$mdFechaFinMod = $Fila["FECHAFIN_021"];

			//Calcula la cantidad de SESIONES del Módulo excluyendo los Feriados
			$mdFechaControlIni = $mdFechaIniMod;
			$mdFechaControlFin = $mdFechaFinMod;
			$mnSesionesControl = $mnSesionesCur;

			while ($mnSesionesControl > 0) //Ciclo para la Fecha Inicial
			{
				$mdFechaControlIni = date("Y-m-d", strtotime($mdFechaControlIni . "+1 days"));
				$msConsulta = "select DETFECHA_REL from KDSA022A where CURSO_REL = ? and FECHA_022 = ?";
				$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				$mDatos->execute([$msCurso, $mdFechaControlIni]);
				$mnRegistros = $mAuxiliar->rowCount();

				if ($mnRegistros == 0)
				{
					$msDiaSemana = date("l", strtotime($mdFechaControlIni));
					if ($msDiaSemana == "Sunday" and $mbDomingo == 1)
						$mnSesionesControl--;

					if ($msDiaSemana == "Monday" and $mbLunes == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Tuesday" and $mbMartes == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Wednesday" and $mbMiercoles == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Thursday" and $mbJueves == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Friday" and $mbViernes == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Saturday" and $mbSabado == 1)
						$mnSesionesControl--;
				}
			}

			$mnSesionesControl = $mnSesionesCur;
			while ($mnSesionesControl > 0) //Ciclo para la Fecha Final
			{
				$mdFechaControlFin = date("Y-m-d", strtotime($mdFechaControlFin . "+1 days"));
				$msConsulta = "select DETFECHA_REL from KDSA022A where CURSO_REL = ? and FECHA_022 = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$msCurso, $mdFechaControlFin]);
				$mnRegistros = $mAuxiliar->rowCount();

				if ($mnRegistros == 0)
				{
					$msDiaSemana = date("l", strtotime($mdFechaControlFin));
					if ($msDiaSemana == "Sunday" and $mbDomingo == 1)
						$mnSesionesControl--;

					if ($msDiaSemana == "Monday" and $mbLunes == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Tuesday" and $mbMartes == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Wednesday" and $mbMiercoles == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Thursday" and $mbJueves == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Friday" and $mbViernes == 1)
						$mnSesionesControl--;
		
					if ($msDiaSemana == "Saturday" and $mbSabado == 1)
						$mnSesionesControl--;
				}
			}

			$msConsulta = "update KDSA021A set FECHAINI_021 = ?, FECHAFIN_021 = ? where MODULO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$mdFechaControlIni, $mdFechaControlFin, $msModulo]);

			//Modifica las fechas de la Planificación Programática en caso de necesitarlo
			$msConsulta = "select KDSA121A.PLANIFICACION_REL, DETPLANIFICACION_REL, FECHA_121 from KDSA120A join KDSA121A on KDSA120A.PLANIFICACION_REL = KDSA121A.PLANIFICACION_REL where MODULO_REL = ?";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$msModulo]);
			$mnRegistros = $mAuxiliar->rowCount();

			if ($mnRegistros > 0)
			{
				//Encuentra el registro. La fecha existe en la Planificacion.
				while ($auxFila = $mAuxiliar->fetch())
				{
					$msPlanificacion = $auxFila["PLANIFICACION_REL"];
					$mnDetPlanificacion = $auxFila["DETPLANIFICACION_REL"];
					$mdFechaPlan = $auxFila["FECHA_121"];
					$mdFechaPlan = date("Y-m-d", strtotime($mdFechaPlan . "+" . $mnSesionesMod . " days"));
					$escribirFecha = false;

					while ($mnSesionesMod > 0)
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
							if ($mnSesionesMod = 0)
							{
								$msConsulta = "update KDSA121A set FECHA_121 = ? where PLANIFICACION_REL = ? and DETPLANIFICACION_REL = ?";
								$mAuxiliar2 = $m_cnx_MySQL->prepare($msConsulta);
								$mAuxiliar2->execute([$mdFechaPlan, $msPlanificacion, $mnDetPlanificacion]);
							}
							
							$mnSesionesMod --;
						}
					}
				}
			}
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
	}
?>