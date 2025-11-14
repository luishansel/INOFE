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
	require_once ("funciones/fxIncidencias.php");
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
		$PermisoUsuario = fxPermisoUsuario("procIncidencias");
		
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
			if (isset($_POST["optGuardar"]))
			{
                $codDocente = $_SESSION["gsDocente"];
                $codCurso = $_POST["codCurso"];
                $gridIncidencias = $_POST["gridIncidencias"];

                fxBorrarTodoIncidencia ($codCurso, $codDocente);
               
                $itemId = 1;
				foreach($gridIncidencias as $Registro)
				{
                    $Fecha = $Registro['fecha'];
                    $Incidencia = $Registro['incidencia'];
                    fxGuardarIncidencia ($codCurso, $codDocente, $itemId, $Fecha, $Incidencia);
                    $itemId++;
				}
			}
			else
			{
                if (isset($_POST["cboCurso"]))
                    $codCurso = $_POST["cboCurso"];
                else
                    $codCurso = "";
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="procIncidencias" name="procIncidencias">
                    <div class="row">
                        <div class="col-auto col-md-8">
                            <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridPlanClase.php';" />
                        </div>
                    </div>

                    <div id="tbINC" style="height:auto; padding-top:1%; padding-bottom:2%; padding-left: 2%">
                        <table width="100%">
                            <tr>
                                <td>Curso</td>
                                <td>
                                    <select class="easyui-combobox" data-options="panelHeight:'auto'" style="width: 60%" id="cboCurso" name="cboCurso">
                                    <?php
                                        $m_cnx_MySQL = fxAbrirConexion();
                                        if ($Administrador == 0)
                                        {
                                            $mDocente = $_SESSION["gsDocente"];
                                            $msConsulta = "select distinct KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA021A, KDSA020A where KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and ACTIVO_020 = 1";
                                            $msConsulta .= " and DOCENTE_REL = ? order by NOMBRE_020";
                                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                    $mDatos->execute([$mDocente]);
                                        }
                                        else
                                        {
                                            $msConsulta = "select distinct KDSA020A.CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA021A, KDSA020A where KDSA021A.CURSO_REL = KDSA020A.CURSO_REL and ACTIVO_020 = 1";
                                            $msConsulta .= " order by NOMBRE_020";
                                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                    $mDatos->execute();
                                        }
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Valor = trim($Fila["CURSO_REL"]);
                                            $Texto = trim($Fila["NOMBRE_020"]) . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")";

                                            if ($codCurso == "")
                                            {
                                                echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                                $codCurso = $Valor;
                                            }
                                            else
                                            {
                                                if ($codCurso == $Valor)
                                                    echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
                                                else
                                                    echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                            }
                                        }
                                    ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>Fecha</td>
                                <td>
                                    <input class="easyui-datebox" id="dtpFecha" data-options="formatter:function() {
                                            var fecha = new Date(); 
                                            var y = fecha.getFullYear();
                                            var m = fecha.getMonth() + 1;
                                            var d = fecha.getDate();
                                            return y + '/' + (m < 10 ? ('0' + m) : m) + '/' + (d < 10 ? ('0' + d) : d);
                                        },parser:function(s) {
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
                                        }" style="width:25%" readonly>
                                    </td>
                            </tr>

                            <tr>
                                <td style="valing:top">Incidencia</td>
                                <td><input id="txtIncidencia" class="easyui-textbox" multiline="true" style="width:95%; height:60px"></td>
                            </tr>
                        </table>
                    </div>

                    <div id="ftINC" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="append()">Agregar</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removeit()">Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="reject()">Deshacer</a>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-auto col-md-12">
                            <?php
								$nombreArchivo = fxEscribeJson($codCurso);
							?>
                            <div id="dvINC" style="margin-top:1%">
                                <table id="dgINC" class="easyui-datagrid table" style="margin-top:1%" data-options="iconCls:'icon-edit', toolbar:'#tbINC', footer:'#ftINC', singleSelect:true, url:'<?php echo(rtrim($nombreArchivo)); ?>', method:'get', onClickCell: onClickCell">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'detalle',width:'5%',align:'left'">#</th>
                                            <th data-options="field:'fecha',width:'15%',align:'left'">Fecha</th>
                                            <th data-options="field:'incidencia',width:'80%',align:'left'">Incidencia</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="col-auto">
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
window.onload = function() {
    escribeFecha();

    $('#_easyui_textbox_input1').keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });

    $('#cboCurso').combobox({
        onChange: function(){
            var codCurso = document.getElementById("cboCurso").value;
            $.redirect("gridIncidencias.php", {cboCurso: codCurso}, "POST");
        }
    });
}

