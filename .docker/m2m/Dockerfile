FROM python:3.11-slim

# zapobiega tworzeniu .pyc i buforowaniu
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

WORKDIR /app

# instalacja zależności systemowych
RUN apt-get update && apt-get install -y \
    curl \
    && rm -rf /var/lib/apt/lists/*

# instalacja zależności python
RUN pip install --no-cache-dir \
    fastapi \
    uvicorn \
    transformers \
    sentencepiece \
    torch

# kopiowanie aplikacji
COPY ./server.py .

EXPOSE 8000

CMD ["uvicorn", "server:app", "--host", "0.0.0.0", "--port", "8000"]
