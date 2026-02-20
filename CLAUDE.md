# CLAUDE.md

Guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP sandbox for experimenting with Ollama (local LLM inference). A Dockerized PHP 8.5 CLI app communicates with the Ollama API to run language model tasks (e.g., translation via `qwen2.5:3b`).

## Commands

All commands use Docker Compose via Makefile:

```bash
make build        # Build Docker images
make up           # Start services (detached)
make down         # Stop and remove services
make sh           # Shell into the PHP container
make get-models   # Pull the qwen2.5:3b model into Ollama
make list-models  # List available Ollama models
make help         # Show all available commands
```

Run PHP scripts from the host via:

```bash
docker compose exec php sh -c '<script>'
```

## Architecture

Two Docker services defined in `compose.yaml`:

- **ollama** — Ollama server on port 11434 with a persistent volume for models
- **php** — PHP 8.5.2 CLI (Alpine) with Composer 2, mounting `src/` as the working directory

The PHP container reaches Ollama at `http://ollama:11434` via the `devapp` Docker network. Application code lives in `src/` and uses the `openai-php/client` library to call Ollama's OpenAI-compatible API (`/v1/chat/completions`).

## Development Environment

- PHP 8.5 with extensions: zip, pcntl, intl, bcmath
- XDebug pre-configured for PHPStorm (`host.docker.internal:9003`, IDE key `PHPSTORM`)
- No test framework, linter, or static analysis config is set up yet — only IDE-level tooling references exist
