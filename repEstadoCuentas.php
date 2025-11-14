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
	public $Nombre;
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',12,6,0,15);
		// Title
		$mid_x = 210; // width of the "PDF screen", fixed by now.

		$this->SetFont('helvetica','B',13);
		$Titulo = 'Estado de Cuentas del estudiante';
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 8, $Titulo);

		$this->SetFont('helvetica','',11);
		$Titulo =  mb_convert_encoding(html_entity_decode($this->Nombre), "UTF-8");
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 14, $Titulo);
	}
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);

		$this->SetFont('helvetica','I',8);
		// Page number
		$this->Cell(0,10, mb_convert_encoding('Página ', "UTF-8").$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
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

$Estudiante = $_POST["cboEstudiante"];
$msConsulta = "Select concat(APELLIDOS_010, ', ', NOMBRES_010) as NOMBRE from KDSA010A where ESTUDIANTE_REL = ?";
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$Estudiante]);
$Fila = $mDatos->fetch();
$NombreEstudiante =  mb_convert_encoding(html_entity_decode($Fila["NOMBRE"]), "UTF-8");

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

$pdf->Nombre=$NombreEstudiante;

//Obtención de datos
$msConsulta = "select MATRICULA_REL, NOMBRE_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, CONVOCATORIA_020, GRUPO_020, ";
$msConsulta .= "fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE from KDSA030A, KDSA020A ";
$msConsulta .= "where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTUDIANTE_REL = ?";
$mMatricula = $m_cnx_MySQL->prepare($msConsulta);
$mMatricula->execute([$Estudiante]);

$mHTML = "";

