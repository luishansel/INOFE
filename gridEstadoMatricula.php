<?php
	session_start();
	if (!isset($_SESSION["gnVerifica"]) or $_SESSION["gnVerifica"] != 1)
	{
		echo('<meta http-equiv="Refresh" content="0;url=index.php">');
		exit('');
    }
	
	include ("MasterWeb.php");
	require_once ("funciones/fxGeneral.php");
	require_once ("funciones/fxUsuarios.php");
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
		$PermisoUsuario = fxPermisoUsuario("procEstadoMatricula", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
		?>
    	<div class="container">
        	<div id="DivContenido">
                <div id="lateral">
                    <?php
                        echo('<label id="editar" data-toggle="tooltip" data-placement="top" title="Editar"><img src="imagenes/btnLateralEditar.png" height="80%" style="cursor:pointer" /></label>');
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button id="edit" type="button" class="btn btn-warning" >Editar</button>
                        
                        <div id="tbMatricula" class="easyui-tabs tabs-narrow" style="width:100%;height:auto">
                            <div title="Activos" style="padding:10px">
                                <table id="grid1" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                                    <thead>
                                        <tr>
                                            <th data-column-id="CURSO_REL" data-identifier="true" data-align="left" data-order="desc" data-header-align="left" data-width="15%">Curso</th>
                                            <th data-column-id="NOMBRE_020" data-order="asc" data-align="left" data-header-align="left" data-width="70%">Nombre del Curso</th>
                                            <th data-column-id="ESTUDIANTES" data-order="asc" data-align="center" data-header-align="center" data-width="15%">Estudiantes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, (select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and ESTADO_030 <> 4 and KDSA030A.ESTADO_030 <> 4) as ESTUDIANTES from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 < CURDATE()";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
				                        $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            echo ("<tr>");
                                            echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
                                            echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
                                            echo ("<td>" . $Fila["ESTUDIANTES"] . "</td>");
                                            echo ("</tr>");
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div title="En matrícula" style="padding:10px">
                                <table id="grid2" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                                    <thead>
                                        <tr>
                                            <th data-column-id="CURSO_REL" data-identifier="true" data-order="desc" data-align="left" data-header-align="left" data-width="15%">Curso</th>
                                            <th data-column-id="NOMBRE_020" data-order="asc" data-align="left" data-header-align="left" data-width="70%">Nombre del Curso</th>
                                            <th data-column-id="ESTUDIANTES" data-order="asc" data-align="center" data-header-align="center" data-width="15%">Estudiantes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, (select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and KDSA030A.ESTADO_030 <> 4) as ESTUDIANTES from KDSA020A where ACTIVO_020 = 1 and FECHAINI_020 >= CURDATE()";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
				                        $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            echo ("<tr>");
                                            echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
                                            echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
                                            echo ("<td>" . $Fila["ESTUDIANTES"] . "</td>");
                                            echo ("</tr>");
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div title="Inactivos" style="padding:10px">
                                <table id="grid3" class="table table-condensed table-hover table-striped" data-selection="true" data-multi-select="false" data-row-select="true" data-keep-selection="true">
                                    <thead>
                                        <tr>
                                            <th data-column-id="CURSO_REL" data-identifier="true" data-align="left" data-order="desc" data-header-align="left" data-width="15%">Curso</th>
                                            <th data-column-id="NOMBRE_020" data-order="asc" data-align="left" data-header-align="left" data-width="70%">Nombre del Curso</th>
                                            <th data-column-id="ESTUDIANTES" data-order="asc" data-align="center" data-header-align="center" data-width="15%">Estudiantes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, (select count(MATRICULA_REL) from KDSA030A where KDSA020A.CURSO_REL = KDSA030A.CURSO_REL and KDSA030A.ESTADO_030 <> 4) as ESTUDIANTES from KDSA020A where ACTIVO_020 = 0";
                                        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
				                        $mDatos->execute();
                                        
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            echo ("<tr>");
                                            echo ("<td>" . $Fila["CURSO_REL"] . "</td>");
                                            echo ("<td>" . $Fila["NOMBRE_020"] . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")</td>");
                                            echo ("<td>" . $Fila["ESTUDIANTES"] . "</td>");
                                            echo ("</tr>");
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>
<script src="bootstrap/lib/jquery-1.11.1.min.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.js"></script>
<script src="bootstrap/dist/jquery.bootgrid.fa.js"></script>
<script src="js/jquery.redirect.js"></script>
<script>
    $(window).scroll(function() {
        var scroll = $(window).scrollTop();
        if (scroll >= 100) {
        $("#lateral").addClass("entra");
        } else {
        $("#lateral").removeClass("entra");
        }
    });
    
    $(function() {
        var mconteo;
        var mAlerta;

        function init() {
            $("#grid1").bootgrid({
                formatters: {
                    "link": function(column, row) {
                        return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
                    }
                },
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#grid1").bootgrid().on("selected.rs.jquery.bootgrid", function(e, selectedRows) {
            mConteo = selectedRows[0]['ESTUDIANTES'];

            if (mConteo == 0)
            {
                mAlerta = 'No hay estudiantes matriculados en el curso ' + selectedRows[0]['NOMBRE_020'];
            }
            else
            {
                $("#grid2").bootgrid("deselect");
                $("#grid3").bootgrid("deselect");
            }
        });

        $("#edit").on("click", function() {
            if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
            {
                if (mConteo == 0)
                    alert(mAlerta);
                else
                {
                    var codCurso = $.trim($("#grid1").bootgrid("getSelectedRows"));
                    $.redirect("procEstadoMatricula.php", {KDSA: codCurso}, "POST");
                }
            }
        });

        $("#editar").on("click", function() {
            if ($.trim($("#grid1").bootgrid("getSelectedRows")) != "")
            {
                if (mConteo == 0)
                    alert(mAlerta);
                else
                {
                    var codCurso = $.trim($("#grid1").bootgrid("getSelectedRows"));
                    $.redirect("procEstadoMatricula.php", {KDSA: codCurso}, "POST");
                }
            }
        });
    });

    $(function() {
        function init() {
            $("#grid2").bootgrid({
                formatters: {
                    "link": function(column, row) {
                        return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
                    }
                },
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#grid2").bootgrid().on("selected.rs.jquery.bootgrid", function(e, selectedRows) {
            mConteo = selectedRows[0]['ESTUDIANTES'];

            if (mConteo == 0)
            {
                mAlerta = 'No hay estudiantes matriculados en el curso ' + selectedRows[0]['NOMBRE_020'];
            }
            else
            {
                $("#grid1").bootgrid("deselect");
                $("#grid3").bootgrid("deselect");
            }
        });

        $("#edit").on("click", function() {
            if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
            {
                if (mConteo == 0)
                    alert(mAlerta);
                else
                {
                    var codCurso = $.trim($("#grid2").bootgrid("getSelectedRows"));
                    $.redirect("procEstadoMatricula.php", {KDSA: codCurso}, "POST");
                }
            }
        });

        $("#editar").on("click", function() {
            if ($.trim($("#grid2").bootgrid("getSelectedRows")) != "")
            {
                if (mConteo == 0)
                    alert(mAlerta);
                else
                {
                    var codCurso = $.trim($("#grid2").bootgrid("getSelectedRows"));
                    $.redirect("procEstadoMatricula.php", {KDSA: codCurso}, "POST");
                }
            }
        });
    });

    $(function() {
        function init() {
            $("#grid3").bootgrid({
                formatters: {
                    "link": function(column, row) {
                        return "<a href=\"#\">" + column.id + ": " + row.id + "</a>";
                    }
                },
                rowCount: [-1, 10, 50, 75]
            });
        }

        init();

        $("#grid3").bootgrid().on("selected.rs.jquery.bootgrid", function(e, selectedRows) {
            mConteo = selectedRows[0]['ESTUDIANTES'];

            if (mConteo == 0)
            {
                mAlerta = 'No hay estudiantes matriculados en el curso ' + selectedRows[0]['NOMBRE_020'];
            }
            else
            {
                $("#grid1").bootgrid("deselect");
                $("#grid2").bootgrid("deselect");
            }
        });
        
        $("#edit").on("click", function() {
            if ($.trim($("#grid3").bootgrid("getSelectedRows")) != "")
            {
                if (mConteo == 0)
                    alert(mAlerta);
                else
                {
                    var codCurso = $.trim($("#grid3").bootgrid("getSelectedRows"));
                    $.redirect("procEstadoMatricula.php", {KDSA: codCurso}, "POST");
                }
            }
        });

        $("#editar").on("click", function() {
            if ($.trim($("#grid3").bootgrid("getSelectedRows")) != "")
            {
                if (mConteo == 0)
                    alert(mAlerta);
                else
                {
                    var codCurso = $.trim($("#grid3").bootgrid("getSelectedRows"));
                    $.redirect("procEstadoMatricula.php", {KDSA: codCurso}, "POST");
                }
            }
        });
    });
</script>
</body>
</html>