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
		public $mbFondoAzul;
		public $mbRecuadro;
		public $mbLineas;
		var $widths; 
		var $aligns;

		function SetWidths($w) 
		{ 
			//Set the array of column widths 
			$this->widths=$w; 
		}

		function SetAligns($a) 
		{ 
			//Set the array of column alignments 
			$this->aligns=$a; 
		}

		function DibujaHorizontal()
		{
			$x=$this->GetX(); 
			$y=$this->GetY();
			$this->Line($x,$y,$x+255,$y);
		}

		function Row($data)
		{ 
			//Calculate the height of the row 
			$nb=0; 
			for($i=0;$i<count($data);$i++)
				$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i])); 
			$h=5*$nb;
			//Issue a page break first if needed 
			$this->CheckPageBreak($h); 

			$this->SetDrawColor(0,100,255);
			
			//Draw the cells of the row 
			for($i=0;$i<count($data);$i++)
			{ 
				$w=$this->widths[$i]; 
				$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L'; 
				//Save the current position 
				$x=$this->GetX(); 
				$y=$this->GetY(); 

				//Draw the border
				if ($this->mbRecuadro)
					$this->Rect($x,$y,$w,$h);

				//Invierte el fondo de las Celdas
				if ($this->mbFondoAzul)
				{
					$this->SetFillColor(0,100,255);
					$this->SetTextColor(255,255,255);
					//Print the text
					$this->MultiCell($w,5,$data[$i],0,$a,$data[0],true);
				}
				else
				{
					$this->SetFillColor(255,255,255);
					$this->SetTextColor(0,0,0);
					//Print the text
					$this->MultiCell($w,5,$data[$i],0,$a,$data[0],false);
				}
				//Dibuja las lineas verticales
				if ($this->mbLineas)
				$this->Line($x,$y,$x,$y+$h);

				//Dibuja la última linea vertical
				if ($i==count($data)-1 and $this->mbLineas)
					$this->Line($x+$w,$y,$x+$w,$y+$h);

				//Put the position to the right of the cell 
				$this->SetXY($x+$w,$y); 
			} 
			//Go to the next line 
			$this->Ln($h); 
		}

		function CheckPageBreak($h) 
		{ 
			//If the height h would cause an overflow, add a new page immediately 
			if($this->GetY()+$h>$this->PageBreakTrigger) 
				$this->AddPage($this->CurOrientation); 
		} 

		function NbLines($w,$txt) 
		{ 
			//Computes the number of lines a MultiCell of width w will take 
			$cw=&$this->CurrentFont['cw']; 
			if($w==0) 
				$w=$this->w-$this->rMargin-$this->x; 
			$wmax=($w-2*$this->cMargin)*1000/$this->FontSize; 
			$s=str_replace("\r",'',$txt); 
			$nb=strlen($s); 
			if($nb>0 and $s[$nb-1]=="\n") 
				$nb--; 
			$sep=-1; 
			$i=0; 
			$j=0; 
			$l=0; 
			$nl=1; 
			while($i<$nb) 
			{ 
				$c=$s[$i]; 
				if($c=="\n") 
				{ 
					$i++; 
					$sep=-1; 
					$j=$i; 
					$l=0; 
					$nl++; 
					continue; 
				} 
				if($c==' ') 
					$sep=$i; 
				$l+=$cw[$c]; 
				if($l>$wmax) 
				{ 
					if($sep==-1) 
					{ 
						if($i==$j) 
							$i++; 
					} 
					else 
						$i=$sep+1; 
					$sep=-1; 
					$j=$i; 
					$l=0; 
					$nl++; 
				} 
				else 
					$i++; 
			} 
			return $nl; 
		}
		
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			// Title
			$mid_x = 278; // width of the "PDF screen", fixed by now.
			// Arial bold 18
			$this->SetFont('arial','B',14);
			$Titulo = utf8_decode('PLANIFICACION PROGRAMATICA');
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 13, $Titulo);
			// Arial normal 18
			$this->SetFont('arial','',11);
			$Titulo = utf8_decode($this->msCurso);
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
	}

	$mnTipoRep = $_POST["mnTipoRep"];
	$msCurso = $_POST["msCurso"];
	$msModulo = $_POST["msModulo"];
	$m_cnx_MySQL = fxAbrirConexion();

	$msConsulta = "select NOMBRE_020, concat(CONVOCATORIA_020, ' / ', 'G', GRUPO_020) as CONVOCATORIA, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIAS from KDSA020A where CURSO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$Fila = $mDatos->fetch();
	$msNomCurso = $Fila["NOMBRE_020"];
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
	$pdf->SetFont('arial','',6);

	//Obtención de datos
	if ($Administrador == 0)
	{
		$mDocente = $_SESSION["gsDocente"];
		$msConsulta = "select KDSA120A.PLANIFICACION_REL, KDSA120A.MODULO_REL, NOMBRE_021, KDSA100A.DOCENTE_REL, NOMBRE_100, KDSA020A.CURSO_REL, NOMBRE_020, ";
		$msConsulta .= "fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, HORAINI_020, HORAFIN_020, FECHA_121, UNIDAD_121, CONTENIDO_121, OBJETIVOS_121, ACTIVIDADES_121, ";
		$msConsulta .= "RECURSOS_121, EVALUACION_121 ";
		$msConsulta .= "from KDSA120A, KDSA121A, KDSA021A, KDSA020A, KDSA100A ";
		$msConsulta .= "where KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL ";
		$msConsulta .= "and KDSA120A.PLANIFICACION_REL = KDSA121A.PLANIFICACION_REL and KDSA021A.CURSO_REL = ? ";
		$msConsulta .= "and KDSA021A.DOCENTE_REL = ?";
		if ($mnTipoRep == 0)
		{
			$msConsulta .= " and KDSA120A.MODULO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso, $mDocente, $msModulo]);
		}
		else
		{
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso, $mDocente]);
		}
	}
	else
	{
		$msConsulta = "select KDSA120A.PLANIFICACION_REL, KDSA120A.MODULO_REL, NOMBRE_021, KDSA100A.DOCENTE_REL, NOMBRE_100, KDSA020A.CURSO_REL, NOMBRE_020, ";
		$msConsulta .= "fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, HORAINI_020, HORAFIN_020, FECHA_121, UNIDAD_121, CONTENIDO_121, OBJETIVOS_121, ACTIVIDADES_121, ";
		$msConsulta .= "RECURSOS_121, EVALUACION_121 ";
		$msConsulta .= "from KDSA120A, KDSA121A, KDSA021A, KDSA020A, KDSA100A ";
		$msConsulta .= "where KDSA120A.MODULO_REL = KDSA021A.MODULO_REL and KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL ";
		$msConsulta .= "and KDSA120A.PLANIFICACION_REL = KDSA121A.PLANIFICACION_REL and KDSA021A.CURSO_REL = ?";
		if ($mnTipoRep == 0)
		{
			$msConsulta .= " and KDSA120A.MODULO_REL = ?";
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso, $msModulo]);
		}
		else
		{
			$mDatos = $m_cnx_MySQL->prepare($msConsulta);
			$mDatos->execute([$msCurso]);
		}
	}
	
	$Registros = $mDatos->rowCount();
	$msModuloAnt = "";
	$msFechaAnt = "";
	$mbPrimerModulo = true;

	while ($Fila = $mDatos->fetch())
	{
		$msModulo = $Fila["MODULO_REL"];
		$msFecha = date_create_from_format('Y-m-d', $Fila["FECHA_121"]);
		if ($msFecha != $msFechaAnt)
		{
			if ($msFechaAnt != "")
				$pdf->DibujaHorizontal();
			$msFechaRep = date_format($msFecha, 'd/m/Y');
			$msFechaAnt = $msFecha;
		}
		else
			$msFechaRep = "";
		
		$msUnidad = $Fila["UNIDAD_121"];
		$msContenido = utf8_decode($Fila["CONTENIDO_121"]);
		$msObjetivos = utf8_decode($Fila["OBJETIVOS_121"]);
		$msActividades = utf8_decode($Fila["ACTIVIDADES_121"]);
		$msRecursos = utf8_decode($Fila["RECURSOS_121"]);
		$msEvaluacion = utf8_decode($Fila["EVALUACION_121"]);

		if ($msModulo != $msModuloAnt)
		{
			$msDocente = utf8_decode($Fila["NOMBRE_100"]);
			$msNomModulo = utf8_decode($Fila["NOMBRE_021"]);
			$pdf->SetFont('arial','',12);
			$pdf->mbRecuadro = false;
			$pdf->mbLineas = false;
			if ($msModuloAnt != "")
			{
				$pdf->SetWidths(array(250));
				$pdf->SetAligns(array('L'));
				$pdf->Row(array(""));
			}
			$pdf->SetWidths(array(20, 230));
			$pdf->SetAligns(array('L', 'L'));
			$pdf->Row(array("Docente:", $msDocente));
			$pdf->Row(array(utf8_decode("Módulo:"), $msNomModulo));
			$pdf->SetWidths(array(30, 20, 40, 40, 45, 45, 35));
			$pdf->SetAligns(array('C','C','C','C','C','C','C'));
			$pdf->mbFondoAzul = true;
			$pdf->mbRecuadro = true;
			$pdf->Row(array("Fecha", "Unidad", "Contenido", "Objetivos", "Actividades", utf8_decode("Recursos didácticos"), utf8_decode("Evaluación")));
			$msModuloAnt = $msModulo;
			$pdf->mbRecuadro = false;
			$pdf->mbFondoAzul = false;
			$pdf->mbLineas = true;
		}
		$pdf->SetAligns(array('C','C','L','L','L','L','L'));
		$pdf->Row(array($msFechaRep, $msUnidad, $msContenido, $msObjetivos, $msActividades, $msRecursos, $msEvaluacion));
	}

	$pdf->DibujaHorizontal();
	$pdf->Output();
}
?>