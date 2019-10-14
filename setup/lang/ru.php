<?php
/**
 * File:        setup/lang/ru.php
 *
 * @package     Danneo Basis kernel
 * @version     Danneo CMS (Next) v1.5.5
 * @copyright   (c) 2005-2019 Danneo Team
 * @link        http://danneo.ru
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 *  SYSTEM
 */
$setup_in = 'Установка или Обновление - ';
$noscript = 'В вашем браузере отключен JavaScript !';

/**
 *  DB LANG
 * ------------------- */
$bd_ok = '<span class="well">Соединение с сервером было успешно установлено!</span><br />
          <em class="gray">Введённые данные позволяют работать с сервером баз данных.</em><br /><br />';
$bd_no = '<span class="bad">Ошибка! Соединение с сервером не установлено!</span><br />
          <em class="gray">Введённые данные не позволяют работать с сервером баз данных.</em><br />';

$nt_ok = '<span class="black">Введённые данные позволяют работать с сервером баз данных.</span><br />';
$nt_no = '<span class="black">Введённые данные не позволяют работать с сервером баз данных.</span><br />';

$sel_ok = '<span class="well">Указанная база доступна!</span><br /><em class="gray">Введённые данные позволяют работать с базой данных.</em><br />';
$sel_no = '<span class="bad">Ошибка! Указанная база не доступна!</span><br />
           <em class="gray">Введённые данные не позволяют работать с базой данных.</em><br />';

$cdb_ok = '<span class="well">База данных была успешно создана.</span><br />';
$cdb_no = '<span class="closed">База данных не была создана!</span><br />';

$cdbu_ok = '<span class="well">Пользователь базы данных был успешно создан.</span><br />';
$cdbu_no = '<span class="closed">Пользователь базы данных не был создан!</span><br />';

$return = '<input class="button" value="« Вернуться, исправить" onclick="javascript:history.go(-1)" type="button" />';

$yes_conf = '<span class="well">Конфигурационный файл был успешно перезаписан!</span><br /><br />';
$no_conf = '<span class="bad">Конфигурационный файл не был перезаписан!</span><br />';

$find_error = '<span class="bad">Найдены ошибки!</span><br />Не выполнено инструкций: ';
$find_error_no1 = 'Выполнено инструкций: ';
$find_error_no2 = '<br /><em class="well">Ошибок не найдено, нажмите продолжить установку!</em>';

$write_no = '……… Нет';
$write_yes = '……… Да';
$create_no = '……… Не создана';
$create_yes = '……… Да';
$progress = 'Прогресс...';

$supported = 'Поддерживается';
$unsupported = 'Не поддерживается';
$enabled = 'Включено';
$disabled = 'Отключено';
$not_installed = 'Не установлен';
$no_need = '<em class="well">Не нуждаются в обновлении, нажмите продолжить установку!</em>';

/**
 *  LICENSE
 * ------------------- */
$license = array(
		'title'=>'Лицензионное соглашение',
		'submit'=>'Продолжить установку',
		'present'=>'С условиями ознакомлен, Согласен'
	);

/**
 *  WRITE
 * ------------------- */
$write_notice = '<em class="bad">Файлы необходимые для правильной установки, не доступны!</em>
                 <em>Установите атрибуты на запись для файлов и папок отмеченных в списке:</em>';
$write_normal = '<em class="well">Все файлы и директории имеют правильные атрибуты!</em>';

/**
 *  SERVER
 * ------------------- */
$gd_notice = 'Требуется библиотека GD >= 2.0';
$php_notice = 'Версия PHP должна быть >= 5.3.0';
$mysqli_notice = 'Должно быть включено расширение MySQLi';
$pcre_notice = 'Должен быть включен модуль PCRE';
$json_notice = 'Должен быть включен модуль JSON';
$zlib_notice = 'Должен быть включен модуль Zlib';
$server_notice = '<em class="bad">Обнаружены критические не соответствия!</em>';
$server_normal = '<em class="well">Параметры сервера соответствуют требованиям системы!</em>';

/**
 *  LICENSE
 * ------------------- */
$license = array(
		'title'=>'Лицензионное соглашение',
		'submit'=>'Продолжить установку',
		'present'=>'С условиями ознакомлен, Согласен'
	);

/**
 *  ERROR UPDATE
 * ------------------- */
$_update['update'] = array
	(
		'aname'=>'Не указано имя администратора',
		'apass'=>'Не указан пароль администратора',
		'site_name'=>'Не указано название сайта',
		'site_url'=>'Не указан URL сайта',
		'site_mail'=>'Не указан E-Mail сайта'
	);

