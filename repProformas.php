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
		// Title
		$mid_x = 210; // width of the "PDF screen", fixed by now.
		// helvetica bold 18
		$this->SetFont('helvetica','B',13);
		$Titulo = utf8_decode('PROFORMA OFICIAL');
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 18, $Titulo);
		// Arial normal 18
		$this->SetFont('helvetica','',7);
		$Titulo = utf8_decode("Calle principal de Altamira, Edificio Super Center 1c. al este");
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 22, $Titulo);
		$Titulo = utf8_decode("Teléfono: 2299 5290 / eMail: info@insitutoinofe.com");
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 26, $Titulo);
		$Titulo = utf8_decode("RUC 4410605910006C");
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 30, $Titulo);

		// Line break
		$this->Ln(20);
	}
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// helvetica italic 8
		$this->SetFont('helvetica','I',8);
		//$this->SetFillColor(229,50,45);
		$this->SetTextColor(0,0,0);
		// Page number
		$this->Cell(0,10,mb_convert_encoding(html_entity_decode('Página '),"UTF-8").$this->PageNo().'/'.$this->getAliasNbPages(),0,0,'L');
		$this->Cell(0,10,'Emitido: ' . date("d/m/Y h:i:s a") . '',0,0,'R',true);
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

function DevuelveFechaLarga($Fecha)
{
	$FechaDividida = explode("-", $Fecha);
	
	$Anno = $FechaDividida[0];
	$Mes = $FechaDividida[1];
	$Dia = $FechaDividida[2];
	
	switch ($Mes)
		{
			case "01":
				$NombreMes = "Enero";
				break;
			case "02":
				$NombreMes = "Febrero";
				break;
			case "03":
				$NombreMes = "Marzo";
				break;
			case "04":
				$NombreMes = "Abril";
				break;
			case "05":
				$NombreMes = "Mayo";
				break;
			case "06":
				$NombreMes = "Junio";
				break;
			case "07":
				$NombreMes = "Julio";
				break;
			case "08":
				$NombreMes = "Agosto";
				break;
			case "09":
				$NombreMes = "Septiembre";
				break;
			case "10":
				$NombreMes = "Octubre";
				break;
			case "11":
				$NombreMes = "Noviembre";
				break;
			case "12":
				$NombreMes = "Diciembre";
				break;
		}
	return ($Dia . " de " . $NombreMes . " de " . $Anno);
}

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
$pdf->AddPage();

$codProforma = trim($_POST["KDSA"]);

//Obtención de datos
$msConsulta = "select KDSA090A.PROFORMA_REL, FECHA_090, TIPO_060, NOMBRE_060, NOMBRECONTACTO_060, CEDULARUC_060, PATRONAL_060, CORREO_060, TELEFONOCONTACTO_060, INATEC_090, ";
$msConsulta .= "TIPOCAMBIO_090, NOMBRE_020 as NOMBRE_KDSA, FECHAINI_020 as FECHA_INI, FECHAFIN_020 as FECHA_FIN, VALOR_020 as VALOR_CURSO, CONCAT('De ', DATE_FORMAT(HORAINI_020, '%h:%i %p'), ' a ', DATE_FORMAT(HORAFIN_020, '%h:%i %p')) as HORARIO, OBSERVACIONES_090, ";
$msConsulta .= "fxDevuelveDias(KDSA020A.CURSO_REL) as DIAS_CLASE, NOMBRE_070 as NOMBRE_INATEC, CODIGO_070 as COD_INATEC, ACUERDO_070 as ACUERDO_INATEC, CANTIDAD_091 as CUPOS, HORASCLASE_070 as HORAS_CLASE ";
$msConsulta .= "from KDSA090A, KDSA091A, KDSA060A, KDSA070A, KDSA020A " ;
$msConsulta .= "where KDSA090A.PROFORMA_REL = KDSA091A.PROFORMA_REL and KDSA090A.PROSPECTO_REL = KDSA060A.PROSPECTO_REL and ";
$msConsulta .= "KDSA091A.CURSO_REL = KDSA020A.CURSO_REL and KDSA020A.CURSOINATEC_REL = KDSA070A.CURSOINATEC_REL and ";
$msConsulta .= "KDSA090A.PROFORMA_REL = ? ";
$msConsulta .= "union ";
$msConsulta .= "select KDSA090A.PROFORMA_REL, FECHA_090, TIPO_060, NOMBRE_060, NOMBRECONTACTO_060, CEDULARUC_060, PATRONAL_060, CORREO_060, TELEFONOCONTACTO_060, INATEC_090, ";
$msConsulta .= "TIPOCAMBIO_090, CURSOKDSA_092 as NOMBRE_KDSA, FECHAINI_092 as FECHA_INI, FECHAFIN_092 as FECHA_FIN, PRECIO_092 as VALOR_CURSO, HORARIO_092 as HORARIO, OBSERVACIONES_090, ";
$msConsulta .= "DIASCLASE_092 as DIAS_CLASE, CURSOINATEC_092 as NOMBRE_INATEC, CODIGOINATEC_092 as COD_INATEC, ACUERDO_092 as ACUERDO_INATEC, CUPOS_092 as CUPOS, HORASCLASE_092 as HORAS_CLASE ";
$msConsulta .= "from KDSA090A, KDSA092A, KDSA060A " ;
$msConsulta .= "where KDSA090A.PROFORMA_REL = KDSA092A.PROFORMA_REL and KDSA090A.PROSPECTO_REL = KDSA060A.PROSPECTO_REL and ";
$msConsulta .= "KDSA090A.PROFORMA_REL = ? ";

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$codProforma, $codProforma]);
$Linea = 40;
$Suma = 0;
$mbPrimeraVez = true;

