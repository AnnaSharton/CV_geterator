<?php
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");
require_once __DIR__.'/data.php'; 

$error = [];  // массив ошибок

if (!empty($_POST)) { // если в массиве post есть какие-то данные, включаю их в массив файла data.php
    $arr = data_upload($arr); 
}

if (isset($_POST['open']) || isset($_POST['download'])) {
    //для всех полей - проверка на пустое значение
    foreach ($arr as $k => $v) {
        if ($arr[$k]['required'] && empty($arr[$k]['value'])) { 
        $error[$k] = "Не заполнено поле {$arr[$k]['field']}";  
        }
    }

    //для имен дополнительная валидация, нет ли в них спец.символов
    if (findChar($arr['name']['value'])) {$error['name'] = 'Не корректно указано имя';} else {}
    if (findChar($arr['surname']['value'])) {$error['surname'] = 'Не корректно указана фамилия';} else {}
   
    //для почты доп.валидация
    if (!strpos($arr['email']['value'], '.') || !strpos($arr['email']['value'], '@')) {$error['email'] = 'Не корректно указана почта';} else {}

    //для поля "отчество" доп.валидация, т.к. поле not required, д.б. выбрано что то одно - или чекбокс или заполнен инпут
    if ((empty($arr['parent']['value']) && empty($arr['parent-not']['value'])) || 
       (!empty($arr['parent']['value']) && !empty($arr['parent-not']['value'])) || 
       findChar($arr['parent']['value'])) {$error['parent'] = 'Не корректно указано отчество';} else {}

    //для поля "файл" 
    if (!is_uploaded_file($_FILES['image']['tmp_name'])) {$error['image'] = 'Не загружено фото';} else {}

    if (count($error) > 0) {
        echo '<div class="error">Проверьте корректность заполнения формы</div>';
    }
    // если заполнены все поля
    else {
        session_start();
        $_SESSION['array'] = $arr; //сохраняю полученный из post массив в сессию для передачи на страницу pdf-creator.php, а в action form оставляю index.php чтобы ошибки обрабатывались на главной странице
        $fileName = basename($_FILES['image']['name']); //получаю имя файла
        move_uploaded_file($_FILES['image']['tmp_name'], $fileName); //загружаю файл и сохраняю в сессию для передачи на pdf-creator
        cropImage($fileName); //обрезаю фото
        $_SESSION['data']=$fileName; //сохраняю файл в сессию
        header('Location:pdf-creator.php'); //перенаправляю в pdf-creator
    } 
   
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV-Creator</title>
    <style>
        .container {padding: 0 16% 4% 16%;}
        h1 {text-align: center;}
        h4 {margin: 7px 0 0 0;}
        fieldset {background-color: #e9f0eb; padding: 20px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 7px;}
        legend {font-weight: 700; font-size: 18px;}
        input[type='text'], input[type='date'], input[type='tel'], input[type='checkbox'], input[type='number'], input[type='textarea']  {padding: 5px;}
        input[type='month']  {padding: 3.5px;}
        input[type='file'] {font-size: 16px;}
        button {background: #9ccf95fe; border: 1px gray solid; padding: 10px; cursor: pointer; font-size: 18px; border-radius: 3px;}
        .error {border: 1px solid gray; background: #faa5a5; padding: 5px; margin: 0 16%;}
        .err {font-size: 16px; color: red;}
        .input-err {border: 2px solid red;}
        .buttons {display: flex; gap: 20px; justify-content: center;}
        .block {display: flex; gap:30px;}
        textarea {resize: vertical;}
        .education, .specializing {width: 80%}
        .hide {display: none;}
        .hide input[type='text'] {margin-bottom: 5px;}
        .hide:target{display: block;}
    </style>
</head>
<body>
    <div class="container">
        <h1>Введите ваши данные для отправки резюме</h1>
        <p>Поля, отмеченные '*', обязательны для заполнения</p>

        <!--------------------ФОРМА ------------------->
        <!--action оставляю пустым чтобы обрабатывались ошибки на index.php
        инпуты вывожу с помощью функции-->
        <form method="post" action="" enctype="multipart/form-data">
        
                <fieldset>
                    <legend>Персональные данные</legend>
                        <?php showInput($arr, 0, 5, $error); ?>
                </fieldset>

                <fieldset>
                    <legend>Контактные данные</legend>   
                        <?php showInput($arr, 6, 2, $error);?>
                        
                        <h4>Адрес регистрации</h4>
                        
                        <div class="block">
                            <?php showInput($arr, 8, 3, $error);?>
                        </div>

                        <h4>Место жительства</h4>

                            <?php showInput($arr, 11, 1, $error);?>
                        <div class="block">
                            <?php showInput($arr, 12, 3, $error);?>
                        </div>      
                </fieldset>

                <fieldset>
                    <legend>Должность</legend> 
                        <?php showInput($arr, 15, 2, $error);?>  
                </fieldset>

                <fieldset>
                    <legend>Образование</legend> 
                        <?php showInput($arr, 17, 3, $error);?>  
                </fieldset>

                <fieldset>
                    <legend>Опыт работы</legend> 

                        <?php showInput($arr, 20, 1, $error);?>  
                        
                        <h4>Последнее место работы</h4>
                        <div class="block">
                            <?php showInput($arr, 21, 3, $error);?>
                        </div>
                            <?php showInput($arr, 24, 2, $error);?>

                        <a href="#second-job">Добавить еще одно место работы<a><br>
                        <div class="hide"  id="second-job">
                            <div class="block">
                                <?php showInput($arr, 26, 3, $error);?>
                            </div>  
                                <?php showInput($arr, 29, 2, $error);?>
                        </div>

                </fieldset>

                <fieldset>
                    <legend>Фотография</legend>
                        <label>Выберите фото портретной ориентации для загрузки (jpg/png)*&nbsp;&nbsp;
                        <input type="file" name="image" accept="image/*"></label><!--указываю группу допустимых файлов - любые графические--> 
                        <?php showError('image', $error); ?><br> 
                </fieldset>

            
                <?php showInput($arr, 31, 1, $error);?><br> 
            <div class="buttons">    
                <button name="open" value="2" type="submit">Создать и открыть</button>
                <button name="download" value="1" type="submit">Скачать</button>
            </div>

        </form>
    </div>
</body>


<?php  
// ф-ия для вывода полей типов текст, число, дата. id задала для тех инпутов, к которым применяла ф-ию для автоматического заполнения с помощью ф-ии sameAdress() 
//для всех полей сохраняю введенные данные value при неправильном заполнении формы
//start - индекс ключа массива с данными, с которого нужно выводить, до ключа с индексом finish, error - массив куда попадают ошибки при валидации формы
function showInput($arr, $start, $finish, $error){
    $i = 0;
    foreach ($arr as $k => $v) {   
        if ($arr[$k]['id'] < $start) continue;  
        ?>   
        <div>
            <label><?=$arr[$k]['field']?>
        <?php  // для textarea отдельный вывод
            if ($arr[$k]['type'] === 'textarea') { ?>
                <br>
                <textarea 
                rows="7" cols="65" 
                name="<?=$arr[$k]['key']?>" 
                placeholder="<?=$arr[$k]['placeholder']?>"
                maxlength="237"
                ><?php if(isset($_POST[$arr[$k]['key']])) {echo htmlentities($_POST[$arr[$k]['key']]);}?>
                </textarea>
        <?php   // для чекбоксов отдельный вывод
            } else if ($arr[$k]['type'] === 'checkbox') { ?>
                <input type="checkbox" id="<?=$arr[$k]['key']?>" name="<?=$arr[$k]['key']?>" 
                <?php if ($arr[$k]['key'] === 'same-adress') { ?> onchange="javascript:sameAdress()" <?php } else {} ?>
                <?php if(isset($_POST[$arr[$k]['key']]) && !empty($_POST[$arr[$k]['key']])) { ?> checked <?php } ?>
                ></label>

            <?php // остальное для текст/число/дата
            } else { ?> 
                <br><input 
                type="<?=$arr[$k]['type']?>" 
                name="<?=$arr[$k]['key']?>" 
                value="<?=$var_name = array_key_exists($arr[$k]['key'], $_POST) ? $arr[$k]['value'] : ''?>" 
                class="<?=$redInput = array_key_exists($arr[$k]['key'], $error) ? 'input-err '.$arr[$k]['key'] : $arr[$k]['key'] ?>" 
                placeholder="<?=$arr[$k]['placeholder']?>"
                id="<?=$arr[$k]['key']?>" 
                min="<?=$arr[$k]['min']?>"
                max="<?=$arr[$k]['max']?>"
                <?php if ($arr[$k]['type'] == 'tel') {?> maxlength="11" minlength="6" pattern="[0-9]{6-11}" <?php } else { ?> maxlength="70" <?php } ?>
                >
            <?php } ?>

            </label><br>
            <?php showError($arr[$k]['key'], $error); ?>
        </div>
    <?php if (++$i == $finish) break; 
    }
}

// ф-ия для загрузки данных, полученных методом post в массив $arr файла data.php
function data_upload($arr) { 
    foreach ($_POST as $k => $val) { // прохожу по ключам и значениям, полученным из полей ввода формы
        if(array_key_exists($k, $arr)) { // если в массиве в файле data.php есть такой же ключ, как и в массиве post
            $arr[$k]['value'] = trim($val);// создаю в массиве элемент с ключом value, содержащий значение из поля ввода
        } else {}
    } 
    return $arr;
}

function boldString($str) {
    return '<b>'.$str.'</b>';
}
//функция для вывода ошибок
function showError($k, $array) {
    if (array_key_exists($k, $array)) { 
        echo '<span class="err">'.$array[$k].'</span>'; 
   } else {}
}

//функция для поиска недопустимых символов в имени пользователя
function findChar($string) { 
    $arr=array('.', ',', ';', '<', '>', '/', '|', '\'', ':', '!', '#', '@', '%', '^', '&', '*', '(', ')', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    foreach ($arr as $v) {
    if (strpos($string, $v)!== false) {return true;} else {}
    } return false;
}

//ф-ия для обработки загруженной фото
function cropImage($image) {
    $im = '';
    $ext=strtolower(pathinfo($image, PATHINFO_EXTENSION));//узнаю расширение файла и перевожу его в нижний регистр если оно окажется PNG/JPEG
    switch ($ext) {
    case 'jpeg': case 'jpg': //если у файла такие расширения
        $im = imagecreatefromjpeg($image);//создаю изображение из файла
        break;
    case 'png':
        $im = imagecreatefrompng($image);
        imageinterlace($im, false);
        break;
    default:
        return false;
    }
    $size = getimagesize($image); //получаю массив с информацией о фото
    $width = $size[0]; //ширину из массива
    $height = $size[1]; //высоту
   
    // определяю соотношение сторон фото, является ли оно больше 1,3 , тогда обрезаю фото
    if (($width > $height && $width/$height > 1.3) || ($width < $height && $height/$width > 1.3)) {
    //обрезаю фото так, чтобы примерно получить портретную фото 4:3
    $w = $width > $height ? $height*0.75 : $width; //если пользователь загрузил альбомную фото, то обрезаю так, чтобы ширина фото стала меньше высоты
    $h = $width < $height ? $width*1.3333 : $height; // если пользователь загрузил портретную фото, обрезаю высоту, если фото слишком длинное, чтобы высота была равна ширине*1,3, чтобы получить соотношение сторон 4:3
    $x = $width > $height ? $width/2.5 : 0; // если пользователь загрузил фото альбомной ориентации, пытаюсь центрировать обрезку, обрезать не с края, а с четверти ширины, если предположить, что лицо в центре фото

    $im = imagecrop($im, ['x' => $x, 'y' => 0, 'width' => $w, 'height' => $h]); 
    } else {} // в обратном случае - фото 1:1 или ближе к квадратному, его не обрезаю
    switch ($ext) {
        case 'jpeg': case 'jpg': //вывожу jpg в файл
            imagejpeg($im, $image);
            break;
        case 'png': //вывожу png в файл
            imagesavealpha($im, true);
            imagepng($im, $image); //
            break;
    }

}
?>

<script> 
//ф-ия на js для переноса value из одного инпута в другой, если пользователь нажал checkbox
function sameAdress() {
    const sameCheck = document.getElementById('same-adress'); //нахожу все элементы по id
    const city = document.getElementById('city');
    const city1 = document.getElementById('city1');
    const street = document.getElementById('street');
    const street1 = document.getElementById('street1');
    const house = document.getElementById('house');
    const house1 = document.getElementById('house1');
    if(sameCheck.checked) { // если чекбокс выбран
        city1.value = city.value; // присваюваю инпуту значение, введенное в другой инпут
        street1.value = street.value;
        house1.value = house.value;
    } else {
    }
}
window.location.hash = '';//сбрасываю target second job при перезагрузке страницы
</script>

</html>