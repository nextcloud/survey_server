/**
 * @author Björn Schießle <schiessle@owncloud.com>
 * @author Marcel Scherello <surveyserver@scherello.de>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

(function ($, OC) {

    $(document).ready(function () {

        /**
         * calculate random color for the charts
         * @returns {string}
         */
        let getRandomColor = function () {
            let letters = '0123456789ABCDEF'.split('');
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        };

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
            $('#instances span').text(formatNumber(instances));
            $('#lastUpdate span').text(formatNumber(lastUpdate));
            $('#maxUsers span').text(formatNumber(users['max']));
            $('#minUsers span').text(formatNumber(users['min']));
            $('#averageUsers span').text(formatNumber(users['average']));
            $('#totalUsers span').text(formatNumber(users['total']));
            $('#maxFiles span').text(formatNumber(files['max']));
            $('#minFiles span').text(formatNumber(files['min']));
            $('#averageFiles span').text(formatNumber(files['average']));
            $('#totalFiles span').text(formatNumber(files['total']));
        };

        /**
         * add general statistics to the page
         * @param id
         * @param data
         */
        let ocNumericStatistics = function (id, data) {
            if (id.substring(0, 3) === 'php' || id.substring(0, 8) === 'database') {
                $('#' + id + 'Max span').text(OC.Util.humanFileSize(data['max']));
                //$('#' + id + 'Min span').text(OC.Util.humanFileSize(data['min']));
                $('#' + id + 'Average span').text(OC.Util.humanFileSize(data['average']));
                //$('#' + id + 'Total span').text(OC.Util.humanFileSize(data['total']));
            } else {
                $('#' + id + 'Max span').text(formatNumber(data['max']));
                //$('#' + id + 'Min span').text(formatNumber(data['min']));
                $('#' + id + 'Average span').text(formatNumber(data['average']));
                //$('#' + id + 'Total span').text(formatNumber(data['total']));
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
                $details = $('#appDetails');
            for (let key in data) {
                $details.append($('<span>').text(key + ': ' + data[key]));
                $details.append($('<br>'));

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
            let $details = $('#' + id + 'Details'); // text output
            let colors = ["#aec7e8", "#ffbb78", "#98df8a", "#ff9896", "#c5b0d5", "#c49c94", "#f7b6d2", "#c7c7c7", "#dbdb8d", "#9edae5"];
            let counter = 0;

            for (let key in rawdata) {
                let colorIndex = counter - (Math.floor(counter / colors.length) * colors.length)
                $details.append($('<span>').text(key + ': ' + rawdata[key]));
                $details.append($('<br>'));

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
