# momoclient
MTN Mobile Money Api Client

## Création de l'api User
```php
use Lepresk\MomoApi\MomoApiClient;

$subscriptionKey = 'YOUR SUBCRIPTION KEY';
$callbackHost = 'myHost.com';

MomoApiClient::setup($subscriptionKey);

$apiUser = MomoApiClient::createApiUser($callbackHost, null);
print_r($apiUser);
```