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
	require_once ("funciones/fxSeguimiento.php");
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
		$PermisoUsuario = fxPermisoUsuario("procSeguimiento");
		
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
			if (isset($_POST["txtCodSeguimiento"]))
			{
				$mnOperacion = $_POST["Operacion"];
				$Codigo = $_POST["txtCodSeguimiento"];
				$Prospecto = $_POST["txtProspecto"];
				$Fecha = $_POST["dtpFecha"];
				$Proximo = $_POST["dtpProximo"];
				$Observaciones = $_POST["txtObservaciones"];
				$Usuario = $_POST["txtUsuario"];
				$gridDetalle = $_POST["gridDetalle"];
				$gridOtros = $_POST["gridOtros"];

				{
					if ($mnOperacion == 0)
					{
						$Codigo = fxGuardarSeguimiento ($Prospecto, $Fecha, $Proximo, $Observaciones, $Usuario);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA080A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarSeguimiento ($Codigo, $Prospecto, $Fecha, $Proximo, $Observaciones, $Usuario);
						fxBorrarDetSeguimiento ($Codigo);
						fxBorrarDetSeguimiento2 ($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA080A", $Codigo, "", "Modificar");
					}
				}
				
				foreach($gridDetalle as $Registro)
				{
					$Curso = $Registro['curso'];
					fxGuardarDetSeguimiento ($Codigo, $Curso);
				}
				
				$itemId = 1;
				foreach($gridOtros as $Registro)
				{
					$CursoKdsa = $Registro['cursoKdsa'];
					fxGuardarDetSeguimiento2 ($Codigo, $itemId, $CursoKdsa);
					$itemId++;
				}
								
				?><meta http-equiv="Refresh" content="0;url=gridSeguimiento.php"/><?php
			}
			else
			{
				if (isset($_POST["mOperacion"]))
					$mnOperacion = $_POST["mOperacion"];
				else
				$mnOperacion = 0;
				
				if ($mnOperacion == 0)
				{
					$Codigo = "";
					$Prospecto = "";
				}
				
				if ($mnOperacion == 1)
				{
					$Codigo = $_POST["mCodigo"];
					$Prospecto = "";
				}
				
				if ($mnOperacion == 2)
				{
					$Codigo = "";
					$Prospecto = $_POST["mProspecto"];
				}

				if ($mnOperacion != 2)
				{
					if ($Codigo != "")
					{
						$RecordSet = fxDevuelveSeguimiento (0, $Codigo);
						$Fila = $RecordSet->fetch();
						$Prospecto = $Fila["PROSPECTO_REL"];
					}
					else
						$Prospecto = "";
				}

				if ($Prospecto != "")
				{
					$msConsulta = "select NOMBRE_060 from KDSA060A where PROSPECTO_REL = ?";
					$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				    $mDatos->execute([$Prospecto]);
                    $fAux = $mDatos->fetch();
				    $NomProspecto = $fAux["NOMBRE_060"];
					if ($Codigo != "")
					{
						$Fecha = $Fila["FECHA_080"];
						$Proximo = $Fila["PROXIMOCONTACTO_080"];
						$Observaciones = $Fila["OBSERVACIONES_080"];
						$Usuario = $Fila["USUARIO_080"];
					}
					else
					{
						$Fecha = "1900-01-01";
						$Proximo = "1900-01-01";
						$Observaciones = "";
						$Usuario = "";
					}
				}
				else
				{
					$NomProspecto = "";
					$Fecha = "";
					$Proximo = "";
					$Observaciones = "";
					$Usuario = "";
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Seguimiento de prospectos</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
				<form id="procSeguimiento" name="procSeguimiento">
                	<div class = "form-group row">
                        <label for="txtCodSeguimiento" class="col-sm-12 col-md-3 col-form-label">Código del Seguimiento</label>
                        <div class="col-sm-12 col-md-3">
                        <?php echo('<input type="text" class="form-control" id="txtCodSeguimiento" name="txtCodSeguimiento" value="' . $Codigo . '" readonly />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txtProspecto" class="col-sm-12 col-md-3 col-form-label">Prospecto</label>
                        <div class="col-sm-12 col-md-3">
                        <?php echo('<input type="text" class="form-control" id="txtProspecto" name="txtProspecto" value="' . $Prospecto . '" onblur="escribeProspecto()" />');?>
                        </div>
                        <br/>
                        <div class="col-sm-auto col-md-7 col-sm-offset-none col-md-offset-3">
                        <?php echo('<input type="text" class="form-control" id="txtNomProspecto" name="txtNomProspecto" value="' . $NomProspecto . '" readonly />');?>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dtpFecha" class="col-sm-12 col-md-3 col-form-label">Fecha</label>
                        <div class="col-sm-12 col-md-3">
						<?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" />');
							else
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>

                    <div class = "form-group row">
						<label for="dtpProximo" class="col-sm-12 col-md-3 col-form-label">Próximo contacto</label>
                        <div class="col-sm-12 col-md-3">
						<?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpProximo" name="dtpProximo" value="' . date("Y-m-d") . '" />');
							else
								echo('<input type="date" class="form-control" id="dtpProximo" name="dtpProximo" value="' . $Proximo . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                                        
                    <div class = "form-group row">
						<label for="txtObservaciones" class="col-sm-12 col-md-3 form-label">Observaciones</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<textarea class="form-control" id="txtObservaciones" name="txtObservaciones" rows="3">' . $Observaciones . '</textarea>'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                             
                    <div class = "form-group row">
						<label for="dgDET" class="col-sm-12 col-md-3 form-label">Cursos de interés</label>
                        <div class="col-sm-auto col-md-7">
                            <select class="form-control" id="cboCurso" name="cboCurso">
                                <?php
									$msConsulta = "select CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE_020 from KDSA020A where ACTIVO_020 = 1 order by NOMBRE_020 desc";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
									$mDatos->execute();
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["CURSO_REL"]);
                                        $Texto = $Fila["NOMBRE_020"];
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
                                        <th data-options="field:'curso',width:'20%',align:'left'">Curso</th>
                                        <th data-options="field:'nombre',width:'70%',align:'left'">Nombre</th>
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
						<label for="dgDET2" class="col-sm-12 col-md-3 form-label">Otros cursos de interés<p style="color:rgb(150,150,150)"><i><small>Cursos que no están en el Catálogo</small></i></p></label>
                        <div class="col-sm-auto col-md-7">
                            <?php
								$nombreArchivoDET2 = fxEscribeJsonDET2($Codigo);
							?>
                            <div id="dvDET2">
                            <table id="dgDET2" class="easyui-datagrid table" data-options="iconCls:'icon-edit', toolbar:'#tbDET2', footer:'#ftDET2', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoDET2)); ?>', method:'get', onClickCell: onClickCellDET2">
                                <thead>
                                    <tr>
                                        <th data-options="field:'cursoKdsa',width:'100%',align:'left',editor:'text'">Curso</th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div id="tbDET2" style="height:auto; padding-top:1%; padding-bottom:2%">
                    	<table width="100%">
                        	<tr><td>Nombre de curso</td><td><input id="txtCursoKdsa" class="easyui-textbox" style="width:100%"></td></tr>
                        </table>
                    </div>
                
                    <div id="ftDET2" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick= "appendDET2()">Agregar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitDET2()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitDET2()">Aceptar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectDET2()">Deshacer</a>
                    </div>
                    
                     <div class = "form-group row">
						<label for="txtUsuario" class="col-sm-12 col-md-3 form-label">Usuario</label>
                        <div class="col-sm-12 col-md-4">
						<?php
							if ($Codigo == "")
								echo('<input type="text" class="form-control" id="txtUsuario" name="txtUsuario" value="' . $_SESSION["gsUsuario"] . '" readonly />');
							else
								echo('<input type="text" class="form-control" id="txtUsuario" name="txtUsuario" value="' . $Usuario . '" readonly />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                                       
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-8 col-md-offset-3">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridSeguimiento.php';"/>
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
	function myformatter(date){
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
	}
	
	function myparser(s){
		if (!s) return new Date();
		var ss = (s.split('-'));
		var y = parseInt(ss[0],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[2],10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
			return new Date(d,m-1,y);
		} else {
			return new Date();
		}
	}
	
	function fechaSQL(date){
		var y = date.substring(6);
		var m = date.substring(3, 5);
		var d = date.substring(0, 2);
		var resultado = y+'-'+m+'-'+d;

		return (resultado);
	}
	
	function escribeProspecto()
	{
		var mnCero = "0";
		var mnNumero = document.getElementById('txtProspecto').value;
		var mnLongitud;
		var mnIndice;
		
		if (mnNumero.length < 10)
		{
			mnIndice = mnNumero.indexOf("PT");
			if(mnIndice > -1)
				mnNumero = mnNumero.substring(2);

			mnLongitud = 8 - mnNumero.length;
			document.getElementById('txtProspecto').value = 'PT' + mnCero.repeat(mnLongitud) + mnNumero;
		}
		obtieneProspecto();
	}

	function obtieneProspecto()
	{
		parametros = '{"txtProspecto":"' +document.getElementById("txtProspecto").value+ '"}';
		datosJson = JSON.parse(parametros);
	
		return $.ajax({
			url:'funciones/fxDatosExternos.php',
			type:'post',
			async: false,
			data:datosJson,
			success: function(respuesta){document.getElementById("txtNomProspecto").value = respuesta}
		})
	}
	
	function verificarFormulario()
	{
		var regDET = $('#dgDET').datagrid('getRows').length;
		var regDET2 = $('#dgDET2').datagrid('getRows').length;
		
		if (document.getElementById('txtProspecto').value=="")
		{
			$.messager.alert('INOFE','Falta el Prospecto.','warning');
			return false;
		}
				
		if (regDET == 0 && regDET2 == 0)
		{
			$.messager.alert('INOFE','Faltan los Cursos de interés.','warning');
			return false;
		}
		
		return true;
	}
	
	/*Grid de Cursos*/
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
			var existeCurso = false;
			var datos = $('#dgDET').datagrid('getData');
			var registros = $('#dgDET').datagrid('getRows').length;
			
			if (registros > 0)
            {
    			for (i=0; i<registros; i++)
    			{
    				if (datos.rows[i].curso == $('#cboCurso option:selected').val())
						existeCurso = true;
    			}
			}
			
			if (existeCurso == true)
			{
				$.messager.alert('INOFE',$('#cboCurso option:selected').text() + ' ya fue incluido.','warning');
				$('#cboCurso').focus()
			}
			else
			{
				$('#dgDET').datagrid('appendRow',{curso:$('#cboCurso option:selected').val(), nombre:$('#cboCurso option:selected').text(), cantidad:1});
				editIndex = $('#dgDET').datagrid('getRows').length;
				$('#dgDET').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
			}
		}
	}
		
	function removeit(){
		if (editIndex == undefined){return}
		$('#dgDET').datagrid('cancelEdit', editIndex)
				.datagrid('deleteRow', editIndex);
		editIndex = undefined;
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
	
	/*Grid de Cursos que no están en el catálogo*/
	var editIndexDET2 = undefined;
	var lastIndexDET2;
	
	$('#dgDET2').datagrid({
		onClickRow:function(rowIndexDET2){
			if (lastIndexDET2 != rowIndexDET2){
				$(this).datagrid('endEdit', lastIndexDET2);
				$(this).datagrid('beginEdit', rowIndexDET2);
			}
			lastIndexDET2 = rowIndexDET2;
		}
	});
	
	function endEditingDET2(){
		if (editIndexDET2 == undefined){return true}
		if ($('#dgDET2').datagrid('validateRow', editIndexDET2)){
			$('#dgDET2').datagrid('endEdit', editIndexDET2);
			editIndexDET2 = undefined;
			return true;
		} else {
			return false;
		}
	}
	
	function onClickCellDET2(index, field){
		if (editIndexDET2 != index){
			if (endEditingDET2()){
				$('#dgDET').datagrid('selectRow', index)
						.datagrid('beginEdit', index);
				editIndexDET2 = index;
			} else {
				setTimeout(function(){
					$('#dgDET').datagrid('selectRow', editIndexDET2);
				},0);
			}
		}
	}
  	
	function appendDET2(){
		if (endEditingDET2()){
			$('#dgDET2').datagrid('appendRow',{cursoKdsa:$('#txtCursoKdsa').val()});
			editIndexDET2 = $('#dgDET2').datagrid('getRows').length;
			$('#dgDET2').datagrid('selectRow', editIndexDET2).datagrid('beginEdit', editIndexDET2);
		}
	}
		
	function removeitDET2(){
		if (editIndexDET2 == undefined){return}
		$('#dgDET2').datagrid('cancelEdit', editIndexDET2)
				.datagrid('deleteRow', editIndexDET2);
		editIndexDET2 = undefined;
	}
	
	function acceptitDET2(){
		if (endEditingDET2()){
			$('#dgDET2').datagrid('acceptChanges');
		}
	}
	
	function rejectDET2(){
		$('#dgDET2').datagrid('rejectChanges');
		editIndexDET2 = undefined;
	}
	
	$('form').submit(function(e){
	e.preventDefault();

	if (verificarFormulario() == true)
	{
		var texto;
		var datos;
		var registros;
		var i;
		var fechaIni;
		var fechaFin;
		var sinCursos = true;
		var gridDetalle = $('#dgDET').datagrid('getData');
		var gridOtros = $('#dgDET2').datagrid('getData');
		
		texto = '{"txtCodSeguimiento":"' + document.getElementById("txtCodSeguimiento").value + '", ';
		if (document.getElementById("txtCodSeguimiento").value == "")
			texto += '"Operacion":"0", ';
		else
			texto += '"Operacion":"1", ';
		texto += '"txtProspecto":"' + document.getElementById("txtProspecto").value + '", ';
		texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';
		texto += '"dtpProximo":"' + document.getElementById("dtpProximo").value + '", ';
		texto += '"txtObservaciones":"' + document.getElementById("txtObservaciones").value + '", ';
		texto += '"txtUsuario":"' + document.getElementById("txtUsuario").value + '", ';

		registros = $('#dgDET').datagrid('getRows').length - 1;
		
		if (registros >= 0)
		{
			sinCursos = false;
			texto += '"gridDetalle": [';
			for (i=0; i<=registros; i++)
			{
				texto += '{"curso":"' + gridDetalle.rows[i].curso + '", "nombre":"' + gridDetalle.rows[i].nombre;
				if (i==registros)
					texto += '"}],';
				else
					texto += '"},';
			}
		}
		
		registros = $('#dgDET2').datagrid('getRows').length - 1;
		
		if (registros >= 0)
		{
			texto += '"gridOtros": [';
			for (i=0; i<=registros; i++)
			{
				texto += '{"cursoKdsa":"' + gridOtros.rows[i].cursoKdsa;
				
				if (i==registros)
					texto += '"}]}';
				else
					texto += '"},';
			}
		}
		else
		{
			if (sinCursos == true)
				texto = texto.substr(0, texto.length - 2) + '}'
			else
				texto = texto.substr(0, texto.length - 1) + '}'
		}

		datos = JSON.parse(texto);

		$.ajax({
			url:'procSeguimiento.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}	
		})
		.done(function(){location.href="gridSeguimiento.php";})
		.fail(function(){console.log('Error')});
		}
	});
</script>
<?php
function fxEscribeJson($Seguimiento)
{
	if ($Seguimiento == "")
		$nombreArchivo = "SG00000000.json";
	else
		$nombreArchivo = $Seguimiento . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetSeguimiento($Seguimiento);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_020']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);
	
	return($nombreArchivo);
}

function fxEscribeJsonDET2($Seguimiento)
{
	if ($Seguimiento == "")
		$nombreArchivo = "SG00000000A.json";
	else
		$nombreArchivo = $Seguimiento . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetSeguimiento2($Seguimiento);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"cursoKdsa":"' . rtrim($Fila['CURSO_082']) . '"');
		
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