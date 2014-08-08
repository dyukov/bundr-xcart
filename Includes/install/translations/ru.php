<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */


/**
 * X-Cart installation texts (Russian)
 */


if (!defined('XLITE_INSTALL_MODE')) {
  die('Incorrect call of the script. Stopping.');
}

$translation = array (
  'Installation script' => 'Мастер установки',
  'Installation wizard' => 'Мастер установки',
  'PHP version' => 'Версия PHP',
  'PHP safe_mode' => 'Безопасный режим PHP',
  'Disabled functions' => 'Отключенные функции',
  'Memory limit' => 'Предел памяти',
  'File uploads' => 'Загрузка файлов',
  'MySQL support' => 'Поддержка MySQL',
  'PDO extension' => 'Расширение PDO',
  'Upload file size limit' => 'Предел размера загружаемого файла',
  'Memory allocation test' => 'Проверка распределения памяти',
  'Recursion test' => 'Проверка рекурсии',
  'File permissions' => 'Права на файлы',
  'MySQL version' => 'Версия MySQL',
  'GDlib extension' => 'Расширение GDlib',
  'Phar extension' => 'Расширение Phar',
  'HTTPS bouncers' => 'HTTPS баунсеры',
  'XML extensions support' => 'Поддержка XML расширений',
  'Internal error: function :func() does not exists' => 'Внутренняя ошибка: функция :func() не существует',
  'Checking requirements is successfully complete' => 'Проверка требований успешно завершена',
  'Some requirements are failed' => 'Не все требования выполнены',
  'X-Cart 5 installation script not found. Restore it  and try again' => 'Файл установки X&#8209;Cart 5 не найден. Восстановите файл и попробуйте снова.',
  'PHP Version must be :minver as a minimum' => 'Версия PHP должна быть как минимум :minver',
  'PHP Version must be not greater than :maxver' => 'Весрия PHP должнв быть не выше :maxver',
  'Unsupported PHP version detected' => 'Обнаружена неподдерживаемая версия PHP',
  'PHP option sql.safe_mode value should be Off' => 'Значение PHP опции sql.safe_mode должно быть Off',
  'Unlimited' => 'Без ограничений',
  'PHP memory_limit option value should be :minval as a minimum' => 'Значение PHP опции memory_limit должно быть как минимум :minval',
  'PHP file_uploads option value should be On' => 'Значение PHP опции file_uploads должно быть On',
  'Support MySQL is disabled in PHP. It must be enabled.' => 'Поддержка MySQL в PHP отключена. Необходимо ее включить.',
  'PDO extension with MySQL support must be installed.' => 'Необходимо установить расширение PDO  с поддержкой MySQL',
  'PHP option upload_max_filesize should contain a value. It is empty currently.' => 'Необходимо задать значение для PHP опции upload_max_filesize. Сейчас значение отсутствует.',
  'PHP allow_url_fopen option value should be On' => 'Статус PHP опции allow_url_fopen должен быть On',
  'Memory allocation test failed. Response:' => 'Проверка распределения памяти не выполнена. Ответ:',
  'Recursion test failed.' => 'Проверка рекурсии не выполнена',
  'unknown' => 'не известно',
  'Can\'t connect to MySQL server' => 'Нет соединения с MySQL сервером',
  'MySQL version must be :minver as a minimum.' => 'Версия MySQL должна быть как минимум :minver.',
  'Cannot get the MySQL server version' => 'Не удалось определить версию MySQ: сервера',
  'GDlib extension v.2.0 or later required for some modules.' => 'Для некоторых модулей необходимо расширение GDlib версии 2.0 или выше',
  'Phar extension is not loaded' => 'Расширение Phar не загружено',
  'libcurl extension is not found' => 'Расширение libcurl не найдено',
  'libcurl extension found but it does not support secure protocols' => 'Расширение libcurl обнаружено, но оно не поддерживает безопасные протоколы',
  'XML/Expat and DOM extensions are required for some modules.' => 'Для некоторых модулей необходимы расширения XML/Expat и DOM',
  'config_writing_error' => 'Не удалось открыть файл конфигурации \':configfile\' для записи. Установка прервана из-за данной непредвиденной ошибки. Пожалуйста, исправьте проблему и запустите установку снова.',
  'mysql_connection_error' => 'Не удалось подключиться к MySQL sсерверу:pdoerr.<br />Установка прервана из-за данной непредвиденной ошибки. Пожалуйста, исправьте проблему и запустите установку снова.',
  'doRemoveCache() failed' => 'Ошибка в doRemoveCache()',
  'Creating directories...' => 'Идет создание директорий...',
  'Creating .htaccess files...' => 'Идет создание .htaccess файлов...',
  'Copying templates...' => 'Идет копирование темплейтов...',
  'copy_files() failed' => 'Ошибка в copy_files()',
  'Updating config file...' => 'Идет обновление файла конфигурации...',
  'fatal_error_creating_dirs' => 'Во время создания директорий возникла неустранимая ошибка; вероятно, причина ошибки - неверные права на директории. Установка прервана из-за данной непредвиденной ошибки. Пожалуйста, исправьте проблему и запустите установку снова.',
  'Login and password can\'t be empty.' => 'Поля Логин и Парль не могут быть пустыми',
  'Updating primary administrator profile...' => 'Идет обновление основного администраторского профиля...',
  'Registering primary administrator profile...' => 'Регистрация основного администраторского профиля...',
  'ERROR' => 'ОШИБКА',
  'cannot_connect_mysql_server' => 'Не удалось установить соединение с MySQL сервером или выбранной базой данных :pdoerr.<br />Нажмите кнопку \'НАЗАД\' и проверьте введенные данные о MySQL сервере.',
  'script_renamed_text' => '
В целях защиты вашей установки X&#8209;Cart 5, файл "install.php" был переименован в ":newname".

При возникновении необходимости переустановки X&#8209;Cart 5, переименуйте файл ":newname" обратно в "install.php" и пройдите по следующей ссылке:
     http://:host:webdir/install.php
',
  'script_renamed_text_html' => '
<p>В целях защиты вашей установки X&#8209;Cart 5, файл "install.php" был переименован в ":newname".</p>

<p>При возникновении необходимости переустановки X&#8209;Cart 5, переименуйте файл ":newname" обратно в "install.php"</p>',
  'script_cannot_be_renamed_text' => '<P><font color="red"><b>ВНИМАНИЕ!</b> Не удалось переименовать файл install.php! Чтобы обезопасить свою установку X&#8209;Cart 5 и предотвратить неавторизованное использование файла, переименуйте или удалите файл вручную.</font></P>',
  'correct_permissions_text' => '
Перед началом работы с магазином X&#8209;Cart 5 необходимо установить следующие безопасные права на файлы:<br /><br />

<code>:perms</code>
',
  'congratulations_text' => 'Поздравляем!

Магазин на базе X&#8209;Cart 5 успешно установлен и доступен по следующим ссылкам:

ЗОНА ПОКУПАТЕЛЯ (ГЛАВНАЯ СТРАНИЦА)
     http://:host:webdir/cart.php

ЗОНА АМИНИСТРАТОРА (ПАНЕЛЬ УПРАВЛЕНИЯ)
     http://:host:webdir/admin.php
     Логин (e-mail): :login
     Пароль:       :password

:perms

:renametext

Код авторизации для запуска install.php: :authcode

Ключ Безопасности магазина :safekey. Этот ключ позволяет использовать магазин в безопасном режиме с отключением всех установленных модулей. Пожалуйста, сохраните этот ключ, он может понадобиться для восстановления магазина в случае его поломки из-за проблем совместимости модулей (например, если магазин выйдет из строя после установки неисправного модуля, или один из установленных модулей станет несовместимым с ядром X&#8209;Cart после апгрейда).

Благодарим вас за выбор X&#8209;Cart 5!

--
Мастер Установки X&#8209;Cart 5

',
  'Installation complete' => 'Установка завершена',
  'X-Cart 5 software has been successfully installed and is now available at the following URLs:' => 'Магазин на базе X&#8209;Cart 5 успешно установлен и доступен по следующим ссылкам:',
  'CUSTOMER ZONE (FRONT-END)' => 'ЗОНА ПОКУПАТЕЛЯ (ГЛАВНАЯ СТРАНИЦА)',
  'ADMINISTRATOR ZONE (BACKOFFICE)' => 'ЗОНА АМИНИСТРАТОРА (ПАНЕЛЬ УПРАВЛЕНИЯ)',
  'Your auth code for running install.php in the future is:' => 'Код авторизации для запуска install.php в будущем:',
  'PLEASE WRITE THIS CODE DOWN UNLESS YOU ARE GOING TO REMOVE ":filename"' => 'ПОЖАЛУЙСТА, ЗАПИШИТЕ ЭТОТ КОД, ЕСЛИ ТОЛЬКО ВЫ НЕ СОБИРАЕТЕСЬ УДАЛЯТЬ ":filename"',
  'Creating directory: [:dirname]... ' => 'Идет создание директории: [:dirname]...',
  'Already exists' => 'Уже существует',
  'Failed to create directories' => 'Не удалось создать директории',
  'Creating file: [:filename]... ' => 'Идет создание файла: [:filename]...',
  'Failed to create files' => 'Не удалось создать файлы',
  'Click here to see more details' => 'Нажмите здесь для получения подробной информации',
  'Failed' => 'Внимание',
  'Skipped' => 'Пропущено',
  'Fatal error' => 'Неустранимая ошибка',
  'Please correct the error(s) before proceeding to the next step.' => 'Пожалуйста, исправьте ошибку(и), прежде чем перейти к следующему шагу',
  'Please correct the error(s) before proceeding to the next step or get help.' => 'Пожалуйста, исправьте ошибку(и), прежде чем перейти к следующему шагу. Если вы не знаете, как решить проблему, пожалуйста, обратитесь за помощью к <em>хостинг-провайдеру</em> или отправьте нам <em>сведения об ошибке установки</em>, специалисты X&#8209;Cart помогут вам найти решение.',
  'Warning' => 'Внимание',
  'Installation script renamed to :filename' => 'Файл установки переименован в :filename',
  'Warning! Installation script renaming failed' => 'Внимание! Не удалось переименовать файл установки.',
  'Incorrect auth code! You cannot proceed with the installation.' => 'Неверный код авторизации! Установка не может быть продолжена.',
  'Config file not found (:filename)' => 'Файл конфигурации (:filename) не найден',
  'Cannot open config file \':filename\' for writing!' => 'Не удалось открыть для записи файл конфигурации  \':filename\'',
  'Config file \':filename\' write failed!' => 'Не удалось сделать запись в файле конфигурации \':filename\'! Отказано в доступе.',
  'You must accept the License Agreement to proceed with the installation. If you do not agree with the terms of the License Agreement, do not install the software.' => 'Чтобы начать установку, необходимо принять условия Лицензионного соглашения. Если вы не принимаете условия Лицензионного соглашения, не устанавливайте программное обеспечение.',
  'Environment checking' => 'Проверка среды',
  'Inspecting server configuration' => 'Проверка конфигурации сервера',
  'Environment' => 'Среда',
  'Environment checking failed' => 'Проверка среды не завершена',
  'Critical dependencies' => 'Критические требования',
  'Critical dependency failed' => 'Критическое требование не выполнено',
  'Critical dependencies failed' => 'Необходимые библиотеки или программное обеспечение отсутствует',
  'Non-critical dependencies' => 'Некритические требования',
  'Non-critical dependencies failed' => 'Рекомендованные библиотеки или программное обеспечение не найдено',
  'Web server name' => 'Имя web-сервера',
  'Hostname of your web server (E.g.: www.example.com).' => 'Имя хоста сервера (напр., www.пример.com)',
  'Secure web server name' => 'Безопасное имя web-сервера',
  'Hostname of your secure (HTTPS-enabled) web server (E.g.: secure.example.com). If omitted, it is assumed to be the same as the web server name.' => 'Безопасный (HTTPS-enabled) web-сервер (например: secure.example.com).<br />Если пропущен, то предполагается, что это совпадает с именем веб-сервера.',
  'X-Cart 5 web directory' => 'Веб-директория X&#8209;Cart 5',
  'Path to X-Cart 5 files within the web space of your web server (E.g.: /shop).' => 'Путь к файлам X&#8209;Cart 5 на сервере (напр., /shop).',
  'MySQL server name' => 'Имя MySQL сервера',
  'Hostname or IP address of your MySQL server.' => 'Имя хоста MySQL сервера или IP адрес',
  'MySQL server port' => 'Порт MySQL сервера',
  'If your database server is listening to a non-standard port, specify its number (e.g. 3306).' => 'Если ваш сервер использует нестандартный порт, укажите его здесь (например 3306)',
  'MySQL server socket' => 'Сокет MySQL сервера',
  'If your database server is used a non-standard socket, specify it (e.g. /tmp/mysql-5.1.34.sock).' => 'Если ваш сервер базы использует нестандартный сокет, укажите его здесь (напр., /tmp/mysql-5.1.34.sock).',
  'MySQL database name' => 'Имя MySQL базы данных',
  'The name of the existing database to use (if the database does not exist on the server, you should create it to continue the installation).' => 'Имя готовой базы данных, которая будет использована (если база данных не существует на сервере, скрипт установки попытается создать ее).',
  'MySQL username' => 'Имя пользователя MySQL',
  'MySQL username. The user must have full access to the database specified above.' => 'Имя пользователя MySQL. Пользователь должен иметь неограниченный доступ к вышеобозначенной базе данных.',
  'MySQL password' => 'MySQL пароль',
  'Password for the above MySQL username.' => 'Пароль вышеупомянутого MySQL пользователя',
  'Install sample catalog' => 'Установить каталог демо-товаров',
  'Specify whether you would like to setup sample categories and products?' => 'Установить пробные категории и товары?',
  'Yes' => 'Да',
  'No' => 'Нет',
  'The web server name and/or web drectory is invalid! Press \'BACK\' button and review web server settings you provided' => 'Неверное имя веб-сервера и/или веб-директории! Нажмите кнопку \'НАЗАД\' и проверьте введенные настройки веб-сервера.',
  'Cannot open file \':filename\' for writing. To install the software, please correct the problem and start the installation again...' => 'Не удалось открыть файл \':filename\' для редактирования. Устраните проблему и запустите установку снова.',
  'Installation Wizard has detected X-Cart 5 tables' => 'Мастер Установки обнаружил, что в указанной базе данных есть есть таблицы X&#8209;Cart 5. Если установка будет продолжена, таблицы будут удалены. <br /><br />Нажмите \'Назад\', чтобы указать другую базу данных, или \'Далее\', чтобы продолжить установку и удалить и переписать существующую базу данных.',
  'Can\'t connect to MySQL server specified:pdoerr<br /> Press \'BACK\' button and review MySQL server settings you provided.' => 'Не удалось установить соединенис с MySQL сервером :pdoerr<br /> Нажмте НАЗАД\' и поверьте введенные настройки MySQL сервера.',
  'The database <i>:dbname</i> cannot be created automatically:pdoerr.<br /> Please go back, create it manually and then proceed with the installation process again.' => 'Невозможно создать базу данных <i>:dbname</i> автоматически:pdoerr.<br /> Пожалуйста, вернитесь на предыдущий шаг, создайте базу вручную и продолжите установку.',
  'You must provide web server name' => 'Необходимо указать имя сервера',
  'You must provide MySQL server name' => 'Необходимо указать имя MySQL сервера',
  'You must provide MySQL username' => 'Необходимо указать имя пользователя MySQL',
  'You must provide MySQL database name' => 'Необходимо указать имя MySQL базы данных',
  'Building cache notice' => 'Идет подготовка вашего магазина к работе, обычно, она занимает не больше минуты. По завершении, нажмите \'Далее\' и приступайте к работе.',
  'E-mail' => 'E-mail',
  'E-mail address of the store administrator' => 'Адрес электронной почты администратора магазина',
  'Password' => 'Пароль',
  'Confirm password' => 'Подтверждение пароля',
  'E-mail and password that you provide on this screen will be used to create primary administrator profile. Use them as credentials to access the Administrator Zone of your online store.' => 'E-mail-адрес и пароль, введенные на этой странице, будут использованы для создания основного администраторского профиля. Они являются именем пользователя и паролям для входа в основную учетную запись администратора.',
  'Please, enter non-empty password' => 'Пожалуйста, введите пароль',
  'Please, enter non-empty password confirmation' => 'Пожалуйста, подтвердите пароль',
  'Password doesn\'t match confirmation!' => 'Пароли не совпадают!',
  'Please, specify a valid e-mail address!' => 'Пожалуйста, укажите действующий e-mail-адрес',
  'Permissions checking failed. Please make sure that the following files have writable permissions:<br /><br /><i>:perms</i>' => 'Проверка требований не завершена. Пожалуйста, убедитесь, что следующие файлы доступны для записи :<br /><br /><i>:perms</i>',
  'Permissions checking failed. Please make sure that the following file permissions are assigned (UNIX only):<br /><br /><i>:perms</i>' => 'Проверка разрешений не завершена. Пожалуйста, убедитесь, что установлены слеующие права на файлы (только для ОС UNIX):<br /><br /><i>:perms</i>',
  'Cache building procedure failed:<br />nnRequest URL: :requesturl<br />nnResponse: :response' => 'Не удалось сгенерировать кеш:<br />nnRequest URL: :requesturl<br />nnResponse: :response',
  'License agreement' => 'Лицензионное соглашение',
  'Configuring X-Cart 5' => 'Настройка параметров X&#8209;Cart 5',
  'Setting up templates' => 'Установка темплейтов',
  'Building cache' => 'Генерация кеш',
  'Creating administrator account' => 'Создание  учетной записи администратора',
  'Building cache: Pass #:step...' => 'Генерация кеш: Шаг #:step...',
  'Cache is built' => 'Генерация кеш завершена',
  'Building cache: Preparing for cache generation and dropping old X-Cart 5 tables if exists' => 'Генерация кеш: Подготовка к генерации кеш и удаление старых таблиц X&#8209;Cart 5 (если есть)',
  'Click here to redirect' => 'Нажмите здесь для перехода',
  'Reason: memory_get_usage() is disabled on your hosting.' => 'Причина: опция memory_get_usage() отключена на вашем сервере',
  'Fatal error: Invalid current step. Stopped.' => 'Неустранимая ошибка: Текущий шаг не выполнен. Усановка приостановлена.',
  'Internal error: function :funcname() not found' => 'Внутренняя ошибка: функция :funcname() не найдена',
  'Installation Wizard' => 'Мастер Установки',
  'Version' => 'Версия',
  'Step :step' => 'Шаг :step',
  'This installer requires JavaScript to function properly.<br />Please enable Javascript in your web browser.' => 'Для работы скрипта установки необходим JavaScript.<br />Пожалуйста, активтруйте Javascript в своем браузере.',
  'Back' => 'Назад',
  'Try again' => 'Попробуйте снова',
  'Next' => 'Далее',
  'Status' => 'Статус',
  'Non-critical dependency failed' => 'Рекомендованные библиотеки или программное обеспечение не найдено',
  'requirements_failed_text' => 'Пожалуйста, обратитесь к своему <em>провайдеру хостинга</em> или отправьте нам <em>сведения об ошибке установки</em>, и наши специалисты помогут вам найти решение.',
  'Send a report' => 'Отправить сведения об ошибках',
  'requirement_warning_text' => 'Конфигурация сервера неблагоприятна для усиановки. Это может привести к частичной или полной неработоспособности магазина X&#8209;Cart 5. <br />Продолжить установку все равно?',
  'Yes, I want to continue the installation.' => 'Да, я хочу продолжить установку.',
  '[original report]' => '[original report]',
  '[replicated report]' => '[replicated report]',
  'Report generation failed.' => 'Отчет не создан',
  'Technical problems report' => 'Отчет о технических проблемах',
  'ask_send_report_text' => 'В ходе тестирования обнаружено несколько проблем. Данный отчет о результатах тестирования будет отправлен в службу поддержки, чтобы наши специалисты проанализировать и исправить проблемы. Чтобы отслеживать ход работы, пожалуйста, укажите свой e-mail-адрес в поле ниже и войдите в своу учетную запись в <a href="https://secure.x-cart.com/" target="_blank">HelpDesk</a>. Если у вас нет учетной записи в системе HelpDesk, вы можете <a href="https://secure.x-cart.com/customer.php?area=login&amp;target=register" target="_blank">зарегистрироваться здесь</a>.',
  'See details' => 'Подробнее',
  'Hide details' => 'Скрыть детали',
  'Additional comments' => 'Дополнительные комментарии',
  'Close window' => 'Закрыть окно',
  'Auth code' => 'Код авторизации',
  'Prevents unauthorized use of installation script' => 'Предотвращает неавторизованное использование<br />скрипта установки',
  'I accept the License Agreement' => 'Я принимаю условия соглашения и <a href="http://www.x-cart.ru/privacy-policy.html" target="_blank">Правила соблюдения конфиденциальности</a>',
  'Could not find license agreement file.<br />Aborting installation.' => 'Файл лицензионного соглашения не найден.<br />Прекращение установки.',
  'lc_php_version_description' => 'Версии PHP <b>5.3.0+</b> поддерживаются.',
  'lc_php_disable_functions_description' => 'Обнаружено, что некоторые функции, используемые X&#8209;Cart 5, отключены. Убедитесь, что эти функции не упомянуты в опции "disable_functions", и все php-расширения, необходимые для работы этих функций, активированы в файле php.ini. Пожалуйста, устраните проблему и попробуйте снова.',
  'lc_php_memory_limit_description' => 'Значение PHP опции memory_limit должно быть как минимум :minval.',
  'lc_php_pdo_mysql_description' => 'При поддержке MySQL,  расширение PDO необходимо для подклчения X&#8209;Cart 5 к базе данных. Пожалуйста, убедитесь, что это расширение включено в файле php.ini, и попробуйте еще раз.',
  'lc_php_file_uploads_description' => 'Конфигурация сервера, на котором будет установлен X&#8209;Cart 5, отвечает системным требованиям; тем не менее, на сервере обнаружены некоторые проблемы с серверным программным обеспечением, которые могут отрицательно повлиять на работе X&#8209;Cart 5.<br /><br />Для стабильной работы X&#8209;Cart 5 в значении переменной upload_max_filesize в файле in php.ini необходимо указать максимальный допустимый размер загружаемых файлов.',
  'lc_php_upload_max_filesize_description' => 'Для стабильной работы X&#8209;Cart 5 в значении переменной upload_max_filesize в файле in php.ini необходимо указать максимальный допустимый размер загружаемых файлов. Пожалуйста, исправьте значение этого параметра или обратитесь в техподдержку хостинг-провайдера.',
  'lc_php_gdlib_description' => 'GDLib версии 2.0 или выше требуется для автоматической генерации уменьшенных изображений товаров из изображений товаров и для некоторых других модулей. Библиотека GDLib должна включать libJpeg (убедитесь, что сконфигурирована PHP опция  --with-jpeg-dir=DIR, где DIR - это директория установки libJpeg). Пожалуйста, обратитесь в поддержку своего хостинг-провайдера для настройки этого параметра.',
  'lc_php_phar_description' => 'Расширение Phar необходимо для установки внешних модулей для X&#8209;Cart 5 и обновлений с Маркетплейса. Рекомендуется использовать Phar версии 2.0.1 или новее, иначе, возможен сбой в работе. Пожалуйста, обратитесь в поддержку своего хостинг-провайдера для настройки данного параметра.',
  'lc_https_bouncer_description' => 'Поддержка безопасного протокола HTTPS, наличии действующего SSL-сертификата и модуль LibCURL необходимы для обработки кредитных карт через Authorize.NET, PayPal и другие платежные системы, предоставляющие услуги расчета стоимости доставки в режиме реального времени (для этого ваш сайт должен воспринимать безопасное подключение посредством HTTPS/SSL). Пожалуйста, обратитесь в техподдержку своего хостинг-провайдера для настройки данных параметров.',
  'lc_xml_support_description' => 'PHP расширения Xml/EXPAT и DOMDocument необходимы для работы модулей, предоставляющих возможность расчета стоимости доставки в режиме реального времени, а также, для платежных модулей. Пожалуйста, обратитесь в техподдержку своего хостинг-провайдера для настройки данных параметров.',
  'DocBlocks support' => 'Поддержка DocBlocks',
  'DockBlock is not supported message' => 'PHP на вашем сервере не поддерживает DocBlock. Эта система необходима для работы X&#8209;Cart 5.',
  'eAccelerator loaded message' => 'Блокировка DocBlock может быть вызвана работой расширения eAccelerator. Отключите это расширение и попробуйте снова.',
  'lc_docblocks_support_description' => 'Комментарии Docblocks используются X&#8209;Cart 5 и не должны быть доступны другим PHP расширениям.<br /><br />Если работает расширение eAccelerator, загрузите его в файл php.ini или перенастройте eAccelerator с помощью параметра --with-eaccelerator-doc-comment-inclusion, затем, удалите кеш расширения eAccelerator.',
  'kb_lc_file_permissions_description' => 'Посмотрите в нашем <a href="//kb.x-cart.com/display/XDD/Installation+Guide#InstallationGuide-2.Permissioncheckingfailed" target="_blank">руководстве</a> как решить проблему с правами файлов.',
  'kb_lc_php_disable_functions_description' => 'Посмотрите в нашем <a href="http://kb.x-cart.com/display/XDD/Installation+Guide#InstallationGuide-3.Disabledfunctions" target="_blank">руководстве</a> как решить проблему с отключенными PHP функциями.',
  'kb_lc_php_pdo_mysql_description' => 'Посмотрите в нашем <a href="//kb.x-cart.com/display/XDD/Installation+Guide#InstallationGuide-4.DisabledPHPextensions" target="_blank">руководстве</a> как решить проблему с библиотекой PDO.',
  'kb_lc_https_bouncer_description' => 'Посмотрите в нашем <a href="//kb.x-cart.com/display/XDD/Installation+Guide#InstallationGuide-5.HTTPSbouncerisnotinstalled" target="_blank">руководстве</a> как решить проблему с библиотекой libCurl.',
  'kb_note_mysql_issue' => 'Посмотрите в нашем <a href="http://kb.x-cart.com/display/XDD/Installation+Guide#InstallationGuide-1.Problemswithconnectiontodatabase" target="_blank">руководстве</a> как решить проблему с MySQL соединением.',
  'Redirecting to the next step...' => 'Переход на следующий шаг...',
  'Preparing data for cache generation...' => 'Поготовка данных для генерации кеш...',
  'Config file' => 'Файл конфигурации',
  'lc_config_file_description' => 'Файл конфигурации не существует, и его нельзя скопировать из стандартного файла конфигурации. Данный файл необходим для установки программы.<br /><br />Пожалуйста, выполните следующие действия: <br /><br />1. Откройте директорию :dir<br />2. Скопируйте <i>:file1</i> to <i>:file2</i><br />3. Установите права на запись <i>:file2</i><br /><br />Попробуйте снова.',
  'PHP option magic_quotes_runtime that must be disabled' => 'PHP опйию magic_quotes_runtime необходимо отключить',
  'lc_php_magic_quotes_runtime_description' => 'PHP опция "magic_quotes_runtime" не рекомендуется для PHP 5.3. Если эта опция присутствует в файле php.ini, ее необходимо отключить для нормальной работы X&#8209;Cart 5.',
  'Oops! Cache rebuild failed.' => 'Ой! Не удалось перестроить кеш.',
  'Check for possible reasons <a href="http://kb.x-cart.com/display/XDD/Setting+time+limit+of+your+server">here</a>.' => 'Проверьте воможные причины <a href="http://kb.x-cart.com/display/XDD/Setting+time+limit+of+your+server" target="_blank" >здесь</a>.',
  'user_email_hint' => 'Чтобы мониторить эту проблему и получить решение, пожалуйста, укажите свой электронный адрес.',
  'Passed' => 'OK',
  'Default time zone' => 'Часовой пояс по умолчанию',
  'By default, dates in this site will be displayed in the chosen time zone.' => 'По умолчанию даты будут отображаться для выбранного часового пояса.',
  'The prefix of the shop tables in database' => 'Префикс для таблиц в базе данных',
  'Administrator zone (backoffice)' => 'Зона администратора (панель управления)',
  'Customer zone (front-end)' => 'Зона покупателя (главная страница)',
  'Setting up directories' => 'Настройка директорий',
  'X-Cart shopping cart software v. :version' => 'Интернет-магазин на платформе X&#8209;Cart :version',
  'xcart_site' => 'http://www.x-cart.ru/',
);