$bad_aname = 'Не указано имя администратора';
$bad_apass = 'Не указан пароль администратора';

/**
 *  SETUP / STEP
 * ------------------- */
$step_lang[1] = array
	(
		'title'=>'Установка шаг 1 из %vt%&nbsp; &#8260; &nbsp;Проверка конфигурации сервера',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Проверка соответствия окружения сервера',
			'nt'=>'<em>Система осуществит проверку сервера на соответствие минимальным требованиям для ее установки.<br /><br />Системные требования: <a href="http://danneo.ru/requirements" target="_blank">http://danneo.ru/requirements</a></em>',
			'course'=>'Отчёт')
	);

$step_lang[2] = array
	(
		'title'=>'Установка шаг 2 из %vt%&nbsp; &#8260; &nbsp;Выбор действия',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'new' => 'Новая установка',
			'warn1' => 'Если Вы будете производить установку в старую базу, все данные будут утеряны!',
			'newnotice' => 'Для новой установки вам понадобится существующая база данных и пользователь с привилегиями ALL',
			'update' => 'Обновить до',
			'upname' => $product,
			'warn2' => 'Перед началом обновления, создайте резервную копию базы данных!',
			'upnotice' => 'Обновление затрагивает только базу данных, файлы должны быть обновлены по FTP!<br />Выполните обновление базы данных, и только после этого загрузите файлы новой версии!')
	);

$step_lang[3] = array
	(
		'title'=>'Установка шаг 3 из %vt%&nbsp; &#8260; &nbsp;Проверка атрибутов',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Проверка доступности прав на запись и чтение',
			'nt'=>'<em>Если у вас возникли проблемы с чтением файлов или директорий, установите атрибуты на запись соответствующие вашему хостингу.</em>',
			'course'=>'Отчёт')
	);

$step_lang[4] = array
	(
		'title'=>'Установка шаг 4 из %vt%&nbsp; &#8260; &nbsp;Введите данные для подключения к БД',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Примечания:',
			'nt'=>'Для полноценной работы с базой данных, пользователь должен иметь привилегии <strong>ALL</strong>. При добавлении пользователя не забудьте указать соответствующие права.',
			'warning'=>'Поля помеченные <b>*</b> обязательны к заполнению!',
			'server'=>'<b>*</b> Сервер базы данных:',
			'name'=>'<b>*</b> Название базы данных:',
			'user'=>'<b>*</b> Имя пользователя базы данных:',
			'pass'=>'&nbsp;&nbsp;Пароль базы данных:',
			'pref'=>'<b>*</b> Префикс таблиц базы данных:',
			'check'=>'&nbsp;&nbsp;Проверка соединения с базой',
			'newbase'=>'&nbsp;&nbsp;Создать базу данных по названию:',
			'newuser'=>'&nbsp;&nbsp;Создать нового пользователя базы данных:',
			'newuserpass'=>'&nbsp;&nbsp;Пароль нового пользователя базы данных:')
	);

$step_lang[5] = array
	(
		'title'=>'Установка шаг 5 из %vt%&nbsp; &#8260; &nbsp;Запись конфигурационного файла и проверка базы данных',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Примечания:',
			'nt'=>'На этом этапе проверяется подключение к серверу базы данных, а так же доступность самой базы.',
			'course'=>'Отчёт')
	);

$step_lang[6] = array
	(
		'title'=>'Установка шаг 6 из %vt%&nbsp; &#8260; &nbsp;Создание таблиц',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Примечания:',
			'nt'=>'На этом этапе создаётся структура таблиц базы данных.',
			'course'=>'Отчёт')
	);

$step_lang[7] = array
	(
		'title'=>'Установка шаг 7 из %vt%&nbsp; &#8260; &nbsp;Добавление языковой локали',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Примечания:',
			'nt'=>'На этом этапе добавляются языковые файлы.',
			'course'=>'Отчёт')
	);

$step_lang[8] = array
	(
		'title'=>'Установка шаг 8 из %vt%&nbsp; &#8260; &nbsp;Обновление настроек',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Примечания:',
			'nt'=>'Обновление настроек.',
			'course'=>'Отчёт')
	);

