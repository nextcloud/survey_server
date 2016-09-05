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
<ul>
	<li><a href="#generalStatistics">General Statistics</a></li>
	<li><a href="#enabledApps">Enabled Apps</a></li>
	<?php foreach ($_['statistics']['categories'] as $category => $data) { ?>
		<li><a href="#<?php p('survey' . ucfirst($category));?>"><?php p(ucwords(str_replace('_', ' ', $category)));?></a></li>
	<?php } ?>
</ul>
