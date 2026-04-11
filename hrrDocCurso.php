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
	require_once ("funciones/fxDocCursos.php");
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
		$PermisoUsuario = fxPermisoUsuario("hrrDocCursos");
		
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
			if (isset($_POST["txtCodDocumentos"]))
			{
				$msCodigo = $_POST["txtCodDocumentos"];
				$msCurso = $_POST["txtCurso"];
                if (isset($_POST["gridDocumentos"]))
                    $gridDocumentos = $_POST["gridDocumentos"];
                
                if ($msCodigo == "")
                {
                    $msCodigo = fxGuardarDocCurso ($msCurso);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA200A", $msCodigo, "", "Agregar");
                }
                else
                {
                    fxModificarDocCurso ($msCodigo, $msCurso);
                    fxBorrarDetDocCurso ($msCodigo);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA200A", $msCodigo, "", "Modificar");
                }
				
				$itemId = 1;
				foreach($gridDocumentos as $Registro)
				{
					$archivo = $Registro['archivo'];
                    $ruta = $Registro['ruta'];
                    fxGuardarDetDocCurso ($msCodigo, $itemId, $archivo, $ruta);
                    $itemId++;
				}
											
				?>
<meta http-equiv="Refresh" content="0;url=gridDocCurso.php" /><?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $msCodigo = $_POST["KDSA"];
                else
                    $msCodigo = "";
                
                if ($msCodigo != "")
                {
                    $mDatos = fxDevuelveDocCurso (0, $msCodigo);
                    $Fila = $mDatos->fetch();
                    $msCurso = $Fila["CURSO_200"];
                }
                else
                    $msCurso = "";
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Documentos obligatorios de los cursos</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="hrrDocCurso" name="hrrDocCurso">
                    <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                        <div class="form-group row">
                            <label for="txtCodDocumentos" class="col-sm-auto col-md-2 col-form-label">Código</label>
                            <div class="col-sm-12 col-md-3">
                                <?php echo('<input type="text" class="form-control" id="txtCodDocumentos" name="txtCodDocumentos" value="' . $msCodigo . '" readonly />'); ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txtCurso" class="col-sm-auto col-md-2 col-form-label">Curso asociado</label>
                            <div class="col-sm-12 col-md-9">
                                <?php echo('<input type="text" class="form-control" id="txtCurso" name="txtCurso" value="' . $msCurso . '" />'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                        <div class="form-group row">
                            <label for="dgDOC" class="col-sm-12 col-md-2 form-label">Documentos del Curso</label>
                            <div class="col-sm-auto col-md-9">
                                <div id="dvDOC">
                                    <table id="dgDOC" class="easyui-datagrid table"
                                        data-options="iconCls:'icon-edit', toolbar:'#tbDOC', footer:'#ftDOC', singleSelect:true, method:'get', onClickCell: onClickCell">
                                        <thead>
                                            <tr>
                                                <th data-options="field:'consecutivo', hidden:'true'">Consecutivo</th>
                                                <th data-options="field:'archivo',width:'50%',align:'left'">Archivo</th>
                                                <th data-options="field:'ruta',width:'50%',align:'left'">Ruta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $mDatos = fxDevuelveDetDocCurso($msCodigo);

                                            while($mFila = $mDatos->fetch())
                                            {
                                                echo('<tr>');
                                                echo('<td>' . $mFila["DOCCURSOCONS_REL"] . '</td>');
                                                echo('<td>' . $mFila["ARCHIVO_201"] . '</td>');
                                                echo('<td>' . $mFila["RUTA_201"] . '</td>');
                                                echo('</tr>');
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div id="tbDOC" style="height:auto; padding-top:1%; padding-bottom:2%">
                            <table width="100%">
                                <tr>
                                    <td>Archivo</td>
                                    <td width="90%"><input type="file" id="fbArchivo" name="fbArchivo"></td>
                                </tr>
                            </table>
                        </div>

                        <div id="ftDOC" style="height:auto">
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Agregar</a>
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Borrar</a>
                        </div>
                    </div>

                    <div class="col-xs-auto col-xs-offset-none col-md-11 col-md-offset-1">
                        <div class="row">
                            <div class="col-auto col-xs-offset-none col-md-8 col-md-offset-2">
                                <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                                <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning"  onclick="location.href='gridDocCurso.php';" />
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
        $.messager.alert('KDSA', 'Falta el Nombre del Curso.', 'warning');
        return false;
    }

    if (document.getElementById('txtCodDocumentos').value != "")
    {
        if ($('#dgDOC').datagrid('getRows').length <= 0) {
            $.messager.alert('KDSA', 'Faltan los archivos del Curso.', 'warning');
            return false;
        }
    }

    return true;
}

var editIndex = undefined;
var lastIndex;

$('#dgDOC').datagrid({
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
    if ($('#dgDOC').datagrid('validateRow', editIndex)) {
        $('#dgDOC').datagrid('endEdit', editIndex);
        editIndex = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCell(index, field) {
    if (editIndex != index) {
        if (endEditing()) {
            $('#dgDOC').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndex = index;
        } else {
            setTimeout(function() {
                $('#dgDOC').datagrid('selectRow', editIndex);
            }, 0);
        }
    }
}

function append() {
    var msArchivo = $('#fbArchivo').val();
    var msDocumentos = $('#txtCodDocumentos').val();
    var regDoc = $('#dgDOC').datagrid('getRows').length;
    var gridDoc = $('#dgDOC').datagrid('getData');
    var mbExisteArchivo = false;

    if (msDocumentos == ""){
        $.messager.alert('KDSA', 'Guarde primero el curso asociado y luego suba los archivos.', 'warning');
        return false;
    }

    if (msArchivo == ""){
        $.messager.alert('KDSA', 'No ha seleccionado el archivo.', 'warning');
        return false;
    }

    for (i=0; i<regDoc; i++)
    {
        if (msArchivo == gridDoc.rows[i].archivo)
            mbExisteArchivo = true;
    }

    if (mbExisteArchivo == false)
    {
        var datos = new FormData();
        var files = $('#fbArchivo')[0].files[0];
        datos.append('archivo', files);
        datos.append('txtCodDocumentos', msDocumentos);

        $.ajax({
            url: 'funciones/fxDatosDocCurso.php',
            type: 'post',
            data: datos,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response != "") {
                    datos = JSON.parse(response);
                    $('#dgDOC').datagrid({data: datos});
                    $('#dgDOC').datagrid('reload');
                    $('#fbArchivo').val("");
                } else {
                    $.messager.alert('KDSA', 'Error en la subida del archivo.', 'warning');
                }
            }
        });
        return false;   
    }
    else
        $.messager.alert('KDSA', 'El archivo ya ha sido ingresado.', 'warning');
}

function removeit() {
    if (editIndex == undefined) {
        return
    }
    var datos = new FormData();
    var msDocumentos = $('#txtCodDocumentos').val();
    var filas = $('#dgDOC').datagrid('getRows');
    var mnConsecutivo = filas[editIndex].consecutivo;
    var msRuta = filas[editIndex].ruta;

    datos.append('codDocumento', msDocumentos);
    datos.append('codConsecutivo', mnConsecutivo);
    datos.append('msRuta', msRuta);

    $.ajax({
        url: 'funciones/fxDatosDocCurso.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response != "") {
                datos = JSON.parse(response);
                $('#dgDOC').datagrid({data: datos});
                $('#dgDOC').datagrid('reload');
            } else {
                $.messager.alert('KDSA', 'Error en la eliminación del archivo.', 'warning');
            }
        }
    });
    return false;
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario() == true) {
        var texto;
        var datos;
        var registros;
        var i;
        var gridDocumentos = $('#dgDOC').datagrid('getData');

        texto = '{"txtCodDocumentos":"' + document.getElementById("txtCodDocumentos").value + '", ';
        texto += '"txtCurso":"' + document.getElementById("txtCurso").value + '"';

        registros = $('#dgDOC').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '", gridDocumentos": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"archivo":"' + gridDocumentos.rows[i].archivo;
                texto += '{"ruta":"' + gridDocumentos.rows[i].ruta;
                if (i == registros)
                    texto += '"}]';
                else
                    texto += '"},';
            }
        }

        texto += "}";

        datos = JSON.parse(texto);

        $.ajax({
                url: 'hrrDocCurso.php',
                type: 'post',
                data: datos,
                beforeSend: function() {
                    console.log(datos)
                }
            })
            .done(function() {
                location.href = "gridDocCurso.php";
            })
            .fail(function() {
                console.log('Error')
            });
    }
});
</script>