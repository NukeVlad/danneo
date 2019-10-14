UPDATE {pref}_settings SET `setopt` = 'team', `setval` = '1.5.5' WHERE (`setopt` = 'team');
UPDATE {pref}_settings SET `setname` = 'lastup', `setval` = '{time}' WHERE (`setname` = 'lastup');