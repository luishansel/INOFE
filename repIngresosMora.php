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
require_once ("fpdf181/fpdf.php");
require_once ("libchart/classes/libchart.php");

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
class PDF extends FPDF
{
	public $Periodo;
	public $RotuloMoneda;
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
		// Title
		$mid_x = 210; // width of the "PDF screen", fixed by now.
		// Arial bold 18
		$this->SetFont('arial','B',13);
		$Titulo = utf8_decode('Ingresos Moratorios por Período');
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 13, $Titulo);
		// Arial normal 18
		$this->SetFont('arial','',11);
		$Titulo = utf8_decode($this->Periodo);
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 18, $Titulo);
		$Titulo = utf8_decode($this->RotuloMoneda);
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 23, $Titulo);

		$LineaTitulo = 28;
		$this->SetFont('arial','B',9);
		$this->SetFillColor(0,100,255);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$LineaTitulo);
		$this->Cell(18,5,'Fecha',0,0,'C',true);
		$this->SetXY(33,$LineaTitulo);
		$this->Cell(15,5,'Recibo',0,0,'C',true);
		$this->SetXY(48,$LineaTitulo);
		$this->Cell(50,5,'Beneficiario',0,0,'L',true);
		$this->SetXY(98,$LineaTitulo);
		$this->Cell(15,5,'Monto',0,0,'R',true);
		$this->SetXY(113,$LineaTitulo);
		$this->Cell(95,5,'Concepto',0,0,'L',true);
		// Line break
		$this->Ln(20);
	}
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Page number
		$this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'L');
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

function generarGrafico($Arreglo)
{
	//Generación del Gráfico
	$chart = new PieChart();
	$chart->getPlot()->getPalette()->setPieColor(array(
		new Color(255, 0, 0),
		new Color(0, 255, 0),
		new Color(0, 0, 255),
		new Color(255, 255, 0),
		new Color(0, 255, 255),
		new Color(255, 0, 255)
	));
	
	$dataSet = new XYDataSet();
	$dataSet->addPoint(new Point("Efectivo", $Arreglo['Efectivo']));
	$dataSet->addPoint(new Point("Tarjeta", $Arreglo['Tarjeta']));
	$dataSet->addPoint(new Point("Cheque", $Arreglo['Cheque']));
	$dataSet->addPoint(new Point("Depósito", $Arreglo['Deposito']));
	$dataSet->addPoint(new Point("eCommerce", $Arreglo['eCommerce']));
	$chart->setDataSet($dataSet);

	$chart->setTitle("Ingresos por Período");
	$fechaHoy = date('YmdHis');
	$nombreArchivo = "Grafico" . $fechaHoy . ".png";
	$chart->render($nombreArchivo);
	return $nombreArchivo;
}

$FechaIni = date("Y-m-d", strtotime($_POST["dtpFechaIni"]));
$FechaFin = date("Y-m-d", strtotime($_POST["dtpFechaFin"]));
$Serie = $_POST["optSerie"];
$MonedaRep = $_POST["optMoneda"];

if ($FechaIni == $FechaFin)
	$Rotulo = "Fecha del " . DevuelveFecha($_POST["dtpFechaIni"]);
else
	$Rotulo = "Del " . DevuelveFecha($_POST["dtpFechaIni"]) . " al " . DevuelveFecha($_POST["dtpFechaFin"]);
	
if ($MonedaRep == 0)
	$RotuloMoneda = "Expresado en Córdobas";
else
	$RotuloMoneda = "Expresado en Dólares";

$pdf = new PDF('P','mm','Letter','Ingresos');
$pdf->AliasNbPages();
$pdf->Periodo=$Rotulo;
$pdf->RotuloMoneda=$RotuloMoneda;
$pdf->AddPage();
$pdf->SetFillColor(255,255,255);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('arial','',7);

