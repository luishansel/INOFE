<?php
try
{
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php"/>');
		exit('');
	}
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
	require_once ("funciones/fxAsistencia.php");
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
		$PermisoUsuario = fxPermisoUsuario("procAsistencia");
		
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
			if (isset($_POST["Operacion"]))
			{
				$mnOperacion = $_POST["Operacion"];
				$Codigo = $_POST["txtCodAsistencia"];
				$Modulo = $_POST["cboModulo"];
				$Fecha = $_POST["cboFechaClase"];
				$gridEstudiantes = $_POST["gridEstudiantes"];

                if ($mnOperacion == 0)
                {
                    $Codigo = fxGuardarAsistencia ($Modulo, $Fecha);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA140A", $Codigo, "", "Agregar");
                }
                else
                {
                    fxModificarAsistencia ($Codigo, $Modulo, $Fecha);
                    fxBorrarDetEstudiantes ($Codigo);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA140A", $Codigo, "", "Modificar");
                }
                
				foreach($gridEstudiantes as $Registro)
				{
                    $Matricula = $Registro['matricula'];
                    $Asistencia = $Registro['estado'];
                    $Justificacion = $Registro['justificacion'];
                    switch ($Asistencia)
                    {
                        case "Presente":
                            $Estado = 0;
                        break;

                        case "Ausente":
                            $Estado = 1;
                        break;

                        default:
                            $Estado = 2;
                    }
                    fxGuardarDetEstudiantes ($Codigo, $Matricula, $Estado, $Justificacion);
                    $itemId++;
				}

				?><meta http-equiv="Refresh" content="0;url=gridAsistencia.php" /><?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $Codigo = trim($_POST["KDSA"]);
                else
                    $Codigo = "";

				$RecordSet = fxDevuelveAsistencia (0, $Codigo);
                $mnRegistros = $RecordSet->rowCount();
                if ($mnRegistros > 0)
                {
                    $Fila = $RecordSet->fetch();
                    $Modulo = $Fila["MODULO_REL"];
                    $FechaClase = $Fila["FECHA_140"];
                }
                else 
                {
                    $Modulo = "";
                    $FechaClase = "";
                }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Asistencia</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
                <form id="procAsistencia" name="procAsistencia">
                    <div class="form-group row">
                        <label for="txtCodAsistencia" class="col-sm-12 col-md-2 col-form-label">Código de Asistencia</label>
                        <div class="col-sm-12 col-md-2">
                            <?php 
                                echo('<input type="text" class="form-control" id="txtCodAsistencia" name="txtCodAsistencia" value="' . $Codigo . '" readonly />');
                                echo('<input type="hidden" class="form-control" id="txtArch" name="txtArch" value="0" />');
                                echo('<input type="hidden" class="form-control" id="txtWeb" name="txtWeb" value="0" />');
                            ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cboCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
                        <div class="col-sm-12 col-md-6">
                            <?php
                                if ($Codigo == "")
                                {
                                    echo('<select class="form-control" id="cboCurso" name="cboCurso" onchange="llenaModulos(this.value, ' . $Administrador . ')">');

                                    if (trim($_SESSION["gsDocente"]) != "" and $Administrador == 0)
                                    {
                                        $mDocente = trim($_SESSION["gsDocente"]);
                                        $msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                        $msConsulta .= "from KDSA020A, KDSA021A ";
                                        $msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
                                        $msConsulta .= "and KDSA021A.DOCENTE_REL = ? ";
                                        $msConsulta .= "order by KDSA020A.CURSO_REL desc";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute([$mDocente]);
                                    }
                                    else
                                    {
                                        $msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                        $msConsulta .= "from KDSA020A, KDSA021A ";
                                        $msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
                                        $msConsulta .= "order by KDSA020A.CURSO_REL desc";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute();
                                    }
                                }
                                else
                                {
                                    echo('<select class="form-control" id="cboCurso" name="cboCurso" disabled>');

                                    $msConsulta = "select KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE ";
                                    $msConsulta .= "from KDSA020A, KDSA021A where KDSA020A.CURSO_REL = KDSA021A.CURSO_REL and MODULO_REL = ?";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                            $mDatos->execute([$Modulo]);
                                }

                                $codCurso = "";

                                while ($mFila = $mDatos->fetch())
                                {
                                    $msCurso = rtrim($mFila["CURSO_REL"]);
                                    $msTexto = rtrim($mFila["NOMBRE"]);

                                    if ($codCurso == "")
                                        $codCurso = $msCurso;
                                    
                                    if ($codCurso == $msCurso)
                                        echo("<option value=\"" . $msCurso . "\" selected>" . $msTexto . "</option>");
                                    else
                                        echo("<option value=\"" . $msCurso . "\">" . $msTexto . "</option>");
                                }
                            ?>
                        </select>
                        </div>
                    </div>

                    <div class="form-group  row">
                        <label for="cboModulo" class="col-sm-12 col-md-2 col-form-label">Módulo</label>
                        <div class="col-sm-12 col-md-6">
                            <?php
                                if ($Codigo == "")
                                    echo('<select class="form-control" id="cboModulo" name="cboModulo" onchange="actualizaDatos()">');
                                else
                                    echo('<select class="form-control" id="cboModulo" name="cboModulo" disabled>');

                                if ($Administrador == 1 or $_SESSION["gsDocente"] == "")
                                {
                                    $msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? order by NUMERO_021";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                            $mDatos->execute([$codCurso]);
                                }
                                else
                                {
                                    $mDocente = $_SESSION["gsDocente"];
                                    $msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? and DOCENTE_REL = ? order by NUMERO_021";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                            $mDatos->execute([$codCurso, $mDocente]);
                                }

                                $msResponse = "";
                                $mbPrimeraLinea = true;

                                while ($Fila = $mDatos->fetch())
                                {
                                    $codModulo = rtrim($Fila["MODULO_REL"]);
                                    $Texto = rtrim($Fila["NOMBRE_021"]);

                                    if ($mbPrimeraLinea == true)
                                    {
                                        $PrimerModulo = $codModulo;
                                        $mbPrimeraLinea = false;
                                    }
                                    
                                    if ($Modulo == "")
                                        $Modulo= $codModulo;
                                    
                                    if ($codModulo == $Modulo)
                                        $msResponse .= "<option value=\"" . $codModulo . "\" selected>" . $Texto . "</option>";
                                    else
                                        $msResponse .= "<option value=\"" . $codModulo . "\">" . $Texto . "</option>";
                                }
                                echo($msResponse);
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cboFechaClase" class="col-sm-12 col-md-2 col-form-label">Fecha de la Clase</label>
                        <div class="col-sm-12 col-md-2">
                            <?php
                                if ($Codigo == "")
                                {
                                    echo('<select class="form-control" id="cboFechaClase" name="cboFechaClase" onchange="verificaFechas(' . $Administrador . ', \'' . $_SESSION["gsDocente"] . '\')">');
                                    $msConsulta = "select CLASE_REL, FECHACLASE_130 as FECHAASISTENCIA from KDSA130A where not exists(select ASISTENCIA_REL from KDSA140A where FECHA_140 = FECHACLASE_130 and KDSA140A.MODULO_REL = KDSA130A.MODULO_REL) and MODULO_REL = ?";
                                }
                                else
                                {
                                    echo('<select class="form-control" id="cboFechaClase" name="cboFechaClase" disabled>');
                                    $msConsulta = "select ASISTENCIA_REL, FECHA_140 as FECHAASISTENCIA from KDSA140A where MODULO_REL = ?";
                                }
                                
                                $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                        $mDatos->execute([$Modulo]);
                                while ($Fila = $mDatos->fetch())
                                {
                                    $fechaBD = date_create_from_format('Y-m-d', $Fila["FECHAASISTENCIA"]);
                                    $Valor = date_format($fechaBD, 'Y-m-d');
                                    $Texto = date_format($fechaBD, 'd / m / Y');

                                    if ($Codigo == "")
                                    {
                                        echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        if ($FechaClase == "")
                                            $FechaClase = $Valor;
                                    }
                                    else
                                    {
                                        if ($FechaClase == "")
                                        {
                                            echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                            $FechaClase = $Valor;
                                        }
                                        else
                                        {
                                            if ($FechaClase == $Valor)
                                                echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
                                            else
                                                echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        }
                                    }
                                }
                                echo("</select>");
						    ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="dgEST" class="col-sm-12 col-md-2 form-label">Estudiantes</label>
                        <div class="col-sm-auto col-md-8">
                            <div id="dvEST">
                                <table id="dgEST" class="easyui-datagrid table" data-options="iconCls:'icon-edit', toolbar:'#tbEST', singleSelect:true, method:'get', onClickCell: onClickCellEST">
                                    <thead>
                                        <tr>
                                            <th data-options="field:'matricula', hidden:'true'">Matrícula</th>
                                            <th data-options="field:'estudiante', width:'70%', align:'left'">Estudiante</th>
                                            <!--th data-options="field:'estado', width:'15%', align:'center',
                                                editor: {type:'combobox',
                                                options:{panelHeight:'auto', data:[{value:'Presente',text:'Presente'}, {value:'Ausente',text:'Ausente'}, {value:'Justificado',text:'Justificado'}]}}">Asistencia</th>
                                            <th data-options="field:'justificacion', width:'40%', align:'left', editor:'text'">Justificación</th LHVG 20221122-->
                                            <th data-options="field:'estado', width:'30%', align:'center',
                                                editor: {
                                                            type:'combobox',
                                                            options:
                                                            {
                                                                panelHeight:'auto',
                                                                data:[{value:'Presente',text:'Presente'}, {value:'Ausente',text:'Ausente'}],
                                                                editable:false
                                                            }
                                                        }">Asistencia</th>
                                            <th data-options="field:'justificacion', width:'40%', align:'left', editor:'text', hidden:true">Justificación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $mDatos = fxDevuelveDetEstudiantes($Codigo, $Modulo);

                                        if ($mDatos->rowCount()>0)
                                        {
                                            while($Fila = $mDatos->fetch())
                                            {
                                                echo('<tr>');
                                                echo('<td>' . rtrim($Fila['MATRICULA_REL']) . '</td>');
                                                echo('<td>' . rtrim($Fila['ESTUDIANTE']) . '</td>');
                                                switch ($Fila['ESTADO_141'])
                                                {
                                                    case 0:
                                                        echo('<td>Presente</td>');
                                                        break;

                                                    case 1:
                                                        echo('<td>Ausente</td>');
                                                        break;

                                                    default:
                                                        echo('<td>Justificado</td>');
                                                }
                                                echo('<td></td>');
                                                echo('</tr>');
                                            }
                                        }
                                        else
                                        {
                                            echo('<tr>');
                                            echo('<td></td>');
                                            echo('<td></td>');
                                            echo('<td></td>');
                                            echo('<td></td>');
                                            echo('</tr>');
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="tbEST" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="acceptitEST()">Salir del Modo de Edición</a>
                    </div>

                    <div class="row">
                        <div class="col-auto col-xs-offset-none col-md-8 col-md-offset-2">
                        <?php
                            $FechaHoy = date('Y-m-d');
                            $HoraHoy = date('H:i:s');

                            if ($Administrador == 1 or $_SESSION["gsDocente"] == "" or $FechaClase == $FechaHoy)
                                echo('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />');
                            else
                                echo('<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" disabled />');
                        ?>
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridAsistencia.php';" />
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
<?php
}
catch (Exception $e) 
{
    depurar($e->getMessage());
}
?>

<script>
window.onload = function() {
    if (document.getElementById('txtCodAsistencia').value == "")
        actualizaDatos();

    llenaArchWeb();
}

function verificarFormulario() {
    var gridEstudiantes = $('#dgEST').datagrid('getData');
    var registros = $('#dgEST').datagrid('getRows').length - 1;
    var arch = document.getElementById('txtArch').value;
    var web = document.getElementById('txtWeb').value;
    
    if (document.getElementById('cboFechaClase').value == "")
    {
        $.messager.alert('INOFE', 'Falta la Fecha de la Clase', 'warning');
        return false;
    }

    if (registros < 0)
    {
        $.messager.alert('INOFE', 'Faltan los Estudiantes', 'warning');
        return false;
    }

    if (arch == 0 && web == 0){
        $.messager.alert('INOFE', 'La planificación de clases está incompleta. Faltan los archivos o los sitios web de apoyo.', 'warning');
        return false;
    }

    for (i = 0; i <= registros; i++) {
        if (gridEstudiantes.rows[i].estado == "")
        {
            $.messager.alert('KDSA', 'Falta la asistencia de ' + gridEstudiantes.rows[i].estudiante, 'warning');
            return false;
        }

        if (gridEstudiantes.rows[i].estado == "Justificado" && gridEstudiantes.rows[i].justificacion == "")
        {
            $.messager.alert('KDSA', 'Falta la justificación de ' + gridEstudiantes.rows[i].estudiante, 'warning');
            return false;
        }
    }

    return true;
}

function llenaModulos (curso, administrador)
{
    var datos = new FormData();
    datos.append('modulosCurso', curso);
    datos.append('modulosDocente', '<?php echo($_SESSION["gsDocente"]) ?>');
    datos.append('mbAdministrador', administrador);
    datos.append('mnTipo', 1);

    $.ajax({
        url: 'funciones/fxDatosAsistencia.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response){
            document.getElementById('cboModulo').innerHTML = response;
            actualizaDatos();
            llenaArchWeb();
        }
    })
}

