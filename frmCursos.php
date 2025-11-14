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
		$PermisoUsuario = fxPermisoUsuario("frmCursos", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
			if (isset($_POST["cboCurso"]))
				$codCurso = $_POST["cboCurso"];
			else
				$codCurso = "";
		?>
		<div class="container">
        	<div id="DivContenido">
                <div class="row">
                    <div class="col-md-12 col-md-12">
                        <form id="frmCursos" name="frmCursos" method="post">
							<div class="form-group row">
								<label for="cboCurso" class="col-sm-12 col-md-1 form-label">Curso</label>
								<select class="form-control col-sm-12 col-md-10" id="cboCurso" name="cboCurso" onchange="this.form.submit()">
									<?php
										if (trim($_SESSION["gsDocente"]) != "" and $Administrador == 0)
										{
											$mDocente = $_SESSION["gsDocente"];
											$msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIAS ";
											$msConsulta .= "from KDSA020A, KDSA021A ";
											$msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
											$msConsulta .= "and KDSA021A.DOCENTE_REL = ? ";
											$msConsulta .= "order by KDSA020A.CURSO_REL desc";
											$mDatos = $m_cnx_MySQL->prepare($msConsulta);
											$mDatos->execute([$mDocente]);
										}
										else
										{
											$msConsulta = "select distinct KDSA020A.CURSO_REL, concat(NOMBRE_020, ' (', CONVOCATORIA_020, '/G', GRUPO_020, ')') as NOMBRE, HORAINI_020, HORAFIN_020, fxDevuelveDias(KDSA020A.CURSO_REL) as DIAS ";
											$msConsulta .= "from KDSA020A, KDSA021A ";
											$msConsulta .= "where ACTIVO_020 = 1 and KDSA020A.CURSO_REL = KDSA021A.CURSO_REL ";
											$msConsulta .= "order by KDSA020A.CURSO_REL desc";
											$mDatos = $m_cnx_MySQL->prepare($msConsulta);
											$mDatos->execute();
										}
										
										while ($Fila = $mDatos->fetch())
										{
											$Curso = rtrim($Fila["CURSO_REL"]);
											$Texto = rtrim($Fila["NOMBRE"]);
											
											if ($codCurso == "")
												$codCurso = $Curso;
											
											if ($codCurso == $Curso)
											{
												echo("<option value='" . $Curso . "' selected>" . $Texto . "</option>");
												$msHoraIni = date_create($Fila["HORAINI_020"]);
												$msHoraFin = date_create($Fila["HORAFIN_020"]);
												$msDias = $Fila["DIAS"];
												$msHorario = $msDias . " / De " . date_format($msHoraIni, 'h:i:s a') . " a " . date_format($msHoraFin, 'h:i:s a');
											}
											else
												echo("<option value='" . $Curso . "'>" . $Texto . "</option>");
										}
									?>
								</select>
							</div>

							<div class="form-group row">
								<label for="txtHorario" class="col-sm-12 col-md-1 col-form-label">Horario</label>
								<?php echo('<input type="text" class="form-control col-sm-12 col-md-10" id="txtHorario" name="txtHorario" value="' . $msHorario . '" readonly />'); ?>
								<div class="col-auto">
								</div>
							</div>

							<div class="row" style="margin-top: 1%">
								<div class="col-xs-auto col-md-12">
									<div class="form-group row">
										<div class="col-sm-auto col-md-12">
											<?php 
											$nombreArchivoMOD = fxEscribeJsonModulo($codCurso);
											?>
											<div id="dvMOD">
												<table id="dgMOD" class="easyui-datagrid table"
													data-options="iconCls:'icon-edit', singleSelect:true, url:'<?php echo(trim($nombreArchivoMOD)); ?>', method:'get'">
													<thead>
														<tr>
															<th data-options="field:'curso', align:'left', hidden:true">Curso</th>
															<th data-options="field:'modulo', align:'left', hidden:true">CodModulo</th>
															<th data-options="field:'codDocente', align:'left', hidden:true">CodDocente</th>
															<th data-options="field:'numero', width:'10%', align:'center'">Módulo</th>
															<th data-options="field:'docente', width:'30%', align:'left'">Docente</th>
															<th data-options="field:'nombre', width:'40%', align:'left'">Nombre del módulo</th>
															<th data-options="field:'fechaIni', width:'10%', align:'center', 
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
															<th data-options="field:'fechaFin', width:'10%', align:'center', 
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
							</div>
                        </form>
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>
</body>
</html>

<?php
function fxEscribeJsonModulo($msCurso)
{
	$Administrador = fxVerificaAdministrador();
	
	if ($msCurso == "")
		$nombreArchivo = "CUR0000000A.json";
	else
		$nombreArchivo = $msCurso . "A.json";

	if (file_exists($nombreArchivo))
		unlink($nombreArchivo);
	
	//Escribe el Json
	$m_cnx_MySQL = fxAbrirConexion();
	if (trim($_SESSION["gsDocente"]) != "" and $Administrador == 0)
	{
		$mDocente = $_SESSION["gsDocente"];
		$msConsulta = "select KDSA021A.MODULO_REL, CURSO_REL, KDSA021A.DOCENTE_REL, NUMERO_021, NOMBRE_021, NOMBRE_100, FECHAINI_021, FECHAFIN_021 ";
		$msConsulta .= "from KDSA021A, KDSA100A where KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL and CURSO_REL = '" . trim($msCurso) . "' ";
        $msConsulta .= "and KDSA021A.DOCENTE_REL = ? ";
		$msConsulta .= "order by NUMERO_021, MODULO_REL";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$mDocente]);
	}
	else
	{
		$msConsulta = "select KDSA021A.MODULO_REL, CURSO_REL, KDSA021A.DOCENTE_REL, NUMERO_021, NOMBRE_021, NOMBRE_100, FECHAINI_021, FECHAFIN_021 ";
		$msConsulta .= "from KDSA021A, KDSA100A where KDSA021A.DOCENTE_REL = KDSA100A.DOCENTE_REL and CURSO_REL = '" . trim($msCurso) . "' ";
		$msConsulta .= "order by NUMERO_021, MODULO_REL";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute();
	}
	
	$numRegistros = $mDatos->rowCount();

	$archivo = fopen($nombreArchivo, "a");
	
	fwrite($archivo, "[" . PHP_EOL);
	
	for ($i = 1; $i <= $numRegistros; $i++)
	{
		$Fila = $mDatos->fetch();
		fwrite($archivo, "{");
		fwrite($archivo, '"modulo":"' . rtrim($Fila['MODULO_REL']) . '", ');
		fwrite($archivo, '"curso":"' . rtrim($Fila['CURSO_REL']) . '", ');
        fwrite($archivo, '"codDocente":"' . rtrim($Fila['DOCENTE_REL']) . '", ');
		fwrite($archivo, '"docente":"' . rtrim($Fila['NOMBRE_100']) . '", ');
		fwrite($archivo, '"numero":"' . rtrim($Fila['NUMERO_021']) . '", ');
		fwrite($archivo, '"nombre":"' . rtrim($Fila['NOMBRE_021']) . '", ');
		fwrite($archivo, '"fechaIni":"' . rtrim($Fila['FECHAINI_021']) . '", ');
        fwrite($archivo, '"fechaFin":"' . rtrim($Fila['FECHAFIN_021']) . '"');

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