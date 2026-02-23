FROM python:3.11-slim

RUN pip install --no-cache-dir fastapi uvicorn transformers sentencepiece torch

WORKDIR /app

COPY server.py .

CMD ["uvicorn", "server:app", "--host", "0.0.0.0", "--port", "8000"]
