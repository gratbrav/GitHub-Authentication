# GitHub-Authentication
Basic GitHub Authentication

How to use it:
```sh
require_once 'Github_Login.php';

$config = array(
  'client_id' => 'github client id',
  'client_secret' => 'github client secret',
  'redirect_url' => 'github redirect url',
  'app_name' => 'github app name'
);

$login = new Github_Login($config);
$data = $login->authenticate($_GET);
```