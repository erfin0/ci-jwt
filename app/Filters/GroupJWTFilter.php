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
 * Group Authorization Filter.
 */
class GroupJWTFilter extends AbstractAuthJWTFilter
{
    /**
     * Ensures the user is logged in and a member of one or
     * more groups as specified in the filter.
     */
    protected function isAuthorized(array $arguments): bool
    {
        return auth()->user()->inGroup(...$arguments);
    }

    /**
     * If the user does not belong to the group, redirect to the configured URL with an error message.
     */
    protected function deniedAccess(): ResponseInterface
    {
        return Services::response()
            ->setJSON(['error' => 'Insufficient privileges'])
            ->setStatusCode(ResponseInterface::HTTP_SEE_OTHER);
    }

}
