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

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Permission Authorization Filter.
 */
class PermissionJWTFilter extends AbstractAuthJWTFilter
{
    /**
     * Ensures the user is logged in and has one or more
     * of the permissions as specified in the filter.
     */
    protected function isAuthorized(array $arguments): bool
    {
        foreach ($arguments as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the user does not have the permission, return 303 status with JSON response.
     */
    protected function deniedAccess(): ResponseInterface
    {
        return Services::response()
            ->setJSON(['error' => 'Insufficient privileges'])
            ->setStatusCode(ResponseInterface::HTTP_SEE_OTHER);
    }
}