while ($filaMatricula = $mMatricula->fetch())
{
	$msMatricula = $filaMatricula["MATRICULA_REL"];
	$msCurso = mb_convert_encoding(html_entity_decode($filaMatricula["NOMBRE_020"]), "UTF-8");
	$msFechaIni = $filaMatricula["FECHAINI_020"];
	$msFechaFin = $filaMatricula["FECHAFIN_020"];
	$msHoraIni = date_create($filaMatricula["HORAINI_020"]);
	$msHoraFin = date_create($filaMatricula["HORAFIN_020"]);
	$msConvocatoria = $filaMatricula["CONVOCATORIA_020"];
	$msGrupo = $filaMatricula["GRUPO_020"];
	$msDiasClase = $filaMatricula["DIASCLASE"];
	$msVigencia = "Del " . DevuelveFecha($msFechaIni) . " al " . DevuelveFecha($msFechaFin);
	$msHorario = $msDiasClase . ". De " . date_format($msHoraIni, 'h:i a') . " a " . date_format($msHoraFin, 'h:i a');
	
	$mHTML .= '<h3>' . $msCurso . ' (' . $msConvocatoria . ' / G' . $msGrupo . ')</h3>';
	$mHTML .= '<label><i>' . $msVigencia . '</i></label><br>';
	$mHTML .= '<label><i>' . $msHorario . '</i></label><br><br>';

	$msConsulta = "select KDSA050A.COBRO_REL, FECHAPREVISTA_050, IFNULL((select FECHA_040 from KDSA041A, KDSA040A where KDSA041A.PAGO_REL = KDSA040A.PAGO_REL ";
	$msConsulta .= "and KDSA041A.COBRO_REL = KDSA051A.COBRO_REL and KDSA041A.MATRICULA_REL = KDSA051A.MATRICULA_REL order by FECHA_040 desc limit 1), '1900-01-01') as FECHAPAGO, ";
	$msConsulta .= "CONCEPTO_050, MONTO_050, ABONADO_051, ADEUDADO_051, TIPO_050 from KDSA051A, KDSA050A where KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ";
	$msConsulta .= "KDSA051A.MATRICULA_REL = ? and ANULADO_051 = 0 and EXONERADO_051 = 0 order by FECHAPREVISTA_050";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msMatricula]);

	$mHTML .= '<table width="100%">';
	$mHTML .= '<tr style="background-color: rgb(0,0,255); color: rgb(255,255,255)">';
	$mHTML .= '<td style="width: 40%;">Concepto</td>';
	$mHTML .= '<td style="width: 15%; text-align: center;">Fecha de cobro</td>';
	$mHTML .= '<td style="width: 15%; text-align: center;">Fecha de pago</td>';
	$mHTML .= '<td style="width: 10%; text-align: right">Valor del cobro</td>';
	$mHTML .= '<td style="width: 10%; text-align: right;">Abonado</td>';
	$mHTML .= '<td style="width: 10%; text-align: right;">Adeudado</td>';
	$mHTML .= '</tr>';

	$mnLinea = 1;
	$mnTotalAbonado = 0;
	$mnTotalAdeudado = 0;
	while ($fila = $mDatos->fetch())
	{
		$msCobro = $fila["COBRO_REL"];
		$msConcepto = $fila["CONCEPTO_050"];
		$msFechaPrevista = $fila["FECHAPREVISTA_050"];
		$msFechaPago = $fila["FECHAPAGO"];
		$mnMonto = $fila["MONTO_050"];
		$mnAbonado = $fila["ABONADO_051"];
		$mnTipo = $fila["TIPO_050"];
		$mnAdeudado = $fila["ADEUDADO_051"];
		$mnMoratorio = $mnAbonado + $mnAdeudado;
		$mnTotalAbonado += $mnAbonado;
		$mnTotalAdeudado += $mnAdeudado;

		$mHTML .= '<tr>';
		if ($mnLinea % 2 == 0)
		{
			$mHTML .= '<td style="height:15px; width: 40%; background-color: rgb(230,230,230)">' . $msConcepto . '</td>';
			$mHTML .= '<td style="height:15px; width: 15%; text-align: center; background-color: rgb(230,230,230)">' . DevuelveFecha($msFechaPrevista) . '</td>';
			if ($msFechaPago == '1900-01-01')
				$mHTML .= '<td style="height:15px; width: 15%; text-align: center; background-color: rgb(230,230,230)"></td>';
			else
				$mHTML .= '<td style="height:15px; width: 15%; text-align: center; background-color: rgb(230,230,230)">' . DevuelveFecha($msFechaPago) . '</td>';
			if ($mnTipo == 1)
				$mHTML .= '<td style="height:15px; width: 10%; text-align: right; background-color: rgb(230,230,230)">' . number_format($mnMoratorio, 2) . '</td>';
			else
				$mHTML .= '<td style="height:15px; width: 10%; text-align: right; background-color: rgb(230,230,230)">' . $mnMonto . '</td>';
			$mHTML .= '<td style="height:15px; width: 10%; text-align: right; background-color: rgb(230,230,230)">' . $mnAbonado . '</td>';
			$mHTML .= '<td style="height:15px; width: 10%; text-align: right; background-color: rgb(230,230,230)">' . $mnAdeudado . '</td>';
		}
		else
		{
			$mHTML .= '<td style="height:15px; width: 40%">' . $msConcepto . '</td>';
			$mHTML .= '<td style="height:15px; width: 15%; text-align: center">' . DevuelveFecha($msFechaPrevista) . '</td>';
			if ($msFechaPago == '1900-01-01')
				$mHTML .= '<td style="height:15px; width: 15%; text-align: center"></td>';
			else
				$mHTML .= '<td style="height:15px; width: 15%; text-align: center">' . DevuelveFecha($msFechaPago) . '</td>';
			if ($mnTipo == 1)
				$mHTML .= '<td style="height:15px; width: 10%; text-align: right">' . number_format($mnMoratorio, 2) . '</td>';
			else
				$mHTML .= '<td style="height:15px; width: 10%; text-align: right">' . $mnMonto . '</td>';
			$mHTML .= '<td style="height:15px; width: 10%; text-align: right">' . $mnAbonado . '</td>';
			$mHTML .= '<td style="height:15px; width: 10%; text-align: right">' . $mnAdeudado . '</td>';
		}
		$mHTML .= '</tr>';

		$mnLinea++;
	}

	$mHTML .= '<tr style="background-color: rgb(0,0,255); color: rgb(255,255,255)">';
	$mHTML .= '<td style="height:15px; width: 40%;"></td>';
	$mHTML .= '<td style="height:15px; width: 15%; text-align: center;"></td>';
	$mHTML .= '<td style="height:15px; width: 15%; text-align: center;"></td>';
	$mHTML .= '<td style="height:15px; width: 10%; text-align: center"></td>';
	$mHTML .= '<td style="height:15px; width: 10%; text-align: right;">C$ ' . number_format($mnTotalAbonado, 2) . '</td>';
	$mHTML .= '<td style="height:15px; width: 10%; text-align: right;">C$ ' . number_format($mnTotalAdeudado, 2) . '</td>';
	$mHTML .= '</tr>';
	$mHTML .= '</table>';
}

$pdf->AddPage();
$pdf->SetXY(40,25);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(255,255,255);
$pdf->SetFont('helvetica','',8);
$pdf->writeHTML($mHTML, true, false, true, false, '');
$pdf->Output();
}
?>