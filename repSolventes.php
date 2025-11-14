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
	public $Curso;
	public $Vigencia;
	public $Horario;
	public $DiasClase;
	public $Convocatoria;
	public $FechaFin;
	
	// Page header
	function Header()
	{
		// Logos
		$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
		$mid_x = 210;
		// Title
		$this->SetFont('arial','B',15);
		$Titulo = utf8_decode('ESTUDIANTES SOLVENTES');
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 15, $Titulo);
		$this->SetFont('arial','',12);
		$Titulo = 'Cerrado al ' . $this->FechaFin;
		$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 20, $Titulo);

		$Linea = 30;
		
		//DATOS DEL CURSO
		$this->SetTextColor(0,0,0);
		
		$this->SetFont('arial','B',8);
		$this->Text(50, $Linea, 'Curso:');
		$this->SetFont('arial','',8);
		$this->Text(75, $Linea, utf8_decode(html_entity_decode($this->Curso)));
		
		$Linea += 5;
		$this->SetFont('arial','B',8);
		$this->Text(50, $Linea, 'Vigencia:');
		$this->SetFont('arial','',8);
		$this->Text(75, $Linea, $this->Vigencia);
		
		$Linea += 5;
		$this->SetFont('arial','B',8);
		$this->Text(50, $Linea, 'Dias de clase:');
		$this->SetFont('arial','',8);
		$this->Text(75, $Linea, utf8_decode(html_entity_decode($this->DiasClase)));
		
		$Linea += 5;
		$this->SetFont('arial','B',8);
		$this->Text(50, $Linea, 'Horario:');
		$this->SetFont('arial','',8);
		$this->Text(75, $Linea, utf8_decode(html_entity_decode($this->Horario)));
		
		$Linea += 5;
		$this->SetFont('arial','B',8);
		$this->Text(50, $Linea, 'Convocatoria:');
		$this->SetFont('arial','',8);
		$this->Text(75, $Linea, $this->Convocatoria);
		
		$Linea += 5;
		$this->SetFillColor(0,100,255);
		$this->SetTextColor(255,255,255);
		$this->SetXY(50,$Linea);
		$this->Cell(20,7,utf8_decode('Matrícula'),0,0,'L',true);
		$this->SetXY(70,$Linea);
		$this->Cell(20,7,utf8_decode('Estudiante'),0,0,'L',true);
		$this->SetXY(90,$Linea);
		$this->Cell(70,7,utf8_decode('Nombre del Estudiante'),0,0,'L',true);
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

$codCurso = trim($_POST["cboCurso"]);
$fecha = $_POST["dtpFechaFin"];

//Obtención de datos
$msConsulta = "select A.* from ";
$msConsulta .= "(select distinct KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, KDSA030A.MATRICULA_REL, KDSA010A.ESTUDIANTE_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as NOMBRECOMPLETO from KDSA051A, KDSA050A, KDSA030A, KDSA010A, KDSA020A where KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTADO_030 <> 2 and KDSA050A.ANULADO_050 = 0 and (KDSA051A.PAGADO_051 = 1 or KDSA051A.EXONERADO_051 = 1) and KDSA051A.ANULADO_051 = 0 and FECHAPREVISTA_050 <= ? and KDSA020A.CURSO_REL = ?) as A ";
$msConsulta .= "left join ";
$msConsulta .= "(select distinct KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAINI_020, FECHAFIN_020, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, KDSA030A.MATRICULA_REL, KDSA010A.ESTUDIANTE_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as NOMBRECOMPLETO from KDSA051A, KDSA050A, KDSA030A, KDSA010A, KDSA020A where KDSA051A.COBRO_REL = KDSA050A.COBRO_REL and KDSA051A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ESTADO_030 <> 2 and KDSA050A.ANULADO_050 = 0 and KDSA051A.PAGADO_051 = 0 and KDSA051A.EXONERADO_051 = 0 and KDSA051A.ANULADO_051 = 0 and FECHAPREVISTA_050 <= ? and KDSA020A.CURSO_REL = ?) as B ";
$msConsulta .= "on A.MATRICULA_REL = B.MATRICULA_REL where B.MATRICULA_REL is null ";
$msConsulta .= "order by A.CURSO_REL, A.MATRICULA_REL";

$m_cnx_MySQL = fxAbrirConexion();
$mDatos = $m_cnx_MySQL->prepare($msConsulta);
$mDatos->execute([$fecha, $codCurso, $fecha, $codCurso]);
$Registros = $mDatos->rowCount();
$Fila = $mDatos->fetch();

$Curso = $Fila["NOMBRE_020"];
$FechaIni = $Fila["FECHAINI_020"];
$FechaFin = $Fila["FECHAFIN_020"];
$HoraIni = date_create($Fila["HORAINI_020"]);
$HoraFin = date_create($Fila["HORAFIN_020"]);
$Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
$DiasClase = $Fila["DIASCLASE"];
$Convocatoria = $Fila["CONVOCATORIA_020"];

$pdf = new PDF('P','mm','Letter','Estudiantes Solventes');
$pdf->AliasNbPages();

if ($Registros > 0)
{	
	$pdf->Curso=$Curso;
	$pdf->Vigencia= "Del " . DevuelveFecha($FechaIni) . " al " . DevuelveFecha($FechaFin);
	$pdf->Horario=$Horario;
	$pdf->DiasClase=$DiasClase;
	$pdf->Convocatoria=$Convocatoria;
	$pdf->FechaFin=DevuelveFecha($fecha);
	
	$pdf->AddPage();
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('arial','',8);
	
	$Linea = 63;
	for ($i = 0; $i < $Registros; $i++)
	{
		$Matricula = $Fila["MATRICULA_REL"];
		$Estudiante = $Fila["ESTUDIANTE_REL"];
		$NombreCompleto = utf8_decode(html_entity_decode($Fila["NOMBRECOMPLETO"]));
		
		$pdf->SetXY(50,$Linea);
		$pdf->Cell(20,5,$Matricula,0,0,'L',false);
		
		$pdf->SetXY(70,$Linea);
		$pdf->Cell(20,5,$Estudiante,0,0,'L',false);
		
		$pdf->SetXY(90,$Linea);
		$pdf->Cell(70,5,$NombreCompleto,0,0,'L',false);
		
		$Linea += 5;
		
		if ($Linea >= 245)
		{
			$Linea=63;
			$pdf->AddPage();
		}
		$Fila = $mDatos->fetch();
	}
	
	$Linea += 5;
	$pdf->SetXY(90,$Linea);
	if ($Registros == 1)
		$pdf->Cell(70,5,$Registros . " solvente",0,0,'R',false);
	else
		$pdf->Cell(70,5,$Registros . " solventes",0,0,'R',false);
}
$pdf->Output();
}
?>