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
			$Titulo = 'BITACORA';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			$this->SetFont('helvetica','',9);
			$Titulo = $this->msPeriodo;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 17, $Titulo);

			$posX = 15;
			$posY = 24;
			$this->SetFillColor(0,0,255);
			$this->SetTextColor(255,255,255);
			$this->SetXY($posX, $posY);
			$this->Cell(50, 6,'Usuario', 0, 0, 'L', true);
			$posX += 50;
			$this->SetXY($posX, $posY);
			$this->Cell(20, 6,'Fecha', 0, 0, 'L', true);
			$posX += 20;
			$this->SetXY($posX, $posY);
			$this->Cell(50, 6,'Tabla', 0, 0, 'L', true);
			$posX += 50;
			$this->SetXY($posX, $posY);
			$this->Cell(40, 6,'Registro', 0, 0, 'L', true);
			$posX += 40;
			$this->SetXY($posX, $posY);
			$this->Cell(30, 6,'Operación', 0, 0, 'L', true);
			$this->Ln(3);
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

	$mFechaIni = $_POST["dtpFechaIni"];
	$mFechaFin = $_POST["dtpFechaFin"];
	if (isset($_POST["txtUsuario"]))
		$mUsuario = $_POST["txtUsuario"];
	else
		$mUsuario = "";

	if ($mFechaIni == $mFechaFin)
		$msPeriodo = "Día " . DevuelveFecha($mFechaIni);
	else
		$msPeriodo = "Del " . DevuelveFecha($mFechaIni) . " hasta " . DevuelveFecha($mFechaFin);

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

	$mFechaIni = $mFechaIni . " 00:00:00";
	$mFechaFin = $mFechaFin . " 23:59:59";

	if ($mUsuario != "")
	{
		$msConsulta = "select NOMBRE_002, FECHA_000, TABLA_000, fxNombreTabla(TABLA_000) as TABLA_NOMBRE, (case LLAVE2_000 when '' then LLAVE1_000 else concat(LLAVE1_000, '/', LLAVE2_000) end) as LLAVE, OPERACION_000 ";
		$msConsulta .= "from KDSA000A join KDSA002A on USUARIO_000 = USUARIO_REL where FECHA_000 between ? and ? and USUARIO_000 = ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mFechaIni, $mFechaFin, $mUsuario]);
	}
	else
	{
		$msConsulta = "select NOMBRE_002, FECHA_000, TABLA_000, fxNombreTabla(TABLA_000) as TABLA_NOMBRE, (case LLAVE2_000 when '' then LLAVE1_000 else concat(LLAVE1_000, '/', LLAVE2_000) end) as LLAVE, OPERACION_000 ";
		$msConsulta .= "from KDSA000A join KDSA002A on USUARIO_000 = USUARIO_REL where FECHA_000 between ? and ?";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mFechaIni, $mFechaFin]);
	}

	$mbColorea = 0;
	$pdf->SetY(30);

	while ($fila = $mDatos->fetch())
	{
		$posY = $pdf->GetY();
		$posX = 15;

		if ($posY >= 245)
		{
			$posY= 30;
			$pdf->AddPage();
		}

		$msUsuario = html_entity_decode($fila["NOMBRE_002"]);
		$msFecha = $fila["FECHA_000"];
		$msTabla = $fila["TABLA_NOMBRE"];
		$msRegistro = $fila["LLAVE"];
		$msOperacion = $fila["OPERACION_000"];

		if ($mbColorea == 0)
			$pdf->SetFillColor(255,255,255);
		else
			$pdf->SetFillColor(242,242,242);

		$pdf->SetXY($posX, $posY);
		$pdf->MultiCell(50, 10, trim($msUsuario), 0, 'L', true, 1);
		$posX += 50;
		$pdf->SetXY($posX, $posY);
		$pdf->MultiCell(20, 10, trim($msFecha), 0, 'L', true, 1);
		$posX += 20;
		$pdf->SetXY($posX, $posY);
		$pdf->MultiCell(50, 10, trim($msTabla), 0, 'L', true, 1);
		$posX += 50;
		$pdf->SetXY($posX, $posY);
		$pdf->MultiCell(40, 10, trim($msRegistro), 0, 'L', true, 1);
		$posX += 40;
		$pdf->SetXY($posX, $posY);
		$pdf->MultiCell(30, 10, trim($msOperacion), 0, 'L', true, 1);

		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;
	}
	$pdf->Output();
}
?>