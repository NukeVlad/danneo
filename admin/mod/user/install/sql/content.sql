INSERT INTO {pref}_{mod}_field VALUES (NULL, 'textarea', 'interest', '', 'Интересы', 'no', '', 0, 255, 'yes', 8, 'yes', 'no');
INSERT INTO {pref}_{mod}_field VALUES (NULL, 'date', 'birthday', '', 'День рождения', 'no', '', 0, 10, 'yes', 6, 'yes', 'no');
INSERT INTO {pref}_{mod}_field VALUES (NULL, 'select', 'persona', '{"men":"Мужчина","women":"Женщина"}', 'Пол', 'no', '', 0, 10, 'yes', 3, 'yes', 'no');
INSERT INTO {pref}_{mod}_field VALUES (NULL, 'radio', 'votes', '{"yes":"Да","no":"Нет"}', 'Женат (замужем)', 'no', '', 0, 10, 'yes', 4, 'yes', 'no');

INSERT INTO {pref}_{mod}_group VALUES (NULL, 0, 'Публикатор', 0);
INSERT INTO {pref}_{mod}_group VALUES (NULL, 0, 'Покупатель', 0);

INSERT INTO {pref}_{mod} VALUES (NULL, 0, 'tester', 'e10adc3949ba59abbe56e057f20f883e', 'tester@test.ru', {time}, {time}, '70000000000', 'Москва', 'http://danneo.ru', 'skype-login', '', '', 1, 0, '', '{"1":"Веб-программирование","2":{"d":1,"m":1,"y":1971},"3":"men","4":""}', 9, 106);