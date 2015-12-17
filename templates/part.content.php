<div id="surveyResults">

	<div class="section" id="generalStatistics">
		<h1>General Statistics</h1>

		<p id="instances">Counted ownCloud installations: <span></span></p>
		<br />
		<p id="maxUsers">Number of user (largest installation): <span></span></p>
		<br />
		<p id="minUsers">Number of users (smallest installation): <span></span></p>
		<br />
		<p id="averageUsers">Number of user (average): <span></span></p>
	</div>

	<div class="section" id="enabledApps">
		<h1>Enabled Apps</h1>
		<canvas id="appChart" width="800" height="400"></canvas>
	</div>

	<?php foreach ($_['appStatistics'] as $category => $data) { ?>
		<div class="section" id="<?php p('survey' . ucfirst($category)); ?>">
			<h1><?php p(ucfirst($category));?></h1>
			<?php foreach($data as $key => $value) { ?>
				<h2><?php p(ucfirst($key));?></h2>
				<canvas id="<?php p($category . $key . 'Chart');?>" width="400" height="300"></canvas>
			<?php } ?>
		</div>
	<?php } ?>

</div>
