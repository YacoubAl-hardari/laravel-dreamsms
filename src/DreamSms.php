<?php

namespace DreamSms\LaravelDreamSms;

use Illuminate\Support\Facades\Http;
use DreamSms\LaravelDreamSms\Exceptions\DreamSmsException;

class DreamSms
{
    protected string $baseUrl;
    protected string $user;
    protected string $secretKey;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct(
        ?string $baseUrl = null,
        ?string $user = null,
        ?string $secretKey = null,
        ?string $clientId = null,
        ?string $clientSecret = null
    ) {
        $this->baseUrl      = rtrim($baseUrl ?? config('dreamsms.base_url'), '/');
        $this->user         = $user ?? config('dreamsms.user');
        $this->secretKey    = $secretKey ?? config('dreamsms.secret_key');
        $this->clientId     = $clientId ?? config('dreamsms.client_id');
        $this->clientSecret = $clientSecret ?? config('dreamsms.client_secret');
    }

    /**
     * Obtain access token from OAuth endpoint.
     */
    protected function postToken(array $data): array
    {
        $base = preg_replace('#/api$#', '', $this->baseUrl);

        $resp = Http::asForm()
            ->acceptJson()
            ->post("{$base}/token/generate", $data);

        if (! $resp->successful()) {
            throw DreamSmsException::fromResponse($resp->body(), $resp->status());
        }

        return $resp->json();
    }

    /**
     * Send a POST request to the SMS API with Bearer token.p
     */
    protected function post(string $endpoint, array $data): array
    {
        $response = Http::asForm()->post("{$this->baseUrl}/{$endpoint}", $data);

        if (!$response->successful()) {
            throw new DreamSmsException($response->body(), $response->status());
        }

        $jsonResponse = $response->json();


        if (is_numeric($jsonResponse) && (int)$jsonResponse < 0) {
            throw DreamSmsException::fromResponse($response->body(), (int)$jsonResponse);
        }

        return $jsonResponse;
    }

    /**
     * Expose raw OAuth token endpoint.
     */
    public function generateToken(): array
    {
        return $this->postToken([
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);
    }

    public function register(array $user): array
    {
        return $this->post('Register', array_merge($user, [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
        ]));
    }

    public function activate(string $username, string $code): array
    {
        return $this->post('activate', [
            'user'       => $username,
            'secret_key' => $this->secretKey,
            'code'       => $code,
        ]);
    }

    public function checkUser(): array
    {
        return $this->post('chk_user', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
        ]);
    }

    public function balance(): array
    {
        return $this->post('chk_balance', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
        ]);
    }

    public function addSender(string $sender): array
    {
        return $this->post('newsender', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'sendertext' => $sender,
        ]);
    }

    public function senderStatus(string $sender): array
    {
        return $this->post('senderstatus', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'sendertext' => $sender,
        ]);
    }

    public function userSenders(): array
    {
        return $this->post('usersender', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
        ]);
    }

    public function sendSms(
        string $to,
        string $message,
        string $sender,
        array $options = []
    ): array {
        return $this->post('sendsms', array_merge([
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'to'         => $to,
            'message'    => $message,
            'sender'     => $sender,
        ], $options));
    }

    public function sendMulti(array $toWithMsg, string $sender): array
    {
        return $this->post('sendsms_multi', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'sender'     => $sender,
            'to'         => json_encode($toWithMsg, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
