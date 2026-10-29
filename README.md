# symfony-signature-demo

Version: 2.0.17

Demo of symfony-signature: a sample envelope sent from the demo account to another and to someone outside, signed, verified, its proof downloaded

## Table of Contents

- [Installation](#installation)

## Installation

```php
// config/bundles.php, beside symfony-signature, -ds, symfony-media, -ds, and symfony-user-demo
Wexample\SymfonySignatureDemo\WexampleSymfonySignatureDemoBundle::class => ['all' => true],
```

The demo of symfony-signature, at `/signature-demo/`, on the accounts of symfony-user-demo — it has no users of its own.

- A sample envelope — a contract written for the demo, kept in the media library folder `Signature demo` — sent from the signed-in account to itself, to the admin account and to `client@example.org`, someone outside; sealed at once.
- The signed links of the outside signers, shown on the page: the demo has nobody outside to receive the mail.
- `DemoSignerAccountResolver` finds the demo accounts by their address, so that a signer added as `demo-admin@example.com` signs signed in.
- Where symfony-activity is installed, its `signature` category is declared, and the history of each envelope kept.
