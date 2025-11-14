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
	require_once ("funciones/fxCobrosInatec.php");
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
		$PermisoUsuario = fxPermisoUsuario("procCobrosInatec");
		
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
			if (isset($_POST["CodCobro"]))
			{
				$mnOperacion = $_POST["Operacion"];
				$Codigo = $_POST["CodCobro"];
				$Descripcion = $_POST["txtDescripcion"];
				$RetDgi = $_POST["txnRetDgi"];
				$RetAlcaldia = $_POST["txnRetAlcaldia"];
				$gridDetalle = $_POST["gridDetalle"];

				{
					if ($mnOperacion == 0)
					{
						$Codigo = fxGuardarCobroInatec ($Descripcion, $RetDgi, $RetAlcaldia);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA054A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarCobroInatec ($Codigo, $Descripcion, $RetDgi, $RetAlcaldia);
						fxBorrarDetCobroInatec ($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA054A", $Codigo, "", "Modificar");
					}
				}
				
				foreach($gridDetalle as $Registro)
				{
					$Cobro = $Registro['cobro'];
					fxGuardarDetCobroInatec ($Codigo, $Cobro);
				}
								
				?><meta http-equiv="Refresh" content="0;url=gridCobrosInatec.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo == "")
				{
					$mnOperacion = 0;
					$Descripcion = "";
					$RetDgi = 0;
					$RetAlcaldia = 0;
					$Abonado = 0;
					$Pagado = 0;
					$Exonerado = 0;
					$Anulado = 0;
				}
				else
				{
					$mnOperacion = 1;
					$RecordSet = fxDevuelveCobroInatec (0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Descripcion = $Fila["DESC_054"];
					$RetDgi = $Fila["RETENCION_DGI_054"];
					$RetAlcaldia = $Fila["RETENCION_ALCALDIA_054"];
					$Abonado = $Fila["ABONADO_054"];
					$Pagado = $Fila["PAGADO_054"];
					$Exonerado = $Fila["EXONERADO_054"];
					$Anulado = $Fila["ANULADO_054"];
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Cobros de INATEC</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
				<form id="procCobrosInatec" name="procCobrosInatec">
                	<div class = "form-group row">
                        <label for="txtCodCobro" class="col-sm-12 col-md-3 col-form-label">Código del Cobro</label>
                        <div class="col-sm-12 col-md-3">
                        <?php echo('<input type="text" class="form-control" id="txtCodCobro" name="txtCodCobro" value="' . $Codigo . '" readonly />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtDescripcion" class="col-sm-12 col-md-3 form-label">Descripción</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<textarea class="form-control" id="txtDescripcion" name="txtDescripcion" rows="3">' . $Descripcion . '</textarea>'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnRetDgi" class="col-sm-12 col-md-3 col-form-label">Porcentaje de retención D.G.I.</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetDgi" name="txnRetDgi" value="0" />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetDgi" name="txnRetDgi" value="' . $RetDgi . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnRetAlcaldia" class="col-sm-12 col-md-3 col-form-label">Porcentaje de retención Alcaldía</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetAlcaldia" name="txnRetAlcaldia" value="0" />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnRetAlcaldia" name="txnRetAlcaldia" value="' . $RetAlcaldia . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dgDET" class="col-sm-12 col-md-3 form-label">Cobros abarcados</label>
                        <div class="col-sm-auto col-md-7">
                            <select class="form-control" id="cboCobro" name="cboCobro">
                                <?php
									$msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 = 4 and ACTIVO_050 = 1 order by CURSO_REL desc, COBRO_REL";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
									$mDatos->execute();
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["COBRO_REL"]);
                                        $Texto = $Fila["CONCEPTO_050"];
                                       	echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                    }
                                ?>
                            </select>
                            <?php
								$nombreArchivo = fxEscribeJson($Codigo);
							?>
                            <div id="dvDET">
                            <table id="dgDET" class="easyui-datagrid table" data-options="iconCls:'icon-edit', toolbar:'#tbDET', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCell">
                                <thead>
                                    <tr>
                                        <th data-options="field:'cobro',width:'25%',align:'left'">Cobro</th>
                                        <th data-options="field:'concepto',width:'75%',align:'left'">Concepto</th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div id="tbDET" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Agregar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptit()">Aceptar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Deshacer</a>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnAbonado" class="col-sm-12 col-md-3 col-form-label">Abonado</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnAbonado" name="txnAbonado" value="0" disabled />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnAbonado" name="txnAbonado" value="' . $Abonado . '" disabled />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optPagado" class="col-sm-12 col-md-3 form-label">Pagado</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Anulado == 1)
                                {
                                    echo('<input type="radio" id="optPagado1" name="optPagado" value="0" disabled /> No <input type="radio" id="optPagado2" name="optPagado" value="1" checked disabled /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optPagado1" name="optPagado" value="0" checked disabled /> No <input type="radio" id="optPagado2" name="optPagado" value="1" disabled /> Si');
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optExonerado" class="col-sm-12 col-md-3 form-label">Exonerado</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Anulado == 1)
                                {
                                    echo('<input type="radio" id="optExonerado1" name="optExonerado" value="0" disabled /> No <input type="radio" id="optExonerado2" name="optExonerado" value="1" checked disabled /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optExonerado1" name="optExonerado" value="0" checked disabled /> No <input type="radio" id="optExonerado2" name="optExonerado" value="1" disabled /> Si');
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optAnulado" class="col-sm-12 col-md-3 form-label">Anulado</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($Anulado == 1)
                                {
                                    echo('<input type="radio" id="optAnulado1" name="optAnulado" value="0" disabled /> No <input type="radio" id="optAnulado2" name="optAnulado" value="1" checked disabled /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optAnulado1" name="optAnulado" value="0" checked disabled /> No <input type="radio" id="optAnulado2" name="optAnulado" value="1" disabled /> Si');
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                    
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-8 col-md-offset-3">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridCobrosInatec.php';"/>
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
		if (document.getElementById('txnRetDgi').value==0 && document.getElementById('txnRetAlcaldia').value==0)
		{
			$.messager.alert('INOFE','Debe incluir al menos un porcentaje de Retención.','warning');
			return false;
		}
		
		return true;
	}
	
	var editIndex = undefined;
	var lastIndex;
	
	$('#dgDET').datagrid({
		onClickRow:function(rowIndex){
			if (lastIndex != rowIndex){
				$(this).datagrid('endEdit', lastIndex);
				$(this).datagrid('beginEdit', rowIndex);
			}
			lastIndex = rowIndex;
		}
	});
	
	function endEditing(){
		if (editIndex == undefined){return true}
		if ($('#dgDET').datagrid('validateRow', editIndex)){
			$('#dgDET').datagrid('endEdit', editIndex);
			editIndex = undefined;
			return true;
		} else {
			return false;
		}
	}
	
	function onClickCell(index, field){
		if (editIndex != index){
			if (endEditing()){
				$('#dgDET').datagrid('selectRow', index)
						.datagrid('beginEdit', index);
				editIndex = index;
			} else {
				setTimeout(function(){
					$('#dgDET').datagrid('selectRow', editIndex);
				},0);
			}
		}
	}
	
	function append(){
		if (endEditing()){
			var i;
			var codigo;
			var existeCobro = false;
			var datos = $('#dgDET').datagrid('getData');
			var registros = $('#dgDET').datagrid('getRows').length;
			
			if (registros > 0)
            {
    			for (i=0; i<registros; i++)
    			{
    				if (datos.rows[i].cobro == $('#cboCobro option:selected').val())
						existeCobro = true;
    			}
			}
			
			if (existeCobro == true)
			{
				$.messager.alert('INOFE',$('#cboCobro option:selected').text() + ' ya fue incluido.','warning');
				$('#cboCobro').focus()
			}
			else
			{
				$('#dgDET').datagrid('appendRow',{cobro:$('#cboCobro option:selected').val(), concepto:$('#cboCobro option:selected').text()});
				editIndex = $('#dgDET').datagrid('getRows').length;
				$('#dgDET').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
			}
		}
	}
		
	function removeit(){
		if (editIndex == undefined){return}
		$('#dgDET').datagrid('cancelEdit', editIndex)
				.datagrid('deleteRow', editIndex);
		editIndexGRP = undefined;
	}
	
	function acceptit(){
		if (endEditing()){
			$('#dgDET').datagrid('acceptChanges');
		}
	}
	
	function reject(){
		$('#dgDET').datagrid('rejectChanges');
		editIndex = undefined;
	}

	$('form').submit(function(e){
	e.preventDefault();

	if (verificarFormulario() == true)
	{
		var texto;
		var datos;
		var registros;
		var i;
		var gridDetalle = $('#dgDET').datagrid('getData');
		
		texto = '{"CodCobro":"' + document.getElementById("txtCodCobro").value + '", ';
		if (document.getElementById("txtCodCobro").value == "")
			texto += '"Operacion":"0", ';
		else
			texto += '"Operacion":"1", ';
		texto += '"txtDescripcion":"' + document.getElementById("txtDescripcion").value + '", ';
		texto += '"txnRetDgi":"' + document.getElementById("txnRetDgi").value + '", ';
		texto += '"txnRetAlcaldia":"' + document.getElementById("txnRetAlcaldia").value + '", ';

		registros = $('#dgDET').datagrid('getRows').length - 1;
		
		if (registros >= 0)
		{
			texto += '"gridDetalle": [';
			for (i=0; i<=registros; i++)
			{
				texto += '{"cobro":"' + gridDetalle.rows[i].cobro + '", "concepto":"' + gridDetalle.rows[i].concepto;
				if (i==registros)
					texto += '"}]}';
				else
					texto += '"},';
			}
		}

		datos = JSON.parse(texto);

		$.ajax({
			url:'procCobrosInatec.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}	
		})
		.done(function(){location.href="gridCobrosInatec.php";})
		.fail(function(){console.log('Error')});
		}
	});
</script>
<?php
function fxEscribeJson($Cobro)
{
	if ($Cobro == "")
		$nombreArchivo = "CI0000000.json";
	else
		$nombreArchivo = $Cobro . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetCobroInatec($Cobro);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"cobro":"' . rtrim($Fila['COBRO_REL']) . '", ');
		fwrite($archivo, '"concepto":"' . rtrim($Fila['CONCEPTO_050']) . '"');
		
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