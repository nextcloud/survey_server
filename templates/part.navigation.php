<ul>
	<li><a href="#generalStatistics">General Statistics</a></li>
	<li><a href="#enabledApps">Enabled Apps</a></li>
	<?php foreach ($_['categories'] as $category => $data) { ?>
		<li><a href="#<?php p('survey' . ucfirst($category));?>"><?php p(ucwords(str_replace('_', ' ', $category)));?></a></li>
	<?php } ?>
</ul>
