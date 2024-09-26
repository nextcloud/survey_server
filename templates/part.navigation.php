<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

?>
<ul>
	<li><a href="#generalStatistics">- General Statistics</a></li>
	<li><a href="#enabledApps">- Enabled Apps</a></li>
	<?php foreach ($_['statistics']['categories'] as $category => $data) { ?>
		<li><a href="#<?php p('survey' . ucfirst($category));?>">- <?php p(ucwords(str_replace('_', ' ', $category)));?></a></li>
	<?php } ?>
</ul>