while ($Fila = $mDatos->fetch())
{
	$Proforma = $Fila["PROFORMA_REL"];
	$Fecha = DevuelveFechaLarga($Fila["FECHA_090"]);
	$Solicitante = utf8_decode(html_entity_decode($Fila["NOMBRECONTACTO_060"]));
	$Empresa = utf8_decode(html_entity_decode($Fila["NOMBRE_060"]));
	$Tipo = $Fila["TIPO_060"];
	$Patronal = $Fila["PATRONAL_060"];
	$Correo = $Fila["CORREO_060"];
	$CedulaRuc = $Fila["CEDULARUC_060"];
	$Telefono = $Fila["TELEFONOCONTACTO_060"];
	$CodigoInatec = $Fila["COD_INATEC"];
	$AcuerdoInatec = $Fila["ACUERDO_INATEC"];
	$Cantidad = $Fila["CUPOS"];
	$Inatec = $Fila["INATEC_090"];
	$Horario = $Fila["HORARIO"];
	$Oservaciones = utf8_decode(html_entity_decode($Fila["OBSERVACIONES_090"]));
	$Dias = utf8_decode(html_entity_decode($Fila["DIAS_CLASE"]));
	$Valor = $Fila["VALOR_CURSO"];
	$DetalleHorario = trim($Dias . " " . $Horario);
	$TipoCambio = $Fila["TIPOCAMBIO_090"];
	$Total = $Valor * $Cantidad;
	$FechaIni = DevuelveFecha($Fila["FECHA_INI"]);
	$FechaFin = DevuelveFecha($Fila["FECHA_FIN"]);
	$DetalleFecha = "Del " . trim($FechaIni) . " al " . trim($FechaFin);
	$HorasClase = $Fila["HORAS_CLASE"];
	
	if ($Inatec == 0)
		$Curso = mb_convert_encoding(html_entity_decode($Fila["NOMBRE_KDSA"]), "UTF-8");
	else
		$Curso = mb_convert_encoding(html_entity_decode($Fila["NOMBRE_INATEC"] . " (Código " . trim($CodigoInatec) . " / Acuerdo ". trim($AcuerdoInatec) . ")"), "UTF-8");
	
	if ($mbPrimeraVez == true)
	{
		$pdf->SetTextColor(0,0,0);
		
		$pdf->SetFont('helvetica','B',9);
		$Texto = utf8_decode("Consecutivo:");
		$pdf->SetXY(15,$Linea);
		$pdf->Cell(30,5,$Texto,0,0,'L');

		$pdf->SetFont('helvetica','',9);
		$pdf->SetXY(55,$Linea);
		$pdf->Cell(100,5,$Proforma,0,0,'L');
		
		$Linea += 5;
		$pdf->SetFont('helvetica','B',9);
		$Texto = utf8_decode("Fecha:");
		$pdf->SetXY(15,$Linea);
		$pdf->Cell(30,5,$Texto,0,0,'L');

		$pdf->SetFont('helvetica','',9);
		$pdf->SetXY(55,$Linea);
		$pdf->Cell(100,5,$Fecha,0,0,'L');
		
		$Linea += 5;
		$pdf->SetFont('helvetica','B',9);
		$Texto = utf8_decode("Nombre del solicitante:");
		$pdf->SetXY(15,$Linea);
		$pdf->Cell(30,5,$Texto,0,0,'L');

		$pdf->SetFont('helvetica','',9);
		$pdf->SetXY(55,$Linea);
		$pdf->Cell(100,5,$Solicitante,0,0,'L');
		
		if ($Tipo = 1) //Prospecto de Empresa
		{
			$Linea += 5;
			$pdf->SetFont('helvetica','B',9);
			$Texto = utf8_decode("Nombre de la empresa:");
			$pdf->SetXY(15,$Linea);
			$pdf->Cell(30,5,$Texto,0,0,'L');
	
			$pdf->SetFont('helvetica','',9);
			$Texto = $Empresa . " (RUC " . $CedulaRuc . ")";
			$pdf->SetXY(55,$Linea);
			$pdf->Cell(100,5,$Texto,0,0,'L');
			
			$Linea += 5;
			$pdf->SetFont('helvetica','B',9);
			$Texto = mb_convert_encoding(html_entity_decode("Número patronal:"), "UTF-8");
			$pdf->SetXY(15,$Linea);
			$pdf->Cell(30,5,$Texto,0,0,'L');
	
			$pdf->SetFont('helvetica','',9);
			$pdf->SetXY(55,$Linea);
			$pdf->Cell(100,5,$Patronal,0,0,'L');
		}
		else //Prospecto Natural
		{
			$Linea += 5;
			$pdf->SetFont('helvetica','B',9);
			$Texto = utf8_decode("Cédula:");
			$pdf->SetXY(15,$Linea);
			$pdf->Cell(30,5,$Texto,0,0,'L');
	
			$pdf->SetFont('helvetica','',9);
			$pdf->SetXY(55,$Linea);
			$pdf->Cell(100,5,$CedulaRuc,0,0,'L');
		}
		
		$Linea += 5;
		$pdf->SetFont('helvetica','B',9);
		$Texto = utf8_decode("Correo del solicitante:");
		$pdf->SetXY(15,$Linea);
		$pdf->Cell(30,5,$Texto,0,0,'L');

		$pdf->SetFont('helvetica','',9);
		$pdf->SetXY(55,$Linea);
		$pdf->Cell(100,5,$Correo,0,0,'L');
		
		$Linea += 5;
		$pdf->SetFont('helvetica','B',9);
		$Texto = mb_convert_encoding(html_entity_decode("Lugar de la capacitación:"), "UTF-8");
		$pdf->SetXY(15,$Linea);
		$pdf->Cell(30,5,$Texto,0,0,'L');

		$pdf->SetFont('helvetica','',9);
		$pdf->SetXY(55,$Linea);
		$Texto = utf8_decode("Instalaciones de INOFE");
		$pdf->Cell(100,5,$Texto,0,0,'L');
		
		//Inicio del Cuadro de detalle
		$pdf->SetTextColor(255,255,255);
		$pdf->SetDrawColor(229,50,45);
		$pdf->SetFillColor(229,50,45);
		$pdf->SetFont('helvetica','',10);
		
		$Linea += 10;
		$EjeY_Ini = $Linea;
		$pdf->SetXY(15,$Linea);
		$pdf->Cell(15,5,"Cupos",1,0,'C',True);
		$pdf->SetXY(30,$Linea);
		$pdf->Cell(125,5,mb_convert_encoding(html_entity_decode("Descripción"),"UTF-8"),1,0,'C',True);
		$pdf->SetXY(155,$Linea);
		$pdf->Cell(25,5,"Precio unitario",1,0,'R',True);
		$pdf->SetXY(180,$Linea);
		$pdf->Cell(20,5,"Precio total",1,0,'R',True);
		
		$Linea += 5;
		$mbPrimeraVez = false;
	}
	
	$pdf->SetTextColor(0,0,0);
	$pdf->SetDrawColor(229,50,45);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('helvetica','',9);

	$pdf->SetXY(15,$Linea);
	$pdf->Cell(15,7,$Cantidad,0,0,'C');
	
	$pdf->SetXY(30,$Linea);
	$pdf->MultiCell(125,4,mb_convert_encoding(html_entity_decode($Curso),"UTF-8"),0,'L');
	
	$pdf->SetXY(155,$Linea);
	$pdf->Cell(25,7,number_format($Valor,2,'.',','),0,0,'R');
	
	$pdf->SetXY(180,$Linea);
	$pdf->Cell(20,7,number_format($Total,2,'.',','),0,0,'R');
	
	/*Detalle de la Descripción*/
	$Linea += 8;
	$pdf->SetFont('helvetica','B',7);
	$pdf->SetXY(15,$Linea);
	$pdf->Cell(15,7,"",0,0,'C');
	$pdf->SetXY(30,$Linea);
	$pdf->Cell(10,5,'Horario:',0,0,'L');
	$pdf->SetFont('helvetica','',7);
	$pdf->SetXY(47,$Linea);
	$pdf->Cell(105,5,mb_convert_encoding(html_entity_decode($DetalleHorario),"UTF-8"),0,0,'L');
	$pdf->SetXY(155,$Linea);
	$pdf->Cell(25,7,"",0,0,'R');
	$pdf->SetXY(180,$Linea);
	$pdf->Cell(20,7,"",0,0,'R');
	
	$Linea += 4;
	$pdf->SetFont('helvetica','B',7);
	$pdf->SetXY(15,$Linea);
	$pdf->Cell(15,5,"",0,0,'C');
	$pdf->SetXY(30,$Linea);
	$pdf->Cell(10,5,mb_convert_encoding(html_entity_decode('Período:'), "UTF-8"),0,0,'L');
	$pdf->SetFont('helvetica','',7);
	$pdf->SetXY(47,$Linea);
	$pdf->Cell(105,5,$DetalleFecha,0,0,'L');
	$pdf->SetXY(155,$Linea);
	$pdf->Cell(25,5,"",0,0,'R');
	$pdf->SetXY(180,$Linea);
	$pdf->Cell(20,5,"",0,0,'R');

	$Linea += 4;
	$pdf->SetFont('helvetica','B',7);
	$pdf->SetXY(15,$Linea);
	$pdf->Cell(15,5,"",0,0,'C');
	$pdf->SetXY(30,$Linea);
	$pdf->Cell(10,5,utf8_decode('Horas-clase:'),0,0,'L');
	$pdf->SetFont('helvetica','',7);
	$pdf->SetXY(47,$Linea);
	$pdf->Cell(105,5,$HorasClase,0,0,'L');
	$pdf->SetXY(155,$Linea);
	$pdf->Cell(25,5,"",0,0,'R');
	$pdf->SetXY(180,$Linea);
	$pdf->Cell(20,5,"",0,0,'R');
	
	$Suma += $Total;
	$Linea += 7;
	
	if ($Linea >= 250)
	{
		$Linea=40;
		$pdf->AddPage();
	}
}

