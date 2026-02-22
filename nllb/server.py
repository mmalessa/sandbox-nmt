from fastapi import FastAPI
from pydantic import BaseModel
from transformers import AutoModelForSeq2SeqLM, NllbTokenizerFast

import os

model_name = os.getenv("MODEL_NAME", "facebook/nllb-200-distilled-600M")

tokenizer = NllbTokenizerFast.from_pretrained(model_name)
model = AutoModelForSeq2SeqLM.from_pretrained(model_name)

app = FastAPI()

class TranslateRequest(BaseModel):
    text: str
    source: str
    target: str

@app.post("/translate")
def translate(req: TranslateRequest):
    tokenizer.src_lang = req.source

    encoded = tokenizer(req.text, return_tensors="pt")

    generated_tokens = model.generate(
        **encoded,
        forced_bos_token_id=tokenizer.convert_tokens_to_ids(req.target),
        max_length=200
    )

    result = tokenizer.batch_decode(generated_tokens, skip_special_tokens=True)

    return {"translatedText": result[0]}