/**
 * Survey Server
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <surveyserver@scherello.de>
 * @copyright 2023 Marcel Scherello
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('surveyYearsSave').addEventListener('click', surveyYearsSave);

    function surveyYearsSave() {
        let params = 'time=' + document.getElementById('deletion_time').value;
        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/survey_server/settings', true), true);
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send(params);
    }

})