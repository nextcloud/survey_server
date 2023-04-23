<?php
/**
 * Survey Server
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <analytics@scherello.de>
 * @copyright 2023 Marcel Scherello
 */

namespace OCA\Survey_Server\Controller;

use OCA\Survey_Server\Service\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller
{

    /** @var LoggerInterface */
    private LoggerInterface $logger;
    private SettingsService $SettingsService;

    public function __construct(
        $appName,
        IRequest $request,
        LoggerInterface $logger,
        SettingsService $SettingsService
    )
    {
        parent::__construct($appName, $request);
        $this->logger = $logger;
        $this->SettingsService = $SettingsService;
    }

    /**
     * update settings
     *
     * @NoAdminRequired
     * @param int $time
     * @return DataResponse
     */
    public function update(int $time): DataResponse
    {
        return new DataResponse($this->SettingsService->update($time));
    }
}