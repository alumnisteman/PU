from fastapi import FastAPI
from pydantic import BaseModel

app = FastAPI()

class RoadData(BaseModel):
    kondisi: float
    traffic: float
    rainfall: float
    age: float
    reports: float
    length: float

@app.post("/predict")
def predict_priority(data: RoadData):
    # Mock AI Prediction Logic based on user inputs
    # Normalize inputs to a 0-100 scale (Assuming conditions are worse at higher numbers for mock)
    # This is a placeholder for a real ML model (e.g. Random Forest, XGBoost)
    
    # Simple weighted sum for mock AI prediction
    w_kondisi = 0.4
    w_traffic = 0.2
    w_rainfall = 0.1
    w_age = 0.15
    w_reports = 0.15
    
    # Calculate mock risk score
    risk_score = (
        (data.kondisi * w_kondisi) +
        (data.traffic * w_traffic) +
        (data.rainfall * w_rainfall) +
        (data.age * w_age) +
        (data.reports * w_reports)
    )
    
    # Add length factor (longer roads with high risk score get slightly more priority)
    length_multiplier = 1.0 + (min(data.length, 10) / 100) # Max 10% bump for 10km+ roads
    
    final_score = min(risk_score * length_multiplier, 100)
    
    return {"risk_score": final_score}

@app.get("/")
def health_check():
    return {"status": "AI Service is running"}
