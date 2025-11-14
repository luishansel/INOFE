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
	require_once ("funciones/fxCursos.php");
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
		$PermisoUsuario = fxPermisoUsuario("consCursos");
		
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
	?>
<div class="container text-left">
    <div id="DivContenido">
        <div class = "row">
            <div class="col-xs-12 col-md-11">
                <div class="degradado"><strong>Cursos activos</strong></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
                <form id="consCursos" name="consCursos">
                    <div class="col-xs-auto col-xs-offset-none col-md-10 col-md-offset-1">
                        <div class="form-group row">
                            <select class="form-control" id="cboCurso" name="cboCurso" onchange="llenaCurso()">
                                <?php
                                    $m_cnx_MySQL = fxAbrirConexion();
                                    $Curso = "";
									$msConsulta = "select CURSO_REL, NOMBRE_020, GRUPO_020, CONVOCATORIA_020, FECHAINI_020 from KDSA020A where ACTIVO_020 = 1 order by NOMBRE_020";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		                            $mDatos->execute();
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["CURSO_REL"]);

                                        if ($Curso=="")
                                            $Curso = $Valor;
                                        $Texto = rtrim($Fila["NOMBRE_020"]) . " (" . rtrim($Fila["CONVOCATORIA_020"]) . " / G" . rtrim($Fila["GRUPO_020"]) . ")  Inicio " . DevuelveFecha($Fila["FECHAINI_020"]);

										echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="form-group row">
                            <label for="dtpFechaIni" class="col-sm-12 col-md-3 col-form-label">Fecha inicial</label>
                            <div class="col-sm-12 col-md-3">
                                <input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="" readonly/>
                            </div>
                            <div class="col-auto">
                                <label id="lblFechaIni" style="font-size:small; color:rgb(150,150,150)"></label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="dtpFechaFin" class="col-sm-12 col-md-3 col-form-label">Fecha final</label>
                            <div class="col-sm-12 col-md-3">
                                <input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="" readonly/>
                            </div>
                            <div class="col-auto">
                                <label id="lblFechaFin" style="font-size:small; color:rgb(150,150,150)"></label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="dtpHoraIni" class="col-sm-12 col-md-3 col-form-label">Hora inicial de cada sesión</label>
                            <div class="col-sm-12 col-md-3">
                                <input type="time" class="form-control" id="dtpHoraIni" name="dtpHoraIni" value="" readonly/>
                            </div>
                            <div class="col-auto">
                                <label id="lblHoraIni" style="font-size:small; color:rgb(150,150,150)"></label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="dtpHoraFin" class="col-sm-12 col-md-3 col-form-label">Hora final de cada sesión</label>
                            <div class="col-sm-12 col-md-3">
                                <input type="time" class="form-control" id="dtpHoraFin" name="dtpHoraFin" value="" readonly/>
                            </div>
                            <div class="col-auto">
                                <label id="lblHoraFin" style="font-size:small; color:rgb(150,150,150)"></label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txtTipo" class="col-sm-12 col-md-3 col-form-label">Tipo de estudio</label>
                            <div class="col-sm-12 col-md-3">
                                <input type="text" class="form-control" id="txtTipo" name="txtTipo" value="" readonly/>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txtTurno" class="col-sm-12 col-md-3 col-form-label">Turno</label>
                            <div class="col-sm-12 col-md-3">
                                <input type="text" class="form-control" id="txtTurno" name="txtTurno" value="" readonly/>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txtTipoAsistencia" class="col-sm-12 col-md-3 col-form-label">Tipo de asistencia</label>
                            <div class="col-sm-12 col-md-2">
                                <input type="text" class="form-control" id="txtTipoAsistencia" name="txtTipoAsistencia" value="" readonly/>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txtDiasClase" class="col-sm-12 col-md-3 col-form-label">Días de clase</label>
                            <div class="col-sm-12 col-md-7">
                                <input type="text" class="form-control" id="txtDiasClase" name="txtDiasClase" value="" readonly/>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txnValor" class="col-sm-12 col-md-3 col-form-label">Valor del curso</label>
                            <div class="col-sm-12 col-md-2">
                                <input type="text" step="0.01" style="text-align:right" class="form-control" id="txnValor" name="txnValor" value="0" readonly/>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txnMatricula" class="col-sm-12 col-md-3 col-form-label">Valor de la matrícula</label>
                            <div class="col-sm-12 col-md-2">
                                <input type="text" step="0.01" style="text-align:right" class="form-control" id="txnMatricula" name="txnMatricula" value="0" readonly/>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txnCuota" class="col-sm-12 col-md-3 col-form-label">Valor de la cuota</label>
                            <div class="col-sm-12 col-md-2">
                                <input type="text" step="0.01" style="text-align:right" class="form-control" id="txnCuota" name="txnCuota" value="0" readonly/>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txnCertificacion" class="col-sm-12 col-md-3 col-form-label">Valor de la certificación</label>
                            <div class="col-sm-12 col-md-2">
                                <input type="text" step="0.01" style="text-align:right" class="form-control" id="txnCertificacion" name="txnCertificacion" value="0" readonly/>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="txnMora" class="col-sm-12 col-md-3 col-form-label">Porcentaje por Mora</label>
                            <div class="col-sm-12 col-md-2">
                                <input type="number" style="text-align:right" class="form-control" id="txnMora" name="txnMora" value="1" readonly/>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-auto col-md-12">
                        <!--Inicio del DIV de Módulos-->
                        <div class="form-group row">
                            <div class="col-sm-auto col-md-12">
                                <?php 
                                $nombreArchivoMOD = fxEscribeJsonModulo($Curso);
                                ?>
                                <div id="dvMOD">
                                    <table id="dgMOD" class="easyui-datagrid table"
                                        data-options="iconCls:'icon-edit', singleSelect:true, url:'<?php echo(trim($nombreArchivoMOD)); ?>', method:'get'">
                                        <thead>
                                            <tr>
                                                <th data-options="field:'curso', align:'left', hidden:true">Curso</th>
                                                <th data-options="field:'modulo', align:'left', hidden:true">CodModulo</th>
                                                <th data-options="field:'codDocente', align:'left', hidden:true">CodDocente</th>
                                                <th data-options="field:'plan', align:'left', hidden:true">Planificación</th>
                                                <th data-options="field:'numero', width:'10%', align:'center'">Módulo</th>
                                                <th data-options="field:'docente', width:'30%', align:'left'">Docente</th>
                                                <th data-options="field:'nombre', width:'30%', align:'left'">Nombre del módulo</th>
                                                <th data-options="field:'fechaIni', width:'15%', align:'left', 
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
                                                <th data-options="field:'fechaFin', width:'15%', align:'left', 
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
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Fin del DIV de Módulos-->
                </form>
            </div>
