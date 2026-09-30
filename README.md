# Symplast Webhook

Receives CallRail's post-call webhook at `/wp-json/symplast/webhook/postcall` and logs each call to `callrail-logs/calls/`. Six hours later it sends answered calls to the LeadConnector referral source workflow, and calls with a recording to the call recording workflow. Failed sends are retried twice, an hour apart, and every attempt is logged to `callrail-logs/cron/`.

## Settings

All settings go in `wp-config.php`:

```php
define( 'SYMPLAST_WEBHOOK_REFERRAL_URL', '' );        // referral source workflow URL
define( 'SYMPLAST_WEBHOOK_RECORDING_URL', '' );       // call recording workflow URL
define( 'SYMPLAST_WEBHOOK_SIGNING_KEY', '' );         // token from CallRail's Webhooks settings page
define( 'SYMPLAST_WEBHOOK_ENFORCE_SIGNATURE', false ); // reject requests without a valid signature
```

A workflow without a URL isn't sent to, so leave the URLs out on local and staging. On production, add them before deploying the plugin.

## Turning on signature checks

1. Set `SYMPLAST_WEBHOOK_SIGNING_KEY`.
2. After a few real calls, check that new call logs show `"signature_check": "valid"`.
3. Set `SYMPLAST_WEBHOOK_ENFORCE_SIGNATURE` to `true`.
