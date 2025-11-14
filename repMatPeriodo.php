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
		$mid_x = 210; // width of the "PDF screen", fixed by now.
		// Arial bold 18
		$this->SetFont('arial','B',13);
		$Titulo = utf8_decode('Matrículas por Período');
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 13, $Titulo);
		// Arial normal 18
		$this->SetFont('arial','',11);
		$Titulo = utf8_decode($this->Periodo);
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 18, $Titulo);

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
if (isset($_POST["chkConDetalle"]))
	$chkConDetalle = 1;
else
	$chkConDetalle = 0;

if ($FechaIni == $FechaFin)
	$Rotulo = "Fecha del " . DevuelveFecha($_POST["dtpFechaIni"]);
else
	$Rotulo = "Del " . DevuelveFecha($_POST["dtpFechaIni"]) . " al " . DevuelveFecha($_POST["dtpFechaFin"]);

$pdf = new PDF('P','mm','Letter','Matriculas');
$pdf->AliasNbPages();
$pdf->Periodo=$Rotulo;
$pdf->AddPage();

//Obtención de datos
if ($chkConDetalle == 1)
{
	$msConsulta = "select FECHA_030, KDSA030A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as NOMBRE_020, ";
	$msConsulta .= "count(KDSA030A.MATRICULA_REL) as CONTEO from KDSA030A, KDSA020A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTADO_030 <> 4 ";
	$msConsulta .= "and FECHA_030 between ? and ? ";
	$msConsulta .= "GROUP BY FECHA_030, KDSA030A.CURSO_REL, NOMBRE_020 ORDER BY FECHA_030, NOMBRE_020";
}
else
{
	$msConsulta = "select year(FECHA_030) as ANNO, (case month(FECHA_030) when 1 then 'Enero' when 2 then 'Febrero' when 3 ";
	$msConsulta .= "then 'Marzo' when 4 then 'Abril' when 5 then 'Mayo' when 6 then 'Junio' when 7 then 'Julio' when 8 then 'Agosto' when 9 then ";
	$msConsulta .= "'Septiembre' when 10 then 'Octubre' when 11 then 'Noviembre' else 'Diciembre' end) as NOMBRE_MES, KDSA030A.CURSO_REL, ";
	$msConsulta .= "concat(NOMBRE_020, ' (', CONVOCATORIA_020, ' /G', GRUPO_020, ')') as NOMBRE_020, ";
	$msConsulta .= "count(KDSA030A.MATRICULA_REL) as CONTEO from KDSA030A, KDSA020A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTADO_030 <> 4 ";
	$msConsulta .= "and FECHA_030 between ? and ? ";
	$msConsulta .= "GROUP BY year(FECHA_030), month(FECHA_030), KDSA030A.CURSO_REL, NOMBRE_020 ORDER BY year(FECHA_030), month(FECHA_030), NOMBRE_020";
}

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$FechaIni, $FechaFin]);
$Registros = $mDatos->rowCount();
$Linea = 30;

$mbTitulos = true;
$msFechaControl = "";
$mnCuentaMes = 0;

while ($Fila = $mDatos->fetch())
{
	if ($chkConDetalle == 1)
		$Fecha = DevuelveFecha($Fila["FECHA_030"]);
	else
		$Fecha = $Fila["NOMBRE_MES"] . " " . $Fila["ANNO"];
	$Curso = utf8_decode(html_entity_decode($Fila["NOMBRE_020"]));
	$CodCurso = $Fila["CURSO_REL"];
	$Matriculados = $Fila["CONTEO"];
	
	if ($mbTitulos)
	{
		$pdf->SetFont('arial','B',9);
		$pdf->SetFillColor(0,100,255);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetXY(30,$Linea);
		$pdf->Cell(25,5,'Fecha',0,0,'C',true);
		$pdf->SetXY(55,$Linea);
		$pdf->Cell(100,5,'Curso',0,0,'L',true);
		$pdf->SetXY(155,$Linea);
		$pdf->Cell(15,5,'Matriculados',0,0,'R',true);
		$Linea += 5;
		$mbTitulos = false;
	}
	
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('arial','',9);	
	
	if ($Fecha <> $msFechaControl)
	{
		if ($msFechaControl <> "")
		{
			$pdf->SetFillColor(187,215,255);
			$pdf->SetFont('arial','B',9);
			$pdf->SetXY(30,$Linea);
			if ($chkConDetalle == 1)
				$Texto = "Matriculados el " . $msFechaControl;
			else
				$Texto = "Matriculados en " . $msFechaControl;
			$pdf->Cell(125,5,$Texto,0,0,'R',true);
			
			$pdf->SetXY(155,$Linea);
			$pdf->Cell(15,5,$mnCuentaMes,0,0,'R',true);
			
			$mnCuentaMes = 0;
			$Linea += 5;
			$pdf->SetFillColor(255,255,255);
			$pdf->SetFont('arial','',9);
		}
		
		$msFechaControl = $Fecha;
		$pdf->SetXY(30,$Linea);
		$pdf->Cell(25,5,$Fecha,0,0,'L');
	}
	else
	{
		$pdf->SetXY(30,$Linea);
		$pdf->Cell(25,5,"",0,0,'L');
	}

	$pdf->SetXY(55,$Linea);
	$pdf->Cell(100,5,$Curso,0,0,'L');
	
	$pdf->SetXY(155,$Linea);
	$pdf->Cell(15,5,$Matriculados,0,0,'R',true);
	
	$Linea += 5;
	$mnCuentaMes += $Matriculados;
	
	if ($Linea >= 255)
	{
		$Linea=30;
		$mbTitulos = true;
		$pdf->AddPage();
	}
}

//Subtotales de la última fecha
$pdf->SetFillColor(187,215,255);
$pdf->SetFont('arial','B',9);
$pdf->SetXY(30,$Linea);
if ($chkConDetalle == 1)
	$Texto = "Matriculados el " . $msFechaControl;
else
	$Texto = "Matriculados en " . $msFechaControl;
	
$pdf->Cell(125,5,$Texto,0,0,'R',true);

$pdf->SetXY(155,$Linea);
$pdf->Cell(15,5,$mnCuentaMes,0,0,'R',true);

$pdf->Output();
}
?>