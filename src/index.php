<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('env.php');

print '<pre>';
print_r([DB_HOST, DB_PORT, DB_DB, DB_USER, DB_PASSWORD]);
print '</pre>';

?>
<a href="20250121/index">file upload</a>
<?php

phpinfo();
