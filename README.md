# Sandbox NMT

A PHP sandbox for experimenting with Neural Machine Translation (NMT) using [LibreTranslate](https://libretranslate.com/) and [NLLB-200](https://ai.meta.com/research/no-language-left-behind/) — self-hosted machine translation engines.
Dockerized environment, ready to go right after `make up`.

## Architecture

Three services defined in `compose.yaml`:

- **libretranslate** — LibreTranslate server (port 5000) with a persistent volume for language models, memory limited to 2GB
- **nllb-translator** — Python FastAPI server (port 8081) wrapping Meta's NLLB-200 model (`facebook/nllb-200-distilled-600M`), memory limited to 3GB
- **php** — PHP 8.4/8.5 CLI (Alpine) with Composer 2, mounting the project directory as `/app`

The PHP container communicates with LibreTranslate at `http://libretranslate:5000` and NLLB at `http://nllb-translator:8000` via the `devapp` Docker network.

## Requirements

- Docker + Docker Compose

## Quick Start

```bash
make build       # Build Docker images
make up          # Start services (detached)
make init        # Install Composer dependencies
```

On the first run, LibreTranslate and NLLB will download language models — this may take a few minutes.

### LibreTranslate API

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

Full API documentation: https://docs.libretranslate.com/

### NLLB API

Translate text (uses [FLORES-200 language codes](https://github.com/facebookresearch/flores/blob/main/flores200/README.md#languages-in-flores-200)):

```http
POST http://localhost:8081/translate
Content-Type: application/json

{
  "text": "chłodziarka",
  "source": "pol_Latn",
  "target": "eng_Latn"
}
```

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
- [NLLB-200](https://ai.meta.com/research/no-language-left-behind/) (`facebook/nllb-200-distilled-600M`) — Meta's multilingual translation model supporting 200 languages
- FastAPI + Uvicorn — Python server exposing NLLB as a REST API
- XDebug pre-configured for PHPStorm (`host.docker.internal:9003`, IDE key `PHPSTORM`)
