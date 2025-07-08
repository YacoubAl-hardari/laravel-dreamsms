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
    protected string $senderName;


    public function __construct(
        ?string $baseUrl = null,
        ?string $user = null,
        ?string $secretKey = null,
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $senderName = null,
    ) {
        $this->baseUrl      = rtrim($baseUrl ?? config('dreamsms.base_url'), '/');
        $this->user         = $user ?? config('dreamsms.account_username');
        $this->secretKey    = $secretKey ?? config('dreamsms.secret_key');
        $this->clientId     = $clientId ?? config('dreamsms.client_id');
        $this->clientSecret = $clientSecret ?? config('dreamsms.client_secret');
        $this->senderName   = $senderName ?? config('dreamsms.sender_name');
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
            $exception = DreamSmsException::fromResponse($response->body(), $response->status());
            if ($exception !== null) {
                return [
                    'message' => $exception->getMessage(),
                    'code'    => $exception->getCode(),
                ];
            }
        }

        $body = trim($response->body());

        if (is_numeric($body)) {
            if ((int)$body < 0) {
                $exception = DreamSmsException::fromResponse($body, (int)$body);
                if ($exception !== null) {
                    return [
                        'message' => $exception->getMessage(),
                        'code'    => $exception->getCode(),
                    ];
                }
            }

            return ['code' => 200, 'balance' => (int)$body];
        }

        if ($body === 'Success') {
            return ['message' => 'Success', 'code' => 200];
        }

        $jsonResponse = $response->json();

        if (!is_array($jsonResponse)) {
            $exception = DreamSmsException::fromResponse($body, $response->status());
            if ($exception !== null) {
                return [
                    'message' => $exception->getMessage(),
                    'code'    => $exception->getCode(),
                ];
            }

            return ['message' => $body, 'code' => 400];
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

    public function addSender(): array
    {
        return $this->post('newsender', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'sendertext' => $this->senderName,
        ]);
    }

    public function senderStatus(): array
    {
        return $this->post('senderstatus', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'sendertext' => $this->senderName,
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
        array $options = []
    ): array {
        return $this->post('sendsms', array_merge([
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'to'         => $to,
            'message'    => $message,
            'sender'     => $this->senderName,
        ], $options));
    }

    public function sendMulti(array $toWithMsg): array
    {
        return $this->post('sendsms_multi', [
            'user'       => $this->user,
            'secret_key' => $this->secretKey,
            'sender'     => $this->senderName,
            'to'         => json_encode($toWithMsg, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
