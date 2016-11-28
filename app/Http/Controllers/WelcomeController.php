<?php

namespace App\Http\Controllers;

use EventFarm\Restforce\Oauth\AccessToken;
use EventFarm\Restforce\RestforceClient;
use EventFarm\Restforce\TokenRefreshInterface;
use Illuminate\Http\Request;
use Stevenmaguire\OAuth2\Client\Provider\Salesforce;

class WelcomeController extends Controller implements TokenRefreshInterface
{

    private $provider;
    private $clientCallback;
    private $clientSecret;
    private $clientId;


    public function __construct()
    {
        $this->clientId = env('CLIENT_ID');
        $this->clientSecret = env('CLIENT_SECRET');
        $this->clientCallback = env('CLIENT_CALLBACK');

        $this->provider = new Salesforce([
            'clientId'                => $this->clientId,    // The client ID assigned to you by the provider
            'clientSecret'            => $this->clientSecret,   // The client password assigned to you by the provider
            'redirectUri'             => $this->clientCallback
        ]);
    }

    public function refresh()
    {
        if (! \Cache::has('access_token')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            exit;
        }

        $accessToken = \Cache::get('access_token');

        $refreshToken = $accessToken->getRefreshToken();

        $newAccessToken = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken
        ]);

        \Cache::put('access_token', $accessToken, 23423);

        dd($accessToken, $newAccessToken);

    }

    public function index()
    {

        if (! \Cache::has('access_token')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            exit;
        }

        $accessToken = \Cache::get('access_token');

        $restforce = RestforceClient::withDefaults(
            $accessToken->getToken(),
            $accessToken->getRefreshToken(),
            $accessToken->getInstanceUrl(),
            $accessToken->getResourceOwnerId(),
            $this->clientId,
            $this->clientSecret,
            $this->clientCallback,
            $this,
            'v37.0',
            5
        );

//        dd($restforce->picklistValues('Task', 'Status'));
//        dd($restforce->picklistValues('Lead', 'Status'));
//        dd($restforce->picklistValues('Contact', 'LeadSource'));
//        dd($restforce->picklistValues('CampaignMember', 'LeadSource'));

//        dd($restforce->query('SELECT Id, Name, Status, Phone, FirstName, LastName From CampaignMember WHERE Id=\'00v41000002KvErAAK\''));

        echo json_encode($restforce->fieldList('CampaignMemberStatus'));
//        dd($restforce->describe('CampaignMemberStatus'));

    }

    public function callback(Request $request)
    {
        if ($request->has('code')) {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
        }
        \Cache::put('access_token', $accessToken, 1000);
        dd(\Cache::get('access_token'));
    }

    public function tokenRefreshCallback(AccessToken $accessToken)
    {
        \Cache::put('access_token', $accessToken, 1000);
    }
}
