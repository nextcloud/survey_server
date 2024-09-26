/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

(function ($, OC) {

    $(document).ready(function () {

        let formatNumber = function (number) {
            number = number.toString();
            return number.replace(/(\d)(?=(\d{3})+(\.|$))/g, '$1,');
        };

        /**
         * add general statistics to the page
         * @param instances how many instances are counted
         * @param users statistics about the users
         * @param files
         * @param lastUpdate
         */
        let showGeneralStatistics = function (instances, users, files, lastUpdate) {
            document.querySelector('#instances span').textContent = formatNumber(instances);
            document.querySelector('#lastUpdate span').textContent = formatNumber(lastUpdate);
            document.querySelector('#maxUsers span').textContent = formatNumber(users['max']);
            document.querySelector('#minUsers span').textContent = formatNumber(users['min']);
            document.querySelector('#averageUsers span').textContent = formatNumber(users['average']);
            document.querySelector('#totalUsers span').textContent = formatNumber(users['total']);
            document.querySelector('#maxFiles span').textContent = formatNumber(files['max']);
            document.querySelector('#minFiles span').textContent = formatNumber(files['min']);
            document.querySelector('#averageFiles span').textContent = formatNumber(files['average']);
            document.querySelector('#totalFiles span').textContent = formatNumber(files['total']);
        };

        /**
         * add general statistics to the page
         * @param id
         * @param data
         */
        let ocNumericStatistics = function (id, data) {
            if (id.substring(0, 3) === 'php' || id.substring(0, 8) === 'database') {
                document.querySelector('#' + id + 'Max span').textContent = OC.Util.humanFileSize(data['max']);
                document.querySelector('#' + id + 'Average span').textContent = OC.Util.humanFileSize(data['average']);
            } else {
                document.querySelector('#' + id + 'Max span').textContent = formatNumber(data['max']);
                document.querySelector('#' + id + 'Average span').textContent = formatNumber(data['average']);
            }
        };

        /**
         * draw the chart of enabled apps
         *
         * @param data
         */
        let appsChart = function (data) {
            let appLabels = [],
                appValues = [],
                numApps = 0,
                details = document.querySelector('#appDetails');
            for (let key in data) {
                var span = document.createElement('span');
                span.textContent = key + ': ' + data[key];
                details.appendChild(span);

                var br = document.createElement('br');
                details.appendChild(br);

                if (numApps < 75) {
                    appLabels.push(key);
                    appValues.push(100 * data[key] / (data['survey_client']));
                    numApps++;
                }
            }

            let chartData = {
                labels: appLabels,
                datasets: [
                    {
                        label: "Enabled Apps (in %)",
                        backgroundColor: "rgba(151,187,205,0.5)",
                        data: appValues
                    }
                ]
            };

            let ctx = document.getElementById('appChart').getContext("2d");
            let myPieChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        };

        /**
         * draw the chart of Nextcloud versions
         *
         * @param id
         * @param data
         */
        let ocChart = function (id, rawdata) {
            let chartLabels = [];
            let data = [];
            let backgroundColor = [];
            let details = document.querySelector('#' + id + 'Details');
            let colors = ["#aec7e8", "#ffbb78", "#98df8a", "#ff9896", "#c5b0d5", "#c49c94", "#f7b6d2", "#c7c7c7", "#dbdb8d", "#9edae5"];
            let counter = 0;

            for (let key in rawdata) {
                let colorIndex = counter - (Math.floor(counter / colors.length) * colors.length)
                var span = document.createElement('span');
                span.textContent = key + ': ' + rawdata[key];
                details.appendChild(span);

                var br = document.createElement('br');
                details.appendChild(br);

                chartLabels.push(key);
                data.push(rawdata[key]);
                backgroundColor.push(colors[colorIndex]);
                counter++;
            }

            let chartData = {
                labels: chartLabels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColor
                }]
            };

            let ctx = document.getElementById(id + 'Chart').getContext("2d");
            let myPieChart = new Chart(ctx, {
                type: 'pie',
                data: chartData,
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        };

        $.get(
            OC.generateUrl('/apps/survey_server/api/v1/data'), {}
        ).done(
            function (data) {
                if (data.length !== 0) {
                    showGeneralStatistics(data['instances'], data['categories']['stats']['num_users']['statistics'], data['categories']['stats']['num_files']['statistics'],data['lastUpdate']);
                    appsChart(data['apps']);

                    for (let category in data['categories']) {
                        for (let key in data['categories'][category]) {
                            if (key !== 'stats') {
                                if (data['categories'][category][key]['presentation'] === 'diagram') {
                                    ocChart((category + key).replace('.', '-'), data['categories'][category][key]['statistics']);
                                } else if (data['categories'][category][key]['presentation'] === 'numerical evaluation') {
                                    ocNumericStatistics(category + key + 'Numeric', data['categories'][category][key]['statistics']);
                                }
                            }
                        }
                    }
                }
            }
        );

    });

})(jQuery, OC);
