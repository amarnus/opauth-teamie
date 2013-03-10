Opauth-Teamie
=============

Teamie is a social, collaborative learning management system. The Teamie API helps developers take their applications right inside the classroom allowing a richer, more meaningful learning experiences for learners and instructors alike that's actually fun and engaging.

Teamie Platform Guide (includes the API console)
https://playground.theteamie.com/platform/guide

Teamie
https://theteamie.com

Getting started
----------------
1. Install Opauth-Teamie as a git submodule in your existing Opauth setup
   ```
     git submodule add git://github.com/amarnus/opauth-teamie lib/Opauth/Strategy/Teamie
   ```
2. Configure Opauth to include the Teamie strategy.

```
<?php
'Teamie' => array(
	'client_id' => '[Teamie-Client-ID]',
	'client_secret' => '[Teamie-Client-Secret]'
)
```