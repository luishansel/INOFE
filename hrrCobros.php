<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
    include ("MasterWeb.php");
    require_once ("crearImagenCobro.php");
    require_once ("crearContactosCobro.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
    $Registro = fxVerificaUsuario();
    $m_cnx_MySQL = fxAbrirConexion();
	
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
		$PermisoUsuario = fxPermisoUsuario("hrrCobros", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{ ?>
            <div class="container text-center">
                <div id="DivContenido">
                    <img src="imagenes/errordeacceso.png"/>
                </div>
            </div>
		<?php }
		else
		{
            if (isset($_POST["msCodCobro"]) and isset($_POST["msConcepto"]) and isset($_POST["msFecha"]) and isset($_POST["cboCurso"]))
            {
                $msCodCurso = trim($_POST["cboCurso"]);
                $msArchivoPNG = trim($_POST["msCodCobro"]) . ".png";
                $msArchivoCSV = trim($_POST["msCodCobro"]) . ".csv";
                crearImagen($_POST["msCodCobro"], $_POST["msConcepto"], $_POST["msFecha"]);
                crearContactos($_POST["msCodCobro"]);

                echo('<script>$.messager.alert("KDSA","Los archivos se generaron correctamente.","info");</script>');
            }
            else
            {
                $msCodCurso = "";
                $msArchivoPNG = "";
                $msArchivoCSV = "";
            }
        ?>
    	<div class="container">
        	<div id="DivContenido">
                <div class = "row">
                    <div class="col-xs-12 col-md-11">
                        <div class="degradado"><strong>Envío masivo de Cobros a los estudiantes</strong></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 col-xs-offset-none col-md-offset-1">
                        <div class = "form-group row">
                            <button id="files" type="button" class="btn btn-warning" onclick="fxGenerarArchivos()">Generar archivos</button>
                            <div class="col-xs-2 col-md-1">
                                <?php
                                    echo('<a href="' . $msArchivoCSV . '" id="objCSV" target="_blank"><img src="imagenes/archivo-csv.png" class="img-fluid" title="Descargar contactos WhatsApp" alt=""></a>');
                                ?>
                            </div>
                            <div class="col-xs-2 col-md-1">
                                <?php
                                    echo('<a href="' . $msArchivoPNG . '" id="objPNG" target="_blank"><img src="imagenes/archivo-png.png" class="img-fluid" title="Descargar imagen del mensaje" alt=""></a>');
                                ?>
                            </div>
                        </div>
                        
                        <div class = "form-group row">
                            <label for="cboCurso" class="col-sm-12 col-md-1 col-form-label">Curso</label>
                            <div class="col-sm-12 col-md-9">
                                <select class="col-sm-12 col-md-12 form-control" id="cboCurso" name="cboCurso" onchange="fxLlenaCobros()">
                                    <?php
                                        $msConsulta = "select CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                        $msConsulta .= "from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 <= CURDATE() order by CURSO_REL desc";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
			                            $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Curso = rtrim($Fila["CURSO_REL"]);
                                            $Texto = rtrim($Fila["NOMBRE"]);

                                            if ($msCodCurso == "")
                                                $msCodCurso = $Curso;
                                            
                                            if ($msCodCurso == $Curso)
                                                echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
                                            else
                                                echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="dgCOB" class="col-sm-12 col-md-1 form-label">Cobros</label>
                            <div class="col-sm-12 col-md-9">
                                <div id="dvCOB">
                                    <table id="dgCOB" class="easyui-datagrid table", data-options="iconCls:'icon-edit', toolbar:'#tbCOB', singleSelect:true, method:'get', onClickCell: onClickCellCOB">
                                        <thead>
                                            <tr>
                                                <th data-options="field:'ck', checkbox:true"></th>
                                                <th data-options="field:'COBRO_REL', width:'15%', align:'left'">Cobro</th>
                                                <th data-options="field:'CONCEPTO_050', width:'70%', align:'left'">Concepto</th>
                                                <th data-options="field:'FECHAPREVISTA_050', width:'15%', align:'left'">Vencimiento</th>
                                                <th data-options="field:'FECHA', hidden:'true'">Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $msConsulta = "select COBRO_REL, CONCEPTO_050, FECHAPREVISTA_050 FROM KDSA050A where CURSO_REL = ? and TIPO_050 <> 1";
                                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                            $mDatos->execute([$msCodCurso]);

                                            while ($mFila = $mDatos->fetch())
                                            {
                                                $msCobro = trim($mFila["COBRO_REL"]);
                                                $msConcepto = $mFila["CONCEPTO_050"];
                                                $msFechaPrevista = fxDevuelveFecha($mFila["FECHAPREVISTA_050"]);
                                                $FechaDividida = explode("-", $mFila["FECHAPREVISTA_050"]);
                                                $Anno = $FechaDividida[0];
                                                $Mes = $FechaDividida[1];
                                                $Dia = $FechaDividida[2];
                                                $msFecha = $Anno . "-" . $Mes . "-" . $Dia;
                                                    
                                                echo('<tr>');
                                                echo('<td></td>');
                                                echo('<td>' . $msCobro . '</td>');
                                                echo('<td>' . $msConcepto . '</td>');
                                                echo('<td>' . $msFechaPrevista . '</td>');
                                                echo('<td>' . $msFecha . '</td>');
                                                echo('</tr>');
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div id="tbCOB" style="height:auto">
                            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="acceptitCOB()">Salir del Modo de Edición</a>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
<?php 
    }
}

function fxDevuelveFecha($Fecha)
{
    $FechaDividida = explode("-", $Fecha);
    
    $Anno = $FechaDividida[0];
    $Mes = $FechaDividida[1];
    $Dia = $FechaDividida[2];
    
    switch ($Mes)
        {
            case "01":
                $NombreMes = "Ene";
                break;
            case "02":
                $NombreMes = "Feb";
                break;
            case "03":
                $NombreMes = "Mar";
                break;
            case "04":
                $NombreMes = "Abr";
                break;
            case "05":
                $NombreMes = "May";
                break;
            case "06":
                $NombreMes = "Jun";
                break;
            case "07":
                $NombreMes = "Jul";
                break;
            case "08":
                $NombreMes = "Ago";
                break;
            case "09":
                $NombreMes = "Sep";
                break;
            case "10":
                $NombreMes = "Oct";
                break;
            case "11":
                $NombreMes = "Nov";
                break;
            case "12":
                $NombreMes = "Dic";
                break;
        }
    return ($Dia . "-" . $NombreMes . "-" . $Anno);
}
?>
<script>
function fxGenerarArchivos()
{
    var curso = $('#cboCurso').val(); 
    var rows = $('#dgCOB').datagrid('getSelections');
    var dbCobro = rows[0].COBRO_REL;
    var dbConcepto = rows[0].CONCEPTO_050;
    var dbFecha = rows[0].FECHA;
    var mFecha = dbFecha.split("-");
    var dia = mFecha[2];
    var mes = parseInt(mFecha[1]);
    var anno = mFecha[0];
    var fecha = "";

    switch(mes)
    {
        case 1:
            sMes = "enero";
            break;
        case 2:
            sMes = "febrero";
            break;
        case 3:
            sMes = "marzo";
            break;
        case 4:
            sMes = "abril";
            break;
        case 5:
            sMes = "mayo";
            break;
        case 6:
            sMes = "junio";
            break;
        case 7:
            sMes = "julio";
            break;
        case 8:
            sMes = "agosto";
            break;
        case 9:
            sMes = "septiembre";
            break;
        case 10:
            sMes = "octubre";
            break;
        case 11:
            sMes = "noviembre";
            break;
        case 12:
            sMes = "diciembre";
            break;
    }

    fecha = dia + " de " + sMes + " de " + anno;

    $.redirect("hrrCobros.php", {msCodCobro: dbCobro, msConcepto: dbConcepto, msFecha: fecha, cboCurso: curso}, "POST");
}

function fxLlenaCobros()
{
    var curso = $('#cboCurso').val();
    var datos = new FormData();
    datos.append('msCurso', curso);

    $.ajax({
        url: 'funciones/fxDatosCobros.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            datos = JSON.parse(response);
            $('#dgCOB').datagrid({data: datos});
            $('#dgCOB').datagrid('reload');
        }
    })
}

/*Grid de Cobros*/
var editIndexCOB = undefined;
var lastIndexCOB;

$('#dgCOB').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexCOB != rowIndex) {
            $(this).datagrid('endEdit', lastIndexCOB);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexCOB = rowIndex;
    }
});

function endEditingCOB() {
    if (editIndexCOB == undefined) {
        return true
    }
    if ($('#dgCOB').datagrid('validateRow', editIndexCOB)) {
        $('#dgCOB').datagrid('endEdit', editIndexCOB);
        editIndexCOB = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellCOB(index, field) {
    if (editIndexCOB != index) {
        if (endEditingCOB()) {
            $('#dgCOB').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexCOB = index;
        } else {
            setTimeout(function() {
                $('#dgCOB').datagrid('selectRow', editIndexCOB);
            }, 0);
        }
    }
}

function acceptitCOB() {
    if (endEditingCOB()) {
        $('#dgCOB').datagrid('acceptChanges');
    }
}
</script>
</body>
</html>
