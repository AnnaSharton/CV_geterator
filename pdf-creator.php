<?php 
error_reporting(E_ALL);
session_start();
$arr = $_SESSION['array']; //считываю сохраненный массив
$image = $_SESSION['data']; //и загруженную пользователем фотографию

$arrText = []; // заношу в новый массив элементы value из массива data.php:
foreach ($arr as $k => $v) {
    $arrText[$k] = $arr[$k]['value']; 
}
// некоторые элементы заношу отдельно (весь текст на кириллице включаю в массив, чтобы пройтись по нему,указав кодировку) 
$arrText['parent']            = $arr['parent']['value'] ? $arr['parent']['value'] : '';
$arrText['birth']             = 'ДАТА РОЖДЕНИЯ:';
$arrText['birthday']          = date("d-m-Y", strtotime($arr['birthday']['value']));
$arrText['adress']            = 'МЕСТО ПРОЖИВАНИЯ:'; 
$arrText['street']            = 'ул. '.$arr['street']['value'];
$arrText['adress1']           = 'АДРЕС ПРОПИСКИ:'; 
$arrText['street1']           = 'ул. '.$arr['street1']['value'];
$arrText['position-title']    = 'РЕЗЮМЕ СОИСКАТЕЛЯ НА ДОЛЖНОСТЬ: ';
$arrText['wish-salary']       = 'Желаемый размер заработной платы: ';
$arrText['salary']            = $arr['salary']['value']. 'руб/мес';
$arrText['education-title']   = 'ОБРАЗОВАНИЕ';
$arrText['experience-title']  = 'ОПЫТ РАБОТЫ';
$arrText['year-experience']   = 'Опыт работы в данной сфере: '.$arr['year-experience']['value'].' '.YearTextArg($arr['year-experience']['value']);

//переделываю названия месяцев на русском
$arr_mon = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
$m_start = date("n", strtotime($arr['income-last-job']['value']))-1;// номер месяца поступления на работу
$m_end = date("n", strtotime($arr['drop-last-job']['value']))-1; //номер месяца увольнения
$arrText['date-job'] = $arr_mon[$m_start].' '.date("Y", strtotime($arr['income-last-job']['value'])).' - '.$arr_mon[$m_end].' '.date("Y", strtotime($arr['drop-last-job']['value']));

//для дат второй работы все то же
if (!empty($arr['income-last-job-2']['value']) && !empty($arr['drop-last-job-2']['value'])) {
$m_start2 = date("n", strtotime($arr['income-last-job-2']['value']))-1;
$m_end2 = date("n", strtotime($arr['drop-last-job-2']['value']))-1; 
$arrText['date-job-2'] = '***'."\n".$arr_mon[$m_start2].' '.date("Y", strtotime($arr['income-last-job-2']['value'])).' - '.$arr_mon[$m_end2].' '.date("Y", strtotime($arr['drop-last-job-2']['value']));
}
//значение action - что нужно будет делать с пдф-файлом - будет зависеть от того, нажато open или download submit
$arrText['action']   = $arr['open']['value'] ? $arr['open']['value'] : $arr['download']['value'];


foreach ($arrText as &$text) { //для всех значений массива преобразовываю в кириллическую кодировку 
   $text = iconv('utf-8', 'windows-1251', $text);
}

require('fpdf.php'); //подключаю код fpdf
require('makefont/makefont.php'); // подключаю шрифт


