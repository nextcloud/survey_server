/**
 * @author Björn Schießle <schiessle@owncloud.com>
 * @author Marcel Scherello <survey@scherello.de>
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

            let appData = {
                labels: appLabels,
                datasets: [
                    {
                        label: "Enabled Apps (in %)",
                        fillColor: "rgba(151,187,205,0.5)",
                        strokeColor: "rgba(151,187,205,0.8)",
                        highlightFill: "rgba(151,187,205,0.75)",
                        highlightStroke: "rgba(151,187,205,1)",
                        data: appValues
                    }
                ]
            };

            let ctx = document.getElementById("appChart").getContext("2d");
            //let myBarChart = new Chart(ctx).Bar(appData);
        };

        /**
         * draw the chart of Nextcloud versions
         *
         * @param id
         * @param data
         */
        let ocChart = function (id, data) {
            let ocChartData = [],
                $details = $('#' + id + 'Details');

            for (let key in data) {
                $details.append($('<span>').text(key + ': ' + data[key]));
                $details.append($('<br>'));

                ocChartData.push(
                    {
                        value: data[key],
                        color: getRandomColor(),
                        label: key
                    }
                );

            }

            let chartOptions = {
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
//                        let datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                            let datasetLabel = data.datasets[tooltipItem.datasetIndex].label || data.labels[tooltipItem.index];
                            if (tooltipItem['yLabel'] !== '') {
                                return datasetLabel + ': ' + parseFloat(tooltipItem['yLabel']).toLocaleString();
                            } else {
                                return datasetLabel;
                            }
                        }
                    }
                },
             };


            let ctx = document.getElementById(id + 'Chart').getContext("2d");
            let myPieChart = new Chart(ctx, {
                options: chartOptions
                }).Pie(ocChartData);

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
