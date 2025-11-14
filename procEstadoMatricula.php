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
		$PermisoUsuario = fxPermisoUsuario("procEstadoMatricula");
		
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
			if (isset($_POST['KDSA']))
				$msCurso = $_POST['KDSA'];
			
			if (isset($_POST["Guardar"]))
			{
				
				{
					$txtCurso = $_POST["txtCurso"];
					$msConsulta = "select MATRICULA_REL, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, ESTADO_030 from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = ? order by MATRICULA_REL";
					$RecordSet = $m_cnx_MySQL->prepare($msConsulta);
					$RecordSet->execute([$txtCurso]);

					while ($Fila = $RecordSet->fetch())
					{
						$codMatricula = $Fila['MATRICULA_REL'];
						$valEstado = "cbo" . $Fila['MATRICULA_REL'];
						$cboEstado = $_POST[$valEstado];

						$mnRetorno = fxCambiaEstadoMatricula ($codMatricula, $cboEstado);
						if ($mnRetorno == 1)
						{
							switch(intval($cboEstado))
							{
								case 0:
									$msEstado = "Estado Activo";
									break;
								case 1:
									$msEstado = "Estado Inactivo";
									break;
								case 2:
									$msEstado = "Estado Deserción";
									break;
								case 3:
									$msEstado = "Estado Certificado";
									break;
								case 4:
									$msEstado = "Estado Anulado";
									break;
								case 5:
									$msEstado = "Estado De baja";
							}
							fxAgregarBitacora ($_SESSION["gsUsuario"], "KDSA030A", $codMatricula, "", $msEstado);
						}
					}
				}
				
					
				?><meta http-equiv="Refresh" content="0;url=gridEstadoMatricula.php"/><?php
			}
			else
			{
				$msConsulta = "select MATRICULA_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020, concat(APELLIDOS_010, ', ', NOMBRES_010) as ESTUDIANTE, ESTADO_030 from KDSA030A, KDSA020A, KDSA010A where KDSA030A.CURSO_REL = KDSA020A.CURSO_REL and KDSA030A.ESTUDIANTE_REL = KDSA010A.ESTUDIANTE_REL and KDSA030A.CURSO_REL = ? order by MATRICULA_REL";
				$RecordSet = $m_cnx_MySQL->prepare($msConsulta);
				$RecordSet->execute([$msCurso]);
	?>
    <div class="container text-left">
    	<div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Estado de la matrícula</strong></div>
				</div>
			</div>

			<div class = "row">
                <div class="col-xs-12 col-xs-offset-none col-md-10 col-md-offset-1">
				<form id="procMatricula" name="procMatricula" action="procEstadoMatricula.php" method="post">
					<div class = "row">
                    	<div class="col-auto col-xs-offset-none col-md-12">
                        	<input type="submit" id="Guardar" name="Guardar" value="Guardar" class="btn btn-warning" />
                            <input type="button" id="Cancelar" name="Cancelar" value="Cancelar" class="btn btn-warning" onclick="location.href='gridEstadoMatricula.php';"/>
                        </div>
                    </div>

					<?php
						$mnConteo = 0;
						$mnInicial = 0;
						$mnActivo = 0;
						$mnInactivo = 0;
						$mnDesercion = 0;
						$mnCertificado = 0;
						$mnAnulado = 0;
						$mnBaja = 0;
						$msNombreCurso = "";

						while ($Fila = $RecordSet->fetch())
						{
							$msMatricula = $Fila['MATRICULA_REL'];
							$msNombre = $Fila['ESTUDIANTE'];
							$mnEstado = $Fila['ESTADO_030'];

							if ($msNombreCurso == "")
							{
								$msNombreCurso = $Fila['NOMBRE_020'] . " (" . $Fila['CONVOCATORIA_020'] . "/G" . $Fila['GRUPO_020'] . ")" ;
								echo('<div class = "form-group row" style="background-color: rgb(255,255,255)">');
								echo('<label class="col-sm-12 col-md-11 col-form-label"><strong>' . $msNombreCurso . '</strong></label>');
								echo('</div>');
							}

							echo('<input type="text" class="form-control" id="txtCurso" name="txtCurso" value="' . $msCurso . '" style="display:none" />');

							if ($mnConteo % 2 == 0)
								echo('<div class = "form-group row">');
							else
								echo('<div class = "form-group row" style="background-color: rgba(0,0,0,0.1)">');

							echo('<label class="col-sm-12 col-md-2 col-form-label">' . $msMatricula . '</label>');

							echo('<label class="col-sm-12 col-md-6 col-form-label">' . $msNombre . '</label>');

							echo('<div class="col-sm-12 col-md-2">');
								echo('<select class="form-control" id="cbo' . $msMatricula . '" name="cbo' . $msMatricula . '">');

									if ($mnEstado == 0)
									{
										echo("<option value='0' selected >Activo</option>");
										$mnActivo++;
										$mnInicial++;
									}
									else
										echo("<option value='0' >Activo</option>");

									if ($mnEstado == 1)
									{
										echo("<option value='1' selected >Inactivo</option>");
										$mnInactivo++;
									}
									else
										echo("<option value='1' >Inactivo</option>");

									if ($mnEstado == 2)
									{
										echo("<option value='2' selected >Deserción</option>");
										$mnDesercion++;
										$mnInicial++;
									}
									else
										echo("<option value='2' >Deserción</option>");

									if ($mnEstado == 3)
									{
										echo("<option value='3' selected >Certificado</option>");
										$mnCertificado++;
										$mnInicial++;
									}
									else
										echo("<option value='3' >Certificado</option>");

									if ($mnEstado == 4)
									{
										echo("<option value='4' selected >Anulado</option>");
										$mnAnulado++;
									}
									else
										echo("<option value='4' >Anulado</option>");
			
									if ($mnEstado == 5)
									{
										echo("<option value='5' selected >Baja</option>"); //Estudiantes que nunca iniciaron el curso
										$mnBaja++;
									}
									else
										echo("<option value='5' >Baja</option>");
									
								echo('</select>');
							echo('</div>');
							echo('</div>');

							$mnConteo++;
						}

						echo('<div class = "form-group row" style="background-color: rgb(255,255,255)">');
						echo('<label class="col-sm-12 col-md-11 col-form-label"><strong>');
						echo('Estudiantes: ' . $mnConteo);
						if ($mnInicial > 0)
							echo(' / Inicial: ' . $mnInicial);
						if ($mnActivo > 0)
							echo(' / Activos: ' . $mnActivo);
						if ($mnInactivo > 0)
							echo(' / Inactivos: ' . $mnInactivo);
						if ($mnDesercion > 0)
							echo(' / Deserciones: ' . $mnDesercion);
						if ($mnCertificado > 0)
							echo(' / Certificados: ' . $mnCertificado);
						if ($mnAnulado > 0)
							echo(' / Anulados: ' . $mnAnulado);
						if ($mnBaja > 0)
							echo(' / Bajas: ' . $mnBaja);
						echo('</strong></label>');
						echo('</div>');
					?>
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