function llenaArchWeb(){
    var datos = new FormData();
    var modulo = document.getElementById('cboModulo').value;
    var fecha = document.getElementById('cboFechaClase').value;

    if (fecha != '')
    {
        datos.append('modulo', modulo);
        datos.append('fechaClase', fecha);
        $.ajax({
            url: 'funciones/fxDatosAsistencia.php',
            type: 'post',
            data: datos,
            contentType: false,
            processData: false,
            success: function(response) {
                var data = JSON.parse(response);
                document.getElementById('txtArch').value = data.arch;
                document.getElementById('txtWeb').value = data.web;
            }
        })
    }
    else
    {
        document.getElementById('txtArch').value = 0;
        document.getElementById('txtWeb').value = 0;
    }
}

function actualizaDatos() {
    var datos = new FormData();
    var asistencia = document.getElementById('txtCodAsistencia').value;
    var modulo = document.getElementById('cboModulo').value;
    var usuarioClase = '<?php echo($_SESSION["gsUsuario"]) ?>';
    var docenteClase = '<?php echo($_SESSION["gsDocente"]) ?>';
    var caracter;
    var cboFecha;
    var gridEstudiantes;
    datos.append('asistenciaClase', asistencia);
    datos.append('moduloClase', modulo);
    datos.append('usuarioClase', usuarioClase);
    datos.append('docenteClase', docenteClase);

    $.ajax({
        url: 'funciones/fxDatosAsistencia.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            var texto = "";
            var admin;
            var docente = "";
            var primeraFecha = "";
            var horaFinClase = "";
            var fechaHoy = new Date();
            var anno = fechaHoy.getFullYear();
            var mes = fechaHoy.getMonth() + 1;
            var dia = fechaHoy.getDate();
            if (dia < 10)
            {
                if (mes < 10)
                    var fechaAhora = anno + "-0" + mes + "-0" + dia;
                else
                    var fechaAhora = anno + "-" + mes + "-0" + dia;
            }
            else
            {
                if (mes < 10)
                    var fechaAhora = anno + "-0" + mes + "-" + dia;
                else
                    var fechaAhora = anno + "-" + mes + "-" + dia;
            }
            var hora = fechaHoy.getHours();
            var minutos = fechaHoy.getMinutes();
            var minutosAhora = (hora * 60) + minutos;
            var minutosFinClase;
            var minutosTranscurridos;

            for (i=0; i<response.length; i++)
            {
                caracter = response.charAt(i);
                switch (caracter)
                {
                    case "$":
                        docente = texto;
                        texto = "";
                        break;
                    case "?":
                        admin = texto;
                        texto = "";
                        break;
                    case "%":
                        horaFinClase = texto;
                        texto = "";
                        break;
                    case "&":
                        primeraFecha = texto;
                        texto = "";
                        break;
                    case "@":
                        cboFecha = texto;
                        texto = "";
                        break;
                    case "#":
                        gridEstudiantes = texto;
                        texto = "";
                        break;
                    default:
                        texto += caracter;
                }
            }
            
            if (admin == '0' && docente != '')
            {
                if (fechaAhora != primeraFecha)
                    document.getElementById('Guardar').disabled = true;
                else
                    document.getElementById('Guardar').disabled = false;
            }
            else
            {
                document.getElementById('Guardar').disabled = false;
            }

            document.getElementById('cboFechaClase').innerHTML = cboFecha;
            datos = JSON.parse(gridEstudiantes);
            $('#dgEST').datagrid({data: datos});
            $('#dgEST').datagrid('reload');
            llenaArchWeb();
        }
    })
}

