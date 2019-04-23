
function validaContaBanco(bancos, agencia, dv_agencia, operacao, conta, dv_conta) {

    var i_banco = String("000" + bancos).substr(-3);
    var i_agencia = parseInt(agencia);
    var i_dv_agencia = dv_agencia;
    var i_operacao = parseInt(operacao);
    var i_conta = parseInt(conta);
    var i_dv_conta = dv_conta;

    this.validar = function () {
        switch (i_banco) {
            case "104": // CEF
                return this.validaCEF();
                break;
            case "341": // Itau
                return this.validaItau();
                break;
            case "237": // Bradesco
                return this.validaBradesco();
                break;
            case "001": // Banco do Brasil
                return this.validaBB();
                break;
            case "033": // Santander
                return this.validaSantander();
                break;
            default:
                return true;
        }
    };

    this.validaCEF = function () {
        var multiplicador = "876543298765432";
        var strValidar = String("0000" + i_agencia).substr(-4) + String("000" + i_operacao).substr(-3) + String("00000000" + i_conta).substr(-8);
        var soma = 0;
        for (var i = 0; i < 15; i++) {
            soma += (strValidar[i] * multiplicador[i]);
        }
        var digCalc = (soma * 10) - parseInt(((soma * 10) / 11)) * 11;
        digCalc = digCalc === 10 ? 0 : digCalc;
        if (i_dv_conta == digCalc) {
            return true;
        } else {
            return false;
        }
    };

    this.validaItau = function () {
        var multiplicador = "212121212";
        var strValidar = String("0000" + i_agencia).substr(-4) + String("00000" + i_conta).substr(-5);
        var soma = 0;
        for (var i = 0; i < 9; i++) {
            var tempMult = (strValidar[i] * multiplicador[i]);
            if (tempMult < 10) {
                soma += tempMult;
            } else {
                soma += parseInt(String(tempMult)[0]) + parseInt(String(tempMult)[1]);
            }
        }

        var digCalc = ((parseInt(soma / 10) + 1) * 10) - soma;
        digCalc = digCalc === 10 ? 0 : digCalc;

        if (i_dv_conta == digCalc) {
            return true;
        } else {
            return false;
        }
    };

    this.validaBradesco = function () {
        var multiplicador = "2765432";
        var strValidar = String("0000000" + i_agencia).substr(-7);
        var soma = 0;
        for (var i = 0; i < 7; i++) {
            soma += (strValidar[i] * multiplicador[i]);
        }
        var digCalc = (soma * 10) - parseInt(((soma * 10) / 11)) * 11;
        digCalc = digCalc === 10 ? 0 : digCalc;

        if (i_dv_agencia != digCalc) {
            return false;
        } else {
            strValidar = String("0000000" + i_conta).substr(-7);
            soma = 0;
            for (var i = 0; i < 7; i++) {
                soma += (strValidar[i] * multiplicador[i]);
            }
            var digCalc = (soma * 10) - parseInt(((soma * 10) / 11)) * 11;
            digCalc = digCalc === 10 ? 0 : digCalc;
            if (i_dv_conta != digCalc) {
                return false;
            } else {
                return true;
            }
        }
    };

    this.validaBB = function () {
        var multiplicador = "765432";
        var strValidar = String("000000" + i_agencia).substr(-6);
        var soma = 0;
        for (var i = 0; i < 6; i++) {
            soma += (strValidar[i] * multiplicador[i]);
        }
        var digCalc = (soma * 10) - parseInt(((soma * 10) / 11)) * 11;
        digCalc = digCalc === 10 ? "X" : digCalc;

        if (String(i_dv_agencia).toUpperCase() != digCalc) {
            return false;
        } else {
            strValidar = String("000000" + i_conta).substr(-6);
            soma = 0;
            for (var i = 0; i < 6; i++) {
                soma += (strValidar[i] * multiplicador[i]);
            }
            var digCalc = (soma * 10) - parseInt(((soma * 10) / 11)) * 11;
            digCalc = digCalc === 10 ? "X" : digCalc;
            if (String(i_dv_conta).toUpperCase() != digCalc) {
                return false;
            } else {
                return true;
            }
        }
    };

    this.validaSantander = function () {
        var multiplicador = "973100097131973";
        var strValidar = String("0000" + i_agencia).substr(-4) + String("00000000000" + i_conta).substr(-11);
        var soma = 0;
        for (var i = 0; i < 15; i++) {
            var tempMult = (strValidar[i] * multiplicador[i]);
            if (tempMult < 10) {
                soma += tempMult;
            } else {
                soma += parseInt(String(tempMult)[1]);
            }
        }

        var digCalc = (soma >= 10) ? (10 - parseInt(String(soma)[1])) : (10 - soma);
        digCalc = digCalc === 10 ? 0 : digCalc;

        if (i_dv_conta == digCalc) {
            return true;
        } else {
            return false;
        }
    };

}