<?php	}
	}
?>
        </div>
    </div>
</div>
</body>
</html>

<script>
    window.onload = function() {llenaCurso();}

    function llenaCurso()
    {
        var datos = new FormData();
        var codCurso = document.getElementById('cboCurso').value;
        var msTexto ="";
        var mnIteracion = 1;
        var msCaracter = "";
        var mdHora;
        var i;
        datos.append('consCurso', codCurso);

        $.ajax({
            url: 'funciones/fxDatosExternos.php',
            type: 'post',
            data: datos,
            contentType: false,
            processData: false,
            success: function(respuesta) 
            {
                for (i=0; i<respuesta.length; i++)
				{
					msCaracter = respuesta.charAt(i);

					if (msCaracter == "~")
					{
						switch (mnIteracion)
						{
							case 1:
								document.getElementById("dtpFechaIni").value = msTexto;
								break;
							case 2:
								document.getElementById("dtpFechaFin").value = msTexto;
								break;
							case 3:
								document.getElementById("dtpHoraIni").value = msTexto;
								break;
							case 4:
								document.getElementById("dtpHoraFin").value = msTexto;
								break;
							case 5:
								document.getElementById("txtTipo").value = msTexto;
								break;
							case 6:
								document.getElementById("txtTurno").value = msTexto;
								break;
							case 7:
								document.getElementById("txtTipoAsistencia").value = msTexto;
								break;
							case 8:
								document.getElementById("txtDiasClase").value = msTexto;
								break;
							case 9:
								document.getElementById("txnValor").value = 'U$ ' + msTexto;
								break;
							case 10:
								document.getElementById("txnMatricula").value = 'U$ ' + msTexto;
								break;
							case 11:
								document.getElementById("txnCuota").value = 'U$ ' + msTexto;
								break;
							case 12:
								document.getElementById("txnCertificacion").value = 'U$ ' + msTexto;
								break;
							case 13:
								document.getElementById("txnMora").value = msTexto;
								break;
                            case 14:
                                $('#dgMOD').datagrid({url: msTexto});
                                break;
						}

						msTexto = "";
						mnIteracion++;
					}
					else
					{
						msTexto += msCaracter;
					}
				}

                llenaEtiquetas();
            }
        });
    }

    function llenaEtiquetas()
    {
        document.getElementById("lblFechaIni").innerHTML = fxFecha(document.getElementById("dtpFechaIni").value);
        document.getElementById("lblFechaFin").innerHTML = fxFecha(document.getElementById("dtpFechaFin").value);
        document.getElementById("lblHoraIni").innerHTML = fxHora(document.getElementById("dtpHoraIni").value);
        document.getElementById("lblHoraFin").innerHTML = fxHora(document.getElementById("dtpHoraFin").value);
    }

    function fxHora(dHora)
    {
        var mdHour = parseInt(dHora.substr(0,2));
	    var mdMinutes = parseInt(dHora.substr(3,2));
	    var mdSeconds = parseInt(dHora.substr(6,2));
        var msResultado = "";

        if (mdHour>12)
        {
            mdHora = mdHour-12;
            if (mdHora<10)
                msResultado = '0' + mdHora; 
            else
                msResultado = mdHora;
        }
        else 
        {
            if (mdHour<10)
                msResultado += '0'+ mdHour;
            else 
                msResultado += mdHour;
        }
        msResultado += ":"
        
        if (mdMinutes<10)
            msResultado += '0' + mdMinutes;
        else
            msResultado += mdMinutes;
        msResultado += " "

        if (mdHour<12)
            msResultado += 'am';
        else
            msResultado += 'pm';
        
        return msResultado;
    }

    function fxFecha(dFecha)
    {
	    var mdYear = parseInt(dFecha.substr(0,4));
	    var mdMonth = parseInt(dFecha.substr(5,2));
	    var mdDay = parseInt(dFecha.substr(8,2));

        switch (mdMonth)
        {
            case 1:
                return (mdDay<10?'0':'') + mdDay + '-Enero-' + mdYear;
            case 2:
                return (mdDay<10?'0':'') + mdDay + '-Febrero-' + mdYear;
            case 3:
                return (mdDay<10?'0':'') + mdDay + '-Marzo-' + mdYear;
            case 4:
                return (mdDay<10?'0':'') + mdDay + '-Abril-' + mdYear;
            case 5:
                return (mdDay<10?'0':'') + mdDay + '-Mayo-' + mdYear;
            case 6:
                return (mdDay<10?'0':'') + mdDay + '-Junio-' + mdYear;
            case 7:
                return (mdDay<10?'0':'') + mdDay + '-Julio-' + mdYear;
            case 8:
                return (mdDay<10?'0':'') + mdDay + '-Agosto-' + mdYear;
            case 9:
                return (mdDay<10?'0':'') + mdDay + '-Septiembre-' + mdYear;
            case 10:
                return (mdDay<10?'0':'') + mdDay + '-Octubre-' + mdYear;
            case 11:
                return (mdDay<10?'0':'') + mdDay + '-Noviembre-' + mdYear;
            case 12:
                return (mdDay<10?'0':'') + mdDay + '-Diciembre-' + mdYear;
        }
    } 
