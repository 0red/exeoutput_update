# Files to be installed on the server

`pmo.php` main update file

`.htaccess` should be updated or rewriten (this is the Apache version -> rename to __.htaccess__)

`pmo_ver.txt` should be created on the server

__in case of change pmo.php or other change rules to make them hidden__

## Please remember to change ownership for dir and files 
i.e. (Linux) `chown www-data:www-data .htaccess pmo_ver.txt pmo.php`

## please remeber to change the url in 
`exeoutput/main.php` and `exeoutput/enc_data.php` or `exeoutput/exe_update.js` `function jr_check_update`  to show `https://\<your server\>/pmo.php`
