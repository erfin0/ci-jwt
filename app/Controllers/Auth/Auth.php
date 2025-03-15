<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Validation\ValidationRules;
use App\Models\RefreshTokenModel;

class Auth extends BaseController
{
    use ResponseTrait;

    public function jwtLogin(): ResponseInterface
    {
        $rules = $this->getValidationRules();
        $data = $this->request->getJSON(true);

        if (!$this->validateData($data, $rules, [], config('Auth')->DBGroup)) {
            return $this->fail(['errors' => $this->validator->getErrors()], 401);
        }

        $credentials = array_filter([
            'email' => $data['email'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
        ]);

        $authenticator = auth()->getAuthenticator();
        $result = $authenticator->check($credentials);

        if (!$result->isOK()) {
            return $this->failUnauthorized($result->reason());
        }

        return $this->generateTokenResponse();
    }

    protected function getValidationRules(): array
    {
        $rules = new ValidationRules();
        return $rules->getLoginRules();
    }

    protected function generateToken(): string
    {
        $manager = service('jwtmanager');
        $user = auth()->user();

        return $manager->generateToken($user);
    }

    protected function generateRefreshToken(int $drefresh = 0): string
    {
        $user = auth()->user();
        $refreshToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + config('AuthJWT.refreshTokenLifetime'));
        $userAgent = $this->request->getUserAgent();

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

    protected function generateTokenResponse(int $drefresh = 0): ResponseInterface
    {
        return $this->respond([
            'access_token' => $this->generateToken(),
            'refresh_token' => $this->generateRefreshToken($drefresh),
        ]);
    }

    public function refresh(): ResponseInterface
    {
        $refreshTokenModel = new RefreshTokenModel();
        $data = $this->request->getPost();
        $refreshToken = $data['refresh_token'] ?? null;
        $userAgent = $this->request->getUserAgent();

        if (!$refreshToken) {
            return $this->failUnauthorized('Refresh token is required.');
        }

        $tokenData = $refreshTokenModel->where('token', $refreshToken)->first();

        if (!$tokenData || strtotime($tokenData['expires_at']) < time() || $tokenData['user_agent'] !== $userAgent) {
            return $this->failUnauthorized('Invalid or expired refresh token.');
        }

        return $this->generateTokenResponse($tokenData['id']);
    }

    public function jwtlogout(): ResponseInterface
    {
        $refreshTokenModel = new RefreshTokenModel();
        $data = $this->request->getPost();
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return $this->failUnauthorized('Refresh token is required.');
        }

        $refreshTokenModel->where('token', $refreshToken)->delete();

        return $this->respond(['message' => 'Logged out successfully.']);
    }
}