</script>
<?php
function fxEscribeJsonModulo($Curso)
{
	if ($Curso == "")
		$nombreArchivo = "CUR0000000A.json";
	else
		$nombreArchivo = $Curso . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$mDatos = fxDevuelveDetModulo($Curso);
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "w");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"modulo":"' . rtrim($Fila['MODULO_REL']) . '", ');
		fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
        fwrite($archivo, '"codDocente":"' . rtrim($Fila['DOCENTE_REL']) . '", ');
        fwrite($archivo, '"numero":"' . rtrim($Fila['NUMERO_021']) . '", ');
		fwrite($archivo, '"docente":"' . rtrim($Fila['NOMBRE_100']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_021']) . '", ');
		fwrite($archivo, '"fechaIni":"' . rtrim($Fila['FECHAINI_021']) . '", ');
        fwrite($archivo, '"fechaFin":"' . rtrim($Fila['FECHAFIN_021']) . '", ');
        fwrite($archivo, '"plan":"' . rtrim($Fila['PLAN']) . '"');

		if ($i == $numRegistros)
			fwrite($archivo, "}" . PHP_EOL);
		else
			fwrite($archivo, "}," . PHP_EOL);
	}
	fwrite($archivo, "]");
	fclose($archivo);

	/* cerrar el resulset */
	$mDatos->closeCursor();
	
	return($nombreArchivo);
}

function DevuelveFecha($Fecha)
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