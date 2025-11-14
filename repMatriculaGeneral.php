<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}

set_time_limit(500);
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
		public $msRotulo;

		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			// Title
			$mid_x = 278; // width of the "PDF screen", fixed by now.

			$this->SetFont('helvetica','B',15);
			$Titulo = 'MATRICULA GENERAL';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 12, $Titulo);

			$this->SetFont('helvetica','',13);
			$this->Text(($mid_x - $this->GetStringWidth($this->msRotulo)) / 2, 18, $this->msRotulo);

			$this->setFontSize(7);
			$mHTML = '<table><tr>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" rowspan="2">Curso</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" rowspan="2">Horario</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" rowspan="2">Fecha Inicial</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" rowspan="2">Fecha Final</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" colspan="3">Matrícula Inicial</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" colspan="3">Matrícula Final</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" colspan="3">Certificados</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;" colspan="3">Deserciones</th></tr>';
		
			//Matrícula inicial
			$mHTML .= '<tr><th style="text-align: center;background-color:rgb(0,0,255);color: white;">Masculino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Femenino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Total</th>';
		
			//Matrícula final
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Masculino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Femenino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Total</th>';
		
			//Certificados
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Masculino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Femenino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Total</th>';
		
			//Deserciones
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Masculino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Femenino</th>';
			$mHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">Total</th></tr></table>';

			$this->SetY(30);
			$this->writeHTML($mHTML);
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

	$mdFechaIni = $_POST["dtpFechaIni"];
	$mdFechaFin = $_POST["dtpFechaFin"];

	$pdf = new PDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->msRotulo = "Cursos que inician entre " . DevuelveFecha($mdFechaIni) . " y " . DevuelveFecha($mdFechaFin);

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
	$pdf->setFontSize(7);
	
	$msConsulta = "select CURSO_REL, NOMBRE_020, concat(CONVOCATORIA_020, ' / ', 'G', GRUPO_020) as CONVOCATORIA, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIAS, ";
	$msConsulta .= "FECHAINI_020, FECHAFIN_020 from KDSA020A where FECHAINI_020 between ? and ? order by FECHAINI_020";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFechaIni, $mdFechaFin]);

	$mbColorea = 0;
	$msHTML = '';
	$mnLinea = 1;
	while ($Fila = $mDatos->fetch())
	{

		$msCurso = $Fila["CURSO_REL"];
		$msNomCurso = html_entity_decode($Fila["NOMBRE_020"]);
		$msConvocatoria = $Fila["CONVOCATORIA"];
		$msFechaIni = DevuelveFecha($Fila["FECHAINI_020"]);
		$msFechaFin = DevuelveFecha($Fila["FECHAFIN_020"]);
		$msHoraIni = date_create($Fila["HORAINI_020"]);
		$msHoraFin = date_create($Fila["HORAFIN_020"]);
		$msDias = html_entity_decode($Fila["DIAS"]);
		$msHorario = $msDias . " / De " . date_format($msHoraIni, 'h:i a') . " a " . date_format($msHoraFin, 'h:i a');

		$msHTML .= '<table>';
		$msHTML .= '<tr>';

		if ($mbColorea == 0)
		{
			$msHTML .= '<td>' . trim($msNomCurso) . '</td>';
			$msHTML .= '<td>' . trim($msHorario) . '</td>';
			$msHTML .= '<td>' . trim($msFechaIni) . '</td>';
			$msHTML .= '<td>' . trim($msFechaFin) . '</td>';
		}
		else
		{
			$msHTML .= '<td style="background-color: #f2f2f2">' . trim($msNomCurso) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2">' . trim($msHorario) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2">' . trim($msFechaIni) . '</td>';
			$msHTML .= '<td style="background-color: #f2f2f2">' . trim($msFechaFin) . '</td>';
		}

		//Matrícula inicial
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'M' and ESTADO_030 <> 4 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';
		
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'F' and ESTADO_030 <> 4 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 <> 4 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		//Matrícula final
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'M' and ESTADO_030 not in (2, 4) and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';
		
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'F' and ESTADO_030 not in (2, 4) and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 not in (2, 4) and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		//Certificados
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'M' and ESTADO_030 = 3 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';
		
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'F' and ESTADO_030 = 3 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 = 3 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		//Desertados
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'M' and ESTADO_030 = 2 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';
		
		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and SEXO_010 = 'F' and ESTADO_030 = 2 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		$msConsulta = "select count(MATRICULA_REL) as CONTEO from KDSA030A, KDSA010A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ESTADO_030 = 2 and CURSO_REL = ?";
		$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
		$mAuxiliar->execute([$msCurso]);
		$fAux = $mAuxiliar->fetch();
		$mnConteo = $fAux["CONTEO"];
		if ($mbColorea == 0)
			$msHTML .= '<td style="text-align: center">' . trim($mnConteo) . '</td>';
		else
			$msHTML .= '<td style="text-align: center; background-color: #f2f2f2">' . trim($mnConteo) . '</td>';

		$msHTML .= '</tr>';

		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;

		$msHTML .= '</table>';

		$mnLinea += 1;

		if ($mnLinea > 15)
		{
			$pdf->SetY(37);
			$pdf->writeHTML($msHTML, true, false, true, false, '');
			$pdf->lastPage();
			$pdf->AddPage();
			$msHTML = '';
			$mnLinea = 1;
		}
	}
	$pdf->SetY(37);
	$pdf->writeHTML($msHTML);
	$pdf->Output();
}
?>