<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}
require_once ("funciones/fxGeneral.php");
require_once ("funciones/fxUsuarios.php");
require_once ("tcpdf/tcpdf.php");

$Registro = fxVerificaUsuario();
	
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
	class PDF extends TCPDF
	{
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			$mid_x = 210;
			// Title
			$this->SetFont('helvetica','B',14);
			$Titulo = utf8_decode('PLANIFICACION DE ENCUENTRO DE CLASE');
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 14, $Titulo);
		}

		// Page footer
		function Footer()
		{
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			// Arial italic 8
			$this->SetFont('helvetica','I',8);
			// Page number
			$this->Cell(0,10,html_entity_decode('Página ').$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
			$this->Cell(0,10,'Emitido: ' . date("d/m/Y h:i:s a") . '',0,0,'R');
		}
	}

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

	$codPlanClase = $_POST["KDSA"];

	//Obtención de datos para la Cabecera
	$msConsulta = "select FECHACLASE_130, NOMBRE_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, ";
	$msConsulta .= "CONVOCATORIA_020, GRUPO_020, TIPOASISTENCIA_020, NOMBRE_100 ";
	$msConsulta .= "from KDSA130A, KDSA021A, KDSA020A, KDSA100A where KDSA130A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL ";
	$msConsulta .= "and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA130A.CLASE_REL = ?";

	$m_cnx_MySQL = fxAbrirConexion();
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codPlanClase]);
	$Fila = $mDatos->fetch();

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
	$FechaIni = $Fila["FECHAINI_020"];
	$FechaFin = $Fila["FECHAFIN_020"];
	$FechaClase = $Fila["FECHACLASE_130"];
	$HoraIni = date_create($Fila["HORAINI_020"]);
	$HoraFin = date_create($Fila["HORAFIN_020"]);
	$DiasClase = $Fila["DIASCLASE"];
	$Convocatoria = $Fila["CONVOCATORIA_020"];
	$Grupo = $Fila["GRUPO_020"];
	$Docente = $Fila["NOMBRE_100"];

	$pdf = new PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
		require_once(dirname(__FILE__).'/lang/spa.php');
		$pdf->setLanguageArray($l);
	}

	$pdf->SetFont('helvetica', '', 8);

	$mHTML = '<table>';
	$mHTML .= '<tr><td width="20%"><b>Curso:</b></td><td>' . $Curso . '</td></tr>';
	$mHTML .= '<tr><td width="20%"><b>Vigencia:</b></td><td>' . 'Del ' . DevuelveFecha($FechaIni) . ' al ' . DevuelveFecha($FechaFin) . '</td></tr>';
	$mHTML .= '<tr><td width="20%"><b>Horario:</b></td><td>' . 'De ' . date_format($HoraIni, 'h:i a') . ' a ' . date_format($HoraFin, 'h:i a') . '</td></tr>';
	$mHTML .= '<tr><td width="20%"><b>Días de clase:</b></td><td>' . $DiasClase . '</td></tr>';
	$mHTML .= '<tr><td width="20%"><b>Convocatoria:</b></td><td>' . $Convocatoria . ' / G' . $Grupo . '</td></tr>';
	$mHTML .= '<tr><td width="20%"><b>Fecha de la Sesión:</b></td><td>' . DevuelveFecha($FechaClase) . '</td></tr>';
	$mHTML .= '<tr><td width="20%"><b>Docente:</b></td><td>' . $Docente . '</td></tr>';
	$mHTML .= '</table>';

	//Obtención de datos para el Detalle
	$msConsulta = "select CONTENIDOS_130, ASIGNACIONES_130, NOMBRE_021 from KDSA130A, KDSA021A where KDSA130A.MODULO_REL = KDSA021A.MODULO_REL and CLASE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codPlanClase]);
	$Fila = $mDatos->fetch();
	$Contenidos = $Fila["CONTENIDOS_130"];
	$Asignaciones = $Fila["ASIGNACIONES_130"];
	$Modulo = $Fila["NOMBRE_021"];
	
	$msHTML1 = '<table>';
	$msHTML1 .= '<tr><td style="text-align:left; background-color:rgb(185,238,255); color: rgb(0,0,0);"><h3>Módulo</h3></td></tr>';
	$msHTML1 .= '<tr><td style="text-align:left; background-color:rgb(185,238,255); color: rgb(0,0,0);">' . html_entity_decode($Modulo)  .'</td></tr>';
	$msHTML1 .= '</table>';

	$msHTML2 = '<table>';
	$msHTML2 .= '<tr><td style="text-align: left;color: rgb(0,0,0);"><h3>Contenidos a desarrollar</h3></td></tr>';
	if (trim($Contenidos)=="")
		$msHTML2 .= '<tr><td style="text-align: left;color: rgb(0,0,0);">&nbsp;</td></tr>';
	else
		$msHTML2 .= '<tr><td style="text-align: left;color: rgb(0,0,0);">' . html_entity_decode($Contenidos) . '</td></tr>';
	$msHTML2 .= '</table>';

	//Escribe los Objetivos
	$msConsulta = "select DESC_131 from KDSA131A where CLASE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codPlanClase]);
	$mRegistros = $mDatos->rowCount();

	$msHTML3 = '<table>';
	$msHTML3 .= '<tr><td style="text-align: left;background-color:rgb(185,238,255);color: rgb(0,0,0);"><h3>Objetivos de la Clase</h3></td></tr>';

	if ($mRegistros == 0)
		$msHTML3 .= '<tr><td style="text-align: left;background-color:rgb(185,238,255);color: rgb(0,0,0); width=100%">&nbsp;</td></tr>';
	else
	{
		$i=1;
		while ($Fila = $mDatos->fetch())
		{
			$Objetivos = $Fila["DESC_131"];
			$msHTML3 .= '<tr><td style="text-align: left;background-color:rgb(185,238,255);color: rgb(0,0,0); width=100%">' . $i . ') ' . html_entity_decode($Objetivos)  .'</td></tr>';
			$i++;
		}
	}
	$msHTML3 .= '</table>';

	//Escribe las Actividades
	$msConsulta = "select DESC_132 from KDSA132A where CLASE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codPlanClase]);
	$mRegistros = $mDatos->rowCount();

	$msHTML4 = '<table>';
	$msHTML4 .= '<tr><td style="text-align: left;background-color:rgb(255,255,255);color: rgb(0,0,0);"><h3>Actividades de enseñanza</h3></td></tr>';

	if ($mRegistros == 0)
		$msHTML4 .= '<tr><td style="text-align: left;background-color:rgb(255,255,255);color: rgb(0,0,0); width=100%">&nbsp;</td></tr>';
	else
	{
		$i=1;
		while ($Fila = $mDatos->fetch())
		{
			$Actividades = $Fila["DESC_132"];
			$msHTML4 .= '<tr><td style="text-align: left;background-color:rgb(255,255,255);color: rgb(0,0,0); width=100%">' . $i . ') ' . html_entity_decode($Actividades) . '</td></tr>';
			$i++;
		}
	}
	$msHTML4 .= '</table>';

	//Escribe los Materiales de apoyo
	$msConsulta = "select DESC_133 from KDSA133A where CLASE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codPlanClase]);
	$mRegistros = $mDatos->rowCount();

	$msHTML5 = '<table>';
	$msHTML5 .= '<tr><td style="text-align: left;background-color:rgb(185,238,255);color: rgb(0,0,0);"><h3>Materiales de apoyo</h3></td></tr>';

	if ($mRegistros == 0)
		$msHTML5 .= '<tr><td style="text-align: left;background-color:rgb(185,238,255);color: rgb(0,0,0); width=100%">&nbsp;</td></tr>';
	else
	{
		$i=1;
		while ($Fila = $mDatos->fetch())
		{
			$Materiales = $Fila["DESC_133"];
			$msHTML5 .= '<tr><td style="text-align: left;background-color:rgb(185,238,255);color: rgb(0,0,0); width=100%">' . $i . ') ' . html_entity_decode($Materiales)  .'</td></tr>';
			$i++;
		}
	}
	$msHTML5 .= '</table>';

	$msHTML6 = '<table>';
	$msHTML6 .= '<tr><td style="text-align: left;background-color:rgb(255,255,255);color: rgb(0,0,0);"><h3>Asignación de tareas extra clase</h3></td></tr>';
	$msHTML6 .= '<tr><td style="text-align: left;background-color:rgb(255,255,255);color: rgb(0,0,0); width=100%">' . html_entity_decode($Asignaciones) . '</td></tr>';
	$msHTML6 .= '</table>';

	$pdf->AddPage();
	$pdf->SetXY(40,20);
	$pdf->writeHTML($mHTML, true, false, true, false, '');
	$pdf->writeHTML($msHTML1, true, false, true, false, '');
	$pdf->writeHTML($msHTML2, true, false, true, false, '');
	$pdf->writeHTML($msHTML3, true, false, true, false, '');
	$pdf->writeHTML($msHTML4, true, false, true, false, '');
	$pdf->writeHTML($msHTML5, true, false, true, false, '');
	$pdf->writeHTML($msHTML6, true, false, true, false, '');
	$pdf->Output();
}
?>