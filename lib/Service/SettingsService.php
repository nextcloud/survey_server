<?php
/**
 * Survey Server
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <surveyserver@scherello.de>
 * @copyright 2023 Marcel Scherello
 */

namespace OCA\Survey_Server\Service;

use OCP\IConfig;

class SettingsService
{
    /** @var IConfig */
    protected IConfig $config;

    /**
     * @param IConfig $config
     */
    public function __construct(IConfig $config)
    {
        $this->config = $config;
    }

    /**
     * update
     *
     * @param $time
     * @return int
     */
    public function update(int $time)
    {
        $this->config->setAppValue('survey_server', 'deletion_time', $time);
        return $time;
    }
}