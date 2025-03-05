# Symfony Docker Discord Message Eraser Bot

Schedule automated message deletion tasks in your Discord channels.

![CI](https://github.com/kidthales/symfony-docker-discord-message-eraser-bot/workflows/CI/badge.svg)

> ⚠️ Under Development - Not Ready ⚠️

## Requirements

- [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
- [Ngrok](https://ngrok.com/) (or similar proxy agent for development on localhost)

## Quick Start

1. Create the git ignored `.env.dev.local` file and add the following:
    ```dotenv
    DISCORD_APP_PUBLIC_KEY=!ChangeThisDiscordAppPublicKey!
    DISCORD_OAUTH2_CLIENT_ID=!ChangeThisDiscordOAuthClientId!
    DISCORD_OAUTH2_CLIENT_SECRET=!ChangeThisDiscordOAuthClientSecret!
    NGROK_AUTHTOKEN=!ChangeThisNgrokAuthToken!
    ```
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --detach` to start the bot
4. Run `docker run -it --rm --net=host --env-file .env.dev.local ngrok/ngrok http https://localhost:443 --host-header=localhost` to start the ngrok agent and forward public traffic
5. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Docs

1. [Options available](docs/options.md)
2. [Support for extra services](docs/extra-services.md)
3. [Debugging with Xdebug](docs/xdebug.md)
4. [TLS Certificates](docs/tls.md)
5. [Troubleshooting](docs/troubleshooting.md)

## License

Symfony Docker Discord Message Eraser Bot is available under the MIT License.

## Credits

- [Symfony Docker](https://github.com/dunglas/symfony-docker)
