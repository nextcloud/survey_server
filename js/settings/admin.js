'use strict';

document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('surveyYearsSave').addEventListener('click', surveyYearsSave);

    function surveyYearsSave() {
        alert(document.getElementById('deletion_years').value);
    }

})