//Obtención de datos
$msConsulta = "select FECHA_040, NOMBRE_040, RECIBO_040, TIPOPAGO_040, TIPOCAMBIO_040, MONEDA_040, CONCEPTO_050, sum(MONTO_041) as MONTO, 0 as RETENCION, ";
$msConsulta .= "KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020 from KDSA040A, KDSA041A, KDSA050A, KDSA020A ";
$msConsulta .= "where KDSA040A.PAGO_REL = KDSA041A.PAGO_REL and KDSA041A.COBRO_REL = KDSA050A.COBRO_REL and TIPO_050 = 1 and OTROINGRESO_040 = 0 and ";
$msConsulta .= "KDSA050A.CURSO_REL = KDSA020A.CURSO_REL and EMPRESARIAL_040 = 0 and INATEC_040 = 0 and ANULADO_040 = 0 and SERIE_040 = ? and ";
$msConsulta .= "FECHA_040 between ? and ? group by KDSA041A.PAGO_REL ";

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$Serie, $FechaIni, $FechaFin]);
$Registros = $mDatos->rowCount();
$Linea = 33;

$mnEfectivo = 0;
$mnTarjeta = 0;
$mnCheque = 0;
$mnDeposito = 0;
$mneCommerce = 0;
$mnTotalRetencion = 0;
$mnTotalDia = 0;
$mnTotal = 0;
$FechaDeControl = "";

while ($Fila = $mDatos->fetch())
{
	$Fecha = $Fila["FECHA_040"];
	$Recibo = $Fila["RECIBO_040"];
	$Nombre = utf8_decode(html_entity_decode($Fila["NOMBRE_040"]));
	$Concepto = utf8_decode(html_entity_decode($Fila["CONCEPTO_050"]));
	$Moneda = $Fila["MONEDA_040"];
	$Monto = $Fila["MONTO"];
	$Retencion = $Fila["RETENCION"];
	$TipoPago = $Fila["TIPOPAGO_040"];
	$TipoCambio = $Fila["TIPOCAMBIO_040"];
		
	if ($FechaDeControl == "")
		$FechaDeControl = $Fecha;
	
	//Subtotales por día
	if ($FechaDeControl <> $Fecha)
	{
		$pdf->SetFillColor(120,240,194);
		$pdf->SetXY(15,$Linea);
		$Rotulo = "Total de la fecha " . DevuelveFecha($FechaDeControl);
		$pdf->Cell(83,5,$Rotulo,0,0,'L',true);
		$pdf->SetXY(98,$Linea);
		$pdf->Cell(15,5,number_format($mnTotalDia,2,'.',','),0,0,'R',true);
		$pdf->SetXY(113,$Linea);
		$pdf->Cell(95,5,"",0,0,'C',true);
		$mnTotalDia = 0;
		$pdf->SetFillColor(255,255,255);
		$FechaDeControl = $Fecha;
		$Linea += 5;
	}
		
	$pdf->SetXY(15,$Linea);
	$Rotulo = DevuelveFecha($Fecha);
	$pdf->Cell(18,5,$Rotulo,0,0,'C');

	$pdf->SetXY(33,$Linea);
	$pdf->Cell(15,5,$Recibo,0,0,'C');
	
	$pdf->SetXY(48,$Linea);
	$pdf->Cell(50,5,$Nombre);
	
	if ($MonedaRep == 0)
	{
		if ($Moneda == 0)
		{
			$MontoRep = $Monto;
			$RetencionRep = $Retencion;
		}
		else
		{
			$MontoRep = $Monto * $TipoCambio;
			$RetencionRep = $Retencion * $TipoCambio;
		}
	}
	else
	{
		if ($Moneda == 0)
		{
			$MontoRep = $Monto / $TipoCambio;
			$RetencionRep = $Retencion / $TipoCambio;
		}
		else
		{
			$MontoRep = $Monto;
			$RetencionRep = $Retencion;
		}
	}
	$pdf->SetXY(98,$Linea);
	$pdf->Cell(15,5,number_format($MontoRep,2,'.',','),0,0,'R');
	
	switch ($TipoPago)
	{
		case 0:
			$mnEfectivo += $MontoRep;
			break;
		case 1:
			$mnTarjeta += $MontoRep;
			break;
		case 2:
			$mnCheque += $MontoRep;
			break;
		case 3:
			$mnDeposito += $MontoRep;
			break;
		case 4:
			$mnDeposito += $MontoRep;
			break;
		
		case 5:
			$mneCommerce += $MontoRep;
			break;
	}
	$mnTotalDia += $MontoRep;
	$mnTotalRetencion += $RetencionRep;
	$mnTotal += $MontoRep + $RetencionRep;

	$pdf->SetXY(113,$Linea);
	$pdf->Cell(50,5,$Concepto);
	
	$Linea += 5;
	
	if ($Linea >= 250)
	{
		$Linea=33;
		$pdf->AddPage();
	}
}

