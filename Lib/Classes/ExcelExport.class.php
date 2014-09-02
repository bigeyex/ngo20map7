<?php

class ExcelExport{
    function output_excel($fields, $data){
        
        /** Include PHPExcel */

        require_once dirname(__FILE__) . '/PHPExcel.php';


        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("NGO20")
                     ->setLastModifiedBy("NGO20")
                     ->setTitle("NGO20Map")
                     ->setSubject("NGO20Map")
                     ->setDescription("NGO20Map")
                     ->setKeywords("NGO20Map");
                     
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
                     
        // fill in the header of excel
        $i = 0;
        foreach($fields as $field=>$name){
            $column = chr(ord('A')+$i);
            $activeSheet->setCellValue($column.'1', $name);
            $i++;
        }
        
        // fill in the content
        $i = 2;
        foreach($data as $line){
            $j = 0;
            foreach($fields as $field=>$name){
                $column = chr(ord('A')+$j);
                $activeSheet->setCellValue($column.$i, $line[$field]);
                $j++;
            }
            $i++;
        }
        
         // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="导出数据.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}