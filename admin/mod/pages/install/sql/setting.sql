INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'search', 'hide', 0, 'all_search', 'echo ''<select name="set[search]" class="sw165"><option value="yes"''.(($conf[''{mod}''][''search'']==''yes'') ? '' selected'' : '''').''>''.$lang[''all_yes''].''</option><option value="no"''.(($conf[''{mod}''][''search'']==''no'') ? '' selected'' : '''').''>''.$lang[''all_no''].''</option><option value="hide"''.(($conf[''{mod}''][''search'']==''hide'') ? '' selected'' : '''').''>''.$lang[''no_form''].''</option></select>'';\r\n$tm->outhint($lang[''help_no_form'']);', 'if (($set[''search''] == ''yes'')) {\r\n	$set[''search''] = ''yes'';\r\n} elseif ($set[''search''] == ''no'') {   \r\n	$set[''search''] = ''no'';\r\n} else { \r\n	$set[''search''] = ''hide'';\r\n}');
INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'print', 'yes', 0, 'all_print', 'echo "<select name=\\"set[print]\\" class=\\"sw165\\">".\r\n"<option value=\\"yes\\"".(($conf[''{mod}''][''print'']=="yes") ? " selected" : "").">".$lang[''all_yes'']."</option>\\n".\r\n"<option value=\\"no\\"".(($conf[''{mod}''][''print'']=="no") ? " selected" : "").">".$lang[''all_no'']."</option>\\n".\r\n"</select>";', '$set["print"] = ($set["print"] == "yes") ? "yes" : "no";');
INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'date', 'yes', 0, 'show_date', 'echo "<select name=\\"set[date]\\" class=\\"sw165\\">".\r\n"<option value=\\"yes\\"".(($conf[''{mod}''][''date'']=="yes") ? " selected" : "").">".$lang[''all_yes'']."</option>\\n".\r\n"<option value=\\"no\\"".(($conf[''{mod}''][''date'']=="no") ? " selected" : "").">".$lang[''all_no'']."</option>\\n".\r\n"</select>";', '$set["date"] = ($set["date"] == "yes") ? "yes" : "no";');
INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'social', 'yes', 0, 'social_bookmark', 'echo "<select name=\\"set[social]\\" class=\\"sw165\\">".\r\n"<option value=\\"yes\\"".(($conf[''{mod}''][''social'']=="yes") ? " selected" : "").">".$lang[''included'']."</option>\\n".\r\n"<option value=\\"no\\"".(($conf[''{mod}''][''social'']=="no") ? " selected" : "").">".$lang[''not_included'']."</option>\\n".\r\n"</select>";', '$set["social"] = ($set["social"] == "yes") ? "yes" : "no";');
INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'pagcol', '10', 1, 'who_page_all', 'echo "<input type=\\"text\\" name=\\"set[pagcol]\\" value=\\"".$conf[''{mod}''][''pagcol'']."\\" size=\\"25\\" maxlength=\\"2\\" required=\\"required\\">";', '$set["pagcol"] = preparse($set["pagcol"],THIS_INT);');
INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'indcol', '2', 1, 'who_col_all', 'echo "<input type=\\"text\\" name=\\"set[indcol]\\" value=\\"".$conf[''{mod}''][''indcol'']."\\" size=\\"25\\" maxlength=\\"2\\" required=\\"required\\">";', '$set["indcol"] = preparse($set["indcol"],THIS_INT);');
INSERT INTO {pref}_settings VALUES(NULL, '{mod}', 'mods', '[{"mod":"pages","name":"Страницы"}]', 0, '', '', '');