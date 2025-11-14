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
	require_once ("funciones/fxGrupos.php");
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
		$PermisoUsuario = fxPermisoUsuario("catGrupos");
		
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
			if (isset($_POST["CodGrupo"]))
			{
				$Codigo = $_POST["CodGrupo"];
				$Nombre = $_POST["NomGrupo"];
				$gridDetalle = $_POST["gridDetalle"];
				$gridUsuario = $_POST["gridUsuario"];

				{
					if (fxExisteGrupo($Codigo) == 0)
					{
						fxGuardarGrupo ($Codigo, $Nombre);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA003A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarGrupo ($Codigo, $Nombre);
						fxBorrarPermiso ($Codigo);
						fxBorrarUsuarioGrupo ($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA003A", $Codigo, "", "Modificar");
					}
				}
				
				foreach($gridDetalle as $Registro)
				{
					$Pagina = $Registro['pagina'];
					if ($Registro['agregar'] == "x")
						$Agregar = 1;
					else
						$Agregar = 0;
					
					if ($Registro['editar'] == "x")
						$Modificar = 1;
					else
						$Modificar = 0;
						
					if ($Registro['borrar'] == "x")
						$Borrar = 1;
					else
						$Borrar = 0;
					
					if ($Registro['anular'] == "x")
						$Anular = 1;
					else
						$Anular = 0;

					fxGuardarPermiso ($Codigo, $Pagina, $Agregar, $Modificar, $Borrar, $Anular);
				}
				
				foreach($gridUsuario as $Registro)
				{
					$Usuario = $Registro['usuario'];
					fxGuardarUsuarioGrupo ($Codigo, $Usuario);
				}
					
				?><meta http-equiv="Refresh" content="0;url=gridGrupos.php"/><?php
			}
			else
			{
				if (isset($_POST["KDSA"]))
					$Codigo = $_POST["KDSA"];
				else
					$Codigo = "";
				
				if ($Codigo != "")
				{
					$RecordSet = fxDevuelveGrupo(0, $Codigo);
					$Fila = $RecordSet->fetch();
					$Nombre = $Fila["NOMBRE_003"];
				}
				else
				{
					$Nombre = "";
				}
	?>
    <div class="container text-left">
		<div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Grupos de usuarios</strong></div>
            </div>
        </div>

    	<div id="DivContenido">
			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
				<form id="catGrupos" name="catGrupos" action="catGrupos.php">
                	<div class = "form-group row">
                        <label for="CodGrupo" class="col-sm-12 col-md-2 col-form-label">Código del Grupo</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
                            if (trim($Codigo) != "")
                                echo('<input type="text" class="form-control" id="CodGrupo" name="CodGrupo" value="' . $Codigo . '" readonly />'); 
                            else
                                echo('<input type="text" class="form-control" id="CodGrupo" name="CodGrupo" value="' . $Codigo . '" />'); 
                        ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="NomGrupo" class="col-sm-12 col-md-2 col-form-label">Nombre del Grupo</label>
                        <div class="col-sm-12 col-md-7">
						<?php echo('<input type="text" class="form-control" id="NomGrupo" name="NomGrupo" value="' . $Nombre . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dgGRP" class="col-sm-12 col-md-2 form-label">Permisos del Grupo</label>
                        <div class="col-sm-auto col-md-7">
                            <select class="form-control" id="CboPagina" name="CboPagina">
                                <?php
                                    $mDatos = fxDevuelvePaginas(1);
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["PAGINA_REL"]);
                                        $Texto = $Fila["DESC_004"];
                                       	echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                    }
                                ?>
                            </select>
                            <?php
								$nombreArchivo = fxEscribeJsonGRP($Codigo);
							?>
                            <div id="dvGRP">
                            <table id="dgGRP" class="easyui-datagrid table" data-options="iconCls:'icon-edit', toolbar:'#tbGRP', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCellGRP">
                                <thead>
                                    <tr>
                                        <th data-options="field:'pagina',width:'20%',align:'left'">Página</th>
                                        <th data-options="field:'descripcion',width:'40%',align:'left'">Nombre de la página</th>
                                        <th data-options="field:'agregar',width:'10%',align:'center',editor:{type:'checkbox',options:{on:'x',off:''}}">Agregar</th>
                                        <th data-options="field:'editar',width:'10%',align:'center',editor:{type:'checkbox',options:{on:'x',off:''}}">Editar</th>
                                        <th data-options="field:'borrar',width:'10%',align:'center',editor:{type:'checkbox',options:{on:'x',off:''}}">Borrar</th>
                                        <th data-options="field:'anular',width:'10%',align:'center',editor:{type:'checkbox',options:{on:'x',off:''}}">Anular</th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div id="tbGRP" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendGRP()">Agregar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitGRP()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitGRP()">Aceptar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectGRP()">Deshacer</a>
                    </div>
                    
                    <div class = "form-group row">
						<label for="dgUSR" class="col-sm-12 col-md-2 form-label">Usuarios del Grupo</label>
                        <div class="col-sm-auto col-md-7">
                            <select class="form-control" id="CboUsuario" name="CboUsuario">
                                <?php
                                    $mDatos = fxDevuelveUsuario(1);
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["USUARIO_REL"]);
                                        $Texto = rtrim($Fila["NOMBRE_002"]);
                                       	echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                    }
                                ?>
                            </select>
                            <?php
								$nombreArchivo = fxEscribeJsonUSR($Codigo);
							?>
                            <div id="dvUSR">
                            <table id="dgUSR" class="easyui-datagrid table" data-options="iconCls:'icon-edit', toolbar:'#tbUSR', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCellUSR">
                                <thead>
                                    <tr>
                                        <th data-options="field:'nombre',width:'100%',align:'left'">Nombre del Usuario</th>
                                        <th data-options="field:'usuario',hidden:'true'"></th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div id="tbUSR" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendUSR()">Agregar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitUSR()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitUSR()">Aceptar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectUSR()">Deshacer</a>
                    </div>
                    
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-2">
							<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridGrupos.php';"/>
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
		if (document.getElementById('CodGrupo').value=="")
		{
			$.messager.alert('INOFE','Falta el Código del Grupo.','warning');
			return false;
		}
		
		if(document.getElementById('NomGrupo').value=="")
		{
			$.messager.alert('INOFE','Falta el Nombre del Grupo.','warning');
			return false;
		}
		
		return true;
	}
	
	var editIndexGRP = undefined;
	var editIndexUSR = undefined;
	var lastIndexGRP;
	var lastIndexUSR;
	
	$('#dgGRP').datagrid({
		onClickRow:function(rowIndex){
			if (lastIndexGRP != rowIndex){
				$(this).datagrid('endEdit', lastIndexGRP);
				$(this).datagrid('beginEdit', rowIndex);
			}
			lastIndexGRP = rowIndex;
		}
	});
	
	$('#dgUSR').datagrid({
		onClickRow:function(rowIndex){
			if (lastIndexUSR != rowIndex){
				$(this).datagrid('endEdit', lastIndexUSR);
				$(this).datagrid('beginEdit', rowIndex);
			}
			lastIndexUSR = rowIndex;
		}
	});

	function endEditingGRP(){
		if (editIndexGRP == undefined){return true}
		if ($('#dgGRP').datagrid('validateRow', editIndexGRP)){
			$('#dgGRP').datagrid('endEdit', editIndexGRP);
			editIndexGRP = undefined;
			return true;
		} else {
			return false;
		}
	}
	
	function endEditingUSR(){
		if (editIndexUSR == undefined){return true}
		if ($('#dgUSR').datagrid('validateRow', editIndexUSR)){
			$('#dgUSR').datagrid('endEdit', editIndexUSR);
			editIndexUSR = undefined;
			return true;
		} else {
			return false;
		}
	}
	
	function onClickCellGRP(index, field){
		if (editIndexGRP != index){
			if (endEditingGRP()){
				$('#dgGRP').datagrid('selectRow', index)
						.datagrid('beginEdit', index);
				var ed = $('#dgGRP').datagrid('getEditor', {index:index,field:field});
				if (ed){
					($(ed.target).data('checkbox') ? $(ed.target).textbox('checkbox') : $(ed.target)).focus();
				}
				editIndexGRP = index;
			} else {
				setTimeout(function(){
					$('#dgGRP').datagrid('selectRow', editIndexGRP);
				},0);
			}
		}
	}
	
	function onClickCellUSR(index, field){
		if (editIndexUSR != index){
			if (endEditingUSR()){
				$('#dgUSR').datagrid('selectRow', index)
						.datagrid('beginEdit', index);
				editIndexUSR = index;
			} else {
				setTimeout(function(){
					$('#dgUSR').datagrid('selectRow', editIndexUSR);
				},0);
			}
		}
	}
	
	function appendGRP(){
		if (endEditingGRP()){
			var i;
			var codigo;
			var existePagina = false;
			var datos = $('#dgGRP').datagrid('getData');
			var registros = $('#dgGRP').datagrid('getRows').length;
			
			if (registros > 0)
            {
    			for (i=0; i<registros; i++)
    			{
    				if (datos.rows[i].pagina == $('#CboPagina option:selected').val())
						existePagina = true;
    			}
			}
			
			if (existePagina == true)
			{
				$.messager.alert('INOFE',$('#CboPagina option:selected').text() + ' ya fue incluido.','warning');
				$('#CboPagina').focus()
			}
			else
			{
				$('#dgGRP').datagrid('appendRow',{pagina:$('#CboPagina option:selected').val(), descripcion:$('#CboPagina option:selected').text(), agregar:'', editar:'', borrar:'', anular:''});
				editIndex = $('#dgGRP').datagrid('getRows').length;
				$('#dgGRP').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
			}
		}
	}

	function appendUSR(){
		if (endEditingUSR()){
			var i;
			var codigo;
			var existeUsuario = false;
			var datos = $('#dgUSR').datagrid('getData');
			var registros = $('#dgUSR').datagrid('getRows').length;
			
			if (registros > 0)
            {
    			for (i=0; i<registros; i++)
    			{
    				if (datos.rows[i].usuario == $('#CboUsuario option:selected').val())
						existeUsuario = true;
    			}
			}
			
			if (existeUsuario == true)
			{
				$.messager.alert('INOFE',$('#CboUsuario option:selected').text() + ' ya fue incluido.','warning');
				$('#CboUsuario').focus()
			}
			else
			{
				$('#dgUSR').datagrid('appendRow',{usuario:$('#CboUsuario option:selected').val(), nombre:$('#CboUsuario option:selected').text()});
				editIndexUSR = $('#dgUSR').datagrid('getRows').length;
				$('#dgUSR').datagrid('selectRow', editIndexUSR).datagrid('beginEdit', editIndexUSR);
			}
		}
	}
		
	function removeitGRP(){
		if (editIndexGRP == undefined){return}
		$('#dgGRP').datagrid('cancelEdit', editIndexGRP)
				.datagrid('deleteRow', editIndexGRP);
		editIndexGRP = undefined;
	}
	
	function removeitUSR(){
		if (editIndexUSR == undefined){return}
		$('#dgUSR').datagrid('cancelEdit', editIndexUSR)
				.datagrid('deleteRow', editIndexUSR);
		editIndexUSR = undefined;
	}
	
	function acceptitGRP(){
		if (endEditingGRP()){
			$('#dgGRP').datagrid('acceptChanges');
		}
	}

	function acceptitUSR(){
		if (endEditingUSR()){
			$('#dgUSR').datagrid('acceptChanges');
		}
	}
	
	function rejectGRP(){
		$('#dgGRP').datagrid('rejectChanges');
		editIndexGRP = undefined;
	}
	
	function rejectUSR(){
		$('#dgUSR').datagrid('rejectChanges');
		editIndexUSR = undefined;
	}
	
	$('form').submit(function(e){
	e.preventDefault();

	if (verificarFormulario() == true)
	{
		var texto;
		var datos;
		var registros;
		var sinGrupos = true;
		var i;
		var gridDetalle = $('#dgGRP').datagrid('getData');
		var gridUsuario = $('#dgUSR').datagrid('getData');
		
		texto = '{"CodGrupo":"' + document.getElementById("CodGrupo").value + '", ';
		texto += '"NomGrupo":"' + document.getElementById("NomGrupo").value + '", ';

		registros = $('#dgGRP').datagrid('getRows').length - 1;
		
		if (registros >= 0)
		{
			sinGrupos = false;
			texto += '"gridDetalle": [';
			for (i=0; i<=registros; i++)
			{
				texto += '{"pagina":"' + gridDetalle.rows[i].pagina + '", "descripcion":"' + gridDetalle.rows[i].descripcion + '", "agregar":"' + gridDetalle.rows[i].agregar + '", "editar":"' + gridDetalle.rows[i].editar + '", "borrar":"' + gridDetalle.rows[i].borrar + '", "anular":"' + gridDetalle.rows[i].anular;
				if (i==registros)
					texto += '"}],';
				else
					texto += '"},';
			}
		}
		
		registros = $('#dgUSR').datagrid('getRows').length - 1;
		
		if (registros >= 0)
		{
			texto += '"gridUsuario": [';
			for (i=0; i<=registros; i++)
			{
				texto += '{"usuario":"' + gridUsuario.rows[i].usuario;
				if (i==registros)
					texto += '"}]}';
				else
					texto += '"},';
			}
		}
		else
		{
			if (sinGrupos == true)
				texto = texto.substr(0, texto.length - 2) + '}'
			else
				texto = texto.substr(0, texto.length - 1) + '}'
		}

		datos = JSON.parse(texto);

		$.ajax({
			url:'catGrupos.php',
			type:'post',
			data:datos,
			beforeSend: function(){console.log(datos)}	
		})
		.done(function(){location.href="gridGrupos.php";})
		.fail(function(){console.log('Error')});
		}
	});
