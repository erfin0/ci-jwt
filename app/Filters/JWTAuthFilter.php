<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use Config\Services;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        try {
            /** @var JWT $authenticator */
            $authenticator = auth('jwt')->getAuthenticator();
            $token = $authenticator->getTokenFromRequest($request);
            $result = $authenticator->attempt(['token' => $token]);

            if (! $result->isOK()) {
                return Services::response()
                    ->setJSON(['error' => 'Unauthorized access'])
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            // log_message('error', 'JWT Error: ' . $e->getMessage());
            return Services::response()
                ->setJSON(['error' => 'Unauthorized access'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
