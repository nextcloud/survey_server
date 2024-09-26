<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
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