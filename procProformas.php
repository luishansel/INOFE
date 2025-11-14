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
	require_once ("funciones/fxProformas.php");
    $m_cnx_MySQL = fxAbrirConexion();
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
		$PermisoUsuario = fxPermisoUsuario("procProformas");
		
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
			if (isset($_POST["txtCodProforma"]))
			{
				$mnOperacion = $_POST["Operacion"];
				$Codigo = $_POST["txtCodProforma"];
				$Prospecto = $_POST["txtProspecto"];
				$Fecha = $_POST["dtpFecha"];
				//$Inatec = $_POST["optInatec"];
                $Inatec = 0;
				$TipoCambio = $_POST["txnTipoCambio"];
                $Observaciones = $_POST["txtObservaciones"];
                if (isset($_POST["gridDetalle"]))
                    $gridDetalle = $_POST["gridDetalle"];
                if (isset($_POST["gridOtros"]))
				    $gridOtros = $_POST["gridOtros"];

				{
					if ($mnOperacion == 0)
					{
						$Codigo = fxGuardarProformas ($Prospecto, $Fecha, $Inatec, $TipoCambio, $Observaciones);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA090A", $Codigo, "", "Agregar");
					}
					else
					{
						fxModificarProformas ($Codigo, $Prospecto, $Fecha, $Inatec, $TipoCambio, $Observaciones);
						fxBorrarDetProformas ($Codigo);
						fxBorrarOtroDetProformas ($Codigo);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA090A", $Codigo, "", "Modificar");
					}
				}
                
                if (isset($gridDetalle))
                {
                    foreach($gridDetalle as $Registro)
                    {
                        $Curso = $Registro['curso'];
                        $Cantidad = $Registro['cantidad'];
                        fxGuardarDetProformas ($Codigo, $Curso, $Cantidad);
                    }
                }

                if (isset($gridOtros))
                {
                    $itemId = 1;
                    foreach($gridOtros as $Registro)
                    {
                        $CursoKdsa = $Registro['cursoKdsa'];
                        $CursoInatec = $Registro['cursoInatec'];
                        $DiasClase = $Registro['diasClase'];
                        $Horario = $Registro['horario'];
                        $FechaIni = $Registro['fechaIni'];
                        $FechaFin = $Registro['fechaFin'];
                        $HorasClase = $Registro['horasclase'];
                        $CodInatec = $Registro['codInatec'];
                        $Acuerdo = $Registro['acuerdo'];
                        $Precio = $Registro['precio'];
                        $Cupos = $Registro['cupos'];
                        fxGuardarOtroDetProformas ($Codigo, $itemId, $CursoKdsa, $CursoInatec, $DiasClase, $Horario, $FechaIni, $FechaFin, $HorasClase, $CodInatec, $Acuerdo, $Precio, $Cupos);
                        $itemId++;
                    }
                }
								
				?>
                <meta http-equiv="Refresh" content="0;url=gridProformas.php" /><?php
			}
			else
			{
				$mnOperacion = $_POST["mOperacion"];
				
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

				$RecordSet = fxDevuelveProformas (0, $Codigo);
				$Fila = $RecordSet->fetch();
				
				if ($mnOperacion != 2)
                {
                    if ($Codigo == "")
                        $Prospecto = "";
                    else
					    $Prospecto = $Fila["PROSPECTO_REL"];
                }

                if ($Prospecto == "")
                {
                    $NomProspecto = "";
                    $Fecha = "";
                    $Inatec = "";
                    $TipoCambio = 0;
                    $Observaciones = "";
                }
                else
                {
                    $msConsulta = "select NOMBRE_060 from KDSA060A where PROSPECTO_REL = ?";
                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
				    $mDatos->execute([$Prospecto]);
                    $fAux = $mDatos->fetch();
				    $NomProspecto = $fAux["NOMBRE_060"];
                    if ($Codigo != "")
                    {
                        $Fecha = $Fila["FECHA_090"];
                        $Inatec = $Fila["INATEC_090"];
                        $TipoCambio = $Fila["TIPOCAMBIO_090"];
                        $Observaciones = $Fila["OBSERVACIONES_090"];
                    }
                    else
                    {
                        $Fecha = "1900-01-01";
						$Proximo = "1900-01-01";
						$Observaciones = "";
						$Usuario = "";
                    }
                }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Proformas</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-xs-offset-none col-md-12">
                <form id="procProformas" name="procProformas">
                    <div class="form-group row">
                        <label for="txtCodProforma" class="col-sm-12 col-md-2 col-form-label">Código de la Proforma</label>
                        <div class="col-sm-12 col-md-3">
                            <?php echo('<input type="text" class="form-control" id="txtCodProforma" name="txtCodProforma" value="' . $Codigo . '" readonly />'); ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="txtProspecto" class="col-sm-12 col-md-2 col-form-label">Prospecto</label>
                        <div class="col-sm-12 col-md-3">
                            <?php echo('<input type="text" class="form-control" id="txtProspecto" name="txtProspecto" value="' . $Prospecto . '" onblur="escribeProspecto()" />');?>
                        </div>
                        <br />
                        <div class="col-sm-auto col-md-7 col-sm-offset-none col-md-offset-2">
                            <?php echo('<input type="text" class="form-control" id="txtNomProspecto" name="txtNomProspecto" value="' . $NomProspecto . '" readonly />');?>
                        </div>
                    </div>

                    <div class="form-group row">
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

                    <div class="form-group row">
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
<!--
                    <div class="form-group row">
                        <label for="optInatec" class="col-sm-12 col-md-2 form-label">Para INATEC</label>
                        <div class="col-sm-12 col-md-4">
                            <div class="radio">
                                <?php
                                /*
                                if ($Inatec == 1)
                                {
                                    echo('<input type="radio" id="optInatec1" name="optInatec" value="0" /> No <input type="radio" id="optInatec2" name="optInatec" value="1" checked /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="optInatec1" name="optInatec" value="0" checked /> No <input type="radio" id="optInatec2" name="optInatec" value="1" /> Si');
                                }
                                */
                            ?>

                            </div>
                        </div>
                    </div>
-->
                    <div class="form-group row">
                        <label for="txtObservaciones" class="col-sm-12 col-md-2 form-label">Observaciones</label>
                        <div class="col-sm-12 col-md-7">
                            <?php echo('<textarea class="form-control" id="txtObservaciones" name="txtObservaciones" rows="3">' . $Observaciones . '</textarea>'); ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="dgDET" class="col-sm-12 col-md-2 form-label">Cursos de interés</label>
                        <div class="col-sm-auto col-md-10">
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
                                            <th data-options="field:'cantidad',width:'10%',align:'right',editor:{type:'numberbox'}">Cupos</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="tbDET" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Agregar</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptit()">Aceptar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Deshacer</a>
                    </div>

                    <div class="form-group row">
                        <label for="dgPF" class="col-sm-12 col-md-2 form-label">Otros cursos de interés
                            <p style="color:rgb(150,150,150)"><i><small>Cursos que no están en el Catálogo</small></i></p></label>
                        <div class="col-sm-auto col-md-10">
                            <?php
								$nombreArchivoPF = fxEscribeJsonPF($Codigo);
							?>
                            <div id="dvPF">
                                <table id="dgPF" class="easyui-datagrid table"
                                    data-options="iconCls:'icon-edit', toolbar:'#tbPF', footer:'#ftPF', singleSelect:true, url:'<?php echo(rtrim($nombreArchivoPF)); ?>', method:'get', onClickCell: onClickCellPF">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'cursoKdsa',align:'left',editor:'text'">Curso KDSA</th>
                                            <th data-options="field:'cursoInatec',align:'left',editor:'text'">Curso INATEC</th>
                                            <th data-options="field:'diasClase',align:'left',editor:'text'">Días de clase</th>
                                            <th data-options="field:'horario',align:'left',editor:'text'">Horario</th>
                                            <th data-options="field:'fechaIni',align:'left',
                                            editor:
                                                {type:'datebox', 
                                                options:
                                                {
                                                    formatter:function(date) {
                                                        var y = date.getFullYear();
                                                        var m = date.getMonth() + 1;
                                                        var d = date.getDate();
                                                        return y + '/' + (m < 10 ? ('0' + m) : m) + '/' + (d < 10 ? ('0' + d) : d);
                                                    },
                                                    parser:function(s) {
                                                        if (!s) return new Date();
                                                        var ss = (s.split('-'));
                                                        var y = parseInt(ss[0], 10);
                                                        var m = parseInt(ss[1], 10);
                                                        var d = parseInt(ss[2], 10);
                                                        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                                                            return new Date(y, m - 1, d);
                                                        } else {
                                                            return new Date();
                                                        }
                                                    }
                                                }
                                            }">Fecha Inicial</th>
                                            <th data-options="field:'fechaFin',align:'left',
                                            editor:
                                                {type:'datebox', 
                                                options:
                                                {
                                                    formatter:function(date) {
                                                        var y = date.getFullYear();
                                                        var m = date.getMonth() + 1;
                                                        var d = date.getDate();
                                                        return y + '/' + (m < 10 ? ('0' + m) : m) + '/' + (d < 10 ? ('0' + d) : d);
                                                    },
                                                    parser:function(s) {
                                                        if (!s) return new Date();
                                                        var ss = (s.split('-'));
                                                        var y = parseInt(ss[0], 10);
                                                        var m = parseInt(ss[1], 10);
                                                        var d = parseInt(ss[2], 10);
                                                        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                                                            return new Date(y, m - 1, d);
                                                        } else {
                                                            return new Date();
                                                        }
                                                    }
                                                }
                                            }">Fecha Final</th>
											<th data-options="field:'horasclase',align:'right',editor:{type:'numberbox'}">Horas-clase</th>
                                            <th data-options="field:'codInatec',align:'left',editor:'text'">Código INATEC</th>
                                            <th data-options="field:'acuerdo',align:'left',editor:'text'">Acuerdo INATEC</th>
                                            <th data-options="field:'precio',align:'right',editor:{type:'numberbox',options:{precision:2}}">Precio</th>
                                            <th data-options="field:'cupos',align:'right',editor:{type:'numberbox'}">Cupos</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="tbPF" style="height:auto; padding-top:1%; padding-bottom:2%">
                        <table width="100%">
                            <tr>
                                <td>Nombre de curso (KDSA)</td><td><input id="txtCursoKdsa" class="easyui-textbox" style="width:100%"></td>
                            </tr>
                            <tr>
                                <td>Nombre de curso (INATEC)</td>
                                <td><input id="txtCursoInatec" class="easyui-textbox" style="width:100%"></td>
                            </tr>
                            <tr>
                                <td>Días de clase</td>
                                <td><input id="txtDias" class="easyui-textbox" style="width:100%"></td>
                            </tr>
                            <tr>
                                <td>Horario</td>
                                <td><input id="txtHorario" class="easyui-textbox" style="width:100%"></td>
                            </tr>
                            <tr>
                                <td>Fecha Inicial</td>
                                <td><input type="date" id="dtpFechaIni" style="width:50%"></td>
                            </tr>
                            <tr>
                                <td>Fecha final</td>
                                <td><input type="date" id="dtpFechaFin" style="width:50%"></td>
                            </tr>
							<tr>
                                <td>Horas-clase</td>
                                <td><input id="txnHorasClase" class="easyui-numberbox" style="width:50%; text-align:right"></td>
                            </tr>
                            <tr>
                                <td>Código INATEC</td>
                                <td><input id="txtCodInatec" class="easyui-textbox" style="width:50%"></td>
                            </tr>
                            <tr>
                                <td>Acuerdo INATEC</td>
                                <td><input id="txtAcuerdo" class="easyui-textbox" style="width:50%"></td>
                            </tr>
                            <tr>
                                <td>Precio</td>
                                <td><input id="txnPrecio" class="easyui-numberbox" data-options="precision:2" style="width:50%; text-align:right"></td>
                            </tr>
                            <tr>
                                <td>Cupos</td>
                                <td><input id="txnCupos" class="easyui-numberbox" style="width:50%; text-align:right"></td>
                            </tr>
                        </table>
                    </div>

                    <div id="ftPF" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="appendPF()">Agregar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeitPF()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick="acceptitPF()">Aceptar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="rejectPF()">Deshacer</a>
                    </div>

                    <div class="row">
                        <div class="col-auto col-xs-offset-none col-md-8 col-md-offset-2">
                            <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridProformas.php';" />
                            <?php
							if ($Codigo == "")
                            	echo('<input type="button" id="Imprimir" name="Imprimir" value="Imprimir" class="btn btn-warning" disabled />');
							else
								echo('<input type="button" id="Imprimir" name="Imprimir" value="Imprimir" class="btn btn-warning" onclick="generaReporte()" />');
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
$.extend($.fn.datagrid.defaults.editors, {
    datebox: {
        init: function(container, options){
            var input = $('<input type="date">').appendTo(container);
            return input;
        },
        destroy: function(target){
            $(target).remove();
        },
        getValue: function(target){
            return $(target).val();
        },
        setValue: function(target, value){
            $(target).val(value);
        },
        resize: function(target, width){
            $(target)._outerWidth(width);
        }
    }
});

