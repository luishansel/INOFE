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
		public $msNombre;
		public $msCedula;
		
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			// Title
			$mid_x = 210; // width of the "PDF screen", fixed by now.

			$this->SetFont('helvetica','B',12);
			$Titulo = 'SOPORTE DE PAGO A DOCENTES';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			$this->SetFont('helvetica','',9);
			$Titulo = $this->msPeriodo;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 15, $Titulo);
			$this->SetFont('helvetica','B',9);
			$this->Text(15, 30, "Docente");
			$this->SetFont('helvetica','',9);
			$this->Text(30, 30, $this->msNombre);
			$this->SetFont('helvetica','B',9);
			$this->Text(15, 35, "Cédula");
			$this->SetFont('helvetica','',9);
			$this->Text(30, 35, $this->msCedula);
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

	$msDocente = $_POST["cboDocente"];
	$mdFechaIni = $_POST["dtpFechaIni"];
	$mdFechaFin = $_POST["dtpFechaFin"];

	$msConsulta = "select NOMBRE_100, CEDULA_100 from KDSA100A where DOCENTE_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msDocente]);
	$Fila = $mDatos->fetch();
	$msNombre = html_entity_decode($Fila["NOMBRE_100"]);
	$msCedula = $Fila["CEDULA_100"];
	$msPeriodo = "Período del " . DevuelveFecha($mdFechaIni) . " al " . DevuelveFecha($mdFechaFin);

	$pdf = new PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->msNombre=$msNombre;
	$pdf->msCedula=$msCedula;
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
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Fecha</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 27%">Curso</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 27%">Módulo</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 13%">Valor bruto</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Retención</th>';
	$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 13%">Neto</th>';
	$msHTML .= '</tr></thead><tbody>';

	$msConsulta = "select ASISTENCIA_REL, FECHA_140, concat(NOMBRE_020, '(', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, NOMBRE_021, ";
	$msConsulta .= "VALOR_021 from KDSA140A, KDSA021A, KDSA020A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and ";
	$msConsulta .= "KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and FECHA_140 between ? ";
	$msConsulta .= "and ? and KDSA021A.DOCENTE_REL = ? order by KDSA140A.MODULO_REL, FECHA_140";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFechaIni, $mdFechaFin, $msDocente]);

	$mbColorea = 0;
	$mnTotalValor = 0;
	$mnTotalRetencion = 0;
	$mnTotalNeto = 0;

	while ($fila = $mDatos->fetch())
	{
		$msHTML .= '<tr>';
		$mdFecha = DevuelveFecha($fila["FECHA_140"]);
		$msCurso = html_entity_decode($fila["NOMBRE_020"]);
		$msModulo = $fila["NOMBRE_021"];
		$mnValor = floatval($fila["VALOR_021"]);
		$mnRetencion = $mnValor * 0.1;
		$mnNeto = $mnValor - $mnRetencion;
		$mnTotalValor += $mnValor;
		$mnTotalRetencion += $mnRetencion;
		$mnTotalNeto += $mnNeto;

		if ($mbColorea == 0)
		{
			$msHTML .= '<td style="width: 10%">' . trim($mdFecha) . '</td>';
			$msHTML .= '<td style="width: 27%">' . trim($msCurso) . '</td>';
			$msHTML .= '<td style="width: 27%">' . trim($msModulo) . '</td>';
			$msHTML .= '<td style="text-align: right; width: 13%">' . number_format($mnValor, 2, '.', ',') . '</td>';
			$msHTML .= '<td style="text-align: right; width: 10%">' . number_format($mnRetencion, 2, '.', ',') . '</td>';
			$msHTML .= '<td style="text-align: right; width: 13%">' . number_format($mnNeto, 2, '.', ',') . '</td>';
		}
		else
		{
			$msHTML .= '<td style="background-color: #f2f2f2; width: 10%">' . trim($mdFecha) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; width: 27%">' . trim($msCurso) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; width: 27%">' . trim($msModulo) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: right; width: 13%">' . number_format($mnValor, 2, '.', ',') . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: right; width: 10%">' . number_format($mnRetencion, 2, '.', ',') . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2; text-align: right; width: 13%">' . number_format($mnNeto, 2, '.', ',') . '</td>';
		}

		$msHTML .= '</tr>';
		
		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;
	}
	$msHTML .= '</tbody><tfoot><tr>';
	$msHTML .= '<td style="text-align: right; background-color:rgb(0,0,255); color: white;"></td>';
	$msHTML .= '<td style="text-align: right; background-color:rgb(0,0,255); color: white;"></td>';
	$msHTML .= '<td style="text-align: right; background-color:rgb(0,0,255); color: white;"></td>';
	$msHTML .= '<td style="text-align: right; background-color:rgb(0,0,255); color: white;">' . number_format($mnTotalValor, 2, '.', ',') . '</td>';
	$msHTML .= '<td style="text-align: right; background-color:rgb(0,0,255); color: white;">' . number_format($mnTotalRetencion, 2, '.', ',') . '</td>';
	$msHTML .= '<td style="text-align: right; background-color:rgb(0,0,255); color: white;">' . number_format($mnTotalNeto, 2, '.', ',') . '</td>';
	$msHTML .= '</tr></tfoot></table>';
	$pdf->SetY(40);
	$pdf->writeHTML($msHTML);
	$pdf->Output();
}
?>