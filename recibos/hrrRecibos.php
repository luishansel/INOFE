<?php
	require_once ("funciones/fxGeneral.php");
    
    $m_cnx_MySQL = fxAbrirConexion();
    $txtCodPago = "";
    $optMoneda = 0;
    $optTipoPago = 0;

    if (isset($_POST["cmdBuscar"]))
    {
        $txtCodPago = $_POST["txtCodPago"];

        $msConsulta = "select PAGO_REL, FECHA_040, NOMBRE_040, RECIBO_040, SERIE_040, MONTO_040, MONEDA_040, TIPOCAMBIO_040, TIPOPAGO_040, CONCEPTO_040 from KDSA040A where PAGO_REL = ?";
        $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$txtCodPago]);
		$Fila = $mDatos->fetch();
        
        $dtpFecha = $Fila["FECHA_040"];
        $txtNombre = $Fila["NOMBRE_040"];
        $txtRecibo = $Fila["RECIBO_040"];
        $optSerie = $Fila["SERIE_040"];
        $txnMonto = $Fila["MONTO_040"];
        $optMoneda = $Fila["MONEDA_040"];
        $txnTipoCambio = $Fila["TIPOCAMBIO_040"];
        $optTipoPago = $Fila["TIPOPAGO_040"];
        $txtConcepto = $Fila["CONCEPTO_040"];
    }

    if (isset($_POST["cmdGuardar"]))
    {
        $msConexion = fxAbrirConexion();
        $txtUsuario = $_POST["txtUsuario"];
        $txtClave = $_POST["txtClave"];
        $msEncriptado = crypt($txtClave, '_appwKDSA');

        $msConsulta = "Select USUARIO_REL, NOMBRE_002 from KDSA002A where USUARIO_REL =? and CLAVE_002 =? and ACTIVO_002 = 1";
		$mDatos = $m_cnx_MySQL->prepare($msConsulta);
		$mDatos->execute([$txtUsuario, $msEncriptado]);
        $mnRegistros = $mDatos->rowCount();

        if ($mnRegistros = 0)
        {
            ?>
                <script>alert("Usuario no registrado");</script>
            <?php
        }
        else
        {
            $msConsulta = "Select * from KDSA002A where USUARIO_REL =? and SUPERVISOR_002 = 1";
            $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		    $mDatos->execute([$txtUsuario]);
            $mnRegistros = $mDatos->fetch();
            if ($mnRegistros = 0)
            {
                ?>
                    <script>alert("Su Usuario no es Administrador");</script>
                <?php
            }
            else
            {
                $txtCodPago = $_POST["txtCodPago"];
                $dtpFecha = $_POST["dtpFecha"];
                $txtNombre = $_POST["txtNombre"];
                $txtRecibo = $_POST["txtRecibo"];
                $optSerie = $_POST["optSerie"];
                $txnMonto = $_POST["txnMonto"];
                $optMoneda = $_POST["optMoneda"];
                $txnTipoCambio = $_POST["txnTipoCambio"];
                $optTipoPago = $_POST["optTipoPago"];
                $txtConcepto = $_POST["txtConcepto"];

                $msConsulta = "update KDSA040A set FECHA_040 = ?, NOMBRE_040 = ?, RECIBO_040 = ?, SERIE_040 = ?, MONTO_040 = ?, MONEDA_040 = ?, ";
                $msConsulta .= "TIPOCAMBIO_040 = ?, CONCEPTO_040 = ?, TIPOPAGO_040 = ? where PAGO_REL = ?";
                $mDatos = $m_cnx_MySQL->prepare($msConsulta);
		        $mDatos->execute([$dtpFecha, $txtNombre, $txtRecibo, $optSerie, $txnMonto, $optMoneda, $txnTipoCambio, $txtConcepto, $optTipoPago, $txtCodPago]);
                ?>
                    <script>alert("Registro modificado");</script>
                <?php
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es-NI">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" href="imagenes/favicon.png" />
    <title>Corrección de Recibos</title>

    <style>
        html
        {
            font-family: Arial, Helvetica, sans-serif;
        }
        .ctrlAnchoFijo
        {
            width: 150px;
        }
        .ctrlAnchoDoble
        {
            width: 400px;
        }
    </style>
</head>
<body>
    <form id="hrrRecibos" name="hrrRecibos" action="hrrRecibos.php" method="POST">
        <div width="50%" style="margin-left: 20%; margin-top: 2%">
        <h1>KDSA</h1>
            <table>
                <tr>
                    <td class="ctrlAnchoFijo"><label for="txtCodPago">Código del Pago</label></td>
                    <?php
                        echo('<td><input class="ctrlAnchoFijo" type="text" id="txtCodPago" name="txtCodPago" placeholder="Código del Pago" value="' . trim($txtCodPago) . '"/></td>');
                        if ($txtCodPago == "")
                            echo('<td><input type="submit" id="cmdBuscar" name="cmdBuscar" value="Buscar" onclick="return enviarFormulario(0)"></td>');
                        else
                            echo('<td><input type="submit" id="cmdBuscar" name="cmdBuscar" value="Buscar" onclick="return enviarFormulario(0)" disabled></td>');
                    ?>
                </tr>
            </table>
        </div>
        <div width="60%" style="margin-left: 20%; margin-top: 2%">
            <table>
                <tr>
                    <td class="ctrlAnchoFijo"><label for="txtUsuario">Usuario</label></td>
                    <td><input class="ctrlAnchoFijo" type="text" id="txtUsuario" name="txtUsuario" placeholder="Usuario" value=""/></td>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="txtClave">Contraseña</label></td>
                    <td><input class="ctrlAnchoFijo" type="password" id="txtClave" name="txtClave" placeholder="Contraseña" value=""/></td>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="dtpFecha">Fecha del pago</label></td>
                    <?php
                        if ($txtCodPago == "")
                            echo('<td><input class="ctrlAnchoFijo" type="date" id="dtpFecha" name="dtpFecha" value=""/></td>');
                        else
                            echo('<td><input class="ctrlAnchoFijo" type="date" id="dtpFecha" name="dtpFecha" value="' . trim($dtpFecha) . '"/></td>');
                    ?>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="txtNombre">Nombre</label></td>
                    <?php
                        if ($txtCodPago == "")
                            echo('<td><input class="ctrlAnchoDoble" type="text" id="txtNombre" name="txtNombre" placeholder="Nombre" value=""/></td>');
                        else
                            echo('<td><input class="ctrlAnchoDoble" type="text" id="txtNombre" name="txtNombre" placeholder="Nombre" value="' . trim($txtNombre) . '"/></td>');
                    ?>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="txtRecibo">Recibo</label></td>
                    <?php
                        if ($txtCodPago == "")
                            echo('<td><input class="ctrlAnchoFijo" type="text" id="txtRecibo" name="txtRecibo" placeholder="Recibo" value=""/></td>');
                        else
                            echo('<td><input class="ctrlAnchoFijo" type="text" id="txtRecibo" name="txtRecibo" placeholder="Recibo" value="' . trim($txtRecibo) . '"/></td>');
                    ?>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="optSerie">Serie</label></td>
                    <td>
                        <?php
                            if ($txtCodPago == "" or $optSerie == "A")
                            {
                                echo('<input type="radio" id="optSerieA" name="optSerie" value = "A" checked/>A');
                                echo('<input type="radio" id="optSerieB" name="optSerie" value = "B" />B');
                            }
                            else
                            {
                                echo('<input type="radio" id="optSerieA" name="optSerie" value = "A" />A');
                                echo('<input type="radio" id="optSerieB" name="optSerie" value = "B" checked/>B');
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="txnMonto">Monto</label></td>
                    <?php
                        if ($txtCodPago == "")
                            echo('<td><input class="ctrlAnchoFijo" type="number" id="txnMonto" name="txnMonto" placeholder="Monto" step="0.01" value="" readonly/></td>');
                        else
                            echo('<td><input class="ctrlAnchoFijo" type="number" id="txnMonto" name="txnMonto" placeholder="Monto" step="0.01" value="' . trim($txnMonto) . '" readonly/></td>');
                    ?>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="optMoneda">Moneda</label></td>
                    <td>
                        <?php
                            if ($txtCodPago == "" or $optMoneda == 0)
                            {
                                echo('<input type="radio" id="optCorbobas" name="optMoneda" value = "0" checked/>Córdobas');
                                echo('<input type="radio" id="optDolares" name="optMoneda" value = "1" />Dólares');
                            }
                            else
                            {
                                echo('<input type="radio" id="optCorbobas" name="optMoneda" value = "0" />Córdobas');
                                echo('<input type="radio" id="optDolares" name="optMoneda" value = "1" checked/>Dólares');
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="txnTipoCambio">Tipo de cambio</label></td>
                    <?php
                        if ($txtCodPago == "")
                            echo('<td><input class="ctrlAnchoFijo" type="number" id="txnTipoCambio" name="txnTipoCambio" placeholder="Tipo de cambio" step="0.01" value=""/></td>');
                        else
                            echo('<td><input class="ctrlAnchoFijo" type="number" id="txnTipoCambio" name="txnTipoCambio" placeholder="Tipo de cambio" step="0.01" value="' . trim($txnTipoCambio) . '"/></td>');
                    ?>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="optTipoPago">Tipo de pago</label></td>
                    <td>
                    <?php
                        if ($txtCodPago == "" or $optTipoPago == 0)
                        {
                            echo('<input type="radio" id="optEfectivo" name="optTipoPago" value = "0" checked/>Efectivo');
                            echo('<input type="radio" id="optTarjeta" name="optTipoPago" value = "1" />Tarjeta');
                            echo('<input type="radio" id="optCheque" name="optTipoPago" value = "2" />Cheque');
                            echo('<input type="radio" id="optBanpro" name="optTipoPago" value = "3" />Depósito BANPRO');
                            echo('<input type="radio" id="optBac" name="optTipoPago" value = "4" />Depósito BAC');
                            echo('<input type="radio" id="optCommerce" name="optTipoPago" value = "5" />eCommerce');
                        }
                        
                        if ($optTipoPago == 1)
                        {
                            echo('<input type="radio" id="optEfectivo" name="optTipoPago" value = "0" />Efectivo');
                            echo('<input type="radio" id="optTarjeta" name="optTipoPago" value = "1" checked/>Tarjeta');
                            echo('<input type="radio" id="optCheque" name="optTipoPago" value = "2" />Cheque');
                            echo('<input type="radio" id="optBanpro" name="optTipoPago" value = "3" />Depósito BANPRO');
                            echo('<input type="radio" id="optBac" name="optTipoPago" value = "4" />Depósito BAC');
                            echo('<input type="radio" id="optCommerce" name="optTipoPago" value = "5" />eCommerce');
                        }

                        if ($optTipoPago == 2)
                        {
                            echo('<input type="radio" id="optEfectivo" name="optTipoPago" value = "0" />Efectivo');
                            echo('<input type="radio" id="optTarjeta" name="optTipoPago" value = "1" />Tarjeta');
                            echo('<input type="radio" id="optCheque" name="optTipoPago" value = "2" checked/>Cheque');
                            echo('<input type="radio" id="optBanpro" name="optTipoPago" value = "3" />Depósito BANPRO');
                            echo('<input type="radio" id="optBac" name="optTipoPago" value = "4" />Depósito BAC');
                            echo('<input type="radio" id="optCommerce" name="optTipoPago" value = "5" />eCommerce');
                        }

                        if ($optTipoPago == 3)
                        {
                            echo('<input type="radio" id="optEfectivo" name="optTipoPago" value = "0" />Efectivo');
                            echo('<input type="radio" id="optTarjeta" name="optTipoPago" value = "1" />Tarjeta');
                            echo('<input type="radio" id="optCheque" name="optTipoPago" value = "2" />Cheque');
                            echo('<input type="radio" id="optBanpro" name="optTipoPago" value = "3" checked/>Depósito BANPRO');
                            echo('<input type="radio" id="optBac" name="optTipoPago" value = "4" />Depósito BAC');
                            echo('<input type="radio" id="optCommerce" name="optTipoPago" value = "5" />eCommerce');
                        }

                        if ($optTipoPago == 4)
                        {
                            echo('<input type="radio" id="optEfectivo" name="optTipoPago" value = "0" />Efectivo');
                            echo('<input type="radio" id="optTarjeta" name="optTipoPago" value = "1" />Tarjeta');
                            echo('<input type="radio" id="optCheque" name="optTipoPago" value = "2" />Cheque');
                            echo('<input type="radio" id="optBanpro" name="optTipoPago" value = "3" />Depósito BANPRO');
                            echo('<input type="radio" id="optBac" name="optTipoPago" value = "4" checked/>Depósito BAC');
                            echo('<input type="radio" id="optCommerce" name="optTipoPago" value = "5" />eCommerce');
                        }

                        if ($optTipoPago == 5)
                        {
                            echo('<input type="radio" id="optEfectivo" name="optTipoPago" value = "0" />Efectivo');
                            echo('<input type="radio" id="optTarjeta" name="optTipoPago" value = "1" />Tarjeta');
                            echo('<input type="radio" id="optCheque" name="optTipoPago" value = "2" />Cheque');
                            echo('<input type="radio" id="optBanpro" name="optTipoPago" value = "3" />Depósito BANPRO');
                            echo('<input type="radio" id="optBac" name="optTipoPago" value = "4" />Depósito BAC');
                            echo('<input type="radio" id="optCommerce" name="optTipoPago" value = "5" checked/>eCommerce');
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"><label for="txtConcepto">Concepto</label></td>
                    <?php
                        if ($txtCodPago == "")
                            echo('<td><textarea id="txtConcepto" name="txtConcepto" placeholder="Concepto" rows="2" cols="48"/></textarea></td>');
                        else
                            echo('<td><textarea id="txtConcepto" name="txtConcepto" placeholder="Concepto" rows="2" cols="48"/>' . trim($txtConcepto) . '</textarea></td>');
                    ?>
                </tr>
                <tr>
                    <td class="tdAnchoFijo"></td>
                    <?php
                    if ($txtCodPago == "")
                    {
                        echo('<td><input type="submit" id="cmdGuardar" name="cmdGuardar" value="Guardar" onclick="return enviarFormulario(1)" disabled><input type="submit" id="cmdCancelar" name="cmdCancelar" value="Cancelar" onclick="document.getElementById("hrrRecibos").submit()"></td>');
                    }
                    else
                    {
                        echo('<td><input type="submit" id="cmdGuardar" name="cmdGuardar" value="Guardar" onclick="return enviarFormulario(1)"><input type="submit" id="cmdCancelar" name="cmdCancelar" value="Cancelar" onclick="document.getElementById("hrrRecibos").submit()"></td>');
                    }
                    ?>
                </tr>
            </table>
        </div>
    </form>
</body>
<script>
    function enviarFormulario(mnTipo)
    {
        var resultado = verificarFormulario(mnTipo);
        return resultado;
    }

    function verificarFormulario(mnTipo)
    {
        if (mnTipo == 0)
        {
            if (document.getElementById("txtCodPago").value == "")
            {
                alert("Falta el Código del Pago");
                return false;
            }
        }

        if (mnTipo == 1)
        {
            if (document.getElementById("txtUsuario").value == "")
            {
                alert("Falta el Usuario");
                return false;
            }

            if (document.getElementById("txtClave").value == "")
            {
                alert("Falta la Contraseña");
                return false;
            }

            if (document.getElementById("txtNombre").value == "")
            {
                alert("Falta el Nombre");
                return false;
            }

            if (document.getElementById("txtRecibo").value == "")
            {
                alert("Falta el Recibo");
                return false;
            }

            if (document.getElementById("txnMonto").value == "" || document.getElementById("txnMonto").value <= 0)
            {
                alert("Falta el Monto");
                return false;
            }

            if (document.getElementById("txnTipoCambio").value == "" || document.getElementById("txnTipoCambio").value <= 0)
            {
                alert("Falta el Tipo de cambio");
                return false;
            }

            if (document.getElementById("txtConcepto").value == "")
            {
                alert("Falta el Concepto");
                return false;
            }
        }

        return true;
    }
</script>