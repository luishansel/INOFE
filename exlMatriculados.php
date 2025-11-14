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
		$PermisoUsuario = fxPermisoUsuario("repMatriculados", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			$codCurso = trim($_POST["KDSA"]);
			header('Content-type:application/xls; charset=UTF-8');
			header('Content-Disposition: attachment; filename=' . trim($codCurso) . '.xls');
	
			//Obtención de datos
			$msConsulta = "select KDSA030A.MATRICULA_REL, KDSA030A.ESTUDIANTE_REL, concat(trim(APELLIDOS_010), ', ', trim(NOMBRES_010)) as NOMBRECOMPLETO, ";
			$msConsulta .= "NOMBRE_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, CONVOCATORIA_020, CELULAR_010, CORREO_010, ESTADO_030, TIPOASISTENCIA_020, TIPOASISTENCIA_030 ";
			$msConsulta .= "from KDSA030A, KDSA010A, KDSA020A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
			$msConsulta .= "and ESTADO_030 <> 4 and KDSA030A.CURSO_REL = '" . trim($codCurso) . "'";
			
			$m_cnx_MySQL = fxAbrirConexion();
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$mdFechaIni, $mdFechaFin]);
			$Registros = $mDatos->rowCount();
			$Fila = $mDatos->fetch();
		}
		$HoraIni = date_create($Fila["HORAINI_020"]);
		$HoraFin = date_create($Fila["HORAFIN_020"]);
		$Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
		echo('<table>');
		echo('<tr>');
		echo('<td style="font-size: large; font-weight: bold; color: rgb(0,100,255)">ESTUDIANTES</td>');
		echo('<td style="font-size: large; font-weight: bold; color: rgb(0,100,255)">MATRICULADOS</td>');
		echo('</tr>');
		echo('<table>');
		echo('<br>');
		echo('<table>');
    	echo('<tr>');
		echo('<td style="font-weight: bold">Curso:</td>');
		$TipoCurso = $Fila["TIPOASISTENCIA_020"];
		switch ($TipoCurso)
		{
			case 0:
				$Curso = $Fila["NOMBRE_020"] . " (Presencial)";
			break;

			case 1:
				$Curso = $Fila["NOMBRE_020"] . " (Virtual)";
			break;

			case 2:
				$Curso = $Fila["NOMBRE_020"] . " (On-line)";
		}
        echo('<td>' . trim($Curso) . '</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td style="font-weight: bold">Vigencia:</td>');
        echo('<td>Del ' . DevuelveFecha($Fila["FECHAINI_020"]) . ' al ' . DevuelveFecha($Fila["FECHAFIN_020"]) . '</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td style="font-weight: bold">Dias de clase:</td>');
        echo('<td>' . $Fila["DIASCLASE"] . '</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td style="font-weight: bold">Horario:</td>');
        echo('<td>' . trim($Horario) . '</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td style="font-weight: bold">Convocatoria:</td>');
        echo('<td>' . $Fila["CONVOCATORIA_020"] . '</td>');
        echo('</tr>');
    	echo('</table>');
		echo('<br>');
    	echo('<table>');
        echo('<tr>');
        echo('<th align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">' . utf8_decode('Matrícula') . '</th>');
        echo('<th align="left" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Nombre del Estudiante</th>');
        echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Celular</th>');
		echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">eMail</th>');
		echo('<th align="center" style="color:rgb(255,255,255); background-color:rgb(0,100,255);">Asistencia</th>');
		echo('</tr>');
		echo ("<tr>");
		echo ("<td>" . $Fila["MATRICULA_REL"] . "</td>");
		echo ("<td>" . $Fila["NOMBRECOMPLETO"] . "</td>");
		echo ("<td>" . $Fila["CELULAR_010"] . "</td>");
		echo ("<td>" . $Fila["CORREO_010"] . "</td>");
		switch ($Fila["TIPOASISTENCIA_030"]) 
		{
			case 0:
				echo ("<td>Presencial</td>");
				break;
			case 1:
				echo ("<td>Virtual</td>");
				break;
			case 2:
				echo ("<td>On-line</td>");
				break;
		}
		echo ("</tr>");

		while ($Fila = $mDatos->fetch())
        {
            echo ("<tr>");
            echo ("<td>" . $Fila["MATRICULA_REL"] . "</td>");
            echo ("<td>" . $Fila["NOMBRECOMPLETO"] . "</td>");
			echo ("<td>" . $Fila["CELULAR_010"] . "</td>");
			echo ("<td>" . $Fila["CORREO_010"] . "</td>");
			switch ($Fila["TIPOASISTENCIA_030"]) 
			{
				case 0:
					echo ("<td>Presencial</td>");
					break;
				case 1:
					echo ("<td>Virtual</td>");
					break;
				case 2:
					echo ("<td>On-line</td>");
					break;
			}
            echo ("</tr>");
        }
		echo('</table>');
	}
?>