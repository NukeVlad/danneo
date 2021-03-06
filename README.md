# danneo
Danneo CMS 1.5.6 (Next)

Модульная, мультиязычная, мультисайтовая, мультиплатформенная, с открытым исходным 
кодом, система управления сайтами.

Простая установка, легкость в управлении, минимальная нагрузка на сервер, а так же 
широкая базовая комплектация позволяет построить интерактивный веб-сайт любой сложности, 
и в дальнейшем эффективно им управлять.

Распространяется в соответствии с лицензией GNU General Public 2.
http://danneo.ru/license

ВОЗМОЖНОСТИ
-----------
http://danneo.ru/feature

БАЗОВЫЕ МОДУЛИ
--------------
http://danneo.ru/modules


СИСТЕМНЫЕ ТРЕБОВАНИЯ
--------------------
База данных MySQL версии 5.0 или выше.
Интерпретатор PHP версии 5.4 или выше, предпочтительно установленный как модуль (mod_php).
Обязательные модули и расширения: GD, Zlib, ZIP, cURL, mbString, JSON.

Подробнее: http://danneo.ru/requirements


УСТАНОВКА
=========
1. Распаковать архив, загрузить файлы и папки на сервер.
2. Установить права на запись (777) для файлов и папок:
   cache/
   up/
   core/config.php
3. В браузере ввести http://ваш_сайт/setup/
4. Далее следовать инструкциям установки.


ОБНОВЛЕНИЕ
==========
1. Сделать резервную копию файла: core/config.php
2. Распаковать архив, загрузить файлы и папки из каталога www на сервер (с заменой).
3. Восстановить файл core/config.php из резервной копии.
4. В браузере ввести http://ваш_сайт/setup/
5. Далее следовать инструкциям установки.

!!! ВАЖНО !!!
При обновлении, выполнить 1 и 3 пункты в логической последовательности.


ИЗ НОВОВВЕДЕНИЙ:

1. В базовую сборку добавлены модули - Видеогалерея, Организации, Тендеры.
   Функционал данных модулей серьёзно доработан и полностью адаптирован для работы.
   В дальнейшем, данные моды будут поддержирваться на уровне базового функционала.

2. Создан новый шаблон оформления "Modern", полностью адаптивный для всех мобильных устройств.
   Все новые доработки функционала и вывод на сайт, ориентировались на данный шаблон.
   В связи с этим, могут возникнуть некоторые проблемы при обновлении с предыдущей версии.
   Сразу после обновления, для корректной работы, в настройках сайта нужно включить данный шаблон.

3. Добавлены города и посёлки в раздел "География".
   В системе появится база населенных пунктов по странам СНГ, с геоданными. Само собой, база пополняемая и редактируемая.
   Расширен функционал управления географией.
   Появилась возможность выключать определенные страны, регионы или города, без удаления из базы.
   Отключенные данные не будут выводиться на сайте.

   В связи с этим, доработаны модули "Пользователи" и "Каталог товаров".
   Для пользователей, в панели управления администратором и в личном кабинете.
   Для каталога, на странице заказа товара.

   Добавлен выпадающий список "Город".
   Если нужного города или посёлка в нет, можно добавить свой, для этого в выпадающем списке необходимо выбрать пункт "Свой город, посёлок".
   При этом, появится дополнительное поле "Свой город, посёлок".

4. В каталоге товаров, добавлен функционал импорта / экспорта товаров.
   Форматы:
   Экспорт в XLSX, XML, CSV.
   Импорт из XLS, XLSX, XML, CSV.
   Из базы выгружаются четыре параметра (ID товара, категория, название, цена).
   В ядро системы добавлен набор классов для работы с офисными форматами.
   Пока базовый функционал, главное выгрузка и импорт работают, а дальше уже можно дорабатывать.
   При импорте обновляются поля: название, цена, старая цена.
   Обновление старой цены настраивается опционально, можно отключить в настройках.

5. Для пользователей, добавлена загрузка своих аватаров.
   Во время загрузки, имеется возможность выбрать нужный квадрат на фото, и сохранить с обрезкой.
   Количество загружаемых аватаров один, можно удалить или заменить новым.
   Ограничений на количество повторных загрузок нет.
   Форматы загружаемых изображений: gif, jpg, png, webp.
   Максимальный размер загружаемого файла: 2 Мб.