$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('Baltica Plain', '', "Baltica Plain.001.001.php"); //обычный
$pdf->AddFont('ofont.ru_Baltica', '', "ofont.ru_Baltica.php"); //жирный
// левый блок
$pdf->SetFillColor(189, 212, 220); //левый блок заливка
$pdf->Rect(0, 0, 90, 350, "F"); //параметры левого блока
$pdf->Image($image, 15, 20, 60); //аватарка
$pdf->SetFont('ofont.ru_Baltica', '', 22); // шрифт для всего текста документа
$pdf->SetTextColor(74, 74, 74); // цвет для текста блока 
$pdf->SetXY(15, 92); // координаты блока текста для фамилии
$pdf->Cell(100, 40, $arrText['surname'], '0',"L"); // параметры блока текста с фамилией - координаты, содержимое, центрирование
$pdf->SetXY(15, 102); // задаю координаты для следующего блока текста - имени
$pdf->Cell(100, 40, $arrText['name'], '0',"L");
$pdf->SetXY(15, 112);
$pdf->Cell(100, 40, $arrText['parent'], '0',"L");
$pdf->SetLineWidth(0.5);   //линия под именем
$pdf->SetDrawColor(74, 74, 74); //цвет линии под именем
$pdf->Line(16, 140, 70, 140); // размеры линии под именем
$pdf->SetFont('Baltica Plain', '', 13); 
$pdf->SetXY(15, 140);
$pdf->Cell(100, 20, $arrText['birth'], '0',"L");
$pdf->SetXY(15, 148);
$pdf->Cell(100, 20, $arrText['birthday'], '0',"L");
$pdf->Image('https://cdn.icon-icons.com/icons2/1769/PNG/512/4092561-email-envelope-mail-message-mobile-ui-ui-website_114031.png', 15, 166.5, 6); 
$pdf->Image('https://cdn.icon-icons.com/icons2/614/PNG/512/auricular-phone-symbol-in-a-circle_icon-icons.com_56570.png', 15.5, 175, 5); 
$pdf->SetXY(24, 160);
$pdf->Cell(100, 20, $arrText['email'], '0',"L");
$pdf->SetXY(24, 168);
$pdf->Cell(100, 20, $arrText['phone'], '0',"L"); // 15
$pdf->SetXY(15, 186);
$pdf->Cell(100, 20, $arrText['adress'] , '0',"L");
$pdf->SetXY(15, 193);
$pdf->Cell(100, 20, $arrText['city'], '0',"L"); 
$pdf->SetXY(15, 200);
$pdf->Cell(100, 20, $arrText['street'].', '.$arrText['house'], '0',"L"); 
$pdf->SetXY(15, 222);
$pdf->Cell(100, 20, $arrText['adress1'], '0',"L");
$pdf->SetXY(15, 229);
$pdf->Cell(100, 20, $arrText['city1'], '0',"L"); 
$pdf->SetXY(15, 236);
$pdf->Cell(100, 20, $arrText['street1'].', '.$arrText['house1'], '0',"L"); 

//правый блок
$pdf->SetXY(95, 13);
$pdf->Cell(100, 20, $arrText['position-title'], '0', "L", 1); 
$pdf->SetXY(95, 21);
$pdf->SetFont('ofont.ru_Baltica', '', 13); 
$pdf->Cell(100, 20, $arrText['position'], '0', "L", 1); 

$pdf->SetFont('Baltica Plain', '', 13); 
$pdf->SetXY(95, 35);
$pdf->Cell(100, 20, $arrText['wish-salary'], '0', "L", 1); 
$pdf->SetXY(95, 42);
$pdf->SetFont('ofont.ru_Baltica', '', 13); 
$pdf->Cell(100, 20, $arrText['salary'], '0', "L", 1); 

//шапка образование
$pdf->SetLineWidth(0.8);  
$pdf->SetDrawColor(189, 212, 220); 
$pdf->Line(97, 66, 128, 66);
$pdf->Line(170, 66, 198, 66);
$pdf->SetXY(130, 56);
$pdf->Cell(100, 20, $arrText['education-title'], '0', "L"); 
//
$pdf->SetFont('Baltica Plain', '', 13); 
$pdf->SetXY(97, 70);
$pdf->Cell(105, 8, $arrText['education-year'].' - '.$arrText['specializing'], '0', "L", 1); 
$pdf->SetXY(97, 80);
$pdf->MultiCell(105, 8, $arrText['education'], '0', "L", 0); 
//шапка опыт работы
$pdf->Line(97, 106, 128, 106);
$pdf->Line(170, 106, 198, 106);
$pdf->SetFont('ofont.ru_Baltica', '', 13); 
$pdf->SetXY(130, 96);
$pdf->Cell(100, 20, $arrText['experience-title'], '0', "L"); 
// опыт работы:
$pdf->SetFont('Baltica Plain', '', 13); 
$pdf->SetXY(97, 112);
$pdf->Cell(105, 8, $arrText['year-experience'], '0', "L"); 
$pdf->SetXY(97, 126);
$pdf->Cell(105, 8, '***', '0', "C", 1); 
$pdf->SetFont('Baltica Plain', '', 13); 
$pdf->SetXY(97, 134);
$pdf->Cell(105, 8, $arrText['date-job'], '0', "L"); 
$pdf->SetXY(97, 144);
$pdf->Cell(105, 8, $arrText['last-post'], '0', "L"); 
$pdf->SetXY(97, 154);
$pdf->Cell(105, 8, $arrText['organisation'], '0', "L");
$pdf->SetXY(97, 164);
$pdf->MultiCell(105, 8, $arrText['about-job']."\n".$arrText['date-job-2']."\n".$arrText['last-post-2']."\n".$arrText['organisation-2']."\n".$arrText['about-job-2'], '0', "L", 0); 

