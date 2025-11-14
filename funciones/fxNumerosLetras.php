<?php
	function fxNumerosLetras($vnNumero)
	{
        $msNumLetras = '';
        $mnCuentaDigitos = strlen(trim($vnNumero));
        $mnCaracterLong = $mnCuentaDigitos % 3;
	    $mnCaracterInicio = 0;
	    $mnCuentaCero = 0;
        $mbEntroEnElIf = 0;
        $msDigitoAnterior = '';
        $msDigitoPrevioAnterior = '';

        While ($mnCuentaDigitos > 0)
        {
            $msTriada = substr($vnNumero, $mnCaracterInicio, $mnCaracterLong);
		    $mnCuentaTriada = strlen($msTriada);
		    $mnCuenta = $mnCuentaTriada;

            While ($mnCuentaTriada > 0)
            {
                if ($mbEntroEnElIf == 0)
                {
                    $mnControlDigitos = strlen($vnNumero) - (strlen($vnNumero) - $mnCuentaDigitos);
                    if ($mnControlDigitos == 3 or $mnControlDigitos == 9 or $mnControlDigitos == 15)
                    {
                        if ($mnCuentaCero < 3)
                        {
                            $msNumLetras .= 'Mil ';
						    $mbEntroEnElIf = 1;
						    $mnCuentaCero = 0;
                        }
                    }
                    else
                    {
                        if ($mnControlDigitos == 6)
                        {
                            if ($mnCuentaCero < 3)
                            {
                                if (trim($msNumLetras) == 'Un')
								    $msNumLetras .= 'Millón ';
                                else
								    $msNumLetras .= 'Millones ';
	
							    $mbEntroEnElIf = 1;
						    	$mnCuentaCero = 0;
                            }
                        }

                        if ($mnControlDigitos == 12)
                        {
                            if ($mnCuentaCero < 3)
                            {
                                if (trim($msNumLetras) == 'Un')
								    $msNumLetras .= 'Billón ';
                                else
								    $msNumLetras .= 'Billones ';
	
							    $mbEntroEnElIf = 1;
						    	$mnCuentaCero = 0;
                            }
                        }
                    }
                }

                switch (strlen($msTriada))
                {
                    case 3:
                        $msDigito = trim(substr($msTriada, 3 - $mnCuentaTriada, 1));
                        $msDigitoSiguiente = trim(substr($msTriada, 2 - $mnCuentaTriada, 1));
                        break;
                    case 2:
                        $msDigito = trim(substr($msTriada, 2 - $mnCuentaTriada, 1));
                        $msDigitoSiguiente = trim(substr($msTriada, 1 - $mnCuentaTriada, 1));
                        break;
                    case 1:
                        $msDigito = trim(substr($msTriada, 1 - $mnCuentaTriada, 1));
                        $msDigitoSiguiente = "";
                        break;
                }

                If ($mnCuentaTriada == 3)
                {
                    switch(intval($msDigito))
                    {
                        case 0:
                            $mnCuentaCero += 1;
                            break;
                        case 1:
                            $msNumLetras .= 'Ciento ';
                            break;
                        case 2:
                            $msNumLetras .= 'Doscientos ';
                            break;
                        case 3:
                            $msNumLetras .= 'Trescientos ';
                            break;
                        case 4:
                            $msNumLetras .= 'Cuatrocientos ';
                            break;
                        case 5:
                            $msNumLetras .= 'Quinientos ';
                            break;
                        case 6:
                            $msNumLetras .= 'Seiscientos ';
                            break;
                        case 7:
                            $msNumLetras .= 'Setecientos ';
                            break;
                        case 8:
                            $msNumLetras .= 'Ochocientos ';
                            break;
                        case 9:
                            $msNumLetras .= 'Novecientos ';
                            break;
                    }
                    $msDigitoPrevioAnterior = $msDigitoAnterior;
                }

                If ($mnCuentaTriada == 2)
                {
                    switch (intval($msDigito))
                    {
                        case 0:
                            $mnCuentaCero +=1;
                            break;
                        case 3:
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Treinta ';
                            else
                                $msNumLetras .= 'Treinta y ';
                            break;
                        case 4:
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Cuarenta ';
                            else
                                $msNumLetras .= 'Cuarenta y ';
                            break;
                        case 5:
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Cincuenta ';
                            else
                                $msNumLetras .= 'Cincuenta y ';
                            break;
                        case 6:
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Sesenta ';
                            else
                                $msNumLetras .= 'Sesenta y ';
                            break;
                        case 7:    
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Setenta ';
                            else
                                $msNumLetras .= 'Setenta y ';
                            break;
                        case 8:
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Ochenta ';
                            else
                                $msNumLetras .= 'Ochenta y ';
                            break;
                        case 9:
                            if ($msDigitoSiguiente == "0")
                                $msNumLetras .= 'Noventa ';
                            else
                                $msNumLetras .= 'Noventa y ';
                            break;
                    }
                    $msDigitoAnterior = $msDigito;
                }

                If ($mnCuentaTriada == 1)
                {
                    $mbEntroEnElIf = 0;

                    switch (intval($msDigito))
                    {
                        case 0:
                            $mnCuentaCero += 1;
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0')
                            {
                                If ($msDigitoPrevioAnterior == '1')
                                {
                                    $msNumLetras = substr($msNumLetras, 0, strlen($msNumLetras) - 2) + ' ';
                                }
                                else
                                {
                                    If (strlen(trim($vnNumero)) == 1)
                                        $msNumLetras .= 'Cero ';
                                }
                            }
                            else
                            {
                                If ($msDigitoAnterior == '1')
                                    $msNumLetras .= 'Diez ';
                                else
                                {
                                    If ($msDigitoAnterior == '2')
                                        $msNumLetras .= 'Veinte ';
                                    else
                                        $msNumLetras = substr($msNumLetras, 0, strlen($msNumLetras) - 1);
                                }
                            }
                            break;
                        case 1:
                            If ($msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Un ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Once ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintiun ';
                            break;
                        case 2:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Dos ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Doce ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintidos ';
                            break;
                        case 3:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Tres ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Trece ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintitres ';
                            break;
                        case 4:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Cuatro ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Catorce ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veinticuatro ';
                            break;
                        case 5:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Cinco ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Quince ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veinticinco ';
                            break;
                        case 6:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Seis ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Dieciseis ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintiseis ';
                            break;
                        case 7:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Siete ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Diecisiete ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintisiete ';
                            break;
                        case 8:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Ocho ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Dieciocho ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintiocho ';
                            break;
                        case 9:
                            If ($msDigitoAnterior == '' or $msDigitoAnterior == '0' or $msDigitoAnterior == '3' or $msDigitoAnterior == '4' or $msDigitoAnterior == '5' or $msDigitoAnterior == '6' or $msDigitoAnterior == '7' or $msDigitoAnterior == '8' or $msDigitoAnterior == '9')
                                $msNumLetras .= 'Nueve ';
                            If ($msDigitoAnterior == '1')
                                $msNumLetras .= 'Diecinueve ';
                            If ($msDigitoAnterior == '2')
                                $msNumLetras .= 'Veintinueve ';
                            break;
                    }
                }
                $msDigitoAnterior = $msDigito;
                $mnCuentaTriada -= 1;
            }

            $mnCaracterInicio += $mnCaracterLong;
            $mnCuentaDigitos -= $mnCuenta;
            $mnCaracterLong = 3 - ($mnCuentaDigitos % 3);
        }

        $msResultado = trim($msNumLetras);
        return $msResultado;
    }
?>