6. Доработана дата и время последнего посещения сайта пользователем.
   Добавлено доп. поле в таблицу юзеров, в которое записывается дата предыдущего посещения.
   Даты обновляются во время авторизации пользователя, а не при выходе, как было до этого.
   Теперь накладок с датой последнего визита не будет, даже если пользователь просто закроет окно и "уйдет по английски".

7. В систему добавлен сторонний класс отправки почты - PHPMailer.
   В новой версии данный класс используется по умолчанию для работы с почтой.
   Старый класс Mail, встроенный в ядро системы, также работает со старыми параметрами.
   В настройках "Управление почтой", добавлена возможность выбора работы между PHPMailer Mail.
   Поле "Класс отправки".
   В выпадающем списке:
    * Mail - старый класс, встроенный в систему
    * PHPMailer - новый, сторонний класс

8. В панель управления добавлены настройки расположение ватермарки.
   В разделе: Настройка сайта => Загрузка изображений
   Поля:
    * Расположение ватермарки
    * Отступ ватермарки по вертикали
    * Отступ ватермарки по горизонтали
	
9. Для ЧПУ создана возможность добавления своих символов замены для.
   В разделе: Настройки системы => Общие настройки
   Поля:
    * Национальные символы для ЧПУ
    * Латинские символы замены для ЧПУ
	
10. Доработан функционал редиректа внешних ссылок.
    Настройки вынесены в панель управления: Настройки сайта => Редирект
    Поля:
     * Включить / Выключить редирект.
     * Задержка в секундах до переадресации.
     * Параметр строки для переадресации. По умолчанию "go".
    Пример: http://localhost/redirect.php?go=http://site.ru
	
11. В модуле "Каталог товаров":
    * Доработана страница "Корзина".
      Добавлены явные кнопки удаления товара, в крайней справа колонке таблицы.
      Синхронизировано обновление товаров после удаления, в блоке "Корзина" и на странице "Корзина".
      Если находитесь на странице "Корзина", после удаления товара, данные будут обновляться с перезагрузкой страницы.
      Если удаление товара производится в блоке, на любой другой странице, данные в блоке будут обновляться без перезагрузки страницы.
	* Функционал отправки заявок переработан, и вынесен в отдельный файл.
	* Много корректировок и исправлений по функционалу.

12. Новый вывод в шапке шаблона:
    * Добавлена форма глобального поиска.
      В файл template/Modern/top.tpl создана переменная {seatop}, отвечающая за вывод функционала поиска.
	  Функционал поиска находится в методе globsearch() класса Template().
    * Добавлен функционал пользователя.
	  Ссыки "Воийти", "Профиль", "Выход", а также вывод аватара пользователя.
	  В файле template/Modern/top.tpl данные пользователя находятся в блоке: <div class="user-top"><a href="{profile}">{avatar}{uname}</a>{logout}</div>.
	  Функционал поиска находится в методе user() класса Template().
    * Добавлен функционал корзины каталога товаров.
	  В файле template/Modern/top.tpl эти данные находятся в блоке: <div class="basket-top">{basket}</div>.
	  Функционал корзины находится в методе basket() класса Template().

13. Новый вывод в подвале шаблона:
    * Добавлено адаптивное нижнее меню.
      В файл template/Modern/bot.tpl создана переменная {botmenu}, отвечающая за вывод функционала меню.
	  Настройки меню в панели управления.
	  Управление системой => Управление меню: Все позиции => Нижнее меню
	  Для корретной работы меню, необходимо испольовать двухуровневую вложенность.
    * Добавлен блок с контактами организации (сайта).
      В файл template/Modern/bot.tpl создана переменная {contacts}, отвечающая за вывод функционала контактов.
	  Для корректной работы, 
	   * должен быть включен модуль "Контакты".
	   * добавлен блок "Контакты организации" в позицию "Контакты".

14. В целях упрощения знакомства с системой для начинающих пользователей, 
    сразу после установки вы получаете полностью настроенный сайт, со всеми имеющимися модами и демо-контентом.

ФОРУМ ПОДДЕРЖКИ
================
http://forum.danneo.ru

Надеемся, что наши разработки окажутся полезными для вас!
Danneo Team.
