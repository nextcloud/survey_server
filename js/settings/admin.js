/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('surveyYearsSave').addEventListener('click', surveyYearsSave);

    function surveyYearsSave() {
        let params = 'deletion_time=' + document.getElementById('deletion_time').value
        + '&version_aggregation=' + document.getElementById('version_aggregation').value;
        let xhr = new XMLHttpRequest();
        xhr.open('POST', OC.generateUrl('apps/survey_server/settings', true), true);
        xhr.setRequestHeader('requesttoken', OC.requestToken);
        xhr.setRequestHeader('OCS-APIREQUEST', 'true');
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send(params);
    }

})