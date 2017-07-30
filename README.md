# codereadr-api-client
PHP client for interacting with CodeReadr API. See https://www.codereadr.com/apidocs/ for details. 

Note that this package requires PHP 7.1.

```php
<?php
use ArkonEvent\CodeReadr\ApiClient\Client;
$client = new Client('youAPIKey');

//Example get all users and print usernames
try{
	$responseXML = $client->request(Client::SECTION_USERS, Client::ACTION_RETREIVE);
	//Response is a \SimpleXMLElement object
	foreach ($responseXML->user as $user){
		echo 'username: '.$user->username.PHP_EOL;
	}
} catch(\Throwable $e){
	echo 'API error: '.$e->getMessage();
}

```
