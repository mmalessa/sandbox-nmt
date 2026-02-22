# Sandbox NMT

A PHP sandbox for experimenting with Neural Machine Translation (NMT) using [LibreTranslate](https://libretranslate.com/) — a self-hosted machine translation engine.
Dockerized environment, ready to go right after `make up`.

## Architecture

Two services defined in `compose.yaml`:

- **libretranslate** — LibreTranslate server (port 5000) with a persistent volume for language models, memory limited to 2GB
- **php** — PHP 8.4/8.5 CLI (Alpine) with Composer 2, mounting the project directory as `/app`

The PHP container communicates with LibreTranslate at `http://libretranslate:5000` via the `devapp` Docker network.

## Supported Languages

en, pl, cs, de, es, fr, hu, nl, ro, sk, sv, ru, uk, it

The list can be changed via the `LT_LOAD_ONLY` variable in `compose.yaml`.

## Requirements

- Docker + Docker Compose

## Quick Start

```bash
make build       # Build Docker images
make up          # Start services (detached)
make init        # Install Composer dependencies
```

On the first run, LibreTranslate will download language models — this may take a few minutes.

### Running a Translation

```bash
docker compose exec php php src/translate.php
```

The script (`src/translate.php`) translates a given phrase from the source language into all configured target languages and returns the result as JSON.

### API Examples

List supported languages:

```http
GET http://localhost:5000/languages
```

Translate text:

```http
POST http://localhost:5000/translate
Content-Type: application/json

{
  "q": "chłodziarka",
  "source": "pl",
  "target": "en",
  "alternatives": 2
}
```

See `.http/libre_translate.http`.

Full API documentation: https://docs.libretranslate.com/

## Make Commands

```bash
make build   # Build Docker images
make up      # Start services (detached)
make down    # Stop and remove services
make sh      # Enter the PHP container shell (bash)
make init    # Start services and install dependencies
make help    # Show available commands
```

## Tech Stack

- PHP 8.4/8.5 (extensions: zip, pcntl, intl, bcmath)
- [malvik-lab/libre-translate-api-client](https://github.com/AntonioDiPassio/libre-translate-api-client) — PHP client for the LibreTranslate API
- [openai-php/client](https://github.com/openai-php/client) — OpenAI PHP client (for future Ollama integration)
- XDebug pre-configured for PHPStorm (`host.docker.internal:9003`, IDE key `PHPSTORM`)
