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
		$PermisoUsuario = fxPermisoUsuario("procPagosInatec");
		
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
				$CodCobroInatec = $_POST["CodCobroInatec"];
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
				$gridDetalle = $_POST["gridDetalle"];

				if ($Codigo == "")
				{
					$Codigo = fxGuardarPagos ($Fecha, $Nombre, $Recibo, $Serie, $Monto, $RetDgi, $RetAlcaldia, $Moneda, $TipoCambio, $Concepto, $TipoPago, $NumeroCk, $msBancoCk, 0, 0, 1);
					fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA040A", $Codigo, "", "Agregar");
  				
					foreach($gridDetalle as $Registro)
					{
						$Cobro = $Registro['cobro'];
						$Matricula = $Registro['matricula'];
						$Pago = $Registro['monto'];
						$Ret_Dgi = $Registro['retDgi'];
						$Ret_Alcaldia = $Registro['retAlcaldia'];
						if ($Pago > 0)
							fxGuardarDetPagosInatec ($Codigo, $CodCobroInatec, $Matricula, $Cobro, $Pago, $Ret_Dgi, $Ret_Alcaldia);
					}
				}

				?><meta http-equiv="Refresh" content="0;url=gridPagosInatec.php"/><?php
			}
			else
			{
				$Accion = $_POST["mAccion"];
				
				if ($Accion == 1)
				{
					$Codigo = $_POST["mCodigo"];
					$RecordSet = fxDevuelvePagos(0, "", $Codigo);
					$Fila = $RecordSet->fetch();
					$msConsulta = "select COBROINATEC_REL from KDSA043A where PAGO_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$Codigo]);
					$fAux = $mAuxiliar->fetch();
					$Cobro = $fAux["COBROINATEC_REL"];
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
					$msConsulta = "select DESC_054, RETENCION_DGI_054, RETENCION_ALCALDIA_054 from KDSA054A where COBROINATEC_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$Cobro]);
					$fAux = $mAuxiliar->fetch();
					$Descripcion = $fAux["DESC_054"];
					$RetencionDgi = $fAux["RETENCION_DGI_054"];
					$RetencionAlcaldia = $fAux["RETENCION_ALCALDIA_054"];
				}
				else
				{
					$Codigo = "";
					$Cobro = $_POST["mCobro"];
					$Fecha = date("Y-m-d");
					$Nombre = "Instituto Nacional Tecnológico";
					$Recibo = "";
					$Serie = "";
					$Monto = 0;
					$RetDgi = 0;
					$RetAlcaldia = 0;
					$Moneda = 0;
					$TipoCambio = 0;
					$msConsulta = "select DESC_054, RETENCION_DGI_054, RETENCION_ALCALDIA_054 from KDSA054A where COBROINATEC_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$Cobro]);
					$Fila = $mAuxiliar->fetch();
					$Descripcion = rtrim($Fila["DESC_054"]);
					$RetencionDgi = $Fila["RETENCION_DGI_054"];
					$RetencionAlcaldia = $Fila["RETENCION_ALCALDIA_054"];
					$Concepto = "Pago de " . $Fila["DESC_054"];
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
					<div class="degradado"><strong>Pagos de INATEC</strong></div>
				</div>
			</div>
			
			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
				<form id="procIngresos" name="procIngresos">
                	<div class = "form-group row">
                        <label for="CodPago" class="col-sm-12 col-md-2 col-form-label">Código del Pago</label>
                        <div class="col-sm-12 col-md-3">
                        <?php 
							echo('<input type="text" class="form-control" id="CodPago" name="CodPago" value="' . $Codigo . '" readonly />');
							echo('<input type="hidden" class="form-control" id="CodCobroInatec" name="CodCobroInatec" value="' . $Cobro . '" />');
						?>
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
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="0" readonly />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnMonto" name="txnMonto" value="' . $Monto . '" readonly />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnRetDgi" class="col-sm-12 col-md-2 col-form-label">Retención D.G.I.</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetDgi" name="txnRetDgi" value="0" readonly />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetDgi" name="txnRetDgi" value="' . $RetDgi . '" readonly />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnRetAlcaldia" class="col-sm-12 col-md-2 col-form-label">Retención Alcaldía</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetAlcaldia" name="txnRetAlcaldia" value="0" readonly />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetAlcaldia" name="txnRetAlcaldia" value="' . $RetAlcaldia . '" readonly />');
						?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optMoneda" class="col-sm-12 col-md-2 form-label">Moneda</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                 echo('<input type="radio" id="optMoneda1" name="optMoneda" value="0" checked disabled /> Córdobas <input type="radio" id="optMoneda2" name="optMoneda" value="1" disabled /> Dólares');
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

                    <div class = "form-group row">
                    	<div class="col-sm-auto col-md-2">
                            <table>
                                <tr><td><label for="dgDetalle" class="form-label">Detalle del pago</label></td></tr>
                                <tr><td><input type="button" id="Calcular" name="Calcular" value="Pago y Retención" class="btn btn-danger" onclick="calcularRetencion(<?php echo($RetencionDgi . ', ' . $RetencionAlcaldia); ?>)" /></td></tr>
                            </table>
                        </div>
                        <div class="col-sm-auto col-md-8">
                            <?php
								$nombreArchivo = fxEscribeJson($Codigo, $Cobro);
							?>
                            <div id="dvDetalle">
                            <table id="dgDetalle" class="easyui-datagrid table" style="line-height:normal; height:auto" data-options="iconCls:'icon-edit', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get'">
                                <thead>
                                    <tr>
                                    	<th data-options="field:'codigo', width:'15%',align:'left'">Cobro</th>
                                        <th data-options="field:'matricula', hidden:true">Matrícula</th>
                                        <th data-options="field:'estudiante',width:'37%',align:'left'" style="text-wrap:normal">Estudiante</th>
                                        <th data-options="field:'curso',width:'37%',align:'left'" style="text-wrap:normal">Curso</th>
                                        <th data-options="field:'concepto',width:'37%',align:'left'" style="text-wrap:normal">Concepto</th>
                                        <?php
										if ($Codigo == "")
										{?>
                                        	<th data-options="field:'deuda',width:'12%',align:'right'">Deuda U$</th>
                                            <th data-options="field:'deudaCordobas',width:'12%',align:'right'">Deuda C$</th>
										<?php
                                        }
										?>
                                        <th data-options="field:'monto',width:'12%',align:'right',editor:{type:'numberbox',options:{precision:2}}">Pago</th>
                                        <th data-options="field:'retDgi',align:'right',editor:{type:'numberbox',options:{precision:2}}" style="text-wrap:normal">Retención DGI</th>
                                        <th data-options="field:'retAlcaldia',align:'right',editor:{type:'numberbox',options:{precision:2}}" style="text-wrap:normal">Retención Alcaldía</th>
                                    </tr>
                                </thead>
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
						?>
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridPagosEmpresa.php'"/>
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
			mnDolares = gridDetalle.rows[i].deuda;
			mnCordobas = mnTipoCambio * mnDolares;
			$('#dgDetalle').datagrid('updateRow',{index: i, row:{deudaCordobas:Number(mnCordobas).toFixed(2)}});
		}
	}
	
	function sumarDetalle(){
		var gridDetalle = $('#dgDetalle').datagrid('getData');
		var registros = $('#dgDetalle').datagrid('getRows').length - 1;
		var mnPago = 0;
		var mnRetDgi = 0;
		var mnRetAlcaldia = 0;
		var i;
		for (i=0; i<=registros; i++)
		{
			mnPago += Number(gridDetalle.rows[i].monto);
			mnRetDgi += Number(gridDetalle.rows[i].retDgi) 
			mnRetAlcaldia += Number(gridDetalle.rows[i].retAlcaldia);
		}
		
		document.getElementById('txnMonto').value = mnPago;
		document.getElementById('txnRetDgi').value = mnRetDgi;
		document.getElementById('txnRetAlcaldia').value = retAlcaldia;
	};
	
	function calcularRetencion(retDgi, retAlcaldia){
		var gridDetalle = $('#dgDetalle').datagrid('getData');
		var registros = $('#dgDetalle').datagrid('getRows').length - 1;
		var seleccionado = $('#dgDetalle').datagrid('getSelected');
		var mnDeuda = 0;
		var mnPago = 0;
		var mnRetDgi = 0;
		var mnRetAlcaldia = 0;
		var i;

		if (seleccionado)
		{
			for (i=0; i<=registros; i++)
			{
				if (gridDetalle.rows[i].codigo == seleccionado.codigo)
				{
					mnDeuda = Number(gridDetalle.rows[i].deudaCordobas);
					mnRetDgi = Number(mnDeuda * (retDgi / 100));
					mnRetAlcaldia = Number(mnDeuda * (retAlcaldia / 100));
					mnPago = Number(mnDeuda - (mnRetDgi + mnRetAlcaldia));
					
					$('#dgDetalle').datagrid('updateRow',{index: i, row:{monto:Number(mnPago).toFixed(2)}});
					$('#dgDetalle').datagrid('updateRow',{index: i, row:{retDgi:Number(mnRetDgi).toFixed(2)}});
					$('#dgDetalle').datagrid('updateRow',{index: i, row:{retAlcaldia:Number(mnRetAlcaldia).toFixed(2)}});
				}
			}
			
			sumarDetalle();
		}
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
		var gridDetalle = $('#dgDetalle').datagrid('getData');

		texto = '{"CodPago":"' + document.getElementById("CodPago").value + '", ';
		texto += '"CodCobroInatec":"' + document.getElementById("CodCobroInatec").value + '", ';
		texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';
		texto += '"txtNombre":"' + document.getElementById("txtNombre").value + '", ';
		texto += '"txtRecibo":"' + document.getElementById("txtRecibo").value + '", ';
		
		if (document.getElementById("optSerie1").checked)
			texto += '"optSerie":"A", ';
		else
			texto += '"optSerie":"B", ';

		texto += '"txnMonto":"' + document.getElementById("txnMonto").value + '", ';
		texto += '"txnRetDgi":"' + document.getElementById("txnRetDgi").value + '", ';
		texto += '"txnRetAlcaldia":"' + document.getElementById("txnRetAlcaldia").value + '", ';
		
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
				texto += '{"cobro":"' + gridDetalle.rows[i].codigo + '", "matricula":"' + gridDetalle.rows[i].matricula + '", "monto":"' + gridDetalle.rows[i].monto + '", "retDgi":"' + gridDetalle.rows[i].retDgi + '", "retAlcaldia":"' + gridDetalle.rows[i].retAlcaldia;
				
				if (i==registros)
				{
					if (i==0)
						texto = texto + '"}]}';
					else
						texto = texto.substr(0, texto.length) + '"}]}';
				}
				else
					texto += '"},';
			}
		}

		datos = JSON.parse(texto);

		$.ajax({
			url:'procPagosInatec.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}	
		})
		.done(function(){location.href="gridPagosInatec.php";})
		.fail(function(){console.log('Error')});
		}
	});
