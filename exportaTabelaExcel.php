<?php

if (isset($_REQUEST["dados"]) && $_REQUEST["dados"] != "") {
    $dados = utf8_decode(urldecode($_REQUEST["dados"]));
    $nome = isset($_POST["nome"]) ? $_REQUEST["nome"] : "Dados.xlsx";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nome . '"');
    header('Cache-Control: max-age=0');
} else {
    header("status: 204");
    header("HTTP/1.0 204 No Response");
    die("Erro.");
}

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set("memory_limit", "500M");
date_default_timezone_set('America/Sao_Paulo');
define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

require_once ('outros/PHPExcel/PHPExcel.php');

function trataErros($errno, $errstr, $errfile, $errline){
    $string = "$errno - $errstr em $errfile na linha $errline\n";
    file_put_contents('outros/PHPExcel/log/log.txt', $string, FILE_APPEND);
    // se retornar TRUE não faz o tratamento padrão do erro no PHP
    return true;
}
set_error_handler('trataErros');

function num_to_letter($num, $uppercase = True) {
    $num = intval($num);
    if ($num <= 0)
        return "";
    $letter = "";
    while ($num != 0) {
        $p = ($num - 1) % 26;
        $num = intval(($num - $p) / 26);
        $letter = chr(65 + $p) . $letter;
    }
    return ($uppercase ? strtoupper($letter) : $letter);
}

function getStyle($atributos) {
    $style = array(
        'font' => array(
            'name' => 'Calibri',
            'size' => 9,
            'bold' => false,
            'color' => array(
                'rgb' => '000000'
            ),
        ),       
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_NONE,
            'startcolor' => array(
                'rgb' => 'FFFFFF00',
            ),
        ),
        'borders' => array(
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_NONE,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_NONE,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_NONE,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_NONE,
                'color' => array(
                    'rgb' => '000000'
                )
            )
        ),
    );
    foreach ($atributos as $attrName => $attr) {
        switch ($attrName) {
            case "cor":
                $style['font']['color']['rgb'] = $attr;
                break;
            case "backgroud":
                $style['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
                $style['fill']['startcolor']['rgb'] = $attr;
                break;
            case "bold":
                $style['font']['bold'] = $attr;
                break;
            case "border":
                $style['borders']['bottom']['style'] = getBorder($attr);
                $style['borders']['top']['style'] = getBorder($attr);
                $style['borders']['left']['style'] = getBorder($attr);
                $style['borders']['right']['style'] = getBorder($attr);
                break;
        }
    }
    return $style;
}

function getBorder($nome){
    //            BORDER_DASHDOT
//            BORDER_DASHDOTDOT
//            BORDER_DASHED
//            BORDER_DOTTED
//            BORDER_DOUBLE
//            BORDER_HAIR
//            BORDER_MEDIUM
//            BORDER_MEDIUMDASHDOT
//            BORDER_MEDIUMDASHDOTDOT
//            BORDER_MEDIUMDASHED
//            BORDER_NONE
//            BORDER_SLANTDASHDOT
//            BORDER_THICK
//            BORDER_THIN
    switch ($nome) {
        case 'BORDER_DASHDOT':
            return PHPExcel_Style_Border::BORDER_DASHDOT;
            break;
        case 'BORDER_DASHDOTDOT':
            return PHPExcel_Style_Border::BORDER_DASHDOTDOT;
            break;
        case 'BORDER_DASHED':
            return PHPExcel_Style_Border::BORDER_DASHED;
            break;
        case 'BORDER_DOTTED':
            return PHPExcel_Style_Border::BORDER_DOTTED;
            break;
        case 'BORDER_DOUBLE':
            return PHPExcel_Style_Border::BORDER_DOUBLE;
            break;
        case 'BORDER_HAIR':
            return PHPExcel_Style_Border::BORDER_HAIR;
            break;
        case 'BORDER_MEDIUM':
            return PHPExcel_Style_Border::BORDER_MEDIUM;
            break;
        case 'BORDER_MEDIUMDASHDOT':
            return PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT;
            break;
        case 'BORDER_MEDIUMDASHDOTDOT':
            return PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT;
            break;
        case 'BORDER_MEDIUMDASHED':
            return PHPExcel_Style_Border::BORDER_MEDIUMDASHED;
            break;
        case 'BORDER_SLANTDASHDOT':
            return PHPExcel_Style_Border::BORDER_SLANTDASHDOT;
            break;
        case 'BORDER_THICK':
            return PHPExcel_Style_Border::BORDER_THICK;
            break;
        case 'BORDER_THIN':
            return PHPExcel_Style_Border::BORDER_THIN;
            break;
        default:
            return PHPExcel_Style_Border::BORDER_NONE;
            break;
    }
}

