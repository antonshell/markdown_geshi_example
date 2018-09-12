# Пишем CRUD-генератор для Magento 2

<div class="image-wrapper">
    <img src="img/magento-2-crud-generator/1.jpg">
</div>

Создание GRUD и связанной логики в Magento 2 может быть непростой задачей.

Достаточно прочитать <a href="https://habrahabr.ru/post/310098/">это руководство</a>, чтобы понять это.

Постепенно привыкаешь и все это уже выглядит не так сложно.
Но, если ты только начал работать с magento, то создание grid в админке magento напоминает ритуал воскрешения сатаны.
Если же понадобится сделать еще один модуль с grid, то весь процесс придется повторить.

<!--more-->

В этой статье я покажу, как создать свой генератор grid. Те же подходы можно использовать для генерации произвольного кода.
Для Magento 2 уже существуют генераторы кода. Если нужно просто сделать grid, то, возможно, стоит воспользоваться одгим из них.

https://github.com/staempfli/magento2-code-generator
https://github.com/Krifollk/magento2-code-generator

Однако, если нужно сгенерировать кастомный код, то статья может быть полезна. Итак, приступим!

Создим php скрипт <code>generate.php</code> с таким кодом:

``` php
<?php

use src\FileService;

include '_bootstrap.php';
include '_config.php';

$config = require '_config.php';
$variables = require '_variables.php';

$service = new FileService();

$basePath = __DIR__ . '/template';
$outPath = $config['outPath'];

$files = $service->getDirContents($basePath);

$service->recurseRmdir($outPath);
mkdir($outPath);

foreach ($files as $file){
    if(is_dir($file)){
        $file = str_replace($basePath,'', $file);
        $file = $outPath . $file;

        echo $file . "\n";

        $file = $service->processFileName($file,$variables);

        if(!file_exists($file)){
            mkdir($file,0777,true);
        }
    }
}

foreach ($files as $file){
    if(is_dir($file)){
       continue;
    }

    $content = file_get_contents($file);

    $file = str_replace($basePath,'', $file);
    $file = str_replace('_inc','', $file);

    $file = $service->processFileName($file,$variables);


    $file = $outPath . $file;

    $path = dirname($file);

    $content = $service->processContent($content,$variables);

    file_put_contents($file,$content);
}


echo "Job is done\n";
```

Редактируем конфиг <code>_config.php</code>. Указываем путь к установке Magento. Точнее, путь к модулю.

``` php
<?php

return [
    'magento_path' => '/var/www/MAGENTO/app/code/Company/Filters'
];
```

Редактируем <code>_variables.php</code>. Указываем параметры, которые мы будем использовать в генераторе. Название сущности, модуля и т.д.

``` php
<?php

return [
    'vendor' => 'Company',
    'module_name' => 'Filters',
    'module_name_underscore' => 'DIY_Filters',
    'view_path' => 'filters',
    'entity' => 'Filter',
    'view_path_ucf' => 'Filters',
    'entity_lower' => 'filter',
    'setup_version' => '1.0.0',
    'table_name' => 'company_filter',
];
```

Берем уже реализованный grid в качестве шаблона. Кладем в папку <code>template</code>.
Для подстановки placeholder в файлы используем тройные фигурные скобки.


``` php
<?php

namespace {{{vendor}}}\{{{module_name}}}\Block\Adminhtml;
```

Для подстановки placeholder в имена файлов используем тройное нижнее подчеркивание. Например так: <code>___entity___.php_inc</code>

Также имеет смысл поменять расширения файлов .php -> .php_inc. Чтобы убрать исправление ошибок IDE.
Логика работы генератора вынесена в класс: <code>src/FileService.php</code>.
Замену для контента производим таким образом:

``` php
public function processContent($content,$variables)
    {
        foreach($variables as $key => $value){
            $content = str_replace('{{{'.strtolower($key).'}}}', $value, $content);
        }

        return $content;
    }
```
Для имен файлов - через тройное подчеркивание:

``` php
public function processFileName($file, $variables)
    {
        foreach($variables as $key => $value){
            $file = str_replace('___'.strtolower($key).'___', $value, $file);
        }

        return $file;
    }
```

Проверяем работу скрипта:

``` bash
php generate.php
```

Чтобы включить модуль в magento - переходим в папку с magento и выполняем команды.

``` bash
cd /magento2-project
bin/magento module:enable Vendor_ModuleName
bin/magento cache:clean
bin/magento setup:upgrade
```

В результате получаем простой и настраиваемый CRUD генератор.
Код <a href="https://github.com/antonshell/magento2-crud-generator">доступен на github</a>.

На этом пока все. Спасибо за внимание!