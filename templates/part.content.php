<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

?>

<div id="surveyResults">

	<?php if (empty($_['statistics'])) { ?>
        No statistics available, please try later
	<?php } else { ?>

        <div class="section" id="generalStatistics">
            <h1>General Statistics</h1>

            <p id="instances">Counted Nextcloud installations: <span></span></p>
            <p id="lastUpdate">Last Update: <span></span></p>

            <br/>

            <table>
                <thead>
                <tr>
                    <th></th>
                    <th>Total</th>
                    <th>Average</th>
                    <th>Largest</th>
                    <th>Smallest</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Users</td>
                    <td id="totalUsers"><span></span></td>
                    <td id="averageUsers"><span></span></td>
                    <td id="maxUsers"><span></span></td>
                    <td id="minUsers"><span></span></td>
                </tr>
                <tr>
                    <td>Files</td>
                    <td id="totalFiles"><span></span></td>
                    <td id="averageFiles"><span></span></td>
                    <td id="maxFiles"><span></span></td>
                    <td id="minFiles"><span></span></td>
                </tr>
                </tbody>
            </table>

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
            <h1  id="<?php p('survey' . ucfirst($category)); ?>"><?php p(ucwords(str_replace('_', ' ', $category))); ?></h1>
            <div class="section-stats">

				<?php
				$dataKeys = array_keys($data);
				$dataCount = count($data);

				for ($i = 0; $i < $dataCount; $i++) {
					$key = $dataKeys[$i];
					$value = $data[$key];

                    // Your conditions and logic here
                    if ($category === 'stats' && in_array($key, ['num_files', 'num_users'])) {
                        //continue;
                    }

				    $prevValue = $i > 0 ? $data[$dataKeys[$i - 1]] : null;
					$nextValue = $i + 1 < $dataCount ? $data[$dataKeys[$i + 1]] : null;

				    if ($value['presentation'] === \OCA\SurveyServer\EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION
                        && ($prevValue === null
                        || $prevValue['presentation'] === \OCA\SurveyServer\EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM)){
                        // draw a table header because its the first numerical numbers ?>
                        <table><thead><tr><th></th><th>Average</th><th>Largest</th></tr></thead><tbody>
                    <?php }

					if ($value['presentation'] === \OCA\SurveyServer\EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION) { ?>
                        <tr>
                            <td><?php p(($value['description'])); ?></td>
                            <td id="<?php p($category . $key . 'NumericAverage'); ?>"><span></span></td>
                            <td id="<?php p($category . $key . 'NumericMax'); ?>"><span></span></td>
                        </tr>
					<?php } else if ($value['presentation'] === \OCA\SurveyServer\EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM) { ?>
                            <div class="chart">
                                <h2><?php p(($value['description'])); ?></h2>

                                <details id="<?php p(str_replace('.', '-', $category . $key) . 'Details'); ?>">
                                    <summary>
                                        <canvas id="<?php p(str_replace('.', '-', $category . $key) . 'Chart'); ?>"
                                                width="200" height="150"></canvas>
                                    </summary>
                                </details>
                            </div>
					<?php }

					if ($nextValue === null || $nextValue['presentation'] === \OCA\SurveyServer\EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM) { ?>
                        </tbody>
                        </table>
					<?php }

				}
				?>

            </div>
		<?php } ?>
	<?php } ?>
</div>