$pdf->SetFont('helvetica','',9);
$pdf->SetXY(15,$Linea);
$pdf->Cell(15,5,"",0,0,'C');

$pdf->SetXY(30,$Linea);
$pdf->Cell(125,5,"",0,0,'L');

$pdf->SetXY(155,$Linea);
$pdf->Cell(25,5,"Sub-Total (U$)",'T',0,'L');

$pdf->SetXY(180,$Linea);
$pdf->Cell(20,5,number_format($Suma,2,'.',','),'T',0,'R');

$Linea += 5;

$pdf->SetXY(15,$Linea);
$pdf->Cell(15,5,"",0, 0,'C');

$pdf->SetXY(30,$Linea);
$pdf->Cell(125,5,"",0, 0,'L');

$pdf->SetXY(155,$Linea);
$pdf->Cell(25,5,"Tipo de cambio",'T',0,'L');

$pdf->SetXY(180,$Linea);
$pdf->Cell(20,5,number_format($TipoCambio,4,'.',','),'T',0,'R');

$Linea += 5;
$Cordobas = $TipoCambio * $Suma;

$pdf->SetXY(15,$Linea);
$pdf->Cell(15,5,"",'B',0,'C');

$pdf->SetXY(30,$Linea);
$pdf->Cell(125,5,"",'B',0,'L');

