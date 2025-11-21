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
	public $Periodo;
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',15,10,0,15);
		// Title
		$mid_x = 210; // width of the "PDF screen", fixed by now.
		// Arial bold 18
		$this->SetFont('helvetica','B',13);
		$Titulo = 'Cuentas por Cobrar';
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 11, $Titulo);
		// Arial normal 18
		$this->SetFont('helvetica','',11);
		$Titulo = mb_convert_encoding($this->Periodo, "UTF-8");
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 16, $Titulo);
	}
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('helvetica','I',8);
		// Page number
		$this->Cell(0,10,mb_convert_encoding('Página ', "UTF-8").$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
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

$FechaFin = date("Y-m-d", strtotime($_POST["dtpFechaFin"]));
$Activos = $_POST["blActivo"];
$Rotulo = "Hasta la fecha " . DevuelveFecha($_POST["dtpFechaFin"]);

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

$pdf->Periodo=$Rotulo;
$pdf->AddPage();
$pdf->SetFont('helvetica','',8);

//Obtención de datos
if ($Activos == 1)
{
	$msConsulta = "select KDSA051A.MATRICULA_REL, CELULAR_010, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, CONCEPTO_050, ADEUDADO_051 ";
	$msConsulta .= "from KDSA051A, KDSA030A, KDSA010A, KDSA050A, KDSA020A where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and ANULADO_050 = 0 and ";
	$msConsulta .= "KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
	$msConsulta .= "and KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ESTADO_030 in (0, 1, 3) and FECHAPREVISTA_050 <= ? ";
	$msConsulta .= "and ACTIVO_020 = 1 order by KDSA030A.CURSO_REL, KDSA051A.MATRICULA_REL";
}
else
{
	$msConsulta = "select KDSA051A.MATRICULA_REL, CELULAR_010, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, CONCEPTO_050, ADEUDADO_051 ";
	$msConsulta .= "from KDSA051A, KDSA030A, KDSA010A, KDSA050A, KDSA020A where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and ANULADO_050 = 0 and ";
	$msConsulta .= "KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
	$msConsulta .= "and KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ESTADO_030 in (0, 1, 3) and FECHAPREVISTA_050 <= ? ";
	$msConsulta .= "order by KDSA030A.CURSO_REL, KDSA051A.MATRICULA_REL";
}
$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$FechaFin]);
$Registros = $mDatos->rowCount();
$Suma = 0;
$mbFondo = 0;

$msHTML = "<style>";
$msHTML .= "th{";
$msHTML .= "background-color: rgb(0,100,255); color: rgb(255,255,255);";
$msHTML .= "}";
$msHTML .= ".fondoGris{";
$msHTML .= "background-color: rgb(240,240,240); color: rgb(0,0,0);";
$msHTML .= "}";
$msHTML .= "</style>";
$msHTML .= "<table>";
$msHTML .= "<thead>";
$msHTML .= "<tr>";
$msHTML .= '<th style="width: 10%;">Celular</th>';
$msHTML .= '<th style="width: 40%;">Estudiante</th>';
$msHTML .= '<th style="width: 40%;">Concepto</th>';
$msHTML .= '<th style="width: 10%; text-align: right;">Monto</th>';
$msHTML .= "</tr>";
$msHTML .= "</thead>";
$msHTML .= "<tbody>";

while ($Fila = $mDatos->fetch())
{
	$Matricula = $Fila["MATRICULA_REL"];
	$Celular = $Fila["CELULAR_010"];
	$Estudiante = mb_convert_encoding(html_entity_decode($Fila["ESTUDIANTE"]), "UTF-8");
	$Concepto = mb_convert_encoding(html_entity_decode($Fila["CONCEPTO_050"]), "UTF-8");
	$Monto = $Fila["ADEUDADO_051"];
	
	$Suma += $Monto;

	if ($mbFondo == 0)
	{
		$msHTML .= "<tr>";
		$msHTML .= '<td style="width: 10%;">' . $Celular . "</td>";
		$msHTML .= '<td style="width: 40%;">' . $Estudiante . "</td>";
		$msHTML .= '<td style="width: 40%;">' . $Concepto . "</td>";
		$msHTML .= '<td style="width: 10%; text-align: right;">' . number_format($Monto,2,'.',',') . "</td>";
		$msHTML .= "</tr>";
		$mbFondo = 1;
	}
	else
	{
		$msHTML .= "<tr>";
		$msHTML .= '<td class="fondoGris" style="width: 10%;">' . $Celular . "</td>";
		$msHTML .= '<td class="fondoGris" style="width: 40%;">' . $Estudiante . "</td>";
		$msHTML .= '<td class="fondoGris" style="width: 40%;">' . $Concepto . "</td>";
		$msHTML .= '<td class="fondoGris" style="width: 10%; text-align: right;">' . number_format($Monto,2,'.',',') . "</td>";
		$msHTML .= "</tr>";
		$mbFondo = 0;
	}
}
$msHTML .= "</tbody>";
$msHTML .= "</table>";
$msHTML .= "<h3>Total por cobrar: " . number_format($Suma,2,'.',',') . "</h3>";

$pdf->SetXY(15, 27);
$pdf->writeHTML($msHTML);
$pdf->Output();
}
?>