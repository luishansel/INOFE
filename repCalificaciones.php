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

			$this->SetFont('helvetica','B',12);
			$Titulo = 'CALIFICACIONES';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			$this->SetFont('helvetica','',9);
			$Titulo = $this->msCurso;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 17, $Titulo);
			$Titulo = $this->msConvocatoria;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 21, $Titulo);
			$Titulo = $this->msHorario;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 25, $Titulo);
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
	}

	$msCurso = $_POST["msCurso"];

	$msConsulta = "select NOMBRE_020, concat(CONVOCATORIA_020, ' / ', 'G', GRUPO_020) as CONVOCATORIA, HORAINI_020, HORAFIN_020, fxDevuelveDias(CURSO_REL) as DIAS from KDSA020A where CURSO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msCurso]);
	$Fila = $mDatos->fetch();
	$msNomCurso = html_entity_decode($Fila["NOMBRE_020"]);
	$msConvocatoria = "Convocatoria " . $Fila["CONVOCATORIA"];
	$msHoraIni = date_create($Fila["HORAINI_020"]);
	$msHoraFin = date_create($Fila["HORAFIN_020"]);
	//$msDias = utf8_decode($Fila["DIAS"]);
	$msDias = html_entity_decode($Fila["DIAS"]);
	$msHorario = $msDias . " / De " . date_format($msHoraIni, 'h:i a') . " a " . date_format($msHoraFin, 'h:i a');

	$pdf = new PDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->msCurso=$msNomCurso;
	$pdf->msConvocatoria=$msConvocatoria;
	$pdf->msHorario=$msHorario;

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, 35, PDF_MARGIN_RIGHT);
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

	$msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where KDSA021A.CURSO_REL = ? order by NUMERO_021";
	$mDatosModulos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatosModulos->execute([$msCurso]);

	$msHTML = '<table cellpadding="2"><thead><tr><th style="text-align: center;background-color:rgb(0,0,255);color: white;"></th>';

	while ($filaModulo = $mDatosModulos->fetch())
	{
		$msNombre = $filaModulo["NOMBRE_021"];
		//$msNombre = html_entity_decode($filaModulo["NOMBRE_021"]);
		$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white;">' . trim($msNombre) .'</th>';
	}
	$msHTML .= '</tr></thead>';
	$msHTML .= '<tbody>';

	$msConsulta = "select MATRICULA_REL, concat(NOMBRES_010, ' ', APELLIDOS_010) as ESTUDIANTE from KDSA030A join KDSA010A on KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL where CURSO_REL = ?";
	$mDatosEstudiante = $m_cnx_MySQL->prepare($msConsulta);
	$mDatosEstudiante->execute([$msCurso]);

	$mbColorea = 0;
	while ($filaEstudiante = $mDatosEstudiante->fetch())
	{
		$msHTML .= '<tr>';
		$msMatricula = $filaEstudiante["MATRICULA_REL"];
		//$msEstudiante = utf8_decode(html_entity_decode($filaEstudiante["ESTUDIANTE"]));
		$msEstudiante = html_entity_decode($filaEstudiante["ESTUDIANTE"]);
		if ($mbColorea == 0)
			$msHTML .= '<td>' . trim($msEstudiante) . '</td>';
		else
			$msHTML .= '<td style="background-color: #f2f2f2;">' . trim($msEstudiante) . '</td>';

		$msConsulta = "select MODULO_REL from KDSA021A where KDSA021A.CURSO_REL = ? order by NUMERO_021";
		$mDatosModulos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatosModulos->execute([$msCurso]);
		$mnRegistros = $mDatosModulos->rowCount();

		while ($filaModulo = $mDatosModulos->fetch())
		{
			$msModulo = $filaModulo["MODULO_REL"];
			$msConsulta = "select PUNTAJE_151 from KDSA151A, KDSA150A where KDSA151A.CALIFICACION_REL = KDSA150A.CALIFICACION_REL and ";
			$msConsulta .= "MATRICULA_REL = ? and MODULO_REL = ?";
			$mDatosPuntaje = $m_cnx_MySQL->prepare($msConsulta);
			$mDatosPuntaje->execute([$msMatricula, $msModulo]);
			$Registros = $mDatosPuntaje->rowCount();
			if ($Registros > 0)
			{
				$filaPuntaje = $mDatosPuntaje->fetch();
				$mnPuntaje = $filaPuntaje["PUNTAJE_151"];
			}
			else
				$mnPuntaje = 0;
			
			if ($mbColorea == 0)
				$msHTML .= '<td style="text-align: center">' . trim($mnPuntaje) . '</td>';
			else
				$msHTML .= '<td style="background-color: #f2f2f2;text-align: center">' . trim($mnPuntaje) . '</td>';
		}

		$msHTML .= '</tr>';
		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;
	}
	$msHTML .= '</tbody></table>';
	$pdf->SetY(35);
	$pdf->writeHTML($msHTML);
	$pdf->Output();
}
?>