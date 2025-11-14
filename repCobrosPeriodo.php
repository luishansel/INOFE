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
			$this->Image('imagenes/headerLogin.jpg',14,8,0,14);
			// Title
			$mid_x = 210; // width of the "PDF screen", fixed by now.

			$this->SetFont('helvetica','B',12);
			$Titulo = 'COBROS POR PERIODO';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);
			$this->SetFont('helvetica','',9);
			$Titulo = $this->msPeriodo;
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 15, $Titulo);
			
			$msHTML = '<table><thead><tr>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Cobro</th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 40%">Concepto</th>';
			$msHTML .= '<th style="text-align: right;background-color:rgb(0,0,255);color: white; width: 10%">Monto</th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 15%">Fecha prevista</th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 15%">Tipo</th>';
			$msHTML .= '<th style="text-align: center;background-color:rgb(0,0,255);color: white; width: 10%">Estado</th>';
			$msHTML .= '</tr></thead></table>';
			$this->setFontSize(8);
			$this->SetY(23);
			$this->writeHTML($msHTML);
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

	$mdFechaIni = $_POST["mdFechaIni"];
	$mdFechaFin = $_POST["mdFechaFin"];
	$msPeriodo = "Período del " . DevuelveFecha($mdFechaIni) . " al " . DevuelveFecha($mdFechaFin);

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
	$pdf->AddPage();

	$msConsulta = "select COBRO_REL, KDSA050A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, FECHAPREVISTA_050, CONCEPTO_050, MONTO_050, ACTIVO_050, ANULADO_050, ";
	$msConsulta .= "(case TIPO_050 when 0 then 'Cuota' when 1 then 'Moratorio' when 2 then 'Matrícula' when 3 then 'Empresarial' when 4 then 'INATEC' when 5 then 'Otros' else 'Cuota especial' end) as TIPO_050 ";
	$msConsulta .= "from KDSA050A join KDSA020A on KDSA050A.CURSO_REL = KDSA020A.CURSO_REL where FECHAPREVISTA_050 between ? and ? order by KDSA050A.CURSO_REL, COBRO_REL";
	$m_cnx_MySQL = fxAbrirConexion();
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$mdFechaIni, $mdFechaFin]);

	$mbColorea = 0;
	$msCodCursoAnt = "";

	while ($fila = $mDatos->fetch())
	{
		$msHTML = "";
		$msCodCurso = $fila["CURSO_REL"];
		$msNombre = $fila["NOMBRE_020"];
		$msConvocatoria = $fila["CONVOCATORIA_020"];
		$msGrupo = $fila["GRUPO_020"];

		if ($msCodCursoAnt != $msCodCurso){
			$msCurso = html_entity_decode($msNombre) . ' (' . $msConvocatoria . '/G' . $msGrupo . ')';
			$msHTML .= '<label style="color: blue"><strong>' . $msCurso . '</strong></label><br>';
			$msCodCursoAnt = $msCodCurso;
		}

		$pdf->setFontSize(8);
		$msHTML .= '<table><tr>';
		$msCobro = $fila["COBRO_REL"];
		$mdFecha = DevuelveFecha($fila["FECHAPREVISTA_050"]);
		$msConcepto = html_entity_decode($fila["CONCEPTO_050"]);
		$mnMonto = floatval($fila["MONTO_050"]);
		$msTipo = $fila["TIPO_050"];
		$mbActivo = intval($fila["ACTIVO_050"]);
		$mbAnulado = intval($fila["ANULADO_050"]);

		if ($mbColorea == 0)
		{
			if ($mbAnulado == 1)
			{
				$msHTML .= '<td style="width: 10%; color: red">' . trim($msCobro) . '</td>';
				$msHTML .= '<td style="width: 40%; color: red">' . trim($msConcepto) . '</td>';
				$msHTML .= '<td style="text-align: right; width: 10%; color: red">' . number_format($mnMonto, 2, '.', ',') . '</td>';
				$msHTML .= '<td style="text-align: center; width: 15%; color: red">' . trim($mdFecha) . '</td>';
				$msHTML .= '<td style="text-align: center; width: 15%; color: red">' . trim($msTipo) . '</td>';
				$msHTML .= '<td style="text-align: center; width: 10%; color: red">Anulado</td>';
			}
			else
			{
				if ($mbActivo == 0)
				{
					$msHTML .= '<td style="width: 10%; color: orange">' . trim($msCobro) . '</td>';
					$msHTML .= '<td style="width: 40%; color: orange">' . trim($msConcepto) . '</td>';
					$msHTML .= '<td style="text-align: right; width: 10%; color: orange;">' . number_format($mnMonto, 2, '.', ',') . '</td>';
					$msHTML .= '<td style="text-align: center; width: 15%; color: orange">' . trim($mdFecha) . '</td>';
					$msHTML .= '<td style="text-align: center; width: 15%; color: orange">' . trim($msTipo) . '</td>';
					$msHTML .= '<td style="text-align: center; width: 10%; color: orange">Inactivo</td>';
				}
				else
				{
					$msHTML .= '<td style="width: 10%">' . trim($msCobro) . '</td>';
					$msHTML .= '<td style="width: 40%">' . trim($msConcepto) . '</td>';
					$msHTML .= '<td style="text-align: right; width: 10%">' . number_format($mnMonto, 2, '.', ',') . '</td>';
					$msHTML .= '<td style="text-align: center; width: 15%">' . trim($mdFecha) . '</td>';
					$msHTML .= '<td style="text-align: center; width: 15%">' . trim($msTipo) . '</td>';
					$msHTML .= '<td style="text-align: center; width: 10%">Activo</td>';
				}
			}
		}
		else
		{
			if ($mbAnulado == 1)
			{
				$msHTML .= '<td style="background-color: #f2f2f2; width: 10%; color: red">' . trim($msCobro) . '</td>';
				$msHTML .= '<td style="background-color: #f2f2f2; width: 40%; color: red">' . trim($msConcepto) . '</td>';
				$msHTML .= '<td style="background-color: #f2f2f2; text-align: right; width: 10%; color: red">' . number_format($mnMonto, 2, '.', ',') . '</td>';
				$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 15%; color: red">' . trim($mdFecha) . '</td>';
				$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 15%; color: red">' . trim($msTipo) . '</td>';
				$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 10%; color: red">Anulado</td>';
			}
			else
			{
				if ($mbActivo == 0)
				{
					$msHTML .= '<td style="background-color: #f2f2f2; width: 10%; color: orange">' . trim($msCobro) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; width: 40%; color: orange">' . trim($msConcepto) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: right; width: 10%; color: orange">' . number_format($mnMonto, 2, '.', ',') . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 15%; color: orange">' . trim($mdFecha) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 15%; color: orange">' . trim($msTipo) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 10%; color: orange">Inactivo</td>';
				}
				else
				{
					$msHTML .= '<td style="background-color: #f2f2f2; width: 10%">' . trim($msCobro) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; width: 40%">' . trim($msConcepto) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: right; width: 10%">' . number_format($mnMonto, 2, '.', ',') . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 15%">' . trim($mdFecha) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 15%">' . trim($msTipo) . '</td>';
					$msHTML .= '<td style="background-color: #f2f2f2; text-align: center; width: 10%">Activo</td>';
				}
			}
		}

		$msHTML .= '</tr></table>';
		$pdf->writeHTML($msHTML);
		
		if ($mbColorea == 0)
			$mbColorea = 1;
		else
			$mbColorea = 0;
	}
	
	$pdf->SetY(30);
	$pdf->Output();
}
?>