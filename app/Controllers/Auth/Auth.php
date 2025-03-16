<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Validation\ValidationRules;
use App\Models\RefreshTokenModel;
use CodeIgniter\Shield\Entities\User;

class Auth extends BaseController
{
    use ResponseTrait;

    public function jwtLogin(): ResponseInterface
    {


        if (
            !$this->request->hasHeader('Content-Type') ||
            stripos($this->request->getHeaderLine('Content-Type'), 'application/json') === false
        ) {
            return $this->fail(['errors' => 'Invalid content type, expected application/json'], 400);
        }

        $rules = $this->getValidationRules();
        $data = $this->request->getJSON(true)  ?? [];


        if (!$this->validateData($data, $rules, [], config('Auth')->DBGroup)) {
            return $this->fail(['errors' => $this->validator->getErrors()], 401);
        }

        // Get the credentials for login
        $credentials             = $this->request->getJsonVar(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $b = $credentials;
        $credentials['password'] = $this->request->getJsonVar('password');

        $authenticator = auth()->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return $this->failUnauthorized($result->reason());
        }

        $user =    auth()->getProvider()->findByCredentials($b);


        return $this->generateTokenResponse($user);
    }

    protected function getValidationRules(): array
    {
        $rules = new ValidationRules();
        return $rules->getLoginRules();
    }

    protected function generateToken(User $user): string
    {
        $manager = service('jwtmanager');
        $userAgent = hash('sha256', $this->request->getUserAgent()->getAgentString());

        return $manager->generateToken($user, ['user_agent' => $userAgent]);
    }

    protected function generateRefreshToken(int $drefresh = 0,user $user): string
    {
       
        $refreshToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + config('AuthJWT.refreshTokenLifetime'));
        $userAgent = hash('sha256', $this->request->getUserAgent()->getAgentString());

        $refreshTokenModel = new RefreshTokenModel();
        if ($drefresh === 0) {
            $refreshTokenModel->insert([
                'user_id' => $user->id,
                'token' => $refreshToken,
                'expires_at' => $expiresAt,
                'user_agent' => $userAgent,
            ]);
        } else {
            $refreshTokenModel->update($drefresh, [
                'token' => $refreshToken,
                'expires_at' => $expiresAt,
                'user_agent' => $userAgent,
            ]);
        }

        return $refreshToken;
    }

    protected function generateTokenResponse(user $user ,int $drefresh = 0): ResponseInterface
    {
        return $this->respond([
            'access_token' => $this->generateToken($user),
            'refresh_token' => $this->generateRefreshToken($drefresh,$user ),
        ]);
    }

    public function refresh(): ResponseInterface
    {
        $refreshTokenModel = new RefreshTokenModel();
        $data = $this->request->getJSON(true) ?? $this->request->getPost(true) ?? [];
        $refreshToken = $data['refresh_token'] ?? null;
        $userAgent = hash('sha256', $this->request->getUserAgent()->getAgentString());

        if (!$refreshToken) {
            return $this->failUnauthorized('Refresh token is required.');
        }

        $tokenData = $refreshTokenModel->where('token', $refreshToken)->first();

        if (!$tokenData || strtotime($tokenData['expires_at']) < time() || $tokenData['user_agent'] !== $userAgent) {
            return $this->failUnauthorized('Invalid or expired refresh token.');
        }
        $user=auth()->getProvider()->findById(123);
        return $this->generateTokenResponse($user,$tokenData['id']);
    }

    public function jwtlogout(): ResponseInterface
    {
        $refreshTokenModel = new RefreshTokenModel();
        $data = $this->request->getJSON(true) ?? $this->request->getPost(true) ?? [];
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return $this->failUnauthorized('Refresh token is required.');
        }

        $refreshTokenModel->where('token', $refreshToken)->delete();

        return $this->respond(['message' => 'Logged out successfully.']);
    }

    public function validateToken(string $token): bool
    {
        $manager = service('jwtmanager');
        $decoded = $manager->decode($token);
        $currentUserAgent = hash('sha256', $this->request->getUserAgent()->getAgentString());

        return isset($decoded['user_agent']) && $decoded['user_agent'] === $currentUserAgent;
    }
}
