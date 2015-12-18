<div id="surveyResults">

	<div class="section" id="generalStatistics">
		<h1>General Statistics</h1>

		<p id="instances">Counted ownCloud installations: <span></span></p>

		<br />

		<h2>Users</h2>

		<p id="maxUsers">Number of user (largest installation): <span></span></p>
		<br />
		<p id="minUsers">Number of users (smallest installation): <span></span></p>
		<br />
		<p id="averageUsers">Number of user (average): <span></span></p>

		<br />

		<h2>Files</h2>

		<p id="maxFiles">Number of files (largest installation): <span></span></p>
		<br />
		<p id="minFiles">Number of files (smallest installation): <span></span></p>
		<br />
		<p id="averageFiles">Number of files (average): <span></span></p>

	</div>

	<div class="section" id="enabledApps">
		<h1>Enabled Apps</h1>
		<canvas id="appChart" width="800" height="400"></canvas>
	</div>

	<?php foreach ($_['categories'] as $category => $data) { ?>

		<?php if ($category !== 'stats') { ?>
			<div class="section section-stats" id="<?php p('survey' . ucfirst($category)); ?>">
			<h1><?php p(ucwords(str_replace('_', ' ', $category)));?></h1>
			<?php foreach($data as $key => $value) { ?>
				<?php if ($value['presentation'] === \OCA\PopularityContestServer\EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION) {?>
					<h2><?php p(($value['description']));?></h2>
					<p id="<?php p($category . $key . 'NumericMax');?>"><?php p($value['description']); ?> (largest installation): <span></span></p>
					<br />
					<p id="<?php p($category . $key . 'NumericMin');?>"><?php p($value['description']); ?> (smallest installation): <span></span></p>
					<br />
					<p id="<?php p($category . $key . 'NumericAverage');?>"><?php p($value['description']); ?> (average): <span></span></p>
					<br />
				<?php } ?>
			<?php } ?>


				<?php foreach($data as $key => $value) { ?>

					<?php if ($value['presentation'] === \OCA\PopularityContestServer\EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM) {?>
						<div class="chart">
							<h2><?php p(($value['description']));?></h2>
							<canvas id="<?php p($category . $key . 'Chart');?>" width="400" height="300"></canvas>
						</div>
					<?php }
				} ?>

			</div>
		<?php } ?>
	<?php } ?>

</div>