</script>
<?php
function fxEscribeJsonGRP($grupo)
{
	if ($grupo == "")
		$nombreArchivo = "GRP0000.json";
	else
		$nombreArchivo = "GRP" . $grupo . ".json";

	if (file_exists($nombreArchivo))
	{
		unlink($nombreArchivo);
	}
	
	//Escribe el Json
	$mDatos = fxDevuelvePermiso($grupo);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"pagina":"' . rtrim($Fila['PAGINA_REL']) . '", ');
		fwrite($archivo, '"descripcion":"' . rtrim($Fila['DESC_004']) . '", ');
		if ($Fila['INCLUIR_005'] == 1)
			fwrite($archivo, '"agregar":"x", ');
		else
			fwrite($archivo, '"agregar":"", ');
		
		if ($Fila['MODIFICAR_005'] == 1)
			fwrite($archivo, '"editar":"x", ');
		else
			fwrite($archivo, '"editar":"", ');
			
		if ($Fila['BORRAR_005'] == 1)
			fwrite($archivo, '"borrar":"x", ');
		else
			fwrite($archivo, '"borrar":"", ');
			
		if ($Fila['ANULAR_005'] == 1)
			fwrite($archivo, '"anular":"x"');
		else
			fwrite($archivo, '"anular":""');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}

function fxEscribeJsonUSR($grupo)
{
	if ($grupo == "")
		$nombreArchivo = "USR0000.json";
	else
		$nombreArchivo = "USR" . $grupo . ".json";

	if (file_exists($nombreArchivo))
	{
		unlink($nombreArchivo);
	}
	
	//Escribe el Json
	$mDatos = fxDevuelveUsuarioGrupo($grupo);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"usuario":"' . rtrim($Fila['USUARIO_REL']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_002']) . '"');
		
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