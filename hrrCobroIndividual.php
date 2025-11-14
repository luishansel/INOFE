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
		$PermisoUsuario = fxPermisoUsuario("hrrCobroIndividual", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				if (isset($_POST["Aceptar"]))
				{
					$msCurso = $_POST["cboCurso"];
					$msConsulta = "select NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA020A where CURSO_REL = ?";
					$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
					$mAuxiliar->execute([$msCurso]);
					$fAuxiliar = $mAuxiliar->fetch();
					$msNombreCurso = $fAuxiliar["NOMBRE_020"] . " (" . $fAuxiliar["CONVOCATORIA_020"] . "/G" . $fAuxiliar["GRUPO_020"] . ")";

					$msConsulta = "select COBRO_REL, MONTO_050 from KDSA050A where CURSO_REL = ? and TIPO_050 in (0, 2, 3, 4)";
					$mCobros = $m_cnx_MySQL->prepare($msConsulta);
					$mCobros->execute([$msCurso]);

					while ($fCobros = $mCobros->fetch())
					{
						$msCobro = trim($fCobros["COBRO_REL"]);
						$mnMonto = $fCobros["MONTO_050"];
						$msConsulta = "select MATRICULA_REL from KDSA030A where CURSO_REL = ? and ESTADO_030 in (0,1,2)";
						$mEstudiantes = $m_cnx_MySQL->prepare($msConsulta);
						$mEstudiantes->execute([$msCurso]);

						while ($fEstudiantes = $mEstudiantes->fetch())
						{
							$msMatricula = trim($fEstudiantes["MATRICULA_REL"]);
							$msConsulta = "select ADEUDADO_051 from KDSA051A where COBRO_REL = ? and MATRICULA_REL = ?";
							$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
							$mAuxiliar->execute([$msCurso, $msMatricula]);
							$mnRegistros = $mAuxiliar->rowCount();

							if ($mnRegistros == 0) //El Cobro no está asignado
							{
								$msConsulta = "insert into KDSA051A (COBRO_REL, MATRICULA_REL, ADEUDADO_051, ABONADO_051, PAGADO_051, EXONERADO_051, ";
								$msConsulta .= "ANULADO_051) values (?, ?, ?, ?, ?, ?, ?)";
								$mAuxiliar = $m_cnx_MySQL->prepare($msConsulta);
								$mAuxiliar->execute([$msCobro, $msMatricula, $mnMonto, 0, 0, 0, 0]);
							}
						}
					}

					?>
						<script>
							var msCurso = "<?php echo($msNombreCurso); ?>";
							$.messager.alert('KDSA','Cobros de ' + msCurso + ' reajustados.','warning');
						</script>
					<?php
				}
			?>
			<div class="container">
            <div id="DivContenido">
            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form name="hrrCobroIndividual" action="hrrCobroIndividual.php" method="post">
                        <div class="form-group row">
                        	<label for="cboCurso" class="col-xs-12 col-md-1 col-form-label">Curso</label>
                            <div class="col-sm-12 col-md-7">
                                <select class="form-control" id="cboCurso" name="cboCurso">
                                    <?php
										$msConsulta = "select CURSO_REL, NOMBRE_020, CONVOCATORIA_020, GRUPO_020 from KDSA020A where TIPO_020 in (1, 2, 4) and ACTIVO_020 = 1 order by CURSO_REL desc";
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
                                        $mDatos->execute();
										
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Valor = trim($Fila["CURSO_REL"]);
                                            $Texto = trim($Fila["NOMBRE_020"]) . " (" . trim($Fila["CONVOCATORIA_020"]) . " / G" . trim($Fila["GRUPO_020"]) . ")";
											
                                           	echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-2 col-md-offset-1">
                                <input type="submit" id="Aceptar" name="Aceptar" value="Aceptar" class="btn btn-warning" />
                            </div>
                        </div>
                    </form>			
			<?php	}
			}
		?>
        	</div>
        </div>
		</div>
        </div>
</body>
</html>