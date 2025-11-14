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
	class PDF extends FPDF
	{
		public $msCurso;
		public $msConvocatoria;
		public $msHorario;

		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			// Title
			$mid_x = 278; // width of the "PDF screen", fixed by now.
			// Arial bold 18
			$this->SetFont('arial','B',14);
			$Titulo = utf8_decode('ASISTENCIA ESTUDIANTIL');
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 13, $Titulo);
			// Arial normal 18
			$this->SetFont('arial','',11);
			$Titulo = utf8_decode(html_entity_decode($this->msCurso));
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 17, $Titulo);
			$Titulo = "Convocatoria " . $this->msConvocatoria;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 21, $Titulo);
			$Titulo = $this->msHorario;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 25, $Titulo);
			$this->Ln(17);
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

		function TextWithDirection($x, $y, $txt, $direction='R')
		{
			if ($direction=='R')
				$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			elseif ($direction=='L')
				$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			elseif ($direction=='U')
				$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			elseif ($direction=='D')
				$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			else
				$s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			if ($this->ColorFlag)
				$s='q '.$this->TextColor.' '.$s.' Q';
			$this->_out($s);
		}

		function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
		{
			$font_angle+=90+$txt_angle;
			$txt_angle*=M_PI/180;
			$font_angle*=M_PI/180;

			$txt_dx=cos($txt_angle);
			$txt_dy=sin($txt_angle);
			$font_dx=cos($font_angle);
			$font_dy=sin($font_angle);

			$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
			if ($this->ColorFlag)
				$s='q '.$this->TextColor.' '.$s.' Q';
			$this->_out($s);
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

	$msCurso = $_POST["msCurso"];

	$msConsulta = "select NOMBRE_020, concat(CONVOCATORIA_020, ' / ', 'G', GRUPO_020) as CONVOCATORIA, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIAS from KDSA020A where CURSO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$Fila = $mDatos->fetch();
	$msNomCurso = utf8_decode($Fila["NOMBRE_020"]);
	$msConvocatoria = $Fila["CONVOCATORIA"];
	$msHoraIni = date_create($Fila["HORAINI_020"]);
	$msHoraFin = date_create($Fila["HORAFIN_020"]);
	$msDias = utf8_decode($Fila["DIAS"]);
	$msHorario = $msDias . " / De " . date_format($msHoraIni, 'h:i a') . " a " . date_format($msHoraFin, 'h:i a');
	$pdf = new PDF('L','mm','Letter');
	$pdf->AliasNbPages();
	$pdf->msCurso=$msNomCurso;
	$pdf->msConvocatoria=$msConvocatoria;
	$pdf->msHorario=$msHorario;
	$pdf->AddPage();
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('arial','',8);
	$pdf->SetFillColor(185,238,255);

	$primeraVez = true;
	$mbRelleno = true;
	$mbEscribeRotulo = true;
	$Linea = 50;

	$msConsulta = "select MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, CELULAR_010 from KDSA030A, KDSA010A ";
	$msConsulta .= "where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 not in (4, 5) and KDSA030A.CURSO_REL = ?";
	$mEstudiantes = $m_cnx_MySQL->prepare($msConsulta);
	$mEstudiantes->execute([$msCurso]);

	while ($filaEstudiantes = $mEstudiantes->fetch())
	{
		$msMatricula = $filaEstudiantes["MATRICULA_REL"];
		$msEstudiante = html_entity_decode($filaEstudiantes["ESTUDIANTE"]);
		$msTelefono = $filaEstudiantes["CELULAR_010"];
		$Columna = 10;
		
		$pdf->SetXY($Columna,$Linea);
		$pdf->Cell(70,5,utf8_decode($msEstudiante),0,0,'L',$mbRelleno);
		$Columna += 70;

		if ($mbEscribeRotulo)
		{
			$pdf->SetXY($Columna,$Linea-5);
			$pdf->Cell(15,5,utf8_decode("Teléfono"),0,0,'C',false);
			$mbEscribeRotulo = false;
		}
		$pdf->SetXY($Columna,$Linea);
		$pdf->Cell(15,5,$msTelefono,0,0,'C',$mbRelleno);
		$Columna += 15;

		$msConsulta = "select FECHACLASE_130 from KDSA130A, KDSA021A where KDSA130A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? ";
		$msConsulta .= "and not exists(select * from KDSA022A where CURSO_REL = ? and FECHACLASE_130 = FECHA_022) order by FECHA_130";
		$mFechas = $m_cnx_MySQL->prepare($msConsulta);
		$mFechas->execute([$msCurso, $msCurso]);

		while ($filaFechas = $mFechas->fetch())
		{
			$msFecha = date("Y-m-d", strtotime($filaFechas["FECHACLASE_130"]));
			
			if ($primeraVez)
			{
				$msFechaRep = DevuelveFecha($msFecha);
				$pdf->TextWithDirection($Columna+3,47,$msFechaRep,'U');
			}

			$msConsulta = "select ESTADO_141 from KDSA141A, KDSA140A where KDSA141A.ASISTENCIA_REL = KDSA140A.ASISTENCIA_REL and ";
			$msConsulta .= "MATRICULA_REL = ? and FECHA_140 = ?";
			$mAsistencia = $m_cnx_MySQL->prepare($msConsulta);
			$mAsistencia->execute([$msMatricula, $msFecha]);
			$Registros = $mAsistencia->rowCount();
			if ($Registros > 0)
			{
				$filaAsistencia = $mAsistencia->fetch();
				$mnEstado = $filaAsistencia["ESTADO_141"];
				switch ($mnEstado)
				{
					case 0:
						$msEstado = "P";
					break;

					case 1:
						$msEstado = "A";
					break;

					default:
						$msEstado = "J";
				}
			}
			else
				$msEstado = "";
			
			$pdf->SetXY($Columna,$Linea);
			$pdf->Cell(5,5,$msEstado,0,0,'C',$mbRelleno);
			$Columna += 5;
		}
		
		$primeraVez = false;
		$mbRelleno = !$mbRelleno;
		$Linea += 5;
	}

	$msConsulta = "select FECHA_140, JUSTIFICACION_141, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE from KDSA141A, KDSA140A, KDSA030A, KDSA021A, KDSA010A ";
	$msConsulta .= "where KDSA141A.ASISTENCIA_REL = KDSA140A.ASISTENCIA_REL and KDSA140A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = ? ";
	$msConsulta .= "and KDSA141A.MATRICULA_REL = KDSA030A.MATRICULA_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_141 = 2";
	$mJustificaciones = $m_cnx_MySQL->prepare($msConsulta);
	$mJustificaciones->execute([$msCurso]);
	$mnRegistros = $mJustificaciones->rowCount();

	if ($mnRegistros > 0)
	{
		$Linea += 3;
		$pdf->SetFont('arial','B',9);
		$pdf->SetXY(10,$Linea);
		$pdf->Cell(20,5,"JUSTIFICACIONES",0,0,'L',false);
		$Linea += 5;

		while ($filaJustificación = $mJustificaciones->fetch())
		{
			$msFecha = DevuelveFecha($filaJustificación["FECHA_140"]);
			$msEstudiante = $filaJustificación["ESTUDIANTE"];
			$msJustificacion = $filaJustificación["JUSTIFICACION_141"];
			$pdf->SetFont('arial','',8);
			$pdf->SetXY(10,$Linea);
			$pdf->Cell(200,5,$msFecha . " - " . $msEstudiante,0,0,'L',false);
			$Linea += 5;
			$pdf->SetFont('arial','I',8);
			$pdf->SetXY(10,$Linea);
			$pdf->Cell(200,5,$msJustificacion,0,0,'L',false);
			$Linea += 7;

			if ($Linea >= 190)
			{
				$Linea=33;
				$pdf->AddPage();

				$pdf->SetFont('arial','B',9);
				$pdf->SetXY(10,$Linea);
				$pdf->Cell(20,5,"JUSTIFICACIONES",0,0,'L',false);
				$Linea += 5;
			}
		}
	}

	$pdf->Output();
}
?>