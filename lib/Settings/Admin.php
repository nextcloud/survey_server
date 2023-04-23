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