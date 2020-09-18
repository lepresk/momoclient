# momoclient
MTN Mobile Money Api Client

## Création de l'api User
```php
use Lepresk\MomoApi\MomoApiClient;

$subscriptionKey = '563b7379b24145ecad85ab2989869a07';
$callbackHost = 'myHost.com';

MomoApiClient::setup($subscriptionKey);

$apiUser = MomoApiClient::createApiUser($callbackHost, null);
print_r($apiUser);
```