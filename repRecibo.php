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
require_once ("funciones/fxNumerosLetras.php");
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
	$msPago = $_POST["KDSA"];

	//$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); LHVG20260618
	$pdf = new TCPDF('P', 'mm', array(216,140), true, 'UTF-8', false); //Recomendación de la IA

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT); LHVG20260618
	$pdf->SetMargins(0,0,0); //Recomendación de la IA

	// set auto page breaks
	//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); LHVG20260618
	$pdf->SetAutoPageBreak(false); //Recomendación de la IA

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
		require_once(dirname(__FILE__).'/lang/spa.php');
		$pdf->setLanguageArray($l);
	}

	//Recomendación de la IA
	$pdf->SetCellPadding(0);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	$pdf->setFontSize(11);
	$pdf->AddPage();
	
	$msConsulta = "select USUARIO_000 from KDSA000A where LLAVE1_000 = ? limit 1";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msPago]);
	$mFila = $mDatos->fetch();
	$Usuario = $mFila["USUARIO_000"];

	$msConsulta = "select FECHA_040, RECIBO_040, NOMBRE_040, TIPOCAMBIO_040, MONEDA_040, MONTO_040, TIPOPAGO_040, NUMEROCK_040, BANCOCK_040 from KDSA040A where PAGO_REL = ?";
	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
	$mDatos->execute([$msPago]);
	$mFila = $mDatos->fetch();

	$FechaDividida = explode("-", $mFila["FECHA_040"]);
	$Anno = $FechaDividida[0];
	$Mes = $FechaDividida[1];
	$Dia = $FechaDividida[2];

	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('helvetica','',10);

	$mnLinea = 52;
	//FECHA
	$pdf->Text(2, $mnLinea, $Dia);
	$pdf->Text(12, $mnLinea, $Mes);
	$pdf->Text(25, $mnLinea, substr($Anno, -2));

	//NUMERO DEL RECIBO
	$pdf->Text(130, $mnLinea, $mFila["RECIBO_040"]);

	//NOMBRE DEL RECIBO
	$mnLinea += 8;
	$pdf->Text(35, $mnLinea, html_entity_decode($mFila["NOMBRE_040"]));

	//MONTO DEL RECIBO
	$mnLinea += 10;
	$msValorLetras = fxNumerosLetras(floatval($mFila["MONTO_040"]));
	$msTexto = $mFila["MONTO_040"] . " (" . $msValorLetras . ")";
	$pdf->Text(35, $mnLinea, $msTexto);

	//CONCEPTO DEL RECIBO
	$msConsulta = "select distinct TIPO_050 from KDSA041A, KDSA050A where KDSA041A.COBRO_REL = KDSA050A.COBRO_REL and PAGO_REL = ?";
	$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
	$mAuxiliar->execute([$msPago]);
	$msConcepto = "Pago de";
	$mnRegistros = $mAuxiliar->rowCount();
	$i=1;

	while ($mAuxFila = $mAuxiliar->fetch())
	{
		switch (intval($mAuxFila["TIPO_050"])){
			case 0:
				$msTipo = "Arancel";
				break;
			case 1:
				$msTipo = "Moratorio";
				break;
			case 2:
				$msTipo = "Matrícula";
				break;
			case 3:
				$msTipo = "Empresarial";
				break;
			case 4:
				$msTipo = "INATEC";
				break;
			case 5:
				$msTipo = "Certificado";
				break;
			case 6:
				$msTipo = "Arancel especial";
				break;
		}

		if ($i == 1)
			$msConcepto .= " " . $msTipo;
		else{
			if ($i == $mnRegistros)
				$msConcepto .= " y " . $msTipo;
			else
				$msConcepto .= ", " . $msTipo;
		}

		$i++;
	}
	
	$mnLinea += 8;
	$pdf->Text(35, $mnLinea, trim($msConcepto));

	//CURSO
	$msConsulta = "select EMPRESARIAL_040 from KDSA040A where PAGO_REL = ?";
	$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
	$mAuxiliar->execute([$msPago]);
	$mAuxFila = $mAuxiliar->fetch();
	$mbEmpresarial = $mAuxFila["EMPRESARIAL_040"];

	if ($mbEmpresarial == 0)
		$msConsulta = "select NOMBRE_020 from KDSA041A, KDSA050A, KDSA020A where KDSA041A.COBRO_REL = KDSA050A.COBRO_REL and KDSA050A.CURSO_REL = KDSA020A.CURSO_REL and PAGO_REL = ? limit 1";
	else
		$msConsulta = "select NOMBRE_020 from KDSA042A, KDSA050A, KDSA020A where KDSA042A.COBRO_REL = KDSA050A.COBRO_REL and KDSA050A.CURSO_REL = KDSA020A.CURSO_REL and PAGO_REL = ? limit 1";
	$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
	$mAuxiliar->execute([$msPago]);
	$mAuxFila = $mAuxiliar->fetch();
	
	$mnLinea += 10;
	$pdf->Text(35, $mnLinea, mb_convert_encoding(html_entity_decode($mAuxFila["NOMBRE_020"]), "UTF-8"));

	//TIPO DE PAGO
	switch (intval($mFila["TIPOPAGO_040"])){
		case 0: //Efectivo
			$mnLinea += 10;
			$pdf->Text(14, $mnLinea, "X");
			break;
		case 1: //Tarjeta
			$mnLinea += 20;
			$pdf->Text(15, $mnLinea, "X");
			$pdf->Text(20, $mnLinea, $mFila["NUMEROCK_040"]);
			$pdf->Text(50, $mnLinea, $mFila["BANCOCK_040"]);
			break;
		case 2: //Cheque
			$mnLinea += 10;
			$pdf->Text(64, $mnLinea, "X");
			$pdf->Text(85, $mnLinea, $mFila["NUMEROCK_040"]);
			$pdf->Text(110, $mnLinea, $mFila["BANCOCK_040"]);
			break;
		case 3: //Depósito FICOHSA
			$mnLinea += 40;
			$pdf->Text(15, $mnLinea, "X");
			$pdf->Text(25, $mnLinea, $mFila["NUMEROCK_040"]);
			break;
		case 4: //Depósito BAC
			$mnLinea += 35;
			$pdf->Text(15, $mnLinea, "X");
			$pdf->Text(25, $mnLinea, $mFila["NUMEROCK_040"]);
			break;
		case 5: //eCommerce
			$mnLinea += 28;
			$pdf->Text(20, $mnLinea, "X");
			$pdf->Text(30, $mnLinea, $mFila["NUMEROCK_040"]);
			break;
	}

	$mnLinea += 16;
	$pdf->Text(90, 122, $Usuario);

	$pdf->Output('recibo.pdf', 'I');
}
?>