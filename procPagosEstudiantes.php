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
	require_once ("funciones/fxEstudiantes.php");
	require_once ("funciones/fxPagos.php");
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
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("procPagosEstudiantes");
		
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
			if (isset($_POST["CodPago"]))
			{
				$Codigo = $_POST["CodPago"];
				$Fecha = $_POST["dtpFecha"];
				
				$Estudiante = trim($_POST["cboEstudiante"]);
				$msConsulta = "select concat(trim(NOMBRES_010), ' ', trim(APELLIDOS_010)) as NOMBRE from KDSA010A WHERE ESTUDIANTE_REL = ?";
				$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
				$mAuxiliar->execute([$Estudiante]);
				$fAux = $mAuxiliar->fetch();
				$NombreCompleto = $fAux["NOMBRE"];
				
				$Recibo = $_POST["txtRecibo"];
				$Serie = $_POST["optSerie"];
				$Monto = $_POST["txnMonto"];
				$Retencion = 0; //No aplica en Pagos de Estudiantes
				$Moneda = $_POST["optMoneda"];
				$TipoCambio = $_POST["txnTipoCambio"];
				$Concepto = $_POST["txtConcepto"];
				$TipoPago = $_POST["optTipoPago"];
				$NumeroCk = $_POST["txtNumeroCk"];
				$BancoCk = $_POST["txtBancoCk"];
				$gridDetalle = $_POST["gridDetalle"];

				if ($Codigo == "")
				{
					$Codigo = fxGuardarPagos ($Fecha, $NombreCompleto, $Recibo, $Serie, $Monto, $Retencion, $Retencion, $Moneda, $TipoCambio, $Concepto, $TipoPago, $NumeroCk, $BancoCk, 0, 0, 0);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA040A", $Codigo, "", "Agregar");
					
					foreach($gridDetalle as $Registro)
					{
						$Cobro = $Registro['cobro'];
						$Matricula = $Registro['matricula'];
						$Monto = $Registro['pago'];
						$Descuento = $Registro['descuento'];
						fxGuardarDetPagos ($Codigo, $Cobro, $Matricula, $Monto, $Descuento);
					}
				}
				echo('<meta http-equiv="Refresh" content="0;url=gridPagos.php"/>');

			}
			else
			{
				$Accion = $_POST["mAccion"];

				if ($Accion==1)
				{
					$Codigo = $_POST["mCodigo"];
					$msConsulta = "select ESTUDIANTE_REL, CURSO_REL from KDSA030A, KDSA041A where KDSA041A.MATRICULA_REL = KDSA030A.MATRICULA_REL and PAGO_REL = ? limit 1";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$Codigo]);
				}
				else
				{
					$Codigo = "";
					$Matricula = $_POST["mMatricula"];;
					$msConsulta = "select ESTUDIANTE_REL, CURSO_REL from KDSA030A where MATRICULA_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$Matricula]);
				}
				$fAuxiliar = $mAuxiliar->fetch();
				$Estudiante = trim($fAuxiliar["ESTUDIANTE_REL"]);
				$Curso = trim($fAuxiliar["CURSO_REL"]);

				$RecordSet = fxDevuelvePagos(0, "", $Codigo);
				$Fila = $RecordSet->fetch();
				if ($Codigo != "")
				{
					$Fecha = $Fila["FECHA_040"];
					$Recibo = $Fila["RECIBO_040"];
					$Serie = $Fila["SERIE_040"];
					$Monto = $Fila["MONTO_040"];
					$Retencion = 0; //No aplica en Pagos de Estudiantes
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
					$Recibo = "";
					$Serie = "";
					$Monto = 0;
					$Retencion = 0; //No aplica en Pagos de Estudiantes
					$Moneda = 0;
					$TipoCambio = 36.6243;
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
					<div class="degradado"><strong>Pagos de los estudiantes</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
					<form id="procPagos" name="procPagos">
						<div class = "form-group row">
							<label for="CodPago" class="col-sm-12 col-md-2 col-form-label">Código del Pago</label>
							<div class="col-sm-12 col-md-3">
							<?php echo('<input type="text" class="form-control" id="CodPago" name="CodPago" value="' . $Codigo . '" readonly />'); ?>
							</div>
						</div>
						
						<div class="form-group row">
							<label for="cboEstudiante" class="col-sm-12 col-md-2 col-form-label">Estudiante</label>
							<div class="col-sm-12 col-md-7">
								<select class="form-control" id="cboEstudiante" name="cboEstudiante" disabled>
									<?php
										$msConsulta = "select distinct KDSA010A.ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010 from KDSA010A, KDSA030A where KDSA010A.ESTUDIANTE_REL = KDSA030A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = ? order by APELLIDOS_010, NOMBRES_010 desc";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute([$Curso]);
										while ($Fila = $mDatos->fetch())
										{
											$Valor = rtrim($Fila["ESTUDIANTE_REL"]);
											$Texto = rtrim($Fila["APELLIDOS_010"]) . ", " . rtrim($Fila["NOMBRES_010"]);

											if ($Estudiante == $Valor)
												echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
											else
												echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
										}
									?>
								</select>
							</div>
						</div>
						
						<div class = "form-group row">
							<label for="dtpFecha" class="col-sm-12 col-md-2 col-form-label">Fecha</label>
							<div class="col-sm-12 col-md-3">
							<?php
								if ($Codigo == "")
									echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" onchange="fechaCerrada()"/>');
								else
									echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" disabled />');
								
								echo('<input type="hidden" class="form-control" id="txnFechaCerrada" name="txnFechaCerrada" value="" />');
							?>
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
							<label for="txtRecibo" class="col-sm-12 col-md-2 col-form-label">Recibo</label>
							<div class="col-sm-12 col-md-3">
							<?php echo('<input type="text" class="form-control" id="txtRecibo" name="txtRecibo" value="' . $Recibo . '" disabled />'); ?>
							</div>
						</div>
						
						<div class = "form-group row">
							<label for="txnMonto" class="col-sm-12 col-md-2 col-form-label">Monto</label>
							<div class="col-sm-12 col-md-3">
							<?php
								if ($Codigo == "")
									echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="0" readonly />');
								else
									echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="' . $Monto . '" readonly />');
							?>
							</div>
						</div>
						
						<div class = "form-group row">
							<label for="optMoneda" class="col-sm-12 col-md-2 form-label">Moneda</label>
							<div class="col-sm-12 col-md-4">
								<div class = "radio">
								<?php
									if ($Moneda == 1)
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
									echo('<input type="number" step="0.0001" style="text-align:right" class="form-control" id="txnTipoCambio" name="txnTipoCambio" value="0" onchange="recalcularCordobas()" />');
								else
									echo('<input type="number" step="0.0001" style="text-align:right" class="form-control" id="txnTipoCambio" name="txnTipoCambio" value="' . $TipoCambio . '" onchange="recalcularCordobas()" />');
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
							<label for="txtNumeroCk" class="col-sm-12 col-md-2 col-form-label">Referencia</label>
							<div class="col-sm-12 col-md-3">
								<?php echo('<input type="text" class="form-control" id="txtNumeroCk" name="txtNumeroCk" placeholder="Cheque, depósito, POS, eComerce" value="' . $NumeroCk . '" />'); ?>
							</div>
							<label for="txtBancoCk" class="col-sm-12 col-md-2 col-form-label">Banco receptor</label>
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

						<div class = "form-group row">
							<label for="dgDetalle" class="col-sm-12 col-md-2 form-label">Detalle del pago</label>
							<div class="col-sm-auto col-md-8">
								<div id="dvDetalle">
								<table id="dgDetalle" class="easyui-datagrid table" data-options="iconCls:'icon-edit', singleSelect:true, method:'get'">
									<thead>
										<tr>
											<th data-options="field:'matricula', hidden:true">Matrícula</th>
											<?php
											if ($Codigo == "")
											{?>
												<th data-options="field:'cobro', hidden:true">Cobro</th>
												<th data-options="field:'fecha', width:'15%',align:'center'">Fecha</th>
												<th data-options="field:'concepto',width:'40%',align:'left'">Concepto</th>
												<th data-options="field:'deuda',width:'15%',align:'right'">Deuda U$</th>
												<th data-options="field:'cordobas',width:'15%',align:'right'">Deuda C$</th>
											<?php
											}
											else
											{
											?>
												<th data-options="field:'cobro', width:'15%',align:'center'">Cobro</th>
												<th data-options="field:'fecha', hidden:true">Fecha</th>
												<th data-options="field:'concepto',width:'70%',align:'left'">Concepto</th>
												<th data-options="field:'deuda', hidden:true">Deuda U$</th>
												<th data-options="field:'cordobas', hidden:true">Deuda C$</th>
											<?php
											}
											?>

											<th data-options="field:'pago',width:'15%',align:'right',editor:{type:'numberbox',options:{precision:2}}">Pago</th>
											<!--th data-options="field:'descuento',width:'12%',align:'right',editor:{type:'numberbox',options:{precision:2}}">Descuento</th -->
											<th data-options="field:'descuento', hidden:true">Descuento</th>
										</tr>
									</thead>
									<tbody>
										<?php
											$mDatos = fxDevuelveDetPagos($Codigo, $Estudiante);
											while ($mFila = $mDatos->fetch())
											{
												$Cobro = $mFila['COBRO_REL'];
												$msConsulta = "Select MONEDA_050 from KDSA050A where COBRO_REL = ?";
												$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
												$mAuxiliar->execute([$Cobro]);
												$AuxFila = $mAuxiliar->fetch();

												echo('<tr>');
												echo('<td>' . rtrim($mFila['MATRICULA_REL']) . '</td>');
												echo('<td>' . rtrim($mFila['COBRO_REL']) . '</td>');
												$fecha = date_create_from_format('Y-m-d', $mFila["FECHAPREVISTA_050"]);
												echo('<td>' . date_format($fecha, 'd-m-Y') . '</td>');
												echo('<td>' . rtrim($mFila['CONCEPTO_050']) . '</td>');
												if ($AuxFila['MONEDA_050'] == 0)
												{
													echo('<td>0.00</td>');
													echo('<td>' . rtrim($mFila['DEUDA']) . '</td>');
												}
												else
												{
													echo('<td>' . rtrim($mFila['DEUDA']) . '</td>');
													echo('<td>0.00</td>');
												}
												echo('<td>' . rtrim($mFila['PAGO']) . '</td>');
												echo('<td>' . rtrim($mFila['DESCUENTO']) . '</td>');
												echo('</tr>');
											}
										?>
									</tbody>
								</table>
								</div>
							</div>
							<div class="col-auto">
							</div>
						</div>
						
						<div class = "row">
							<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
							<?php
								if ($Anulado == 1 or $Codigo <> "" or $Accion == 1)
									echo('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" disabled/>');
								else
									echo('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />');

								echo('<input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href=\'gridPagos.php\'"/>');
							?>
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
	function fechaCerrada()
	{
		var datos = new FormData();
		var mdFecha = document.getElementById('dtpFecha').value;
		datos.append('fechaCierre', mdFecha);

		$.ajax({
			url: 'funciones/fxDatosCierreCaja.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response) {
				document.getElementById('txnFechaCerrada').value = response;
			}
		});
    	return false;
	}

	function verificarFormulario()
	{
		var gridDetalle = $('#dgDetalle').datagrid('getData');
		var registros = $('#dgDetalle').datagrid('getRows').length - 1;
		var i;
		
		if(document.getElementById('txnFechaCerrada').value=="1")
		{
			$.messager.alert('KDSA','No puede ingresar el pago. Ya se hizo el cierre de caja de ' + $('#dtpFecha').val(),'warning');
			return false;
		}
/*
		if(document.getElementById('txtRecibo').value=="")
		{
			$.messager.alert('INOFE','Falta el Número de Recibo.','warning');
			return false;
		}
*/		
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
		
		if(document.getElementById('optTipoPago2').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número de referencia del POS.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago2').checked && document.getElementById('txtBancoCk').value=="")
		{
			$.messager.alert('INOFE','Falta el banco del POS.','warning');
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
			$.messager.alert('INOFE','Falta el Número del depósito.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago4').checked && document.getElementById('txtBancoCk').value=="")
		{
			$.messager.alert('INOFE','Falta el banco del depósito.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago5').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número del depósito.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago5').checked && document.getElementById('txtBancoCk').value=="")
		{
			$.messager.alert('INOFE','Falta el banco del depósito.','warning');
			return false;
		}

		if(document.getElementById('optTipoPago6').checked && document.getElementById('txtNumeroCk').value=="")
		{
			$.messager.alert('INOFE','Falta el Número del registro eCommerce.','warning');
			return false;
		}
		
		if(document.getElementById('txnMonto').value==0)
		{
			$.messager.alert('INOFE','Faltan los valores en el Detalle del pago.','warning');
			return false;
		}
		
		for (i=0; i<=registros; i++)
		{
			if(document.getElementById('optMoneda1').checked)
			{
				if (Number(gridDetalle.rows[i].pago) > Number(gridDetalle.rows[i].cordobas))
				{
					$.messager.alert('INOFE','El pago de ' + $.trim(gridDetalle.rows[i].concepto) + ' es mayor que la Deuda en córdobas.','warning');
					return false;
				}
			}
			else
			{
				if (Number(gridDetalle.rows[i].pago) > Number(gridDetalle.rows[i].deuda))
				{
					$.messager.alert('INOFE','El pago de ' + $.trim(gridDetalle.rows[i].concepto) + ' es mayor que la Deuda en dólares.','warning');
					return false;
				}
			}
		}
		return true;
	}
	
	function recalcularCordobas()
	{
		var mnTipoCambio = document.getElementById('txnTipoCambio').value;
		var gridDetalle = $('#dgDetalle').datagrid('getData');
		var registros = $('#dgDetalle').datagrid('getRows').length - 1;
		var mnDolares;
		var mnCordobas;
		var i;
		
		for (i=0; i<=registros; i++)
		{
			if (gridDetalle.rows[i].deuda == 0){
				mnCordobas = gridDetalle.rows[i].cordobas;
				mnDolares = mnCordobas / mnTipoCambio;
				$('#dgDetalle').datagrid('updateRow',{index: i, row:{deuda:Number(mnDolares).toFixed(2)}});
			}
			else{
				mnDolares = gridDetalle.rows[i].deuda;
				mnCordobas = mnTipoCambio * mnDolares;
				$('#dgDetalle').datagrid('updateRow',{index: i, row:{cordobas:Number(mnCordobas).toFixed(2)}});
			}
		}
	}
	
	function sumarDetalle(){
		var gridDetalle = $('#dgDetalle').datagrid('getData');
		var registros = $('#dgDetalle').datagrid('getRows').length - 1;
		var mnPago = 0;
		var i;
		for (i=0; i<=registros; i++)
		{
			mnPago = Number(parseFloat(mnPago) + Number(gridDetalle.rows[i].pago)).toFixed(2);
		}
		
		document.getElementById('txnMonto').value = mnPago;
	};

	$.extend($.fn.datagrid.methods, {
		editCell: function(jq,param){
			return jq.each(function(){
				var opts = $(this).datagrid('options');
				var fields = $(this).datagrid('getColumnFields',true).concat($(this).datagrid('getColumnFields'));
				for(var i=0; i<fields.length; i++){
					var col = $(this).datagrid('getColumnOption', fields[i]);
					col.editor1 = col.editor;
					if (fields[i] != param.field){
						col.editor = null;
					}
				}
				$(this).datagrid('beginEdit', param.index);
				var ed = $(this).datagrid('getEditor', param);
				if (ed){
					if ($(ed.target).hasClass('textbox-f')){
						$(ed.target).textbox('textbox').focus();
					} else {
						$(ed.target).focus();
					}
				}
				for(var i=0; i<fields.length; i++){
					var col = $(this).datagrid('getColumnOption', fields[i]);
					col.editor = col.editor1;
				}
			});
		},
		enableCellEditing: function(jq){
			return jq.each(function(){
				var dg = $(this);
				var opts = dg.datagrid('options');
				opts.oldOnClickCell = opts.onClickCell;
				opts.onClickCell = function(index, field){
					if (opts.editIndex != undefined){
						if (dg.datagrid('validateRow', opts.editIndex)){
							dg.datagrid('endEdit', opts.editIndex);
							opts.editIndex = undefined;
						} else {
							return;
						}
					}
					dg.datagrid('selectRow', index).datagrid('editCell', {
						index: index,
						field: field
					});
					opts.editIndex = index;
					opts.oldOnClickCell.call(this, index, field);
					sumarDetalle();
				}
			});
		}
	});

	window.onload = function() {
		fechaCerrada();
		document.getElementById("txnTipoCambio").value = 36.6243;
		recalcularCordobas();
	}

	$(function(){
		$('#dgDetalle').datagrid().datagrid('enableCellEditing');
	})
	
	$('form').submit(function(e){
	e.preventDefault();

	if (verificarFormulario() == true)
	{
		var texto;
		var datos;
		var registros;
		var i;
		var cerrarLlave;
		var gridDetalle = $('#dgDetalle').datagrid('getData');
		var estudiante = document.getElementById("cboEstudiante").value;

		texto = '{"CodPago":"' + document.getElementById("CodPago").value + '", ';
		texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';
		texto += '"cboEstudiante":"' + document.getElementById("cboEstudiante").value + '", ';
		texto += '"txtRecibo":"' + document.getElementById("txtRecibo").value + '", ';
		
		if (document.getElementById("optSerie1").checked)
			texto += '"optSerie":"A", ';
		else
			texto += '"optSerie":"B", ';

		texto += '"txnMonto":"' + document.getElementById("txnMonto").value + '", ';
		texto += '"txnRetencion":"0", '; //No aplica en Pagos de Estudiantes
		
		if (document.getElementById("optMoneda1").checked)
			texto += '"optMoneda":"0", ';
		else
			texto += '"optMoneda":"1", ';
		
		texto += '"txnTipoCambio":"' + document.getElementById("txnTipoCambio").value + '", ';
		texto += '"txtConcepto":"' + document.getElementById("txtConcepto").value + '", ';
		
		if (document.getElementById("optTipoPago1").checked)
			texto += '"optTipoPago":"0", ';
		if (document.getElementById("optTipoPago2").checked)
			texto += '"optTipoPago":"1", ';
		if (document.getElementById("optTipoPago3").checked)
			texto += '"optTipoPago":"2", ';
		if (document.getElementById("optTipoPago4").checked)
			texto += '"optTipoPago":"3", ';
		if (document.getElementById("optTipoPago5").checked)
			texto += '"optTipoPago":"4", ';
		if (document.getElementById("optTipoPago6").checked)
			texto += '"optTipoPago":"5", ';
		
		texto += '"txtNumeroCk":"' + document.getElementById("txtNumeroCk").value + '", ';
		texto += '"txtBancoCk":"' + document.getElementById("txtBancoCk").value + '", ';

		registros = $('#dgDetalle').datagrid('getRows').length - 1;

		if (registros >= 0)
		{
			texto += '"gridDetalle": [';
			for (i=0; i<=registros; i++)
			{
				cerrarLlave = false;
				
				if (gridDetalle.rows[i].pago > 0)
				{
					texto += '{"cobro":"' + gridDetalle.rows[i].cobro + '", "matricula":"' + gridDetalle.rows[i].matricula + '", "pago":"' + gridDetalle.rows[i].pago + '", "descuento":"' + gridDetalle.rows[i].descuento;
					cerrarLlave = true;
				}
				
				if (i==registros)
				{
					if (cerrarLlave == true)
					{
						if (texto.substr(texto.length - 1, 1) == ",")
							texto = texto.substr(0, texto.length - 1) + '"}],';
						else
							texto += '"}],';
					}
					else
					{
						if (texto.substr(texto.length - 1, 1) == ",")
							texto = texto.substr(0, texto.length - 1) + '],';
						else
							texto += '],';
					}
				}
				else
				{
					if (cerrarLlave == true)
						texto += '"},';
				}
			}
		}
		
		texto = texto.substr(0, texto.length - 1) + '}';
		datos = JSON.parse(texto);

		$.ajax({
			url:'procPagosEstudiantes.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}
		})
		.done(function(){location.href="gridPagos.php";})
		.fail(function(){console.log('Error')});
		}
	});
</script>