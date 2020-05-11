# Files to be installed on the server

`pmo.php` main update file (in case `check_user()` returns true - any update for the user will be deleted)

`.htaccess` should be updated or rewriten (this is the Apache version -> rename to __.htaccess__)

`pmo_ver.txt` should be created on the server

__in case of change pmo.php or other change rules to make them hidden__

## Please remember to change ownership for dir and files 
i.e. (Linux) `chown www-data:www-data .htaccess pmo_ver.txt pmo.php`

## please remeber to change the url in 
`exeoutput/main.php` and `exeoutput/enc_data.php` or `exeoutput/exe_update.js` `function jr_check_update`  to show `https://\<your server\>/pmo.php`

And the best - __server has no knowledge of unencrypted version of Your software__. You crypt it on Your PC, upload encryted to the server. User download it and it's unencrypt in the virtual ExeOutput space for the execution time only.
