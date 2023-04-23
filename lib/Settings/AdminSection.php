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

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection
{
    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var IL10N */
    private $l;

    public function __construct(IURLGenerator $urlGenerator, IL10N $l)
    {
        $this->urlGenerator = $urlGenerator;
        $this->l = $l;
    }

    /**
     * returns the relative path to an 16*16 icon describing the section.
     *
     * @returns string
     */
    public function getIcon()
    {
        return $this->urlGenerator->imagePath('survey_server', 'app-dark.svg');
    }

    /**
     * returns the ID of the section. It is supposed to be a lower case string,
     *
     * @returns string
     */
    public function getID()
    {
        return 'survey_server';
    }

    /**
     * returns the translated name as it should be displayed
     *
     * @return string
     */
    public function getName()
    {
        return 'Survey Server';
    }

    /**
     * returns priority for positioning
     *
     * @return int
     */
    public function getPriority()
    {
        return 10;
    }
}