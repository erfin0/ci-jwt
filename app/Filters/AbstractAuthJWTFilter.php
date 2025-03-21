<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Group Authorization Filter.
 */
abstract class AbstractAuthJWTFilter implements FilterInterface
{
    /**
     * Ensures the user is logged in and a member of one or
     * more groups as specified in the filter.
     *
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        if (! auth()->loggedIn()) {
           return Services::response()
            ->setJSON(['error' => 'Unauthorized access'])
            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if ($this->isAuthorized($arguments)) {
            return;
        }

        return $this->deniedAccess();
    }

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Nothing required
    }

    /**
     * Ensures the user is logged in and has one or more
     * of the permissions as specified in the filter.
     */
    abstract protected function isAuthorized(array $arguments): bool;

    /**
     * Returns redirect response when the user does not have access authorizations.
     */
    abstract protected function deniedAccess(): ResponseInterface;
}
