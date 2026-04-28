<!--
  - SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# AGENTS.md

This file provides guidance to all AI agents (Claude, Codex, Gemini, etc.) working with code in this repository.

## What This App Is

**Nextcloud Tables** is a Nextcloud server app (PHP backend + js frontend) that collects anonymized usage data from users of Nextcloud servers.

## General Guidance

Agents should focus on the core application logic and ignore files or folders marked as third-party, sample, or media-related. All changes should preserve the integrity of external dependencies and translations.

For every change, add a meaningful one-liner to the corresponding section (Added, Changed, Fixed) in CHANGELOG.md.

### License Header

Every new file needs to get a SPDX header in the first rows according to this template.
The year in the first line must be replaced with the year when the file is created (for example, 2026 for files first added in 2026).
The commenting signs need to be used depending on the file type.
If a file can not get a header like svg images, these need to be added to the REUSE.toml file.

```plaintext
SPDX-FileCopyrightText: <YEAR> Nextcloud GmbH
SPDX-License-Identifier: AGPL-3.0-or-later
```