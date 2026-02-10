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
		public $Curso;
		public $Vigencia;
		public $Horario;
		public $DiasClase;
		public $Convocatoria;
		
		// Page header
		function Header()
		{
			// Logos
			$this->Image('imagenes/headerLogin.jpg',15,14,0,15);
			$mid_x = 210;
			// Title
			$this->SetFont('helvetica','B',15);
			$Titulo = 'ESTUDIANTES MATRICULADOS';
			$this->Text(($mid_x - $this->GetStringWidth($Titulo)) / 2, 10, $Titulo);

			$Linea = 20;
			
			//DATOS DEL CURSO
			$this->SetTextColor(0,0,0);
			
			$this->SetFont('helvetica','B',8);
			$this->Text(50, $Linea, 'Curso:');
			$this->SetFont('helvetica','',8);
			$this->Text(75, $Linea, mb_convert_encoding(html_entity_decode($this->Curso), "UTF-8"));
			
			$Linea += 5;
			$this->SetFont('helvetica','B',8);
			$this->Text(50, $Linea, 'Vigencia:');
			$this->SetFont('helvetica','',8);
			$this->Text(75, $Linea, $this->Vigencia);
			
			$Linea += 5;
			$this->SetFont('helvetica','B',8);
			$this->Text(50, $Linea, 'Dias de clase:');
			$this->SetFont('helvetica','',8);
			$this->Text(75, $Linea, mb_convert_encoding(html_entity_decode($this->DiasClase), "UTF-8"));
			
			$Linea += 5;
			$this->SetFont('helvetica','B',8);
			$this->Text(50, $Linea, 'Horario:');
			$this->SetFont('helvetica','',8);
			$this->Text(75, $Linea, mb_convert_encoding(html_entity_decode($this->Horario), "UTF-8"));
			
			$Linea += 5;
			$this->SetFont('helvetica','B',8);
			$this->Text(50, $Linea, 'Convocatoria:');
			$this->SetFont('helvetica','',8);
			$this->Text(75, $Linea, $this->Convocatoria);
		}
		// Page footer
		function Footer()
		{
			// Position at 1.5 cm from bottom
			$this->SetY(-15);
			// Arial italic 8
			$this->SetFont('helvetica','I',8);
			// Page number
			$this->Cell(0,10,mb_convert_encoding('Página ', "UTF-8").$this->PageNo().'/{nb}',0,0,'L');
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

	$codCurso = trim($_POST["KDSA"]);

	//Obtención de datos
	$msConsulta = "select KDSA030A.MATRICULA_REL, KDSA030A.ESTUDIANTE_REL, concat(trim(APELLIDOS_010), ', ', trim(NOMBRES_010)) as NOMBRECOMPLETO, MAXIMO_020, NOMBRE_020, FECHAINI_020, ";
	$msConsulta .= "FECHAFIN_020, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIASCLASE, CONVOCATORIA_020, CELULAR_010, CORREO_010, ESTADO_030, TIPOASISTENCIA_020, TIPOASISTENCIA_030 ";
	$msConsulta .= "from KDSA030A, KDSA010A, KDSA020A where KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = KDSA020A.CURSO_REL ";
	$msConsulta .= "and KDSA030A.ESTADO_030 <> 4 and KDSA030A.CURSO_REL = ?";

	$m_cnx_MySQL = fxAbrirConexion();
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$codCurso]);
	$Registros = $mDatos->rowCount();
	$Fila = $mDatos->fetch();

	$TipoCurso = $Fila["TIPOASISTENCIA_020"];
	switch ($TipoCurso)
	{
		case 0:
			$Curso = $Fila["NOMBRE_020"] . " (Presencial)";
		break;

		case 1:
			$Curso = $Fila["NOMBRE_020"] . " (Virtual)";
		break;

		case 2:
			$Curso = $Fila["NOMBRE_020"] . " (On-line)";
	}

	$Maximo = $Fila["MAXIMO_020"];
	$FechaIni = $Fila["FECHAINI_020"];
	$FechaFin = $Fila["FECHAFIN_020"];
	$HoraIni = date_create($Fila["HORAINI_020"]);
	$HoraFin = date_create($Fila["HORAFIN_020"]);
	$Horario = "De " . date_format($HoraIni, 'h:i a') . " a " . date_format($HoraFin, 'h:i a');
	$DiasClase = $Fila["DIASCLASE"];
	$Convocatoria = $Fila["CONVOCATORIA_020"];

	$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$pdf->SetMargins(PDF_MARGIN_LEFT, 50, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
		require_once(dirname(__FILE__).'/lang/spa.php');
		$pdf->setLanguageArray($l);
	}

	$pdf->Curso=$Curso;
	$pdf->Vigencia= "Del " . DevuelveFecha($FechaIni) . " al " . DevuelveFecha($FechaFin);
	$pdf->Horario=$Horario;
	$pdf->DiasClase=$DiasClase;
	$pdf->Convocatoria=$Convocatoria;
	$pdf->AddPage();

	if ($Registros > 0)
	{	
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('helvetica','',8);

		$msHTML = "<style>";
		$msHTML .= "th{";
		$msHTML .= "background-color: rgb(0,100,255); color: rgb(255,255,255);";
		$msHTML .= "}";
		$msHTML .= ".fondoGris{";
		$msHTML .= "background-color: rgb(240,240,240); color: rgb(0,0,0);";
		$msHTML .= "}";
		$msHTML .= "</style>";
		$msHTML .= "<table>";
		$msHTML .= "<thead>";
		$msHTML .= "<tr>";
		$msHTML .= '<th style="width: 10%;">Matrícula</th>';
		$msHTML .= '<th style="width: 10%;">Recibo</th>';
		$msHTML .= '<th style="width: 30%;">Nombre del Estudiante</th>';
		$msHTML .= '<th style="width: 10%;">Celular</th>';
		$msHTML .= '<th style="width: 30%;">eMail</th>';
		$msHTML .= '<th style="width: 10%;">Asistencia</th>';
		$msHTML .= "</tr>";
		$msHTML .= "</thead>";
		$msHTML .= "<tbody>";
		
		$Linea = 50;
		$mbFondo = 0;
		for ($i = 0; $i < $Registros; $i++)
		{
			$Matricula = $Fila["MATRICULA_REL"];
			$Celular = $Fila["CELULAR_010"];
			$Correo = $Fila["CORREO_010"];
			switch ($Fila["TIPOASISTENCIA_030"]) 
			{
				case 0:
					$TipoAsistencia = "Presencial";
					break;
				case 1:
					$TipoAsistencia = "Virtual";
					break;
				case 2:
					$TipoAsistencia = "On-line";
					break;
			}
			$NombreCompleto = mb_convert_encoding(html_entity_decode($Fila["NOMBRECOMPLETO"]), "UTF-8");

			//Pagos individuales
			$msConsulta = "SELECT RECIBO_040 FROM KDSA040A, KDSA041A, KDSA050A where KDSA040A.PAGO_REL = KDSA041A.PAGO_REL ";
			$msConsulta .= "and KDSA041A.COBRO_REL = KDSA050A.COBRO_REL and TIPO_050 = 2 and KDSA041A.MATRICULA_REL = ? ";
			$msConsulta .= "order by KDSA040A.PAGO_REL DESC LIMIT 1";
			$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
			$mAuxiliar->execute([$Matricula]);
			$mnReg = $mAuxiliar->rowCount();
			if ($mnReg == 0)
			{
				//Pagos empresariales
				$msConsulta = "SELECT RECIBO_040 FROM KDSA040A, KDSA042A, KDSA053A where KDSA040A.PAGO_REL = KDSA042A.PAGO_REL and ";
				$msConsulta .= "KDSA042A.COBRO_REL = KDSA053A.COBRO_REL and KDSA053A.MATRICULA_REL = ? order by KDSA040A.PAGO_REL DESC LIMIT 1";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$Matricula]);
				$mnReg = $mAuxiliar->rowCount();

				if ($mnReg == 0)
				{
					//Becados
					$msConsulta = "SELECT BECADOPOR_030 FROM KDSA030A WHERE MATRICULA_REL = ? AND BECADO_030 = 1";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$Matricula]);
					$mnReg = $mAuxiliar->rowCount();

					if ($mnReg == 0)
					{
						//Reingreso
						$msConsulta = "SELECT PRIMERAVEZ_030 FROM KDSA030A WHERE MATRICULA_REL = ?";
						$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
						$mAuxiliar->execute([$Matricula]);
						$mRow = $mAuxiliar->fetch();
						$mnPrimero = $mRow["PRIMERAVEZ_030"];

						if ($mnPrimero == 1)
							$Recibo = "";
						else
							$Recibo = "Reingreso";
					}
					else
					{
						$mrAux = $mAuxiliar->fetch();
						$Recibo = "Becado por " . trim($mrAux["BECADOPOR_030"]);
					}
				}
				else
				{
					$mrAux = $mAuxiliar->fetch();
					$Recibo = $mrAux["RECIBO_040"];
				}
			}
			else
			{
				$mrAux = $mAuxiliar->fetch();
				$Recibo = $mrAux["RECIBO_040"];
			}

			if ($mbFondo == 0)
			{
				$msHTML .= "<tr>";
				$msHTML .= '<td style="width: 10%;">' . $Matricula . "</td>";
				$msHTML .= '<td style="width: 10%;">' . $Recibo . "</td>";
				$msHTML .= '<td style="width: 30%;">' . $NombreCompleto . "</td>";
				$msHTML .= '<td style="width: 10%;">' . $Celular . "</td>";
				$msHTML .= '<td style="width: 30%;">' . $Correo . "</td>";
				$msHTML .= '<td style="width: 10%;">' . $TipoAsistencia . "</td>";
				$msHTML .= "</tr>";
				$mbFondo = 1;
			}
			else
			{
				$msHTML .= "<tr>";
				$msHTML .= '<td class="fondoGris" style="width: 10%;">' . $Matricula . "</td>";
				$msHTML .= '<td class="fondoGris" style="width: 10%;">' . $Recibo . "</td>";
				$msHTML .= '<td class="fondoGris" style="width: 30%;">' . $NombreCompleto . "</td>";
				$msHTML .= '<td class="fondoGris" style="width: 10%;">' . $Celular . "</td>";
				$msHTML .= '<td class="fondoGris" style="width: 30%;">' . $Correo . "</td>";
				$msHTML .= '<td class="fondoGris" style="width: 10%;">' . $TipoAsistencia . "</td>";
				$msHTML .= "</tr>";
				$mbFondo = 0;
			}

			$Fila = $mDatos->fetch();
		}
		
		$msHTML .= "</table>";
		$msHTML .= "<br><br>";
		$msHTML .= "<table>";
		$msHTML .= "<tr>";

		if ($Registros == 1)
			$msHTML .= '<td style="text-align: right;">' . $Registros . ' matriculado</td>';
		else
			$msHTML .= '<td style="text-align: right;">' . $Registros . ' matriculados</td>';
		
		$msHTML .= "</tr>";
		$msHTML .= "<tr>";
		$msHTML .= '<td style="text-align: right;">' . mb_convert_encoding("Máximo permitido: ", "UTF-8") . $Maximo . '</td>';
		$msHTML .= "</tr>";
		$msHTML .= "</table>";

		$pdf->writeHTML($msHTML);
		$pdf->Output();
	}
}
?>