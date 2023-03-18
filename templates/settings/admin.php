<?php
/**
 * Audio Player
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <audioplayer@scherello.de>
 * @copyright 2016-2021 Marcel Scherello
 */

script('survey_server', 'settings/admin');
?>

<div class="section" id="survey_server">
    <h2>Survey Server</h2>
    <div>
        <label for="deletion_years">Data older than x years will be deleted with every statistic run:</label><br>
        <input type="text" id="deletion_years" value="<?php p($_['deletion_years']); ?>"/>
        <br><br>
        <button id="surveyYearsSave">Save</button>
    </div>

</div>