</script>

<?php
function fxEscribeJson($pago, $cobro)
{
	if ($pago == "")
		$nombreArchivo = "PG00000000.json";
	else
		$nombreArchivo = $pago . ".json";

	if (file_exists($nombreArchivo))
	{
		unlink($nombreArchivo);
	}
	
	//Escribe el Json
	$mDatos = fxDevuelveDetPagosInatec($pago, $cobro);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"codigo":"' . rtrim($Fila['COBRO_REL']) . '", ');
		fwrite($archivo, '"matricula":"' . rtrim($Fila['MATRICULA_REL']) . '", ');
		fwrite($archivo, '"deuda":"' . rtrim($Fila['DEUDA']) . '", ');
		fwrite($archivo, '"deudaCordobas":"0", ');
		fwrite($archivo, '"estudiante":"' . rtrim($Fila['ESTUDIANTE']) . '", ');
		fwrite($archivo, '"curso":"' . rtrim($Fila['NOMBRE_020']) . '", ');
		fwrite($archivo, '"concepto":"' . rtrim($Fila['CONCEPTO_050']) . '", ');
		fwrite($archivo, '"monto":"' . rtrim($Fila['MONTO']) . '", ');
		fwrite($archivo, '"retDgi":"' . rtrim($Fila['RET_DGI']) . '", ');
		fwrite($archivo, '"retAlcaldia":"' . rtrim($Fila['RET_ALCALDIA']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}
?>