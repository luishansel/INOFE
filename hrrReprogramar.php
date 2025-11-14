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
	require_once ("funciones/fxReprogramacion.php");
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
		$PermisoUsuario = fxPermisoUsuario("hrrReprogramar", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
		if ($Administrador == 0 and $PermisoUsuario == 0)
		{?>
        <div class="container text-center">
            <div id="DivContenido">
                <img src="imagenes/errordeacceso.png"/>
            </div>
        </div>
		<?php }
		else
		{
			if (isset($_POST["cboCurso"]))
			{
				$codCurso = $_POST["cboCurso"];
				$FechaRep = $_POST["dtpFechaRep"];

				if (isset($_POST["Aceptar"]))
				{
					fxReprogramar($codCurso, $FechaRep);
					$FechaRep = date("Y-m-d", time());
					?>
					<script>$.messager.alert('INOFE','Curso reprogramado.','info');</script>
					<?php
				}
			}
			else
			{
				$codCurso = "";
				$FechaRep = date("Y-m-d", time());
			}
			?>
			<div class="container">
				<div id="DivContenido">
					<div class="row">
						<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
							<form name="hrrReprogramar" action="hrrReprogramar.php" method="post">
								<div class="form-group row">
									<label for="cboCurso" class="col-sm-12 col-md-2 col-form-label">Curso</label>
									<div class="col-sm-12 col-md-7">
										<select class="form-control" id="cboCurso" name="cboCurso" onchange = "this.form.submit()">
											<?php
												$msConsulta = "select CURSO_REL, NOMBRE_020, GRUPO_020, CONVOCATORIA_020, FECHAINI_020, FECHAFIN_020 from KDSA020A where ACTIVO_020 = 1 order by NOMBRE_020";
												$m_cnx_MySQL = fxAbrirConexion();
												$mDatos = $m_cnx_MySQL->prepare($msConsulta);
												$mDatos->execute();
												while ($Fila = $mDatos->fetch())
												{
													$Valor = rtrim($Fila["CURSO_REL"]);
													$Texto = rtrim($Fila["NOMBRE_020"]) . " (" . rtrim($Fila["CONVOCATORIA_020"]) . " / G" . rtrim($Fila["GRUPO_020"]) . ")";
													
													if ($codCurso == "")
													{
														echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
														$FechaIni = $Fila["FECHAINI_020"];
														$FechaFin = $Fila["FECHAFIN_020"];
														$codCurso = $Valor;
													}
													else
													{
														if ($codCurso == $Valor)
														{
															echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
															$FechaIni = $Fila["FECHAINI_020"];
															$FechaFin = $Fila["FECHAFIN_020"];
														}
														else
															echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
													}
												}
											?>
										</select>
									</div>
								</div>
								<div class = "form-group row">
									<label for="dtpFechaIni" class="col-sm-12 col-md-2 col-form-label">Fecha inicial</label>
									<div class="col-sm-12 col-md-2">
										<?php echo('<input type="date" class="form-control" id="dtpFechaIni" name="dtpFechaIni" value="' . $FechaIni . '" readonly />');?>
									</div>
								</div>
								
								<div class = "form-group row">
								<label for="dtpFechaFin" class="col-sm-12 col-md-2 col-form-label">Fecha final</label>
									<div class="col-sm-12 col-md-2">
										<?php echo('<input type="date" class="form-control" id="dtpFechaFin" name="dtpFechaFin" value="' . $FechaFin . '" readonly />');?>
									</div>
								</div>

								<div class = "form-group row">
									<label for="dtpFechaRep" class="col-sm-12 col-md-2 col-form-label">Reprogramación</label>
									<div class="col-sm-12 col-md-2">
										<?php echo('<input type="date" class="form-control" id="dtpFechaRep" name="dtpFechaRep" value="' . $FechaRep . '" />');?>
									</div>
								</div>
								
								<div class = "row">
									<div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
										<input type="submit" id="Aceptar" name="Aceptar" value="Aceptar" class="btn btn-warning" />
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
	?>
</body>
</html>