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

$m_cnx_MySQL = fxAbrirConexion();
$Registro = fxVerificaUsuario();
$Administrador = fxVerificaAdministrador();

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
		public $msPeriodo;
		
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			// Title
			$mid_x = 210; // width of the "PDF screen", fixed by now.

			$this->SetFont('helvetica','B',12);
			$Titulo = 'ASISTENCIA CONSOLIDADA DE LOS ESTUDIANTES';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			$this->SetFont('helvetica','',9);
			$Titulo = $this->msPeriodo;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 15, $Titulo);
		}

		// Page footer
		function Footer()
		{
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			$this->SetFont('helvetica','I',8);
			// Page number
			$this->Cell(0,10,'Página '.$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
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

	$mdFechaIni = $_POST["dtpFechaIni"];
	$mdFechaFin = $_POST["dtpFechaFin"];

	$msPeriodo = "Período del " . DevuelveFecha($mdFechaIni) . " al " . DevuelveFecha($mdFechaFin);

	$pdf = new PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->msPeriodo=$msPeriodo;

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

	$pdf->setFontSize(8);
	$pdf->AddPage();

	$msHTML = '<table><thead><tr>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 50%">Curso</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 20%">Horario</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Presentes</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Ausentes</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Justificados</th>';
	$msHTML .= '</tr></thead><tbody>';

	$msConsulta = "select KDSA020A.CURSO_REL, CONCAT(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, ";
	$msConsulta .= "CONCAT(fxDevuelveDias(KDSA020A.CURSO_REL), ' / ', DATE_FORMAT(HORAINI_020, '%H:%i'), '-', DATE_FORMAT(HORAFIN_020, '%H:%i')) as HORARIO ";
	$msConsulta .= "from KDSA140A, KDSA021A, KDSA020A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL ";
	$msConsulta .= "and FECHA_140 BETWEEN ? and ? GROUP BY KDSA020A.CURSO_REL ";
	$msConsulta .= "ORDER BY KDSA020A.CURSO_REL";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFechaIni, $mdFechaFin]);

	$mbColorea = 0;
	$mnTotalPresentes = 0;
	$mnTotalAusentes = 0;
	$mnTotalJustificados = 0;

	while ($fila = $mDatos->fetch())
	{
		$msHTML .= '<tr>';
		$msCurso = $fila["CURSO_REL"];
		$msNomCurso = html_entity_decode($fila["NOMBRE_020"]);
		$msHorario = $fila["HORARIO"];

		$msAsistencia = "";
		$mnPresentes = 0;
		$mnAusentes = 0;
		$mnJustificados = 0;

		$msConsulta = "select ASISTENCIA_REL from KDSA140A, KDSA021A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and CURSO_REL = '" . trim($msCurso) . "'";
		$msConsulta .= "and FECHA_140 BETWEEN ? and ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$mdFechaIni, $mdFechaFin]);

		while ($auxiliar = $mAuxiliar->fetch())
		{
			$msAsistencia .= "'" . $auxiliar["ASISTENCIA_REL"] . "',";
		}

		$msAsistencia = substr($msAsistencia, 0, -1);

		$msConsulta = "select COUNT(MATRICULA_REL) as CONTEO from KDSA141A where ESTADO_141 = 0 and ASISTENCIA_REL in (" . trim($msAsistencia) . ")";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute();
		$Fila = $mAuxiliar->fetch();
		$mnPresentes = $Fila["CONTEO"];
		$mnTotalPresentes += $mnPresentes;

		$msConsulta = "select COUNT(MATRICULA_REL) as CONTEO from KDSA141A where ESTADO_141 = 1 and ASISTENCIA_REL in (" . trim($msAsistencia) . ")";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute();
		$Fila = $mAuxiliar->fetch();
		$mnAusentes = $Fila["CONTEO"];
		$mnTotalAusentes += $mnAusentes;

		$msConsulta = "select COUNT(MATRICULA_REL) as CONTEO from KDSA141A where ESTADO_141 = 2 and ASISTENCIA_REL in (" . trim($msAsistencia) . ")";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute();
		$Fila = $mAuxiliar->fetch();
		$mnJustificados = $Fila["CONTEO"];
		$mnTotalJustificados += $mnJustificados;

		if ($mbColorea == 0)
		{
			$msHTML .= '<td style="width: 50%">' . trim($msNomCurso) . '</td>';
			$msHTML .= '<td style="width: 20%">' . trim($msHorario) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 10%">' . trim($mnPresentes) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 10%">' . trim($mnAusentes) . '</td>';
			$msHTML .= '<td style="text-align: center; width: 10%">' . trim($mnJustificados) . '</td>';
		}
		else
		{
			$msHTML .= '<td style="background-color: #f2f2f2; width: 50%">' . trim($msNomCurso) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; width: 20%">' . trim($msHorario) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 10%">' . trim($mnPresentes) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 10%">' . trim($mnAusentes) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 10%">' . trim($mnJustificados) . '</td>';
		}

		$msHTML .= '</tr>';
		
		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;
	}
	$msHTML .= '</tbody><tfoot><tr>';
	$msHTML .= '<td style="background-color:rgb(0,0,255); color: white; width: 50%;"></td>';
	$msHTML .= '<td style="background-color:rgb(0,0,255); color: white; width: 20%;"></td>';
	$msHTML .= '<td style="text-align: center; background-color:rgb(0,0,255); color: white; width: 10%;">' . trim($mnTotalPresentes) . '</td>';
	$msHTML .= '<td style="text-align: center; background-color:rgb(0,0,255); color: white; width: 10%;">' . trim($mnTotalAusentes) . '</td>';
	$msHTML .= '<td style="text-align: center; background-color:rgb(0,0,255); color: white; width: 10%;">' . trim($mnTotalJustificados) . '</td>';
	$msHTML .= '</tr></tfoot></table>';
	$pdf->SetY(25);
	$pdf->writeHTML($msHTML);
	$pdf->Output();
}
?>