$step_lang[9] = array
	(
		'title'=>'Установка шаг 9 из %vt%&nbsp; &#8260; &nbsp;Основные настройки сайта',
		'submit'=>'Продолжить установку',
		'alltext'=>array(
			'notice'=>'Примечания:',
			'nt'=>'На этом этапе Вам нужно ввести основные данные сайта.<br /><br />1. URL не должен оканчиваться на слэш.<br />2. Поля помеченные <b>*</b> обязательны к заполнению.',
			'name'=>'<span class="closed">*</span> Название сайта',
			'site_name'=>'Danneo CMS',
			'url'=>'<span class="closed">*</span> URL сайта',
			'site_url'=>'http://'.$_SERVER['HTTP_HOST'].DIRBASE,
			'mail'=>'<span class="closed">*</span> E-Mail администратора',
			'site_mail'=>'admin@'.$_SERVER['HTTP_HOST'].DIRBASE.'',
			'aname'=>'<span class="closed">*</span> Логин администратора',
			'apass'=>'<span class="closed">*</span> Пароль администратора')
	);

$step_lang[10] = array
	(
		'title'=>'Окончание установки',
		'submit'=>'Панель администратора',
		'alltext'=>array('admin_url'=>HOST_URL.DIRBASE.'/admin/login.php')
	);

/**
 *  SETUP / ARRAY / OUT
 */
$_step = array_replace_recursive($_step, $step_lang);

/**
 *  Other lang
 * ---------------------- */
$end_title_no = 'Установка не была закончена!';
$end_nt_no = '<strong class="bad">Найдены ошибки!</strong>';
$end_nt_yes = '<p><strong>В целях безопасности, необходимо!</strong></p><br /> Удалить каталог: <strong class="bad">setup</strong><br />
               Вернуть права (только для чтения) на файл: <strong class="bad">core/config.php</strong><br /><br />
               URL панели: <a href="'.HOST_URL.DIRBASE.'/admin/login.php">'.HOST_URL.DIRBASE.'/admin/login.php</a>';
$admin_no = '<span class="closed">Невозможно создать администратора!</span>
             <br />Не возможно обнаружить сбой, попробуйте позже!';
$end_title_yes = '';
$end_text_yes = '<p><strong class="well">Поздравляем с успешной установкой!</strong></p>';
$end_text_yes.= 'Надеемся, что наши разработки окажутся полезными для Вас!<br />';
$end_text_yes.= 'С уважением, Danneo TM.<br /><br /><br />';

/**
 *  UPDATE
 */
$up_lang[0]['title'] = 'Обновление Danneo CMS 1.5.4 до 1.5.5';
$up_lang[0]['submit'] = 'Продолжить обновление »';
$up_lang[0]['alltext'] = array();

$up_lang[1]['title'] = 'Шаг.1 из %vt% / Обновление языковой локали';
$up_lang[1]['submit'] = 'Продолжить обновление »';
$up_lang[1]['alltext'] = array
	(
		'notice' => 'Примечания:',
		'nt' => 'На этом этапе обновляются языковые переменные.',
		'course'=>'Отчёт'
	);

$up_lang[2]['title'] = 'Шаг.2 из %vt% / Добавление настроек сайта';
$up_lang[2]['submit'] = 'Продолжить обновление »';
$up_lang[2]['alltext'] = array
	(
		'notice' => 'Примечания:',
		'nt' => 'На этом этапе добавляются новые настрйки сайта.',
		'course'=>'Отчёт'
	);

$up_lang[3]['title'] = 'Шаг.2 из %vt% / Обновление имеющихся настроек сайта';
$up_lang[3]['submit'] = 'Продолжить обновление »';
$up_lang[3]['alltext'] = array
	(
		'notice' => 'Примечания:',
		'nt' => 'На этом этапе обновляется таблица настроек сайта.',
		'course'=>'Отчёт'
	);

$up_lang[4]['title'] = 'Окончание обновления';
$up_lang[4]['submit'] = 'Панель администратора »';
$up_lang[4]['alltext'] = array
	(
		'notice' => 'Обновление успешно завершено',
		'admin_url' => HOST_URL.DIRBASE.'/admin/login.php',
		'text' => '<strong class="well">Поздравляем с успешным обновлением!</strong><br /><br />Желаем, приятной работы с системой.<br />Надеемся, что наши разработки окажутся полезными для Вас!<br />С уважением <strong>Danneo TM</strong></u><br /><br /><br />',
		'nt' => 'Не забудьте удалить каталог <strong class="bad">setup/</strong><br /><br />Панель администратора: <a href="'.HOST_URL.DIRBASE.'/admin/login.php">'.HOST_URL.DIRBASE.'/admin/login.php</a>',
	);

/**
 *  UPDATE / ARRAY / OUT
 */
$_up = array_replace_recursive($_up, $up_lang);
