<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	require_once ("funciones/fxPagos.php");
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
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("procOtrosIngresos");
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
        <div class="container text-center">
        	<div id="DivContenido">
				<img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
		{
			if (isset($_POST["Guardar"]))
			{
				$Codigo = $_POST["CodPago"];
				$Fecha = $_POST["dtpFecha"];
				$Nombre = $_POST["txtNombre"];
				$Recibo = $_POST["txtRecibo"];
				$Serie = $_POST["optSerie"];
				$Monto = $_POST["txnMonto"];
				$RetDgi = $_POST["txnRetDgi"];
				$RetAlcaldia = $_POST["txnRetAlcaldia"];
				$Moneda = $_POST["optMoneda"];
				$TipoCambio = $_POST["txnTipoCambio"];
				$Concepto = $_POST["txtConcepto"];
				$TipoPago = $_POST["optTipoPago"];
				$NumeroCk = $_POST["txtNumeroCk"];
				$BancoCk = $_POST["txtBancoCk"];

				if ($Codigo == "")
				{
					$Codigo = fxGuardarPagos ($Fecha, $Nombre, $Recibo, $Serie, $Monto, $RetDgi, $RetAlcaldia, $Moneda, $TipoCambio, $Concepto, $TipoPago, $NumeroCk, $BancoCk, 1, 0, 0);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA040A", $Codigo, "", "Agregar");
				}

				?><meta http-equiv="Refresh" content="0;url=gridOtrosIngresos.php"/><?php
			}
			else
			{
				if (isset($_POST["mAccion"]))
					$Accion = $_POST["mAccion"];
				else
					$Accion = 0;
				
				if (isset($_POST["mCodigo"]))
					$Codigo = $_POST["mCodigo"];
				else
					$Codigo = "";
				
				if ($Codigo != "")
				{
					$RecordSet = fxDevuelvePagos(0, "", $Codigo);
					$Fila = $RecordSet->fetch();
					$Fecha = $Fila["FECHA_040"];
					$Nombre = $Fila["NOMBRE_040"];
					$Recibo = $Fila["RECIBO_040"];
					$Serie = $Fila["SERIE_040"];
					$Monto = $Fila["MONTO_040"];
					$RetDgi = $Fila["RETENCION_DGI_040"];
					$RetAlcaldia = $Fila["RETENCION_ALCALDIA_040"];
					$Moneda = $Fila["MONEDA_040"];
					$TipoCambio = $Fila["TIPOCAMBIO_040"];
					$Concepto = $Fila["CONCEPTO_040"];
					$TipoPago = $Fila["TIPOPAGO_040"];
					$NumeroCk = $Fila["NUMEROCK_040"];
					$BancoCk = $Fila["BANCOCK_040"];
					$Anulado = $Fila["ANULADO_040"];
				}
				else
				{
					$Fecha = "";
					$Nombre = "";
					$Recibo = "";
					$Serie = "";
					$Monto = 0;
					$RetDgi = 0;
					$RetAlcaldia = 0;
					$Moneda = 0;
					$TipoCambio = 0;
					$Concepto = "";
					$TipoPago = 0;
					$NumeroCk = "";
					$BancoCk = "";
					$Anulado = 0;
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Otros ingresos</strong></div>
				</div>
			</div>
			
			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
				<form id="procIngresos" name="procOtrosIngresos" action="procOtrosIngresos.php" method="post" onsubmit="return verificarFormulario()">
                	<div class = "form-group row">
                        <label for="CodPago" class="col-sm-12 col-md-2 col-form-label">Código del Pago</label>
                        <div class="col-sm-12 col-md-3">
                        <?php echo('<input type="text" class="form-control" id="CodPago" name="CodPago" value="' . $Codigo . '" readonly />'); ?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dtpFecha" class="col-sm-12 col-md-2 col-form-label">Fecha</label>
                        <div class="col-sm-12 col-md-3">
						<?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" />');
							else
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtNombre" class="col-sm-12 col-md-2 col-form-label">A nombre de</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtNombre" name="txtNombre" value="' . $Nombre . '" />'); ?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtRecibo" class="col-sm-12 col-md-2 col-form-label">Recibo</label>
                        <div class="col-sm-12 col-md-3">
						<?php echo('<input type="text" class="form-control" id="txtRecibo" name="txtRecibo" value="' . $Recibo . '" />'); ?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optSerie" class="col-sm-12 col-md-2 form-label">Serie</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Serie == "A" or $Codigo == "")
                                    echo('<input type="radio" id="optSerie1" name="optSerie" value="A" checked /> A <input type="radio" id="optSerie2" name="optSerie" value="B" /> B');
                                else
                                    echo('<input type="radio" id="optSerie1" name="optSerie" value="A" /> A <input type="radio" id="optSerie2" name="optSerie" value="B" checked /> B');
                            ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnMonto" class="col-sm-12 col-md-2 col-form-label">Monto</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="0" />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="' . $Monto . '" />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnRetDgi" class="col-sm-12 col-md-2 col-form-label">Retención D.G.I.</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetDgi" name="txnRetDgi" value="0" />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetDgi" name="txnRetDgi" value="' . $RetDgi . '" />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnRetDgi" class="col-sm-12 col-md-2 col-form-label">Retención Alcaldía</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetAlcaldia" name="txnRetAlcaldia" value="0" />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetAlcaldia" name="txnRetAlcaldia" value="' . $RetAlcaldia . '" />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optMoneda" class="col-sm-12 col-md-2 form-label">Moneda</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Moneda == 1 or $Codigo == "")
                                    echo('<input type="radio" id="optMoneda1" name="optMoneda" value="0" /> Córdobas <input type="radio" id="optMoneda2" name="optMoneda" value="1" checked /> Dólares');
                                else
                                    echo('<input type="radio" id="optMoneda1" name="optMoneda" value="0" checked /> Córdobas <input type="radio" id="optMoneda2" name="optMoneda" value="1" /> Dólares');
                            ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnTipoCambio" class="col-sm-12 col-md-2 col-form-label">Tipo de cambio</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.0001" style="text-align:right" class="form-control" id="txnTipoCambio" name="txnTipoCambio" value="0" />');
							else
								echo('<input type="number" step="0.0001" style="text-align:right" class="form-control" id="txnTipoCambio" name="txnTipoCambio" value="' . $TipoCambio . '" />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtConcepto" class="col-sm-12 col-md-2 form-label">Concepto</label>
                        <div class="col-sm-12 col-md-8">
						<?php echo('<textarea class="form-control" id="txtConcepto" name="txtConcepto" rows="2">' . $Concepto . '</textarea>'); ?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optTipoPago" class="col-sm-12 col-md-2 form-label">Tipo de pago</label>
                        <div class="col-sm-12 col-md-8">
                            <div class = "radio">
                            <?php
                                if ($TipoPago == 0 or $Codigo == "")
									echo('<input type="radio" id="optTipoPago1" name="optTipoPago" value="0" checked /> Efectivo ');
								else
									echo('<input type="radio" id="optTipoPago1" name="optTipoPago" value="0" /> Efectivo');

								if ($TipoPago == 1)
									echo('<input type="radio" id="optTipoPago2" name="optTipoPago" value="1" checked /> Tarjeta ');
								else
									echo('<input type="radio" id="optTipoPago2" name="optTipoPago" value="1" /> Tarjeta ');

								if ($TipoPago == 2)
									echo('<input type="radio" id="optTipoPago3" name="optTipoPago" value="2" checked /> Cheque ');
								else
									echo('<input type="radio" id="optTipoPago3" name="optTipoPago" value="2" /> Cheque ');
									
								if ($TipoPago == 3)
									echo('<input type="radio" id="optTipoPago4" name="optTipoPago" value="3" checked /> Depósito FICOHSA ');
								else
									echo('<input type="radio" id="optTipoPago4" name="optTipoPago" value="3" /> Depósito FICOHSA ');										

								if ($TipoPago == 4)
									echo('<input type="radio" id="optTipoPago5" name="optTipoPago" value="4" checked /> Depósito BAC ');
								else
									echo('<input type="radio" id="optTipoPago5" name="optTipoPago" value="4" /> Depósito BAC ');

								if ($TipoPago == 5)
									echo('<input type="radio" id="optTipoPago6" name="optTipoPago" value="5" checked /> eCommerce');
								else
									echo('<input type="radio" id="optTipoPago6" name="optTipoPago" value="5" /> eCommerce');
                            ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtNumeroCk" class="col-sm-12 col-md-2 col-form-label">Número de Cheque</label>
                        <div class="col-sm-12 col-md-3">
							<?php echo('<input type="text" class="form-control" id="txtNumeroCk" name="txtNumeroCk" value="' . $NumeroCk . '" />'); ?>
                        </div>
                        <label for="txtBancoCk" class="col-sm-12 col-md-2 col-form-label">Banco del Cheque</label>
						<div class="col-sm-12 col-md-3">
							<?php echo('<input type="text" class="form-control" id="txtBancoCk" name="txtBancoCk" value="' . $BancoCk . '" />'); ?>
						</div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optAnulado" class="col-sm-12 col-md-2 form-label">Anulado</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Anulado == 1)
                                    echo('<input type="radio" id="Opcion1" name="optAnulado" value="0" disabled /> No <input type="radio" id="Opcion2" name="optAnulado" value="1" checked disabled /> Si');
                                else
                                    echo('<input type="radio" id="Opcion1" name="optAnulado" value="0" checked disabled /> No <input type="radio" id="Opcion2" name="optAnulado" value="1" disabled /> Si');
                            ?>
                            </div>
                        </div>
                    </div>
                    
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
                        <?php
                        	if ($Anulado == 1 or $Codigo <> "" or $Accion == 1)
								echo('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" disabled/>');
							else
								echo('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />');
						?>
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridOtrosIngresos.php'"/>
                        </div>
                    </div>
				</form>
                </div>
	<?php	}
		}
	}
?>
			</div>
		</div>
	</div>
</body>
</html>
<script type='text/javascript'>
	function verificarFormulario()
	{
		if(document.getElementById('txtRecibo').value=="")
		{
			$.messager.alert('INOFE','Falta el Número de Recibo.','warning');
			return false;
		}
		
		if(document.getElementById('txnTipoCambio').value==0)
		{
			$.messager.alert('INOFE','Falta el Tipo de Cambio.','warning');
			return false;
		}
		
		if(document.getElementById('txtConcepto').value=="")
		{
			$.messager.alert('INOFE','Falta el Concepto del pago.','warning');
			return false;
		}
		
		if(document.getElementById('optTipoPago3').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número del Cheque.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago3').checked && document.getElementById('txtBancoCk').value=="")
		{
			$.messager.alert('INOFE','Falta el banco del Cheque.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago4').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número del depósito FICHOSA.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago5').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número del depósito BAC.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago6').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número del registro eCommerce.','warning');
			return false;
		}
		
		if(document.getElementById('txnMonto').value==0)
		{
			$.messager.alert('INOFE','Faltan el valor del pago.','warning');
			return false;
		}
		
		return true;
	}
</script>