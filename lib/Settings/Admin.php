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

namespace OCA\Survey_Server\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IConfig;

class Admin implements ISettings
{

    private $userId;
    private $configManager;

    public function __construct(
        $userId,
        IConfig $configManager
    )
    {
        $this->userId = $userId;
        $this->configManager = $configManager;
    }

    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     * @since 9.1
     */
    public function getForm()
    {

        $parameters = [
            'deletion_time' => $this->configManager->getAppValue('survey_server', 'deletion_time', '99')
        ];
        return new TemplateResponse('survey_server', 'settings/admin', $parameters, '');
    }

    /**
     * Print config section (ownCloud 10)
     *
     * @return TemplateResponse
     */
    public function getPanel()
    {
        return $this->getForm();
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     * @since 9.1
     */
    public function getSection()
    {
        return 'survey_server';
    }

    /**
     * Get section ID (ownCloud 10)
     *
     * @return string
     */
    public function getSectionID()
    {
        return 'survey_server';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     *
     * E.g.: 70
     * @since 9.1
     */
    public function getPriority()
    {
        return 10;
    }
}