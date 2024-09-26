<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use OCP\Util;

Util::addStyle('survey_server', 'style');
Util::addScript('survey_server', 'script');
Util::addScript('survey_server', 'vendor/chart.min');
?>

<div id="app-navigation">
    <?php print_unescaped($this->inc('part.navigation')); ?>
    <?php print_unescaped($this->inc('part.settings')); ?>
</div>

<div id="app-content">
    <?php print_unescaped($this->inc('part.content')); ?>
</div>