function generaReporte() {
    var msProforma = document.getElementById('txtCodProforma').value;
    $.redirect("repProformas.php", {KDSA: msProforma}, "POST", "_blank");
}

function escribeProspecto() {
    var mnCero = "0";
    var mnNumero = document.getElementById('txtProspecto').value;
    var mnLongitud;
    var mnIndice;

    if (mnNumero.length < 10) {
        mnIndice = mnNumero.indexOf("PT");
        if (mnIndice > -1)
            mnNumero = mnNumero.substring(2);

        mnLongitud = 8 - mnNumero.length;
        document.getElementById('txtProspecto').value = 'PT' + mnCero.repeat(mnLongitud) + mnNumero;
    }
    obtieneProspecto();
}

function obtieneProspecto() {
    parametros = '{"txtProspecto":"' + document.getElementById("txtProspecto").value + '"}';
    datosJson = JSON.parse(parametros);

    return $.ajax({
        url: 'funciones/fxDatosExternos.php',
        type: 'post',
        async: false,
        data: datosJson,
        success: function(respuesta) {
            document.getElementById("txtNomProspecto").value = respuesta
        }
    })
}

function verificarFormulario() {
    var datos = $('#dgDET').datagrid('getData');
    var regDET = $('#dgDET').datagrid('getRows').length;
    var regPF = $('#dgPF').datagrid('getRows').length;

    if (document.getElementById('txtProspecto').value == "") {
        $.messager.alert('INOFE', 'Falta el Prospecto.', 'warning');
        return false;
    }

    if (document.getElementById('txnTipoCambio').value == "") {
        $.messager.alert('INOFE', 'Falta el Tipo de Cambio.', 'warning');
        return false;
    }

    if (regDET == 0 && regPF == 0) {
        $.messager.alert('INOFE', 'Faltan los Cursos de interés.', 'warning');
        return false;
    }

    return true;
}