//submit-download присвоено value 1 в форме
$a = $arrText['action'] == "1" ? 'attachment' : 'inline'; // в зависимости от значения action файл будет открываться или скачиваться
$pdfString = $pdf->Output(null, "S");
header('Content-Type: application/pdf');
header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Content-Disposition:'.$a.'; filename="CV-'.$arrText['surname'].'.pdf"'); 
echo $pdfString; 
unlink($image); //удаляю фото после того как резюме сгенерировано

//отправляю файл по почте если выбран чекбокс
if ($arrText['send-mail'] == 'on') { 
   $fileName = "CV-".$arrText['surname'].".pdf"; 
   $pdf->Output($fileName, "F");
   $attach = $fileName;
   $to = $arrText['email'];
   $subject = 'Резюме на должность: '.$arr['position']['value'].' '.$arr['name']['value'].' '.$arr['surname']['value'];
   $message = 'Пожалуйста, обратите внимание на прикрепленный файл'."\r\n".'С уважением, '.$arr['name']['value'].' '.$arr['surname']['value'];
   
   mailto($to, $subject, $message, $attach);
   unlink($fileName); //удаляю файл после отправки письма и фотографию
   unlink($image); 
} else {}

// ф-ия для отправки письма
function mailto($to, $subject, $message='', $attach=Array(), $from="Робот", $fromAddr="noreply@mail.ru") {

   $mb_internal_encoding = mb_internal_encoding();
   mb_internal_encoding('UTF-8');

   $headers = "Date: ".date("r")."\r\n";
   $headers.= "From: =?UTF-8?B?".base64_encode($from)."?= <".$fromAddr.">\r\n";
   $headers.= "MIME-Version: 1.0\r\n";

   $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
   if (strpos($message, '/>')) $msgType = "text/html"; else $msgType = "text/plain";
   if (is_string($attach)) $attach = Array($attach);
   $files = Array();
   foreach ($attach as $path) if (file_exists($path)) $files[] = $path;

   if ($files) {
       $boundary = md5(time());
       $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

       $body  = "\r\n--$boundary\r\n"; 
       $body .= "Content-Type: $msgType; charset=UTF-8\r\n";
       $body .= "Content-Transfer-Encoding: 8bit\r\n";
       $body .= "\r\n";
       $body .= $message;

       foreach ($files as $path) {
           $filename = mb_substr($path, mb_strrpos($path, '/'));
           $body .= "\r\n--$boundary\r\n"; 
           $body .= "Content-Type: application/octet-stream\r\n";  
           $body .= "Content-Transfer-Encoding: base64\r\n"; 
           $body .= "Content-Disposition: attachment; filename*=windows-1251''".str_replace('+', '%20', urlencode($filename))."\r\n"; 
           $body .= "\r\n";
           $body .= chunk_split(base64_encode(file_get_contents($path)));
       }
       
       $body .= "\r\n--$boundary--\r\n";
   
   } else {
       $headers .= "Content-Type: $msgType; charset=UTF-8\r\n";
       $headers .= "Content-Transfer-Encoding: 8bit\r\n";
       $headers .= "\r\n";
       $body = $message;
   }
   mb_internal_encoding($mb_internal_encoding);
   return mail($to, $subject, $body, $headers);
}

//ф-ия для определения подстановки слов год/года/лет
function YearTextArg($year) {
    $year = abs($year);
    $t1 = $year % 10;
    $t2 = $year % 100;
    return ($t1 == 1 && $t2 != 11 ? "год" : ($t1 >= 2 && $t1 <= 4 && ($t2 < 10 || $t2 >= 20) ? "года" : "лет"));
}

?>