$pdf->SetXY(155,$Linea);
$pdf->Cell(25,5,"Total (C$)",'TB', 0,'L');

$pdf->SetXY(180,$Linea);
$pdf->Cell(20,5,number_format($Cordobas,2,'.',','),'TB',0,'R');

$pdf->SetFont('helvetica','',7);
$Linea += 7;

//Dibuja las lineas verticales
$pdf->Line(15, $EjeY_Ini, 15, $Linea-2);
$pdf->Line(30, $EjeY_Ini, 30, $Linea-2);
$pdf->Line(155, $EjeY_Ini, 155, $Linea-2);
$pdf->Line(180, $EjeY_Ini, 180, $Linea-2);
$pdf->Line(200, $EjeY_Ini, 200, $Linea-2);

if ($Oservaciones <> "")
{
	$pdf->SetFillColor(220,220,220);
	$pdf->SetFont('helvetica','B',7);
	$pdf->SetXY(15,$Linea);
	$pdf->Cell(185,5,"OBSERVACIONES",0,0,'L',true);
	$Linea += 5;
	$pdf->SetXY(15,$Linea);
	$pdf->SetFont('helvetica','',7);
	$pdf->MultiCell(185,5,mb_convert_encoding(html_entity_decode($Oservaciones),"UTF-8"),0,'L',true);
	$Linea += 9;
}
$Texto = utf8_decode("-Esta proforma es válida por quince (15) días a partir de su elaboración.");
$pdf->SetXY(15,$Linea);
$pdf->Cell(200,5,$Texto,0,0,'L');

