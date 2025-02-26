# Symfony Docker Discord Message Eraser Bot

Schedule automated message deletion tasks in your Discord channels.

![CI](https://github.com/kidthales/symfony-docker-discord-message-eraser-bot/workflows/CI/badge.svg)

## Requirements

- [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)

## Quick Start

1. Run `docker compose build --pull --no-cache` to build fresh images
2. Run `docker compose up --detach` to start the bot
3. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
4. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Docs

1. [Options available](docs/options.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using MySQL instead of PostgreSQL](docs/mysql.md)
8. [Using Alpine Linux instead of Debian](docs/alpine.md)
9. [Using a Makefile](docs/makefile.md)
10. [Updating the template](docs/updating.md)
11. [Troubleshooting](docs/troubleshooting.md)

## License

Symfony Docker Discord Message Eraser Bot is available under the MIT License.

## Credits

- [Symfony Docker](https://github.com/dunglas/symfony-docker)
