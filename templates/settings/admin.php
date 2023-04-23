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

script('survey_server', 'settings/admin');
?>

<div class="section" id="survey_server">
    <h2>Survey Server</h2>
    <div>
        <label for="deletion_time">Data older than x years will be deleted with every statistic run:</label><br>
        <input type="text" id="deletion_time" value="<?php p($_['deletion_time']); ?>"/>
        <br><br>
        <button id="surveyYearsSave">Save</button>
    </div>

</div>