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
	require_once ("funciones/fxEstudiantes.php");
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
		$PermisoUsuario = fxPermisoUsuario("repEstadoCuentas", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
				$FechaIni = date("Y-m-d", time());
				$FechaFin = date("Y-m-d", time());
			?>
			<div class="container">
            <div id="DivContenido">
			<div class = "row">
				<div class="col-xs-12 col-md-11">
					<div class="degradado"><strong>Estado de cuentas del estudiante</strong></div>
				</div>
			</div>

            <div class="row">
            	<div class="col-xs-12 col-xs-offset-none col-md-12 col-md-offset-2">
                    <form name="frmEstadoCuentas" action="repEstadoCuentas.php" target="_blank" method="post">
                        <div class="form-group row">
                            <label for="cboEstudiante" class="col-sm-12 col-md-2 col-form-label">Estudiante</label>
                            <div class="col-sm-12 col-md-6">
                                <select class="form-control" id="cboEstudiante" name="cboEstudiante">
                                    <?php
										$m_cnx_MySQL = fxAbrirConexion();
										$msConsulta = "select distinct KDSA010A.ESTUDIANTE_REL, NOMBRES_010, APELLIDOS_010 from KDSA010A, KDSA030A where KDSA010A.ESTUDIANTE_REL = KDSA030A.ESTUDIANTE_REL and ESTADO_030 <> 4 order by APELLIDOS_010, NOMBRES_010 desc";
                                    	$mDatos = $m_cnx_MySQL->prepare($msConsulta);
				    					$mDatos->execute();
                                        while ($Fila = $mDatos->fetch())
                                        {
                                            $Valor = rtrim($Fila["ESTUDIANTE_REL"]);
                                            $Texto = rtrim($Fila["APELLIDOS_010"]) . ", " . rtrim($Fila["NOMBRES_010"]);

                                            echo("<option value='" . $Valor . "'>" . $Texto . "</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                                               
                        <div class = "row">
                            <div class="col-auto col-xs-offset-none col-md-2 col-md-offset-2">
                                <input type="submit" id="Imprimir" name="Imprimir" value="Imprimir" class="btn btn-warning" />
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