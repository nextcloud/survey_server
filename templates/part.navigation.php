<ul>
	<li><a href="#generalStatistics">General Statistics</a></li>
	<li><a href="#enabledApps">Enabled Apps</a></li>
	<?php foreach ($_['categories'] as $category => $data) { ?>
		<li><a href="#<?php p('survey' . ucfirst($category));?>"><?php p(ucfirst($category));?></a></li>
	<?php } ?>
</ul>
