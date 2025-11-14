<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	$Registro = fxVerificaUsuario();

	function DevuelveFecha($Fecha)
	{
		$FechaDividida = explode("-", $Fecha);
		
		$Anno = $FechaDividida[0];
		$Mes = $FechaDividida[1];
		$Dia = $FechaDividida[2];
		
		switch ($Mes)
			{
				case "01":
					$NombreMes = "Ene";
					break;
				case "02":
					$NombreMes = "Feb";
					break;
				case "03":
					$NombreMes = "Mar";
					break;
				case "04":
					$NombreMes = "Abr";
					break;
				case "05":
					$NombreMes = "May";
					break;
				case "06":
					$NombreMes = "Jun";
					break;
				case "07":
					$NombreMes = "Jul";
					break;
				case "08":
					$NombreMes = "Ago";
					break;
				case "09":
					$NombreMes = "Sep";
					break;
				case "10":
					$NombreMes = "Oct";
					break;
				case "11":
					$NombreMes = "Nov";
					break;
				case "12":
					$NombreMes = "Dic";
					break;
			}
		return ($Dia . "-" . $NombreMes . "-" . $Anno);
	}

	if ($Registro == 0)
	{
	?>
		<div class="container text-center">
			<div id="DivContenido">
				<img src="imagenes/errordeacceso.png"/>
			</div>
		</div>
	<?php }
	else
	{
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("repAsistenciaPeriodo", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{ ?>
        <div class="container text-center">
        	<div id="DivContenido">
				<img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
		{
			$mdFechaIni = trim($_POST["dtpFechaIni"]);
			$mdFechaFin = trim($_POST["dtpFechaFin"]);
			header('Content-type:application/xls; charset=UTF-8');
			header('Content-Disposition: attachment; filename=AsistenciaPeriodo' . date('YmdHis') . '.xls');
	
			//Obtención de datos
			$msConsulta = "select ASISTENCIA_REL, FECHA_140, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, ";
			$msConsulta .= "NOMBRE_021, HORAINI_020, HORAFIN_020, NOMBRE_100 ";
			$msConsulta .= "from KDSA140A, KDSA021A, KDSA020A, KDSA100A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and ";
			$msConsulta .= "KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL and ";
			$msConsulta .= "FECHA_140 between ? and ? order by FECHA_140, ASISTENCIA_REL";
			$m_cnx_MySQL = fxAbrirConexion();
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$mdFechaIni, $mdFechaFin]);

			$msPeriodo = "Período del " . DevuelveFecha($mdFechaIni) . " al " . DevuelveFecha($mdFechaFin);
			echo('<table>');
			echo('<tr>');
			echo('<td style="font-size: large; font-weight: bold; color: rgb(0,100,255)">ASISTENCIAS REPORTADAS POR LOS DOCENTES</td>');
			echo('</tr>');
			echo('<tr>');
			echo('<td style="font-size: large; font-weight: bold; color: rgb(0,100,255)">' . utf8_decode($msPeriodo) . '</td>');
			echo('</tr>');
			echo('</table>');
			echo('<br>');
			echo('<table>');
			echo('<tr>');
			echo('<th align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Asistencia</th>');
			echo('<th align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Fecha</th>');
			echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Curso</th>');
			echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . utf8_decode('Módulo') . '</th>');
			echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Horario</th>');
			echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Docente</th>');
			echo('</tr>');

			while ($Fila = $mDatos->fetch())
			{
				echo ("<tr>");
				echo ("<td>" . $Fila["ASISTENCIA_REL"] . "</td>");
				echo ("<td>" . DevuelveFecha($Fila["FECHA_140"]) . "</td>");
				echo ("<td>" . utf8_decode(html_entity_decode($Fila["NOMBRE_020"])) . "</td>");
				echo ("<td>" . utf8_decode(html_entity_decode($Fila["NOMBRE_021"])) . "</td>");
				$HoraIni = date_create($Fila["HORAINI_020"]);
				$HoraFin = date_create($Fila["HORAFIN_020"]);
				$Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
				echo ("<td>" . trim($Horario) . "</td>");
				echo ("<td>" . utf8_decode(html_entity_decode($Fila["NOMBRE_100"])) . "</td>");
				echo ("</tr>");
			}
			echo('</table>');
		}
	}
?>