function verificaFechas(admin, docente) {
    var fechaHoy = new Date();
    var anno = fechaHoy.getFullYear();
    var mes = fechaHoy.getMonth() + 1;
    var dia = fechaHoy.getDate();

    if (dia < 10)
    {
        if (mes < 10)
            var fechaAhora = anno + "-0" + mes + "-0" + dia;
        else
            var fechaAhora = anno + "-" + mes + "-0" + dia;
    }
    else
    {
        if (mes < 10)
            var fechaAhora = anno + "-0" + mes + "-" + dia;
        else
            var fechaAhora = anno + "-" + mes + "-" + dia;
    }

    primeraFecha = document.getElementById('cboFechaClase').value;

    if (admin == 1 || docente == '' || fechaAhora == primeraFecha)
        document.getElementById('Guardar').disabled = false;
    else
        document.getElementById('Guardar').disabled = true;

    llenaArchWeb();
}

/*Grid de Estudiantes*/
var editIndexEST = undefined;
var lastIndexEST;

$('#dgEST').datagrid({
    onClickRow: function(rowIndex) {
        if (lastIndexEST != rowIndex) {
            $(this).datagrid('endEdit', lastIndexEST);
            $(this).datagrid('beginEdit', rowIndex);
        }
        lastIndexEST = rowIndex;
    }
});

