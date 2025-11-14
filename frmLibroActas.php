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
		$PermisoUsuario = fxPermisoUsuario("repLibroActas", $mbAgregar, $mbModificar, $mbBorrar, $mbAnular);
		
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
                <div class = "row">
                    <div class="col-xs-12 col-md-11">
                        <div class="degradado"><strong>Libro de actas</strong></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <div class = "form-group row">
                        	<label for="cboLibro" class="col-sm-2 col-md-2 col-form-label">Libro de actas</label>
                        	<div class="col-sm-12 col-md-5">
								<select class="form-control" id="cboLibro" name="cboLibro">
									<?php
										$msConsulta = "select TOMO_REL, DESCRIPCION_180 from KDSA180A";
										$m_cnx_MySQL = fxAbrirConexion();
										$mDatos = $m_cnx_MySQL->prepare($msConsulta);
										$mDatos->execute();
										while ($Fila = $mDatos->fetch())
										{
											$Valor = rtrim($Fila["TOMO_REL"]);
											$Texto = rtrim($Fila["DESCRIPCION_180"]);
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
                    </div>
                </div>
            </div>
    	</div>
<?php }} ?>
<script>
    $("#Imprimir").on("click", function() {
        var codLibro = $("#cboLibro").val();
        $.redirect("repLibroActas.php", {msTomo: codLibro}, "POST", "_blank");
    });
</script>
</body>
</html>
