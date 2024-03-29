msend receiver for Icinga Alerts
================================

This module will receive Icinga Alerts via `msend` from the **BMC (ProactiveNet)
Event Manager©**). In the future, it should hook into **Icinga Alerts**. Right
now it directly depends on the [Event Tracker](https://github.com/Thomas-Gelf/icingaweb2-module-eventracker)
module.

Security
--------
For historical reasons this module assumes that you're using Certificate-based
authentication. This will probably change in future versions, but as of this
writing please make sure you protect requests to this module in your webserver
configuration unless you want to allow event sending to **everybody**.

Configuration
-------------

Configuration takes place in  `/etc/icingaweb2/modules/msend/config.ini`.

### Logging

Forwarding msend-like parameters via HTTP might become tricky, that's why we
provide a script that behaves like `msend` in `contrib/msend-eventtracker`. In
case you need to wrap this in a custom script and face encoding issues, logging
every single command might help.

```ini
[logging]
force = yes
```

Severe errors are always logged, you do not need this toggle in case you're
interested in errors only.

### Severity Mapping

Eventually you might want to override default msend severity mappings:

```ini
[severity-map]
CRITICAL      = alert
MAJOR         = critical
MINOR         = error
WARNING       = warning
INFORMATIONAL = informational
INFO          = informational
NORMAL        = informational
OK            = informational
```

Problem Handling
----------------

The `property-map` section allows mapping special attributes to event
properties, with `problem_identifier` being the only property being available for
custom mapping right now.

```ini
[property-map]
; problem_identifier = "problem_identifier"
```

This example maps the slot value `problem_identifier` to the `problem_identifier`
Event property. A related configuration in the `msend` module might for example
want to set `problem_identifier` to `{service.vars.problem_identifier}` in the
`[msend_params]` section for a specific cell.

Upgrading
---------

In case you already used msend via the Eventtracker module, please note that the
submission URL changed from **icingaweb2/eventtracker/push/msend** to **icingaweb2/msend**.

Therefore, the `msend-eventtracker` script in this repository differs from the
former one as follows:

```patch
--- a/contrib/msend-eventtracker
+++ b/contrib/msend-eventtracker
@@ -16,4 +16,4 @@ for i in "$@"; do
 done
 echo "$C"
 
-"$CURL" -X POST $ICINGAWEB2/eventtracker/push/msend -H "Content-Type: text/plain" --data-binary "$C"
+"$CURL" -X POST $ICINGAWEB2/msend -H "Content-Type: text/plain" --data-binary "$C"
```

In case you're running a customized version of this script, you need to adjust
the URL accordingly.

Changes
-------

### v0.3.0
* use Event::create() for recent eventtracker
* breaking: requires PHP 7.3

### v0.2.1
* Fix controller namespaces (#2)