function endEditingEST() {
    if (editIndexEST == undefined) {
        return true
    }
    if ($('#dgEST').datagrid('validateRow', editIndexEST)) {
        $('#dgEST').datagrid('endEdit', editIndexEST);
        editIndexEST = undefined;
        return true;
    } else {
        return false;
    }
}

function onClickCellEST(index, field) {
    if (editIndexEST != index) {
        if (endEditingEST()) {
            $('#dgEST').datagrid('selectRow', index)
                .datagrid('beginEdit', index);
            editIndexEST = index;
        } else {
            setTimeout(function() {
                $('#dgEST').datagrid('selectRow', editIndexEST);
            }, 0);
        }
    }
}

function acceptitEST() {
    if (endEditingEST()) {
        $('#dgEST').datagrid('acceptChanges');
    }
}

$('form').submit(function(e) {
    e.preventDefault();

    if (verificarFormulario()) {
        var texto;
        var datos;
        var registros;
        var i;
        var gridEstudiantes = $('#dgEST').datagrid('getData');

        texto = '{"txtCodAsistencia":"' + document.getElementById("txtCodAsistencia").value + '", ';
        if (document.getElementById("txtCodAsistencia").value == "")
            texto += '"Operacion":"0", ';
        else
            texto += '"Operacion":"1", ';
        texto += '"cboModulo":"' + document.getElementById("cboModulo").value + '", ';
        texto += '"cboFechaClase":"' + document.getElementById("cboFechaClase").value + '", ';

        registros = $('#dgEST').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridEstudiantes": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"matricula":"' + gridEstudiantes.rows[i].matricula;
                texto += '","estudiante":"' + gridEstudiantes.rows[i].estudiante;
                texto += '","estado":"' + gridEstudiantes.rows[i].estado;
                texto += '","justificacion":"' + gridEstudiantes.rows[i].justificacion;
                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        }

        datos = JSON.parse(texto);

        $.ajax({
                url: 'procAsistencia.php',
                type: 'post',
                data: datos,
            })
            .done(function() {
                location.href = "gridAsistencia.php";
            })
            .fail(function() {
                console.log('Error')
            });
    }
});
</script>