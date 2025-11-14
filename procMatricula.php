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
	require_once ("funciones/fxMatricula.php");
	require_once ("funciones/fxEstudiantes.php");
	require_once ("funciones/fxCursos.php");
	require_once ("funciones/fxCobrosIndividuales.php");

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
		$PermisoUsuario = fxPermisoUsuario("procMatricula");
		
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
			if (isset($_POST["Guardar"]))
			{
				$Codigo = $_POST["txtCodMatricula"];
				$Estudiante = $_POST["cboEstudiante"];
				$Curso = $_POST["cboCurso"];
				$TipoAsistencia = $_POST["cboTipoAsistencia"];
				$Fecha = $_POST["dtpFecha"];
				$Descuento = $_POST["txnDescuento"];
				$Motivo = $_POST["txtMotivo"];
				$Medio = $_POST["txtMedio"];
				$Becado = 0; //No hay becados
				$FuenteIngreso = $_POST["optFuenteIngreso"];
				$PrimeraVez = $_POST["optPrimeraVez"];
				if (isset($_POST["chkIdentidad"]))
					$Identidad = 1;
				else
					$Identidad = 0;
				if (isset($_POST["chkAcademico"]))
					$Academico = 1;
				else
					$Academico = 0;
				$CertDigital = $_POST["optCertDigital"];
				//$Estado = $_POST["cboEstado"]; 20230503 Ahora es manejado por procEstadoMatricula.php
				$Estado = 0;

				{
					if ($Codigo == "")
					{
						$msConsulta = "select MATRICULA_REL from KDSA030A where ESTUDIANTE_REL = ? and CURSO_REL = ? and ESTADO_030 <> 4";
						$mDatos = $m_cnx_MySQL->prepare($msConsulta);
						$mDatos->execute([$Estudiante, $Curso]);
						
						if ($mDatos->rowCount() == 0)
						{
							$Codigo = fxGuardarMatricula ($Estudiante, $Curso, $TipoAsistencia, $Fecha, $Descuento, $Motivo, $Medio, $FuenteIngreso, $PrimeraVez, 0, "", 0, $Identidad, $Academico, $Estado, $CertDigital);
							fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA030A", $Codigo, "", "Agregar");
							
							if ($Becado == 0)
							{
								//Ingresa automáticamente los Cobros individuales
								$msConsulta = "select COBRO_REL from KDSA050A where CURSO_REL = ? and TIPO_050 in (0, 2, 5) and ANULADO_050 = 0";
								$mCobros = $m_cnx_MySQL->prepare($msConsulta);
								$mCobros->execute([$Curso]);
		
								while ($Fila = $mCobros->fetch())
								{
									$Cobro = $Fila["COBRO_REL"];
									fxGuardarCobroIndividual ($Cobro, $Codigo);
									fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA051A", $Cobro, $Codigo, "Agregar");
								}
							}

							//Crea el usuario para el ambiente de Estudiantes
							$msConsulta = "select NOMBRES_010, APELLIDOS_010, CORREO_010 from KDSA010A where ESTUDIANTE_REL = ?";
							$mUsuario = $m_cnx_MySQL->prepare($msConsulta);
							$mUsuario->execute([$Estudiante]);
							$Fila = $mUsuario->fetch();
							$msNombre = trim($Fila["NOMBRES_010"]) . " " . trim($Fila["APELLIDOS_010"]);
							$msCorreo = trim($Fila["CORREO_010"]);
							fxGuardarUsuario($Codigo, $msNombre, $msCorreo, $Codigo, 0, 0);
						}
						else
						{
							?><script>$.messager.alert('KDSA',$('#cboEstudiante option:selected').text() + ' ya fue matriculado en ' + $('#cboCurso option:selected').text(),'warning');</script><?php
						}
					}
					else
					{
						fxModificarMatricula ($Codigo, $Estudiante, $Curso, $TipoAsistencia, $Fecha, $Descuento, $Motivo, $Medio, $FuenteIngreso, $PrimeraVez, 0, "", 0, $Identidad, $Academico, $CertDigital);
						fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA030A", $Codigo, "", "Modificar");
					}
				}
				
				?><meta http-equiv="Refresh" content="0;url=gridMatricula.php"/><?php
			}
			else
			{
				if (isset($_POST["mAccion"]))
					$mAccion = $_POST["mAccion"];
				else
					$mAccion = 0;
				
				if ($mAccion == 0)
				{
					if (isset($_POST["mCodigo"]))
						$Codigo = $_POST["mCodigo"];
					else
						$Codigo = "";
				}
				else
					$Codigo = "";

				$RecordSet = fxDevuelveMatricula(0, $Codigo);
				$Fila = $RecordSet->fetch();
				if ($Codigo != "")
				{
					$Estudiante = $Fila["ESTUDIANTE_REL"];
					$Curso = $Fila["CURSO_REL"];
					$TipoAsistencia = $Fila["TIPOASISTENCIA_030"];
					$Fecha = $Fila["FECHA_030"];
					$Descuento = $Fila["DESCUENTO_030"];
					$Motivo = $Fila["MOTIVO_030"];
					$Medio = $Fila["MEDIO_030"];
					$FuenteIngreso = $Fila["FUENTEINGRESO_030"];
					$PrimeraVez = $Fila["PRIMERAVEZ_030"];
					$Identidad = $Fila["DOCIDENTIDAD_030"];
					$Academico = $Fila["DOCACADEMICO_030"];
					$CertDigital = $Fila["CERTDIGITAL_030"];
					$Estado = $Fila["ESTADO_030"];
				}
				else
				{
					if (isset($_POST["mEstudiante"])){
						$Estudiante = $_POST["mEstudiante"];
						$msConsulta = "select * from KDSA011A where DESC_011 in ('Cédula', 'Partida de nacimiento') and ESTUDIANTE_REL = ?";
						$mDatos = $m_cnx_MySQL->prepare($msConsulta);
						$mDatos->execute([$Estudiante]);
						$mnRegistros = $mDatos->rowCount();
						if ($mnRegistros > 0)
							$Identidad = 1;
						else
							$Identidad = 0;

						$msConsulta = "select * from KDSA011A where DESC_011 not in ('Cédula', 'Partida de nacimiento') and ESTUDIANTE_REL = ?";
						$mDatos = $m_cnx_MySQL->prepare($msConsulta);
						$mDatos->execute([$Estudiante]);
						$mnRegistros = $mDatos->rowCount();
						if ($mnRegistros > 0)
							$Academico = 1;
						else
							$Academico = 0;
					}
					else{
						$Estudiante = "";
						$Identidad = 0;
						$Academico = 0;
					}
					$Curso = "";
					$TipoAsistencia = "";
					$Fecha = "";
					$Descuento = 0;
					$Motivo = "";
					$Medio = "";
					$FuenteIngreso = 0;
					$PrimeraVez = 0;
					$CertDigital = 0;
					$Estado = 0;
				}
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Matrícula de estudiantes</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-1">
				<form id="procMatricula" name="procMatricula" action="procMatricula.php" method="post" onsubmit="return verificarFormulario()">
                	<div class = "form-group row">
                        <label for="txtCodMatricula" class="col-sm-12 col-md-3 col-form-label">Código de la Matrícula</label>
                        <div class="col-sm-12 col-md-3">
                        <?php echo('<input type="text" class="form-control" id="txtCodMatricula" name="txtCodMatricula" value="' . $Codigo . '" readonly />'); ?>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="cboEstudiante" class="col-sm-12 col-md-3 col-form-label">Estudiante</label>
                        <div class="col-sm-12 col-md-7">
                            <select class="form-control" id="cboEstudiante" name="cboEstudiante">
                                <?php
                                    $msConsulta = "select ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010 from KDSA010A order by APELLIDOS_010, NOMBRES_010 desc";
									$mDatos = $m_cnx_MySQL->prepare($msConsulta);
									$mDatos->execute();

                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = trim($Fila["ESTUDIANTE_REL"]);
                                        $Texto = trim($Fila["APELLIDOS_010"]) . ", " . trim($Fila["NOMBRES_010"]);

										if ($Codigo == "" and $Estudiante == "")
										{
											echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
										}
										else
										{
											if ($Estudiante == "")
											{
												echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
												$Estudiante = $Valor;
											}
											else
											{
												if ($Estudiante == $Valor)
													echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
												else
													echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
											}
										}
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="cboCurso" class="col-sm-12 col-md-3 col-form-label">Curso</label>
                        <div class="col-sm-12 col-md-7">
                            <select class="form-control" id="cboCurso" name="cboCurso" onchange="llenaDisponible(this.value)">
                                <?php
									if ($Codigo == "")
										$msConsulta = "select CURSO_REL, NOMBRE_020, GRUPO_020, CONVOCATORIA_020, ACTIVO_020 from KDSA020A where ACTIVO_020 = 1 and DATEDIFF(CURRENT_DATE, FECHAINI_020) < 15 order by NOMBRE_020";
									else
										$msConsulta = "select CURSO_REL, NOMBRE_020, GRUPO_020, CONVOCATORIA_020, ACTIVO_020 from KDSA020A order by NOMBRE_020";
                                    $mDatos = $m_cnx_MySQL->prepare($msConsulta);
									$mDatos->execute();
                                    while ($Fila = $mDatos->fetch())
                                    {
                                        $Valor = rtrim($Fila["CURSO_REL"]);
                                        $Texto = rtrim($Fila["NOMBRE_020"]) . " (" . rtrim($Fila["CONVOCATORIA_020"]) . " / G" . rtrim($Fila["GRUPO_020"]) . ")";
										$Activo = $Fila["ACTIVO_020"];
										if ($Maximo == -1) {$Maximo = $Fila["MAXIMO_020"];}
										
										if ($Codigo == "")
										{
											if ($Activo == 1)
												echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
										}
										else
										{
											if ($Curso == "")
											{
												echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
												$Curso = $Valor;
											}
											else
											{
												if ($Curso == $Valor)
													echo("<option value='" . $Valor . "' selected>" . $Texto . "</option>");
												else
													echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
											}
										}
                                    }
                                ?>
                            </select>
							<input type="hidden" class="form-control" id="txnDisponible" name="txnDisponible" value="0" />
                        </div>
                    </div>

					<div class="form-group row">
						<label for="cboTipoAsistencia" class="col-sm-12 col-md-3 col-form-label">Tipo de asistencia</label>
						<div class="col-sm-12 col-md-3">
							<select class="form-control" id="cboTipoAsistencia" name="cboTipoAsistencia">
								<?php
									if ($TipoAsistencia == 0 or $Codigo == "")
										echo("<option value='0' selected>Presencial</option>");
									else
										echo("<option value='0'>Presencial</option>");
									
									if ($TipoAsistencia == 1)
										echo("<option value='1' selected>Virtual</option>");
									else
										echo("<option value='1'>Virtual</option>");

									if ($TipoAsistencia == 2)
										echo("<option value='2' selected>On-line</option>");
									else
										echo("<option value='2'>On-line</option>");
								?>
							</select>
						</div>
					</div>
                    
                    <div class = "form-group row">
						<label for="dtpFecha" class="col-sm-12 col-md-3 col-form-label">Fecha</label>
                        <div class="col-sm-12 col-md-3">
						<?php
							if ($Codigo == "")
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . date("Y-m-d") . '" />');
							else
								echo('<input type="date" class="form-control" id="dtpFecha" name="dtpFecha" value="' . $Fecha . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="txnDescuento" class="col-sm-12 col-md-3 col-form-label">Descuento</label>
                        <div class="col-sm-12 col-md-3">
                        <?php
							if ($Codigo == "")
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnDescuento" name="txnDescuento" value="0" />');
							else
								echo('<input type="number" step="0.01" style="text-align:right" class="form-control" id="txnDescuento" name="txnDescuento" value="' . $Descuento . '" />');
						?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtMotivo" class="col-sm-12 col-md-3 col-form-label">Motivo del descuento</label>
                        <div class="col-sm-12 col-md-7">
							<?php echo('<input type="text" class="form-control" id="txtMotivo" name="txtMotivo" value="' . $Motivo . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
						<label for="txtMedio" class="col-sm-12 col-md-3 col-form-label">Medio por el cual se enteró</label>
                        <div class="col-sm-12 col-md-7">
							<?php echo('<input type="text" class="form-control" id="txtMedio" name="txtMedio" value="' . $Medio . '" />'); ?>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optFuenteIngreso" class="col-sm-12 col-md-3 form-label">Fuente de ingreso</label>
                        <div class="col-sm-12 col-md-5">
                            <div class = "radio">
                            <?php
                                if ($FuenteIngreso == 0)
                                    echo('<input type="radio" id="optFuente1" name="optFuenteIngreso" value="0" checked /> Propios <input type="radio" id="optFuente2" name="optFuenteIngreso" value="1" /> Empresa');

                                if ($FuenteIngreso == 1)
                                    echo('<input type="radio" id="optFuente1" name="optFuenteIngreso" value="0" /> Propios <input type="radio" id="optFuente2" name="optFuenteIngreso" value="1" checked /> Empresa');
/*LHVG 20240227
								if ($FuenteIngreso == 2)
                                    echo('<input type="radio" id="optFuente1" name="optFuenteIngreso" value="0" /> Propios <input type="radio" id="optFuente2" name="optFuenteIngreso" value="1" /> Empresa <input type="radio" id="optFuente3" name="optFuenteIngreso" value="2" checked /> Papás <input type="radio" id="optFuente4" name="optFuenteIngreso" value="3" /> Familiar');
								
								if ($FuenteIngreso == 3)
                                    echo('<input type="radio" id="optFuente1" name="optFuenteIngreso" value="0" /> Propios <input type="radio" id="optFuente2" name="optFuenteIngreso" value="1" /> Empresa <input type="radio" id="optFuente3" name="optFuenteIngreso" value="2" /> Papás <input type="radio" id="optFuente4" name="optFuenteIngreso" value="3" checked /> Familiar');
*/							
							?>
                            </div>
                        </div>
                    </div>
                    
                    <div class = "form-group row">
                        <label for="optPrimeraVez" class="col-sm-12 col-md-3 form-label">Primera vez en INOFE</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($PrimeraVez == 1)
                                {
                                    echo('<input type="radio" id="OptPrimero1" name="optPrimeraVez" value="0" /> No <input type="radio" id="OptPrimero2" name="optPrimeraVez" value="1" checked="checked" /> Si');
                                }
                                else
                                {
                                    echo('<input type="radio" id="OptPrimero1" name="optPrimeraVez" value="0" checked="checked" /> No <input type="radio" id="OptPrimero2" name="optPrimeraVez" value="1" /> Si');
                                }
                            ?>
                            </div>
                        </div>
                    </div>

					<div class = "form-group row">
                        <label class="col-sm-12 col-md-3 form-label">Documentos entregados</label>
                        <div class="col-sm-12 col-md-8">
                            <div class = "form-check" style="display: inline-block">
                            <?php
                                if ($Identidad == 1)
                                    echo('<input class="form-check-input" type="checkbox" value="1" name="chkIdentidad" id="chkIdentidad" checked><label class="form-check-label" for="chkIdentidad">Identidad</label>');
                                else
									echo('<input class="form-check-input" type="checkbox" value="0" name="chkIdentidad" id="chkIdentidad"><label class="form-check-label" for="chkIdentidad">Identidad</label>');
                            ?>
                            </div>
							<div class = "form-check" style="display: inline-block">
                            <?php
                                if ($Academico == 1)
                                    echo('<input class="form-check-input" type="checkbox" value="1" name="chkAcademico" id="chkAcademico" checked><label class="form-check-label" for="chkAcademico">Académico</label>');
                                else
									echo('<input class="form-check-input" type="checkbox" value="0" name="chkAcademico" id="chkAcademico"><label class="form-check-label" for="chkAcademico">Académico</label>');
                            ?>
                            </div>

							<div id="btnPopup">Ver documentos</div>
							<div id="divPopup">
								<?php
									$msConsulta = "select DESC_011, RUTA_011 from KDSA011A where ESTUDIANTE_REL = ?";
									$rsDocumentos = $m_cnx_MySQL->prepare($msConsulta);
									$rsDocumentos->execute([$Estudiante]);
                                	$Registros = $rsDocumentos->rowCount();

									if ($Registros == 0)
									{
										echo('<div>');
										echo('<label class="col-form-label">[Sin documentos]</label>');
										echo('</div>');
									}
									else
									{
										echo('<table><tr>');
										while ($fDocumentos = $rsDocumentos->fetch())
										{
											
											$msRuta = $fDocumentos["RUTA_011"];
											echo('<td>');
											echo('<img src="' . trim($msRuta) . '" width="100%" height="150px" /></a>');
											echo('</td>');
										}
										echo('</tr></table>');
									}
								?>
							</div>
                        </div>
                    </div>
                    
					<div class = "form-group row">
                        <label for="optCertDigital" class="col-sm-12 col-md-3 form-label">Certificación digital</label>
                        <div class="col-sm-12 col-md-4">
                            <div class = "radio">
                            <?php
                                if ($CertDigital == 1)
                                    echo('<input type="radio" id="OptCertDigital1" name="optCertDigital" value="0" /> No <input type="radio" id="OptCertDigital2" name="optCertDigital" value="1" checked="checked" /> Si');
                                else
                                    echo('<input type="radio" id="OptCertDigital1" name="optCertDigital" value="0" checked="checked" /> No <input type="radio" id="OptCertDigital2" name="optCertDigital" value="1" /> Si');
                            ?>
                            </div>
                        </div>
                    </div>

                    <div class = "form-group row">
                        <label for="cboEstado" class="col-sm-12 col-md-3 form-label">Estado</label>
                        <div class="col-sm-12 col-md-3">
                            <select class="form-control" id="cboEstado" name="cboEstado" readonly disabled>
							<!--select class="form-control" id="cboEstado" name="cboEstado"-->
                                <?php
									if ($Estado == 0)
										echo("<option value='0' selected >Activo</option>");
									else
										echo("<option value='0' >Activo</option>");

									if ($Estado == 1)
										echo("<option value='1' selected >Inactivo</option>");
									else
										echo("<option value='1' >Inactivo</option>");

									if ($Estado == 2)
										echo("<option value='2' selected >Deserción</option>");
									else
										echo("<option value='2' >Deserción</option>");

									if ($Estado == 3)
										echo("<option value='3' selected >Certificado</option>");
									else
										echo("<option value='3' >Certificado</option>");

									if ($Estado == 4)
										echo("<option value='4' selected >Anulado</option>");
									else
										echo("<option value='4' >Anulado</option>");

									if ($Estado == 5)
										echo("<option value='5' selected >Baja</option>"); //Estudiantes que nunca iniciaron el curso
									else
										echo("<option value='5' >Baja</option>");
                                ?>
                            </select>
                        </div>
                    </div>
                    
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12 col-md-offset-3">
                        	<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridMatricula.php';"/>
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
		var curso = document.getElementById('cboCurso').value;
		llenaDisponible(curso);
	}

	function llenaDisponible (curso)
	{
		var datos = new FormData();
		datos.append('maximoCurso', curso);

		$.ajax({
			url: 'funciones/fxDatosExternos.php',
			type: 'post',
			data: datos,
			contentType: false,
			processData: false,
			success: function(response){
				document.getElementById('txnDisponible').value = response;
			}
		})
	}

	$("#btnPopup").on("mouseenter",
		function() {
			$("#divPopup").addClass("visible");
		}
	);

	$("#btnPopup").on("mouseleave",
		function() {
			$("#divPopup").removeClass("visible");
		}
	);

	function verificarFormulario()
	{
		var administrador = <?php echo($Administrador); ?>;

		if (document.getElementById('txtCodMatricula').value == "")
		{
			if (document.getElementById('txnDisponible').value == 0 && administrador == 0)
			{
				$.messager.alert('KDSA','Se alcanzó la cantidad máxima de alumnos para este curso.','warning');
				return false;
			}
		}

		if(document.getElementById('txnDescuento').value>0 && document.getElementById('txtMotivo').value=="")
		{
			$.messager.alert('KDSA','Falta el Motivo del Descuento.','warning');
			return false;
		}
	
		return true;
	}
</script>