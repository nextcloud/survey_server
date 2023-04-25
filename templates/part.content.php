<?php
/**
 * @copyright Copyright (c) 2016, Björn Schießle <bjoern@schiessle.org>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>

<div id="surveyResults">

	<?php if(empty($_['statistics'])) { ?>
		No statistics available, please try later
	<?php } else { ?>

	<div class="section" id="generalStatistics">
		<h1>General Statistics</h1>

		<p id="instances">Counted Nextcloud installations: <span></span></p>
        <p id="lastUpdate">Last Update: <span></span></p>

		<br />

		<h2>Users</h2>

		<p id="maxUsers">Number of user (largest installation): <span></span></p>
		<br />
		<p id="minUsers">Number of users (smallest installation): <span></span></p>
		<br />
		<p id="averageUsers">Number of user (average): <span></span></p>
		<br />
		<p id="totalUsers">Number of user (total): <span></span></p>

		<br />

		<h2>Files</h2>

		<p id="maxFiles">Number of files (largest installation): <span></span></p>
		<br />
		<p id="minFiles">Number of files (smallest installation): <span></span></p>
		<br />
		<p id="averageFiles">Number of files (average): <span></span></p>
		<br />
		<p id="totalFiles">Number of files (total): <span></span></p>

	</div>

	<div class="section" id="enabledApps">
		<h1>Enabled Apps</h1>

		<h2>Top 75 (in %)</h2>
		<canvas id="appChart" width="1000" height="400"></canvas>

		<details id="appDetails">
			<summary><strong>Full list</strong></summary>
		</details>
	</div>

	<?php foreach ($_['statistics']['categories'] as $category => $data) { ?>
			<div class="section section-stats" id="<?php p('survey' . ucfirst($category)); ?>">
			<h1><?php p(ucwords(str_replace('_', ' ', $category)));?></h1>
			<?php foreach($data as $key => $value) { ?>
				<?php if ($category === 'stats' && in_array($key, ['num_files', 'num_users'])) { continue; } ?>
				<?php if ($value['presentation'] === \OCA\Survey_Server\EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION) {?>
					<h2><?php p(($value['description']));?></h2>
					<p id="<?php p($category . $key . 'NumericMax');?>"><?php //p($value['description']); ?> largest installation: <span></span></p>
					<!--<p id="<?php /*p($category . $key . 'NumericMin');*/?>"><?php /*p($value['description']); */?> (smallest installation): <span></span></p>
					<br />-->
					<p id="<?php p($category . $key . 'NumericAverage');?>"><?php //p($value['description']); ?> average: <span></span></p>
					<br />
					<!--<p id="<?php /*p($category . $key . 'NumericTotal');*/?>"><?php /*p($value['description']); */?> (total): <span></span></p>
					<br />-->
				<?php } ?>
			<?php } ?>


				<?php foreach($data as $key => $value) { ?>

					<?php if ($value['presentation'] === \OCA\Survey_Server\EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM) {?>
						<div class="chart">
							<h2><?php p(($value['description']));?></h2>

							<details id="<?php p(str_replace('.', '-', $category . $key) . 'Details');?>">
								<summary>
									<canvas id="<?php p(str_replace('.', '-', $category . $key) . 'Chart');?>" width="300" height="220"></canvas>
								</summary>
							</details>
						</div>
					<?php }
				} ?>

			</div>
		<?php } ?>
	<?php } ?>
</div>