/*Grid de Cursos*/
var editIndex = undefined;
var lastIndex;

$('#dgDET').datagrid({
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
    if ($('#dgDET').datagrid('validateRow', editIndex)) {
        $('#dgDET').datagrid('endEdit', editIndex);
        editIndex = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCell(index, field) {
    if (editIndex != index) {
        if (endEditing()) {
            $('#dgDET').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndex = index;
        } else {
            setTimeout(function() {
                $('#dgDET').datagrid('selectRow', editIndex);
            }, 0);
        }
    }
}

function append() {
    if (endEditing()) {
        var i;
        var codigo;
        var existeCurso = false;
        var datos = $('#dgDET').datagrid('getData');
        var registros = $('#dgDET').datagrid('getRows').length;

        if (registros > 0) {
            for (i = 0; i < registros; i++) {
                if (datos.rows[i].curso == $('#cboCurso option:selected').val())
                    existeCurso = true;
            }
        }

        if (existeCurso == true) {
            $.messager.alert('INOFE', $('#cboCurso option:selected').text() + ' ya fue incluido.', 'warning');
            $('#cboCurso').focus()
        } else {
            $('#dgDET').datagrid('appendRow', {
                curso: $('#cboCurso option:selected').val(),
                nombre: $('#cboCurso option:selected').text(),
                cantidad: 1
            });
            editIndex = $('#dgDET').datagrid('getRows').length;
            $('#dgDET').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }
}

function removeit() {
    if (editIndex == undefined) {
        return
    }
    $('#dgDET').datagrid('cancelEdit', editIndex)
        .datagrid('deleteRow', editIndex);
    editIndex = undefined;
}

function acceptit() {
    if (endEditing()) {
        $('#dgDET').datagrid('acceptChanges');
    }
}

function reject() {
    $('#dgDET').datagrid('rejectChanges');
    editIndex = undefined;
}

/*Grid de Cursos que no están en el catálogo*/
var editIndexPF = undefined;
var lastIndexPF;

$('#dgPF').datagrid({
    onClickRow: function(rowIndexPF) {
        if (lastIndexPF != rowIndexPF) {
            $(this).datagrid('endEdit', lastIndexPF);
            $(this).datagrid('beginEdit', rowIndexPF);
        }
        lastIndexPF = rowIndexPF;
    }
});

function endEditingPF() {
    if (editIndexPF == undefined) {
        return true
    }
    if ($('#dgPF').datagrid('validateRow', editIndexPF)) {
        $('#dgPF').datagrid('endEdit', editIndexPF);
        editIndexPF = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellPF(index, field) {
    if (editIndexPF != index) {
        if (endEditingPF()) {
            $('#dgPF').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexPF = index;
        } else {
            setTimeout(function() {
                $('#dgPF').datagrid('selectRow', editIndexPF);
            }, 0);
        }
    }
}

function appendPF() {
    if (endEditingPF()) {
        $('#dgPF').datagrid('appendRow', {
            cursoKdsa: $('#txtCursoKdsa').val(),
            cursoInatec: $('#txtCursoInatec').val(),
            diasClase: $('#txtDias').val(),
            horario: $('#txtHorario').val(),
            horario: $('#txtHorario').val(),
            fechaIni: $('#dtpFechaIni').val(),
            fechaFin: $('#dtpFechaFin').val(),
			horasclase: $('#txnHorasClase').val(),
            codInatec: $('#txtCodInatec').val(),
            acuerdo: $('#txtAcuerdo').val(),
            precio: $('#txnPrecio').val(),
            cupos: $('#txnCupos').val()
        });
        editIndexPF = $('#dgPF').datagrid('getRows').length;
        $('#dgPF').datagrid('selectRow', editIndexPF).datagrid('beginEdit', editIndexPF);
    }
}

function removeitPF() {
    if (editIndexPF == undefined) {
        return
    }
    $('#dgPF').datagrid('cancelEdit', editIndexPF)
        .datagrid('deleteRow', editIndexPF);
    editIndexPF = undefined;
}

function acceptitPF() {
    if (endEditingPF()) {
        $('#dgPF').datagrid('acceptChanges');
    }
}

function rejectPF() {
    $('#dgPF').datagrid('rejectChanges');
    editIndexPF = undefined;
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario() == true) {
        var texto;
        var datos;
        var registros;
        var i;
        var fechaIni;
        var fechaFin;
        var sinCursos = true;
        var gridDetalle = $('#dgDET').datagrid('getData');
        var gridOtros = $('#dgPF').datagrid('getData');

        texto = '{"txtCodProforma":"' + document.getElementById("txtCodProforma").value + '", ';
        if (document.getElementById("txtCodProforma").value == "")
            texto += '"Operacion":"0", ';
        else
            texto += '"Operacion":"1", ';
        texto += '"txtProspecto":"' + document.getElementById("txtProspecto").value + '", ';
        texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';
        texto += '"txnTipoCambio":"' + document.getElementById("txnTipoCambio").value + '", ';
        texto += '"txtObservaciones":"' + document.getElementById("txtObservaciones").value + '", ';
/*
        if (document.getElementById("optInatec1").checked)
            texto += '"optInatec":"0", ';
        else
            texto += '"optInatec":"1", ';
*/
        registros = $('#dgDET').datagrid('getRows').length - 1;

        if (registros >= 0) {
            sinCursos = false;
            texto += '"gridDetalle": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"curso":"' + gridDetalle.rows[i].curso + '", "nombre":"' + gridDetalle.rows[i]
                    .nombre + '", "cantidad":"' + gridDetalle.rows[i].cantidad;
                if (i == registros)
                    texto += '"}],';
                else
                    texto += '"},';
            }
        }

        registros = $('#dgPF').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridOtros": [';
            for (i = 0; i <= registros; i++) {
                fechaIni = gridOtros.rows[i].fechaIni;
                fechaFin = gridOtros.rows[i].fechaFin;

                texto += '{"cursoKdsa":"' + gridOtros.rows[i].cursoKdsa + '", "cursoInatec":"' + gridOtros.rows[i].cursoInatec;
                texto += '","diasClase":"' + gridOtros.rows[i].diasClase + '","horario":"' + gridOtros.rows[i].horario;
                texto += '","fechaIni":"' + fechaIni + '","fechaFin":"' + fechaFin + '", "horasclase":"' + gridOtros.rows[i].horasclase;
                texto += '","codInatec":"' + gridOtros.rows[i].codInatec + '","acuerdo":"' + gridOtros.rows[i].acuerdo;
                texto += '","precio":"' + gridOtros.rows[i].precio + '","cupos":"' + gridOtros.rows[i].cupos;

                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        } else {
            if (sinCursos == true)
                texto = texto.substr(0, texto.length - 2) + '}'
            else
                texto = texto.substr(0, texto.length - 1) + '}'
        }

        datos = JSON.parse(texto);

        $.ajax({
                url: 'procProformas.php',
                type: 'post',
                data: datos,
                beforeSend: function() {
                    console.log(datos)
                }
            })
            .done(function() {
                location.href = "gridProformas.php";
            })
            .fail(function() {
                console.log('Error')
            });
    }
});
</script>
<?php
function fxEscribeJson($Proforma)
{
	if ($Proforma == "")
		$nombreArchivo = "PF00000000.json";
	else
		$nombreArchivo = $Proforma . ".json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetProformas($Proforma);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_020']) . '", ');
		fwrite($archivo, '"cantidad":"' . rtrim($Fila['CANTIDAD_091']) . '"');
		
		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	return($nombreArchivo);
}

function fxEscribeJsonPF($Proforma)
{
	if ($Proforma == "")
		$nombreArchivo = "PF00000000A.json";
	else
		$nombreArchivo = $Proforma . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveOtroDetProformas($Proforma);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"cursoKdsa":"' . rtrim($Fila['CURSOKDSA_092']) . '", ');
		fwrite($archivo, '"cursoInatec":"' . rtrim($Fila['CURSOINATEC_092']) . '", ');
		fwrite($archivo, '"diasClase":"' . rtrim($Fila['DIASCLASE_092']) . '", ');
		fwrite($archivo, '"horario":"' . rtrim($Fila['HORARIO_092']) . '", ');
		fwrite($archivo, '"fechaIni":"' . rtrim($Fila['FECHAINI_092']) . '", ');
		fwrite($archivo, '"fechaFin":"' . rtrim($Fila['FECHAFIN_092']) . '", ');
		fwrite($archivo, '"horasclase":"' . rtrim($Fila['HORASCLASE_092']) . '", ');
		fwrite($archivo, '"codInatec":"' . rtrim($Fila['CODIGOINATEC_092']) . '", ');
		fwrite($archivo, '"acuerdo":"' . rtrim($Fila['ACUERDO_092']) . '", ');
		fwrite($archivo, '"precio":"' . rtrim($Fila['PRECIO_092']) . '", ');
		fwrite($archivo, '"cupos":"' . rtrim($Fila['CUPOS_092']) . '"');
		
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