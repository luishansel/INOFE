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
	require_once ("funciones/fxCalificaciones.php");
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
		$PermisoUsuario = fxPermisoUsuario("procCalificaciones");
		
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
				$Codigo = $_POST["txtCodCalificacion"];
				$Modulo = $_POST["cboModulo"];
				$Fecha = $_POST["dtpFecha"];
				$gridEstudiantes = $_POST["gridEstudiantes"];

                if ($mnOperacion == 0)
                {
                    $Codigo = fxGuardarCalificaciones ($Modulo, $Fecha);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA150A", $Codigo, "", "Agregar");
                }
                else
                {
                    fxModificarCalificaciones ($Codigo, $Modulo, $Fecha);
                    fxBorrarDetCalificaciones ($Codigo);
                    fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA150A", $Codigo, "", "Modificar");
                }
                
				foreach($gridEstudiantes as $Registro)
				{
                    $Matricula = $Registro['matricula'];
                    $Puntaje = $Registro['puntaje'];
                    fxGuardarDetCalificaciones ($Codigo, $Matricula, $Puntaje);
				}

				?><meta http-equiv="Refresh" content="0;url=gridCalificaciones.php" /><?php
			}
			else
			{
                if (isset($_POST["KDSA"]))
				    $Codigo = trim($_POST["KDSA"]);
                else
                    $Codigo = "";

				$RecordSet = fxDevuelveCalificaciones (0, $Codigo);
                $mnRegistros = $RecordSet->rowCount();
                if ($mnRegistros > 0)
                {
                    $Fila = $RecordSet->fetch();
                    $Modulo = $Fila["MODULO_REL"];
                    $Fecha = $Fila["FECHA_150"];
                }
                else 
                {
                    $Modulo = "";
                    $Fecha = "";
                }
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Calificaciones</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
                <form id="procCalificaciones" name="procCalificaciones">
                    <div class="form-group row">
                        <label for="txtCodCalificacion" class="col-sm-12 col-md-2 col-form-label">Código de Calificación</label>
                        <div class="col-sm-12 col-md-2">
                            <?php 
                                echo('<input type="text" class="form-control" id="txtCodCalificacion" name="txtCodCalificacion" value="' . $Codigo . '" readonly />');
                                echo('<input type="text" class="form-control" id="txtAdministrador" name="txtAdministrador" value="' . $Administrador . '" style="display: none" />');
                                echo('<input type="text" class="form-control" id="txtDias" name="txtDias" value="0" style="display: none" />');
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
                                        $msConsulta = "select A.* from ";
                                        $msConsulta .= "(select KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE, MODULO_REL, KDSA021A.DOCENTE_REL ";
                                        $msConsulta .= "from KDSA020A, KDSA021A ";
                                        $msConsulta .= "where KDSA020A.CURSO_REL = KDSA021A.CURSO_REL) as A ";
                                        $msConsulta .= "left join KDSA150A on A.MODULO_REL = KDSA150A.MODULO_REL ";
                                        $msConsulta .= "where KDSA150A.MODULO_REL is null ";
                                        $msConsulta .= "and A.DOCENTE_REL = ? order by A.CURSO_REL desc";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute([$mDocente]);
                                    }
                                    else
                                    {
                                        $msConsulta = "select A.* from ";
                                        $msConsulta .= "(select KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE, MODULO_REL, KDSA021A.DOCENTE_REL ";
                                        $msConsulta .= "from KDSA020A, KDSA021A ";
                                        $msConsulta .= "where KDSA020A.CURSO_REL = KDSA021A.CURSO_REL) as A ";
                                        $msConsulta .= "left join KDSA150A on A.MODULO_REL = KDSA150A.MODULO_REL ";
                                        $msConsulta .= "where KDSA150A.MODULO_REL is null order by A.CURSO_REL desc";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute();
                                    }
                                }
                                else
                                {
                                    echo('<select class="form-control" id="cboCurso" name="cboCurso" disabled>');

                                    $msConsulta = "select KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE, MODULO_REL ";
                                    $msConsulta .= "from KDSA020A, KDSA021A where KDSA020A.CURSO_REL = KDSA021A.CURSO_REL and MODULO_REL = ?";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                            $mDatos->execute([$Modulo]);
                                }

                                $mCursoAnt = "";

                                while ($Fila = $mDatos->fetch())
                                {
                                    $Curso = rtrim($Fila["CURSO_REL"]);
                                    $Texto = rtrim($Fila["NOMBRE"]);
                                    
                                    if ($Codigo == "")
                                    {
                                        if ($Curso != $mCursoAnt)
                                        {
                                            if ($mCursoAnt == "")
                                            {
                                                echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
                                                $codCurso = $Curso;
                                            }
                                            else
                                                echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
                                            
                                            $mCursoAnt = $Curso;
                                        }
                                    }
                                    else
                                    {
                                        echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
                                        $codCurso = $Curso;
                                    }
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
                                {
                                    echo('<select class="form-control" id="cboModulo" name="cboModulo" onchange="actualizaDatos()">');
                                    if ($Administrador == 1 or $PermisoUsuario == 1)
                                    {
                                        $msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? and not exists(select CALIFICACION_REL from KDSA150A where KDSA150A.MODULO_REL= KDSA021A.MODULO_REL) order by NUMERO_021";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute([$codCurso]);
                                    }
                                    else
                                    {
                                        $mDocente = trim($_SESSION["gsDocente"]);
                                        $msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? and not exists(select CALIFICACION_REL from KDSA150A where KDSA150A.MODULO_REL= KDSA021A.MODULO_REL) and DOCENTE_REL = ? order by NUMERO_021";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute([$codCurso, $mDocente]);
                                    }
                                }
                                else
                                {
                                    echo('<select class="form-control" id="cboModulo" name="cboModulo" disabled>');
                                    if ($Administrador == 1 or $_SESSION["gsDocente"] == "")
                                    {
                                        $msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? order by NUMERO_021";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute([$codCurso]);
                                    }
                                    else
                                    {
                                        $mDocente = trim($_SESSION["gsDocente"]);
                                        $msConsulta = "select MODULO_REL, NOMBRE_021 from KDSA021A where CURSO_REL = ? and DOCENTE_REL = ? order by NUMERO_021";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                                $mDatos->execute([$codCurso, $mDocente]);
                                    }
                                }

                                $msResponse = "";

                                while ($Fila = $mDatos->fetch())
                                {
                                    $codModulo = rtrim($Fila["MODULO_REL"]);
                                    $Texto = rtrim($Fila["NOMBRE_021"]);

                                    if ($Modulo == "")
                                        $Modulo = $codModulo;
                                    
                                    if ($codModulo == $Modulo)
                                        $msResponse .= "<option value='" . $codModulo . "' selected>" . $Texto . "</option>";
                                    else
                                        $msResponse .= "<option value='" . $codModulo . "'>" . $Texto . "</option>";
                                }
                                echo($msResponse);
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="dtpFecha" class="col-sm-12 col-md-2 col-form-label">Fecha</label>
                        <div class="col-sm-12 col-md-2">
                            <?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" readonly />');
							else
                                echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" readonly />');
                                
                            //Fecha de fin de Módulo
                            $msConsulta = "select FECHAFIN_021 from KDSA021A where MODULO_REL = ?";
                            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                    $mDatos->execute([$Modulo]);
                            $numRegistros = $mDatos->rowCount();

                            if ($numRegistros > 0)
                            {
                                $Fila = $mDatos->fetch();
                                $FechaFinModulo = $Fila["FECHAFIN_021"];
                            }
                            else 
                            {
                                $FechaFinModulo = date("Y-m-d");
                            }

                            echo('<input type="date" class="form-control" id="dtpFinModulo" name="dtpFinModulo" value="' . $FechaFinModulo . '"  style="display: none;" />');
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
                                            <th data-options="field:'estudiante', width:'80%', align:'left'">Estudiante</th>
                                            <!--th data-options="field:'puntaje', width:'20%', align:'right', editor:{type:'numberbox',options:{precision:0}}">Puntaje</th LHVG 20240619-->
                                            <!--Se establecen valores fijos para la calificación LHVG 20240619-->
                                            <th data-options="field:'puntaje', width:'20%', align:'center',
                                                editor: 
                                                {
                                                    type:'combobox',
                                                    options:
                                                    {
                                                        panelHeight:'auto',
                                                        data:[{value:'0',text:'0'}, {value:'70',text:'70'}, {value:'80',text:'80'}, {value:'90',text:'90'}, {value:'100',text:'100'}],
                                                        editable:false
                                                    }
                                                }">Puntaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $mDatos = fxDevuelveDetCalificaciones($Codigo, $Modulo);
                                            while ($Fila = $mDatos->fetch())
                                            {
                                                echo('<tr>');
                                                echo('<td>' . rtrim($Fila['MATRICULA_REL']) . '</td>');
                                                echo('<td>' . rtrim($Fila['ESTUDIANTE']) . '</td>');
                                                echo('<td>' . rtrim($Fila['PUNTAJE_151']) . '</td>');
                                                echo('</tr>');
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>

                    <div id="tbEST" style="height:auto">
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="acceptitEST()">Salir del Modo de Edición</a>
                    </div>

                    <div class="row">
                        <div class="col-auto col-xs-offset-none col-md-8 col-md-offset-2">
                            <input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridCalificaciones.php';" />
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
    if (document.getElementById('txtCodCalificacion').value == "")
        actualizaDatos();
}

function verificarFormulario() {
    var gridEstudiantes = $('#dgEST').datagrid('getData');
    var registros = $('#dgEST').datagrid('getRows').length;
    var fechaHoy = new Date();
    var fechaModulo = new Date(document.getElementById('dtpFinModulo').value);
    var notaEnCero = 0;
    var tiempo;
    var dias;
    
    if (registros < 0)
    {
        $.messager.alert('INOFE', 'Faltan los Estudiantes', 'warning');
        return false;
    }
    else
    {
        for (i = 0; i < registros; i++) {
            if (gridEstudiantes.rows[i].puntaje == 0)
                notaEnCero += 1;
        }

        if (notaEnCero == registros)
        {
            $.messager.alert('INOFE', 'Todas las calificaciones están en cero', 'warning');
            return false;
        }

        for (i = 0; i < registros; i++) {
            if (gridEstudiantes.rows[i].puntaje == "" || gridEstudiantes.rows[i].puntaje < 0)
            {
                $.messager.alert('INOFE', 'Falta el puntaje de ' + gridEstudiantes.rows[i].estudiante, 'warning');
                return false;
            }
        }
		/*
		if (document.getElementById('txtAdministrador').value == 0)
        {
            dias = document.getElementById('txtDias').value;
            if (dias > 10)
            {
                $.messager.alert('KDSA', 'El tiempo para registrar las Calificaciones caducó', 'warning');
                return false;
            }
        }*/
        return true;
    }
}

function llenaModulos (curso, administrador)
{
    var datos = new FormData();
    datos.append('modulosCurso', curso);
    datos.append('modulosDocente', '<?php echo($_SESSION["gsDocente"]) ?>');
    datos.append('mbAdministrador', administrador);
    datos.append('mnTipo', 2);

    $.ajax({
        url: 'funciones/fxDatosCalificacion.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response){
            document.getElementById('cboModulo').innerHTML = response;
            actualizaDatos();
        }
    })
}

