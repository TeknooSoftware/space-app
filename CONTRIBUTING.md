# Contributing

You are welcome to contribute to Space. Please read this page first, then
[AGENTS.md](AGENTS.md) for the project standards and
[documentation/development.md](documentation/development.md) for the development workflow in detail.

## Rules

* The coding standard is **PSR-12**, enforced by `phpcs`. Run `./space.sh phpcs` before sending anything.
* Static analysis is **PHPStan at level `max`**. Run `./space.sh phpstan`.
* Any contribution must provide tests for the conditions it introduces.
* Any unconfirmed issue needs a failing test case before it is accepted.
* Pull requests are sent from a new `feature/…` or `hotfix/…` branch and target **`dev`**. `dev` is merged into
  `main` when a release is cut — never open a pull request from `main`.
* No deprecation warnings. The test suite runs with `failOnDeprecation`, so a deprecation is a failure, not a
  warning to live with.

## Installation

Clone the project and install it with the shipped tooling — do not run Composer by hand:

```sh
git clone https://github.com/TeknooSoftware/space-app.git
cd space-app
./space.sh dev-install
```

`./space.sh` forwards to the Makefile in `appliance/`; `./space.sh help` lists every target. See
[documentation/installation.md](documentation/installation.md) to run the full stack.

## Testing

```sh
./space.sh test            # unit tests + Behat, with coverage
./space.sh units-tests     # unit tests only
./space.sh behavior-test   # Behat features only
./space.sh qa              # lint + phpstan + phpcs + audit
```

Always go through these targets: `phpunit.xml` and `behat.yml` are generated from their `.dist` files, and
Behat additionally needs a warmed test cache. Calling `vendor/bin/phpunit` directly on a fresh clone fails.

The suite is at **100% line coverage** and the project intends to keep it there. A contribution that lowers
coverage will be asked for the missing tests.

## Support this project

This project is free and will remain free, but it is developed on personal time.
If you like it and want to help maintain and evolve it, you can support it on
[Patreon](https://patreon.com/teknoo_software) or [GitHub](https://github.com/sponsors/TeknooSoftware).

For any question, contact me: [richard@teknoo.software](mailto:richard@teknoo.software)

Thanks :) Richard.