$Linea += 3;
$Texto = utf8_decode("-En caso de cancelar a través de cheque, elaborarlo a nombre de ");
$pdf->SetXY(15,$Linea);
$pdf->Cell(60,5,$Texto,0,0,'L');
$pdf->SetFont('helvetica','B',7);
$Texto = utf8_decode("Instituto de Oficios y Formación para el Empleo");
$pdf->SetXY(88,$Linea);
$pdf->Cell(100,5,$Texto,0,0,'L');

$Linea += 3;
$pdf->SetFont('helvetica','',7);
$Texto = utf8_decode("-De no cancelarse en la fecha indicada, se cobrará mantenimiento del valor y mora del 10% mensual.");
$pdf->SetXY(15,$Linea);
$pdf->Cell(200,5,$Texto,0,0,'L');

$Linea += 3;
$Texto = utf8_decode("-La tasa de cambio es proyectada por INOFE.");
$pdf->SetXY(15,$Linea);
$pdf->Cell(200,5,$Texto,0,0,'L');

$Linea += 20;
$mid_x = 190;
$pdf->SetFont('helvetica','B',8);
$Texto = utf8_decode("Lic. Seydi Castillo H.");
$posY = ($mid_x - $pdf->GetStringWidth($Texto)) / 2;
$pdf->SetXY($posY,$Linea);
$pdf->Cell(50,5,$Texto,'T',0,'C');
$Linea += 3;
$pdf->SetFont('helvetica','',6);
$Texto = utf8_decode("Dirección");
$pdf->SetXY($posY,$Linea);
$pdf->Cell(50,5,$Texto,0,0,'C');

$pdf->Image('imagenes/selloKdsa.png',118,$Linea-18,0,20);
$pdf->Image('imagenes/firmaSeydi.png',85,$Linea-15,0,15);

$pdf->Output();
}
?>