$contadorPlan = 0;
$objPHPExcel = new PHPExcel();

if (strpos($dados, "<tables>") === false) {
    $dados = "<tables>" . $dados . "</tables>";
}

$preXml = '<?xml version="1.0" encoding="ISO-8859-1" ?>' . $dados;

$arqXml = simplexml_load_string($preXml);

//die(print_r($arqXml));

foreach ($arqXml->table as $xml) {

    if ($contadorPlan > 0) {
        $objPHPExcel->createSheet($contadorPlan);
        $objPHPExcel->setActiveSheetIndex($contadorPlan);
    }
    $objPHPExcel->getActiveSheet()->setTitle((string) $xml->attributes()->title);
    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
    $contadorPlan++;

    $contadorLinha = 1;
    foreach ($xml->thead->tr as $value) {
        $contadorLetra = 1;
        for ($i = 0; $i < count($value->th); $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue(num_to_letter($contadorLetra) . $contadorLinha, $value->th[$i]);
            $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetra) . $contadorLinha)->applyFromArray(getStyle($value->th[$i]->attributes()));
            $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetra) . $contadorLinha)->getNumberFormat()->setFormatCode('##,##0;[Red]-##,##0');
            if($value->th[$i]->attributes()->width > 0){
                $objPHPExcel->getActiveSheet()->getColumnDimension(num_to_letter($contadorLetra))->setWidth($value->th[$i]->attributes()->width);
            }
            if ($value->th[$i]->attributes()->colspan > 0) {
                $contadorLetraIni = $contadorLetra;
                for ($j = 1; $j < $value->th[$i]->attributes()->colspan; $j++) {
                    $contadorLetra++;
                }
                $objPHPExcel->getActiveSheet()->mergeCells(num_to_letter($contadorLetraIni) . $contadorLinha . ":" . num_to_letter($contadorLetra) . $contadorLinha);
                $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetraIni) . $contadorLinha . ":" . num_to_letter($contadorLetra) . $contadorLinha)->applyFromArray(getStyle($value->th[$i]->attributes()));
            }
            $contadorLetra++;
        }
        $contadorLinha++;
    }

    foreach ($xml->tbody->tr as $value) {

        $contadorLetra = 1;
        for ($i = 0; $i < count($value->td); $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue(num_to_letter($contadorLetra) . $contadorLinha, $value->td[$i]);
            $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetra) . $contadorLinha)->applyFromArray(getStyle($value->td[$i]->attributes()));
            $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetra) . $contadorLinha)->getNumberFormat()->setFormatCode('##,##0;[Red]-##,##0');
//            $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetra) . $contadorLinha)->getNumberFormat()->setFormatCode('#.#0,##;[Red]-#.#0,##');
            if($value->td[$i]->attributes()->width > 0){
                $objPHPExcel->getActiveSheet()->getColumnDimension(num_to_letter($contadorLetra))->setWidth($value->td[$i]->attributes()->width);
            }
            if ($value->td[$i]->attributes()->colspan > 0) {
                $contadorLetraIni = $contadorLetra;
                for ($j = 1; $j < $value->td[$i]->attributes()->colspan; $j++) {
                    $contadorLetra++;
                }
                $objPHPExcel->getActiveSheet()->mergeCells(num_to_letter($contadorLetraIni) . $contadorLinha . ":" . num_to_letter($contadorLetra) . $contadorLinha);
                $objPHPExcel->getActiveSheet()->getStyle(num_to_letter($contadorLetraIni) . $contadorLinha . ":" . num_to_letter($contadorLetra) . $contadorLinha)->applyFromArray(getStyle($value->td[$i]->attributes()));
            }
            $contadorLetra++;
        }
        $contadorLinha++;
    }
}

$objPHPExcel->setActiveSheetIndex(0);
//$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(90);

// Save Excel5 file
//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//$objWriter->save(str_replace('.php', '.xls', __FILE__));
//$objWriter->save('php://output');
// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
$objWriter->save('php://output');
exit;
?>