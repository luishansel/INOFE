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
	require_once ("funciones/fxConfiguracionModulos.php");
	$Registro = fxVerificaUsuario();
	
	if ($Registro == 0)
	{
?>

<div class="container text-center">
    <div id="DivContenido">
        <img src="imagenes/errordeacceso.png" />
    </div>
</div>
<?php }
	else
	{
		$Administrador = fxVerificaAdministrador();
		$PermisoUsuario = fxPermisoUsuario("hrrCfgModulo");
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
		<div class="container text-center">
			<div id="DivContenido">
				<img src="imagenes/errordeacceso.png" />
			</div>
		</div>
		<?php }
		else
		{
			if (isset($_POST["txtCodConfiguracion"]))
			{
				$Codigo = $_POST["txtCodConfiguracion"];
				$Curso = $_POST["txtCurso"];
                if (isset($_POST["gridModulos"]))
                    $gridModulos = $_POST["gridModulos"];
                
                if ($Codigo == "")
                {
                    $Codigo = fxGuardarConfModulos ($Curso);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA110A", $Codigo, "", "Agregar");
                }
                else
                {
                    fxModificarConfModulos ($Codigo, $Curso);
                    fxBorrarDetConfModulos ($Codigo);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA110A", $Codigo, "", "Modificar");
                }
				
				$itemId = 1;
				foreach($gridModulos as $Registro)
				{
					$modulo = $Registro['modulo'];
                    fxGuardarDetConfModulos ($Codigo, $itemId, $modulo);
                    $itemId++;
				}
											
				?>
<meta http-equiv="Refresh" content="0;url=gridCfgModulo.php" /><?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $Codigo = $_POST["KDSA"];
                else
                    $Codigo = "";
                
                if ($Codigo != "")
                {
                    $mDatos = fxDevuelveConfModulos (0, $Codigo);
                    $Fila = $mDatos->fetch();
                    $Curso = $Fila["CURSO_110"];
                }
                else
                    $Curso = "";
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="hrrCfgModulos" name="hrrCfgModulos">
                    <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                        <div class="form-group row">
                            <label for="txtCodConfiguracion" class="col-sm-auto col-md-2 col-form-label">Código</label>
                            <div class="col-sm-12 col-md-3">
                                <?php echo('<input type="text" class="form-control" id="txtCodConfiguracion" name="txtCodConfiguracion" value="' . $Codigo . '" readonly />'); ?>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txtCurso" class="col-sm-auto col-md-2 col-form-label">Curso asociado</label>
                            <div class="col-sm-12 col-md-9">
                                <?php echo('<input type="text" class="form-control" id="txtCurso" name="txtCurso" value="' . $Curso . '" />'); ?>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                        <div class="form-group row">
                            <label for="dgMOD" class="col-sm-12 col-md-2 form-label">Módulos del Curso</label>
                            <div class="col-sm-auto col-md-9">
                                <?php
                                    $nombreArchivo = fxEscribeJson($Codigo);
                                ?>
                                <div id="dvMOD">
                                    <table id="dgMOD" class="easyui-datagrid table"
                                        data-options="iconCls:'icon-edit', toolbar:'#tbMOD', footer:'#ftMOD', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCell">
                                        <thead>
                                            <tr>
                                                <th data-options="field:'modulo',width:'100%',align:'left'">Módulo</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>

                        <div id="tbMOD" style="height:auto; padding-top:1%; padding-bottom:2%">
                            <table width="100%">
                                <tr>
                                    <td>Módulo</td>
                                    <td><input id="txtModulo" class="easyui-textbox" style="width:100%"></td>
                                </tr>
                            </table>
                        </div>

                        <div id="ftMOD" style="height:auto">
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Agregar</a>
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Borrar</a>
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptit()">Aceptar</a>
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Deshacer</a>
                        </div>
                    </div>

                    <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                        <div class="row">
                            <div class="col-auto col-xs-offset-none col-md-8 col-md-offset-2">
                                <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                                <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning"  onclick="location.href='gridCfgModulo.php';" />
                            </div>
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

<script>
function verificarFormulario() {
    if (document.getElementById('txtCurso').value == "") {
        $.messager.alert('INOFE', 'Falta el Nombre del Curso.', 'warning');
        return false;
    }

    if ($('#dgMOD').datagrid('getRows').length <= 0) {
        $.messager.alert('INOFE', 'Faltan los Módulos del Curso.', 'warning');
        return false;
    }

    return true;
}

var editIndex = undefined;
var lastIndex;

$('#dgMOD').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndex != rowIndex) {
            $(this).datagrid('endEdit', lastIndex);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndex = rowIndex;
    }
});

function endEditing() {
    if (editIndex == undefined) {
        return true
    }
    if ($('#dgMOD').datagrid('validateRow', editIndex)) {
        $('#dgMOD').datagrid('endEdit', editIndex);
        editIndex = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCell(index, field) {
    if (editIndex != index) {
        if (endEditing()) {
            $('#dgMOD').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndex = index;
        } else {
            setTimeout(function() {
                $('#dgMOD').datagrid('selectRow', editIndex);
            }, 0);
        }
    }
}

function append() {
    if (endEditing()) {
        $('#dgMOD').datagrid('appendRow', {
            modulo: $('#txtModulo').val()
        });
        editIndex = $('#dgMOD').datagrid('getRows').length;
        $('#dgMOD').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        /*Usa los ID de EasyUI para borrar los TextBox*/
        $('#_easyui_textbox_input1').val(''); //txtModulo
    }
}

function removeit() {
    if (editIndex == undefined) {
        return
    }
    $('#dgMOD').datagrid('cancelEdit', editIndex)
        .datagrid('deleteRow', editIndex);
    editIndex = undefined;
}

function acceptit() {
    if (endEditing()) {
        $('#dgMOD').datagrid('acceptChanges');
    }
}

function reject() {
    $('#dgMOD').datagrid('rejectChanges');
    editIndex = undefined;
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario() == true) {
        var texto;
        var datos;
        var registros;
        var i;
        var gridModulos = $('#dgMOD').datagrid('getData');

        texto = '{"txtCodConfiguracion":"' + document.getElementById("txtCodConfiguracion").value + '", ';
        texto += '"txtCurso":"' + document.getElementById("txtCurso").value + '", ';

        registros = $('#dgMOD').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridModulos": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"modulo":"' + gridModulos.rows[i].modulo;
                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        }

        datos = JSON.parse(texto);

        $.ajax({
                url: 'hrrCfgModulo.php',
                type: 'post',
                data: datos,
                beforeSend: function() {
                    console.log(datos)
                }
            })
            .done(function() {
                location.href = "gridCfgModulo.php";
            })
            .fail(function() {
                console.log('Error')
            });
    }
});
</script>

<?php
function fxEscribeJson($Configuracion)
{
	if ($Configuracion == "")
		$nombreArchivo = "CM00000A.json";
	else
		$nombreArchivo = $Configuracion . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetConfModulos($Configuracion);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"modulo":"' . rtrim($Fila['DESC_111']) . '"');

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