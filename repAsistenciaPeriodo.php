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
	class PDF extends FPDF
	{
		public $Periodo;
		
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			$mid_x = 210;
			// Title
			$this->SetFont('arial','B',10);
			$Titulo = utf8_decode('ASISTENCIAS REPORTADAS POR LOS DOCENTES');
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 16, $Titulo);
			$this->SetFont('arial','B',8);
			$this->Text(($mid_x - $this->GetStringWidth($this->Periodo)) / 2, 20, $this->Periodo);

			$Linea = 28;
			
			$this->SetTextColor(0,0,0);
			$this->SetFont('arial','',8);
			$this->SetFillColor(0,100,255);
			$this->SetTextColor(255,255,255);
			$this->SetXY(10,$Linea);
			$this->Cell(16,7,utf8_decode('Asistencia'),0,0,'L',true);
			$this->SetXY(26,$Linea);
			$this->Cell(18,7,utf8_decode('Fecha'),0,0,'L',true);
			$this->SetXY(44,$Linea);
			$this->Cell(50,7,utf8_decode('Curso'),0,0,'L',true);
			$this->SetXY(94,$Linea);
			$this->Cell(50,7,utf8_decode('Módulo'),0,0,'L',true);
			$this->SetXY(144,$Linea);
			$this->Cell(28,7,utf8_decode('Horario'),0,0,'L',true);
			$this->SetXY(172,$Linea);
			$this->Cell(28,7,utf8_decode('Docente'),0,0,'L',true);
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

	$mdFechaIni = $_POST["dtpFechaIni"];
	$mdFechaFin = $_POST["dtpFechaFin"];

	$pdf = new PDF('P','mm','Letter','Asistencia');
	$pdf->AliasNbPages();
	$pdf->Periodo = "Del " . DevuelveFecha($mdFechaIni) . " al " . DevuelveFecha($mdFechaFin);

	//Obtención de datos
	$msConsulta = "select ASISTENCIA_REL, FECHA_140, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020, ";
	$msConsulta .= "NOMBRE_021, HORAINI_020, HORAFIN_020, NOMBRE_100 ";
	$msConsulta .= "from KDSA140A, KDSA021A, KDSA020A, KDSA100A where KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and ";
	$msConsulta .= "KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL and ";
	$msConsulta .= "FECHA_140 between ? and ? order by FECHA_140, ASISTENCIA_REL";

	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFechaIni, $mdFechaFin]);
	$Linea = 35;
	$pdf->AddPage();
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetFont('arial','',6);

	while ($Fila = $mDatos->fetch())
	{
		$Asistencia = $Fila["ASISTENCIA_REL"];
		$Fecha = DevuelveFecha($Fila["FECHA_140"]);
		$Curso = utf8_decode(html_entity_decode($Fila["NOMBRE_020"]));
		$Modulo = utf8_decode(html_entity_decode($Fila["NOMBRE_021"]));
		$HoraIni = date_create($Fila["HORAINI_020"]);
		$HoraFin = date_create($Fila["HORAFIN_020"]);
		$Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
		$Docente = utf8_decode(html_entity_decode($Fila["NOMBRE_100"]));
				
		$pdf->SetXY(10,$Linea);
		$pdf->Cell(16,5,$Asistencia,0,0,'L',false);
		
		$pdf->SetXY(26,$Linea);
		$pdf->Cell(18,5,$Fecha,0,0,'L',false);
		
		$pdf->SetXY(44,$Linea);
		$pdf->Cell(50,5,$Curso,0,0,'L',true);
		
		$pdf->SetXY(94,$Linea);
		$pdf->Cell(50,5,$Modulo,0,0,'L',true);
		
		$pdf->SetXY(144,$Linea);
		$pdf->Cell(28,5,$Horario,0,0,'L',true);

		$pdf->SetXY(172,$Linea);
		$pdf->Cell(20,5,$Docente,0,0,'L',false);
		
		$Linea += 5;
		
		if ($Linea >= 255)
		{
			$Linea=35;
			$pdf->AddPage();
		}
	}
	$pdf->Output();
}
?>