from fastapi import FastAPI
from pydantic import BaseModel
from transformers import M2M100ForConditionalGeneration, M2M100Tokenizer
import os

model_name = os.getenv("MODEL_NAME", "facebook/m2m100_418M")

tokenizer = M2M100Tokenizer.from_pretrained(model_name)
model = M2M100ForConditionalGeneration.from_pretrained(model_name)

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
        forced_bos_token_id=tokenizer.get_lang_id(req.target)
    )

    result = tokenizer.batch_decode(generated_tokens, skip_special_tokens=True)

    return {"translatedText": result[0]}
