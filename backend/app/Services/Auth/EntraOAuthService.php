<?php

namespace App\Services\Auth;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EntraOAuthService
{
    /** Scopes for authorize + token (keep in sync). GroupMember.Read.All = read group membership for role mapping. */
    private const OAUTH_SCOPES = 'openid profile email User.Read GroupMember.Read.All offline_access';

    public function authorizationUrl(): string
    {
        $tenant = (string) Setting::get('entra_tenant_id');
        $clientId = (string) Setting::get('entra_client_id');
        $redirectUri = route('auth.entra.callback', [], true);
        $state = Str::random(40);
        session(['oauth_entra_state' => $state]);

        $scope = rawurlencode(self::OAUTH_SCOPES);

        return sprintf(
            'https://login.microsoftonline.com/%s/oauth2/v2.0/authorize?client_id=%s&response_type=code&redirect_uri=%s&response_mode=query&scope=%s&state=%s',
            rawurlencode($tenant),
            rawurlencode($clientId),
            rawurlencode($redirectUri),
            $scope,
            rawurlencode($state)
        );
    }

    public function handleCallback(Request $request): array
    {
        $sessionState = session()->pull('oauth_entra_state');
        if (! $sessionState || ! hash_equals((string) $sessionState, (string) $request->query('state', ''))) {
            return ['success' => false, 'message' => __('auth.oauth_state_invalid')];
        }

        if ($request->query('error')) {
            return [
                'success' => false,
                'message' => (string) $request->query('error_description', $request->query('error')),
            ];
        }

        $code = $request->query('code');
        if (! $code) {
            return ['success' => false, 'message' => __('auth.oauth_missing_code')];
        }

        $tenant = (string) Setting::get('entra_tenant_id');
        $clientId = (string) Setting::get('entra_client_id');
        $clientSecret = (string) config('services.entra.client_secret', '');
        $redirectUri = route('auth.entra.callback', [], true);

        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";
        $tokenResponse = Http::asForm()->post($tokenUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'scope' => self::OAUTH_SCOPES,
        ]);

        if (! $tokenResponse->successful()) {
            return [
                'success' => false,
                'message' => __('auth.oauth_token_failed'),
            ];
        }

        $accessToken = $tokenResponse->json('access_token');
        if (! $accessToken) {
            return ['success' => false, 'message' => __('auth.oauth_token_failed')];
        }

        $meResponse = Http::withToken($accessToken)
            ->get('https://graph.microsoft.com/v1.0/me');

        if (! $meResponse->successful()) {
            return ['success' => false, 'message' => __('auth.oauth_graph_failed')];
        }

        $me = $meResponse->json();
        $externalId = (string) ($me['id'] ?? '');
        $email = strtolower(trim((string) ($me['mail'] ?? $me['userPrincipalName'] ?? '')));
        $given = isset($me['givenName']) ? (string) $me['givenName'] : null;
        $family = isset($me['surname']) ? (string) $me['surname'] : null;

        if ($externalId === '' || $email === '') {
            return ['success' => false, 'message' => __('auth.oauth_profile_incomplete')];
        }

        $groupHints = $this->fetchEntraGroupHints($accessToken);

        $provisioner = app(DirectoryUserProvisioner::class);
        $user = $provisioner->findOrCreate('entra', $externalId, $email, $given, $family, null, $groupHints);

        if (! $user) {
            return ['success' => false, 'message' => __('auth.oauth_provision_failed')];
        }

        return ['success' => true, 'user' => $user];
    }

    /**
     * @return list<string>
     */
    private function fetchEntraGroupHints(string $accessToken): array
    {
        $hints = [];
        $url = 'https://graph.microsoft.com/v1.0/me/memberOf?$select=id,displayName';

        while ($url !== '') {
            $response = Http::withToken($accessToken)->get($url);
            if (! $response->successful()) {
                break;
            }
            $body = $response->json();
            foreach ($body['value'] ?? [] as $g) {
                if (! is_array($g)) {
                    continue;
                }
                if (! empty($g['id'])) {
                    $hints[] = (string) $g['id'];
                }
                if (! empty($g['displayName'])) {
                    $hints[] = (string) $g['displayName'];
                }
            }
            $url = isset($body['@odata.nextLink']) && is_string($body['@odata.nextLink'])
                ? $body['@odata.nextLink']
                : '';
        }

        return $hints;
    }
}