//Subtotales de la última fecha
$pdf->SetFillColor(120,240,194);
$pdf->SetXY(15,$Linea);
$Rotulo = "Total de la fecha " . DevuelveFecha($FechaDeControl);
$pdf->Cell(83,5,$Rotulo,0,0,'L',true);
$pdf->SetXY(98,$Linea);
$pdf->Cell(15,5,number_format($mnTotalDia,2,'.',','),0,0,'R',true);
$pdf->SetXY(113,$Linea);
$pdf->Cell(95,5,"",0,0,'C',true);
$mnTotalDia = 0;
$pdf->SetFillColor(255,255,255);
$FechaDeControl = $Fecha;
$Linea += 5;

//Resumen
$Linea += 5;
if ($Linea >= 230)
{
	$Linea=40;
	$pdf->AddPage();
}

$pdf->SetFillColor(0,100,255);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('arial','',8);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,'Forma de Pago','BR',0,'L',true);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,'Monto','BR',0,'R',true);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,'Porcentaje','B',0,'L',true);
$pdf->SetTextColor(0,0,0);

$pdf->SetFont('arial','',7);
$Linea += 5;
$Porcentaje = round(($mnEfectivo / $mnTotal) * 100, 1);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,'Efectivo','BR',0,'L',false);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,number_format($mnEfectivo,2,'.',','),'BR',0,'R',false);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,number_format($Porcentaje,1,'.',','),'B',0,'C',false);
$arreglo['Efectivo'] = $mnEfectivo;

$Linea += 5;
$Porcentaje = round(($mnTarjeta / $mnTotal) * 100, 1);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,'Tarjeta','BR',0,'L',false);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,number_format($mnTarjeta,2,'.',','),'BR',0,'R',false);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,number_format($Porcentaje,1,'.',','),'B',0,'C',false);
$arreglo['Tarjeta'] = $mnTarjeta;

$Linea += 5;
$Porcentaje = round(($mnCheque / $mnTotal) * 100, 1);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,'Cheque','BR',0,'L',false);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,number_format($mnCheque,2,'.',','),'BR',0,'R',false);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,number_format($Porcentaje,1,'.',','),'B',0,'C',false);
$arreglo['Cheque'] = $mnCheque;

$Linea += 5;
$Porcentaje = round(($mnDeposito / $mnTotal) * 100, 1);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,utf8_decode('Depósito'),'BR',0,'L',false);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,number_format($mnDeposito,2,'.',','),'BR',0,'R',false);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,number_format($Porcentaje,1,'.',','),'B',0,'C',false);
$arreglo['Deposito'] = $mnDeposito;

$Linea += 5;
$Porcentaje = round(($mneCommerce / $mnTotal) * 100, 1);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,'eCommerce','BR',0,'L',false);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,number_format($mneCommerce,2,'.',','),'BR',0,'R',false);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,number_format($Porcentaje,1,'.',','),'B',0,'C',false);
$arreglo['eCommerce'] = $mneCommerce;

$Linea += 5;
$pdf->SetFillColor(0,100,255);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('arial','',8);
$pdf->SetXY(20,$Linea);
$pdf->Cell(30,5,'Total','BR',0,'L',true);
$pdf->SetXY(50,$Linea);
$pdf->Cell(25,5,number_format($mnTotal,2,'.',','),'BR',0,'R',true);
$pdf->SetXY(75,$Linea);
$pdf->Cell(15,5,'100.0','B',0,'C',true);

$grafico = generarGrafico($arreglo);
$pdf->Image($grafico,95,$Linea-30,0,50);
$pdf->Output();
}
?>