function actualizaDatos() {
    var datos = new FormData();
    var calificacion = document.getElementById('txtCodCalificacion').value;
    var modulo = document.getElementById('cboModulo').value;
    datos.append('moduloCalificacion', modulo);
    datos.append('codCalificacion', calificacion);

    $.ajax({
        url: 'funciones/fxDatosCalificacion.php',
        type: 'post',
        data: datos,
        contentType: false,
        processData: false,
        success: function(response) {
            var texto = "";
            var dias;
            var caracter;
            var dtpFecha;
            var txnAsistencia;
            var gridEstudiantes;
            for (i=0; i<response.length; i++)
            {
                caracter = response.charAt(i);
                switch (caracter)
                {
                    case "@":
                        dtpFecha = texto;
                        texto = "";
                    break;
                    case "#":
                        txnAsistencia = texto;
                        texto = "";
                    break;
                    case "^":
                        dias = texto;
                        texto = "";
                    break;
                    case "%":
                        gridEstudiantes = texto;
                        texto = "";
                    break;
                    default:
                        texto += caracter;
                }
            }

            document.getElementById('txtDias').value = dias;
            document.getElementById('dtpFinModulo').value = dtpFecha;
            datosGrid = JSON.parse(gridEstudiantes);
            $('#dgEST').datagrid({data: datosGrid});
            $('#dgEST').datagrid('reload');
        }
    })
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

        texto = '{"txtCodCalificacion":"' + document.getElementById("txtCodCalificacion").value + '", ';
        if (document.getElementById("txtCodCalificacion").value == "")
            texto += '"Operacion":"0", ';
        else
            texto += '"Operacion":"1", ';
        texto += '"cboModulo":"' + document.getElementById("cboModulo").value + '", ';
        texto += '"dtpFecha":"' + document.getElementById("dtpFecha").value + '", ';

        registros = $('#dgEST').datagrid('getRows').length - 1;

        if (registros >= 0) {
            texto += '"gridEstudiantes": [';
            for (i = 0; i <= registros; i++) {
                texto += '{"matricula":"' + gridEstudiantes.rows[i].matricula;
                texto += '","estudiante":"' + gridEstudiantes.rows[i].estudiante;
                texto += '","puntaje":"' + gridEstudiantes.rows[i].puntaje;
                if (i == registros)
                    texto += '"}]}';
                else
                    texto += '"},';
            }
        }

        datos = JSON.parse(texto);

        $.ajax({
                url: 'procCalificaciones.php',
                type: 'post',
                data: datos,
            })
            .done(function() {
                location.href = "gridCalificaciones.php";
            })
            .fail(function() {
                console.log('Error')
            });
    }
});
</script>