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
        string $baseUrl,
        string $user,
        string $secretKey,
        string $clientId,
        string $clientSecret
    ) {
        $this->baseUrl       = rtrim($baseUrl, '/');
        $this->user          = $user;
        $this->secretKey     = $secretKey;
        $this->clientId      = $clientId;
        $this->clientSecret  = $clientSecret;
    }

    protected function post(string $endpoint, array $data): array
    {
        $response = Http::asForm()->post("{$this->baseUrl}/{$endpoint}", $data);

        if (! $response->successful()) {
            throw new DreamSmsException($response->body(), $response->status());
        }

        return $response->json();
    }

    public function register(array $user): array
    {
        return $this->post('Register', array_merge($user, [
            'user'     => $this->user,
            'secret_key' => $this->secretKey,
        ]));
    }

    public function generateToken(): array
    {
        return $this->post('token/generate', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);
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

    public function sendSms(string $to, string $message, string $sender, array $options = []): array
    {
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
            'to'         => json_encode($toWithMsg),
        ]);
    }
}
