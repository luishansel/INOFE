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
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
		// Title

		$mid_x = $this->GetPageWidth() / 2; // width of the "PDF screen".
		// Arial bold 18
		$this->SetFont('arial','B',13);
		$Titulo = utf8_decode('PROXIMOS PAGOS DE ESTUDIANTES');
		$this->Text($mid_x - ($this->GetStringWidth($Titulo) / 2), 13, $Titulo);
		// Arial normal 18
		$this->SetFont('arial','',11);
		$Titulo = utf8_decode($this->Periodo);
		$this->Text($mid_x - ($this->GetStringWidth($Titulo) / 2), 18, $Titulo);

		$LineaTitulo = 28;
		$this->SetFont('arial','B',8);
		$this->SetFillColor(0,100,255);
		$this->SetTextColor(255,255,255);
		$this->SetXY(15,$LineaTitulo);
		$this->Cell(50,5,'Estudiante',0,0,'L',true);		
		$this->SetXY(65,$LineaTitulo);
		$this->Cell(15,5,utf8_decode('Teléfono'),0,0,'C',true);
		$this->SetXY(80,$LineaTitulo);
		$this->Cell(65,5,'Curso',0,0,'L',true);
		$this->SetXY(145,$LineaTitulo);
		$this->Cell(70,5,'Concepto',0,0,'L',true);
		$this->SetXY(215,$LineaTitulo);
		$this->Cell(25,5,'Fecha de pago',0,0,'C',true);
		$this->SetXY(240,$LineaTitulo);
		$this->Cell(25,5,'Monto U$',0,0,'R',true);
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

$FechaIni = date("Y-m-d", strtotime($_POST["dtpFechaIni"]));
$FechaFin = date("Y-m-d", strtotime($_POST["dtpFechaFin"]));

if ($FechaIni == $FechaFin)
	$Rotulo = "Fecha del " . DevuelveFecha($_POST["dtpFechaIni"]);
else
	$Rotulo = "Período del " . DevuelveFecha($_POST["dtpFechaIni"]) . " al " . DevuelveFecha($_POST["dtpFechaFin"]);

$pdf = new PDF('L','mm','Letter','Proyecciones');
$pdf->AliasNbPages();
$pdf->Periodo=$Rotulo;
$pdf->AddPage();
$pdf->SetFillColor(255,255,255);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('arial','',7);

//Obtención de datos
$msConsulta = "select KDSA051A.MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, TELEFONO_010, CELULAR_010, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as CURSO, ";
$msConsulta .= "concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, CONCEPTO_050, FECHAPREVISTA_050, ADEUDADO_051 ";
$msConsulta .= "from KDSA051A, KDSA030A, KDSA010A, KDSA020A, KDSA050A where PAGADO_051 = 0 and EXONERADO_051 = 0 and ANULADO_051 = 0 and ";
$msConsulta .= "KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ";
$msConsulta .= "KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and ESTADO_030 <> 4 and FECHAPREVISTA_050 >= ? and FECHAPREVISTA_050 <= ? ";
$msConsulta .= "order by KDSA030A.CURSO_REL, KDSA051A.MATRICULA_REL";

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$FechaIni, $FechaFin]);
$Registros = $mDatos->rowCount();
$Linea = 33;
$Suma = 0;

while ($Fila = $mDatos->fetch())
{
	$Matricula = $Fila["MATRICULA_REL"];
	$Estudiante = utf8_decode(html_entity_decode($Fila["ESTUDIANTE"]));
	if ($Fila["TELEFONO_010"] = "" and $Fila["CELULAR_010"] = "")
	{
		$Telefono = "";
	}
	else
	{
		if (trim($Fila["CELULAR_010"]) <> "")
			$Telefono = trim($Fila["CELULAR_010"]);
		else
			$Telefono = trim($Fila["TELEFONO_010"]);
	}
	$Curso = utf8_decode(html_entity_decode($Fila["CURSO"]));
	$Concepto = utf8_decode(html_entity_decode($Fila["CONCEPTO_050"]));
	$Fecha = DevuelveFecha($Fila["FECHAPREVISTA_050"]);
	$Monto = $Fila["ADEUDADO_051"];
	
	$pdf->SetXY(15,$Linea);
	$pdf->Cell(50,5,$Estudiante);
	
	$pdf->SetXY(65,$Linea);
	$pdf->Cell(15,5,$Telefono,0,0,'L', true);
	
	$pdf->SetXY(80,$Linea);
	$pdf->Cell(65,5,$Curso,0,0,'L',true);
	
	$pdf->SetXY(145,$Linea);
	$pdf->Cell(70,5,$Concepto,0,0,'L',true);
	
	$pdf->SetXY(215,$Linea);
	$pdf->Cell(25,5,$Fecha,0,0,'C',true);
	
	$pdf->SetXY(240,$Linea);
	$pdf->Cell(25,5,number_format($Monto,2,'.',','),0,0,'R');
	
	$Suma += $Monto;
	$Linea += 5;
	
	if ($Linea >= 190)
	{
		$Linea=33;
		$pdf->AddPage();
	}
}

$pdf->SetFont('arial','B',9);
$pdf->SetFillColor(0,100,255);
$pdf->SetTextColor(255,255,255);
$pdf->SetXY(15,$Linea);
$pdf->Cell(225,6,"Totales (U$)",0,0,'R',true);
$pdf->SetXY(240,$Linea);
$pdf->Cell(25,6,number_format($Suma,2,'.',','),0,0,'R',true);
$pdf->Output();
}
?>