function escribeFecha (){
    var fecha = new Date();
    var meses;
    var dias;
    var horas;
    var minutos;
    var segundos;
    var fechaCtrl;

    if ((fecha.getMonth() + 1) < 10)
        meses = '0' + (fecha.getMonth() + 1);
    else
        meses = (fecha.getMonth() + 1);

    if ((fecha.getDate()) < 10)
        dias = '0' + (fecha.getDate());
    else
        dias = (fecha.getDate());

    if ((fecha.getHours()) < 10)
        horas = '0' + (fecha.getHours());
    else
        horas = (fecha.getHours());

    if ((fecha.getMinutes()) < 10)
        minutos = '0' + (fecha.getMinutes());
    else
        minutos = (fecha.getMinutes());

    if ((fecha.getSeconds()) < 10)
        segundos = '0' + (fecha.getSeconds());
    else
        segundos = (fecha.getSeconds());
    
    fechaCtrl = fecha.getFullYear() + '/' + meses + '/'  + dias + ' ' + horas + ':' + minutos + ':' + segundos;
    $('#_easyui_textbox_input3').val(fechaCtrl);
}

/*Grid de Incidencias*/
var editIndex = undefined;
var lastIndex;

$('#dgINC').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndex != rowIndex) {
            $(this).datagrid('endEdit', lastIndex);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndex = rowIndex;
    }
});

function onClickCell(index, field) {
    if (editIndex != index) {
        $('#dgINC').datagrid('selectRow', index);
        editIndex = index;
    }
}

function append() {
    var datos = $('#dgINC').datagrid('getData');

    $('#dgINC').datagrid('appendRow', {
        detalle: '',
        fecha: $('#_easyui_textbox_input3').val(),
        incidencia: $('#_easyui_textbox_input1').val()
    });
    escribeFecha();
    editIndex = $('#dgINC').datagrid('getRows').length;
    $('#dgINC').datagrid('selectRow', editIndex);
}

function removeit() {
    if (editIndex == undefined) {
        return
    }
    $('#dgINC').datagrid('cancelEdit', editIndex)
        .datagrid('deleteRow', editIndex);
    editIndex = undefined;
}

function reject() {
    $('#dgINC').datagrid('rejectChanges');
    editIndex = undefined;
}

$('form').submit(function(e) {
    e.preventDefault();

    var texto;
    var datos;
    var registros;
    var i;
    var codCurso = document.getElementById("cboCurso").value;
    var gridIncidencias = $('#dgINC').datagrid('getData');

    registros = $('#dgINC').datagrid('getRows').length - 1;

    if (registros >= 0) {
        texto = '{"optGuardar":"1", "codCurso":"' + codCurso + '", "gridIncidencias": [';
        for (i = 0; i <= registros; i++) {
            texto += '{"detalle":"' + gridIncidencias.rows[i].detalle + '", "fecha":"' + gridIncidencias.rows[i].fecha + '", "incidencia":"' + gridIncidencias.rows[i].incidencia;
            if (i == registros)
                texto += '"}]}';
            else
                texto += '"},';
        }
    }

    datos = JSON.parse(texto);

    $.ajax({
            url: 'gridIncidencias.php',
            type: 'post',
            data: datos,
        })
        .done(function() {
            var codCurso = document.getElementById("cboCurso").value;
            $.redirect("gridIncidencias.php", {cboCurso: codCurso}, "POST");
        })
        .fail(function() {
            console.log('Error')
        });
});
</script>

<?php
function fxEscribeJson($codCurso)
{
	$nombreArchivo = "IN" . date('YmdHis') . ".json";
	
    //Escribe el Json
    $mDatos = fxDevuelveIncidencia ($codCurso);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"detalle":"' . rtrim($Fila['DETINCIDENCIA_REL']) . '", ');
		fwrite($archivo, '"fecha":"' . rtrim($Fila['FECHA_023']) . '", ');
		fwrite($archivo, '"incidencia":"' . rtrim($Fila['TEXTO_023']) . '"');
		
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