<?php
/**
 * @copyright Copyright (c) 2023, Marcel Scherello <surveyserver@scherello.de>
 *
 * @author Marcel Scherello <surveyserver@scherello.de>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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

    public function update(int $time): int {
        $this->config->setAppValue('survey_server', 'deletion_time', $time);
        return $time;
    }
}