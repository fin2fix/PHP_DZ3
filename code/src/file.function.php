<?php

// function readAllFunction(string $address) : string {
function readAllFunction(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "rb");

        $contents = '';

        while (!feof($file)) {
            $contents .= fread($file, 100);
        }

        fclose($file);
        return $contents;
    } else {
        return handleError("Файл не существует");
    }
}

// function addFunction(string $address) : string {
function addFunction(array $config): string
{
    $address = $config['storage']['address'];

    $name = readline("Введите имя: ");
    $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");

    if (!validate($date)) {
        return handleError("Ошибка, дата введена в неверном формате");
    }

    $data = $name . ", " . $date . "\r\n";

    $fileHandler = fopen($address, 'a');

    if (fwrite($fileHandler, $data)) {
        fclose($fileHandler);
        return "Запись $data добавлена в файл $address";
    } else {
        fclose($fileHandler);
        return handleError("Произошла ошибка записи. Данные не сохранены");
    }
}

// function clearFunction(string $address) : string {
function clearFunction(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "w");

        fwrite($file, '');

        fclose($file);
        return "Файл очищен";
    } else {
        return handleError("Файл не существует");
    }
}

function helpFunction()
{
    return handleHelp();
}

function readConfig(string $configAddress): array|false
{
    return parse_ini_file($configAddress, true);
}

function readProfilesDirectory(array $config): string
{
    $profilesDirectoryAddress = $config['profiles']['address'];

    if (!is_dir($profilesDirectoryAddress)) {
        mkdir($profilesDirectoryAddress);
    }

    $files = scandir($profilesDirectoryAddress);

    $result = "";

    if (count($files) > 2) {
        foreach ($files as $file) {
            if (in_array($file, ['.', '..']))
                continue;

            $result .= $file . "\r\n";
        }
    } else {
        $result .= "Директория пуста \r\n";
    }

    return $result;
}

function readProfile(array $config): string
{
    $profilesDirectoryAddress = $config['profiles']['address'];

    if (!isset($_SERVER['argv'][2])) {
        return handleError("Не указан файл профиля");
    }

    $profileFileName = $profilesDirectoryAddress . $_SERVER['argv'][2] . ".json";

    if (!file_exists($profileFileName)) {
        return handleError("Файл $profileFileName не существует");
    }

    $contentJson = file_get_contents($profileFileName);
    $contentArray = json_decode($contentJson, true);

    $info = "Имя: " . $contentArray['name'] . "\r\n";
    $info .= "Фамилия: " . $contentArray['lastname'] . "\r\n";

    return $info;
}

function searchFunction(array $config): string
{
    $address = $config['storage']['address'];
    $users = [];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "rb");

        while (!feof($file)) {
            $user = fgets($file);
            $userData = explode(',', $user);

            if (isset($userData[1])) {
                $dateData = explode('-', trim($userData[1]));

                if ($dateData[0] == date('d') && $dateData[1] == date('m')) {
                    $users[] = $userData[0];
                }
            }
        }
        $contents = '';

        if (empty($users)) {
            $contents = 'На сегодняшний день именинников нет';
        } else {
            $contents .= "Сегодня " . date("d.m.y") . " день рождения отмечают следующие сотрудники: \r\n";
            foreach ($users as $user) {
                $contents .= $user . "\r\n";
            }
        }
        fclose($file);
        return $contents;
    } else {
        return handleError("Файл не найден");
    }
}

function deleteFunction(array $config): string
{
    $address = $config['storage']['address'];
    $name = readline("Введите имя сотрудника для удаления из базы: ");
    $users = [];

    if (file_exists($address) && is_readable($address) && is_writable($address)) {
        $users = file($address);
        $contents = '';
        $flag = false;

        foreach ($users as $key => $user) {
            $userData = explode(',', $user);

            if ($userData[0] == $name) {
                unset($users[$key]);
                $flag = true;
                $contents .= "\r\n" . "Пользователь " . $name . " удален из базы" . "\r\n";
            }
        }
        if(!$flag){
            $contents .= "\r\n" . "Пользователь " . $name . " не найден в базе" . "\r\n";
        }
        $file = fopen($address, "w");
        fputs($file, implode("", $users));
        fclose($file);
        return $contents;

    } else {
        return handleError("Файл не найден");
    }
}