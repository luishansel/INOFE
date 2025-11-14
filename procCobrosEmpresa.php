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
	require_once ("funciones/fxCobrosEmpresa.php");
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
		$PermisoUsuario = fxPermisoUsuario("procCobrosEmpresa");
		
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
				$Deudor = $_POST["txtDeudor"];
				$gridDetalle = $_POST["gridDetalle"];

				{
					if ($mnOperacion == 0)
					{
						fxGuardarCobroEmpresa ($Codigo, $Deudor);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA052A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarCobroEmpresa ($Codigo, $Deudor);
						fxBorrarDetCobroEmpresa ($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA052A", $Codigo, "", "Modificar");
					}
				}
				
				foreach($gridDetalle as $Registro)
				{
					$Matricula = $Registro['matricula'];
					fxGuardarDetCobroEmpresa ($Codigo, $Matricula);
				}
								
				?><meta http-equiv="Refresh" content="0;url=gridCobrosEmpresa.php"/><?php
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
					$Deudor = "";
					$Abonado = 0;
					$Pagado = 0;
					$Exonerado = 0;
					$Anulado = 0;
				}
				else
				{
					$mnOperacion = 1;
					$RecordSet = fxDevuelveCobroEmpresa(0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Deudor = $Fila["DEUDOR_052"];
					$Abonado = $Fila["ABONADO_052"];
					$Pagado = $Fila["PAGADO_052"];
					$Exonerado = $Fila["EXONERADO_052"];
					$Anulado = $Fila["ANULADO_052"];
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Cobros empresariales</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
				<form id="procCobrosEmpresa" name="procCobrosEmpresa">
                	<div class = "form-group row">
                        <label for="cboCobro" class="col-sm-12 col-md-2 col-form-label">Cobro</label>
                        <div class="col-sm-12 col-md-7">
                        	<?php
								if ($Codigo == "")
								{
									echo('<select class="form-control" id="cboCobro" name="cboCobro">');
									$msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 = 3 and ACTIVO_050 = 1";
								}
								else
								{
									echo('<select class="form-control" id="cboCobro" name="cboCobro" disabled>');
									$msConsulta = "select COBRO_REL, CONCEPTO_050 from KDSA050A where TIPO_050 = 3";
								}
								
								$mDatos = $m_cnx_MySQL->prepare($msConsulta);
								$mDatos->execute();
								while ($Fila = $mDatos->fetch())
								{
									$Valor = rtrim($Fila["COBRO_REL"]);
									$Texto = rtrim($Fila["CONCEPTO_050"]);

									if ($Codigo == "")
									{
										echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
									}
									else
									{
										if ($Codigo == $Valor)
											echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
										else
											echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
									}
								}
								
								echo('</select>');
                        	?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtDeudor" class="col-sm-12 col-md-2 col-form-label">Deudor</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="txtDeudor" name="txtDeudor" value="' . $Deudor . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dgDET" class="col-sm-12 col-md-2 form-label">Estudiantes matriculados</label>
                        <div class="col-sm-auto col-md-7">
                            <select class="form-control" id="cboMatricula" name="cboMatricula">
                                <?php
									$msConsulta = "select MATRICULA_REL, concat(trim(APELLIDOS_010), ', ', trim(NOMBRES_010)) as ESTUDIANTE " ;
									$msConsulta .= "from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and ";
									$msConsulta .= "KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and ACTIVO_020 = 1 and ESTADO_030 <> 4";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
									$mDatos->execute();
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["MATRICULA_REL"]);
                                        $Texto = $Fila["ESTUDIANTE"];
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
                                        <th data-options="field:'matricula',width:'25%',align:'left'">Matrícula</th>
                                        <th data-options="field:'estudiante',width:'75%',align:'left'">Nombre del Estudiante</th>
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
                        <label for="txnAbonado" class="col-sm-12 col-md-2 col-form-label">Abonado</label>
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
                        <label for="optPagado" class="col-sm-12 col-md-2 form-label">Pagado</label>
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
                        <label for="optExonerado" class="col-sm-12 col-md-2 form-label">Exonerado</label>
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
                        <label for="optAnulado" class="col-sm-12 col-md-2 form-label">Anulado</label>
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
                    	<div class="col-auto col-xs-offset-none col-md-8 col-md-offset-2">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridCobrosEmpresa.php';"/>
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
		if (document.getElementById('txtDeudor').value=="")
		{
			$.messager.alert('INOFE','Falta el Deudor.','warning');
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
			var existeEstudiante = false;
			var datos = $('#dgDET').datagrid('getData');
			var registros = $('#dgDET').datagrid('getRows').length;
			
			if (registros > 0)
            {
    			for (i=0; i<registros; i++)
    			{
    				if (datos.rows[i].matricula == $('#cboMatricula option:selected').val())
						existeEstudiante = true;
    			}
			}
			
			if (existeEstudiante == true)
			{
				$.messager.alert('INOFE',$('#cboMatricula option:selected').text() + ' ya fue incluido.','warning');
				$('#cboMatricula').focus()
			}
			else
			{
				$('#dgDET').datagrid('appendRow',{matricula:$('#cboMatricula option:selected').val(), estudiante:$('#cboMatricula option:selected').text()});
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
		
		texto = '{"CodCobro":"' + document.getElementById("cboCobro").value + '", ';
		texto += '"txtDeudor":"' + document.getElementById("txtDeudor").value + '", ';

		registros = $('#dgDET').datagrid('getRows').length - 1;
		
		if (registros >= 0)
		{
			texto += '"gridDetalle": [';
			for (i=0; i<=registros; i++)
			{
				texto += '{"matricula":"' + gridDetalle.rows[i].matricula + '", "estudiante":"' + gridDetalle.rows[i].estudiante;
				if (i==registros)
					texto += '"}]}';
				else
					texto += '"},';
			}
		}
		datos = JSON.parse(texto);

		$.ajax({
			url:'procCobrosEmpresa.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}	
		})
		.done(function(){location.href="gridCobrosEmpresa.php";})
		.fail(function(){console.log('Error')});
		}
	});
</script>
<?php
function fxEscribeJson($Cobro)
{
	if ($Cobro == "")
		$nombreArchivo = "CO0000000.json";
	else
		$nombreArchivo = $Cobro . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetCobroEmpresa($Cobro);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"matricula":"' . rtrim($Fila['MATRICULA_REL']) . '", ');
		fwrite($archivo, '"estudiante":"' . rtrim(utf8_decode($Fila['ESTUDIANTE'])) . '"');
		
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