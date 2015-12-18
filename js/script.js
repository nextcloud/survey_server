/**
 * @author Björn Schießle <schiessle@owncloud.com>
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
		var getRandomColor = function() {
			var letters = '0123456789ABCDEF'.split('');
			var color = '#';
			for (var i = 0; i < 6; i++ ) {
				color += letters[Math.floor(Math.random() * 16)];
			}
			return color;
		};

		/**
		 * add general statistics to the page
		 * @param instances how many instances are counted
		 * @param users statistics about the users
		 */
		var showGeneralStatistics = function(instances, users, files) {
			$('#instances span').text(instances);
			$('#maxUsers span').text(users['max']);
			$('#minUsers span').text(users['min']);
			$('#averageUsers span').text(users['average']);
			$('#maxFiles span').text(files['max']);
			$('#minFiles span').text(files['min']);
			$('#averageFiles span').text(files['average']);

		};

		/**
		 * add general statistics to the page
		 * @param instances how many instances are counted
		 * @param users statistics about the users
		 */
		var ocNumericStatistics = function(id, data) {
			$('#' + id + 'Max span').text(data['max']);
			$('#' + id + 'Min span').text(data['min']);
			$('#' + id + 'Average span').text(data['average']);
		};

		/**
		 * draw the chart of enabled apps
		 *
		 * @param array data
		 */
		var appsChart = function (data) {
			var appLabels = new Array();
			var appValues = new Array();
			for (key in data) {
				appLabels.push(key);
				appValues.push(data[key]);
			}

			var appData = {
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

			var ctx = document.getElementById("appChart").getContext("2d");
			var myBarChart = new Chart(ctx).Bar(appData);
		};

		/**
		 * draw the chart of ownCloud versions
		 *
		 * @param array data
		 */
		var ocChart = function (id, data) {
			var ocChartData = new Array();
			for (key in data) {
				ocChartData.push(
					{
						value: data[key],
						color: getRandomColor(),
						label: key
					}
				);

			}
			var ctx = document.getElementById(id).getContext("2d");
			var myPieChart = new Chart(ctx).Pie(ocChartData);

		};

		$.get(
			OC.generateUrl('/apps/popularitycontestserver/api/v1/data'), {}
		).done(
			function (data) {
				showGeneralStatistics(data['instances'], data['categories']['stats']['num_users']['statistics'], data['categories']['stats']['num_files']['statistics']);
				appsChart(data['apps']);

				for (category in data['categories']) {
					for(key in data['categories'][category]) {
						if (key !== 'stats') {
							if (data['categories'][category][key]['presentation'] === 'diagram') {
								ocChart(category + key + 'Chart', data['categories'][category][key]['statistics']);
							} else if (data['categories'][category][key]['presentation'] === 'numerical evaluation') {
								ocNumericStatistics(category + key + 'Numeric', data['categories'][category][key]['statistics']);
							}
						}
					}
				}
			}
		);

	});

})(jQuery, OC);
