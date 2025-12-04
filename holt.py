from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
from statsmodels.tsa.holtwinters import ExponentialSmoothing
import warnings

warnings.filterwarnings('ignore')

app = Flask(__name__)
CORS(app)  # Enable CORS untuk akses dari frontend

@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Ambil data dari request
        data = request.json
        
        # Validasi input
        if 'data' not in data:
            return jsonify({
                'success': False,
                'message': 'Parameter "data" diperlukan'
            }), 400
        
        sales_data = data['data']
        sales_data = [float(x) for x in sales_data]
        
        # Validasi data minimal
        if len(sales_data) < 8:
            return jsonify({
                'success': False,
                'message': 'Data minimal harus 8 periode'
            }), 400
        
        # Konversi ke pandas Series (tanpa tanggal)
        series = pd.Series(sales_data)
        
        # Buat model Holt (Double Exponential Smoothing)
        # Hanya trend, tanpa seasonal
        model = ExponentialSmoothing(
            series, 
            trend='add',
            seasonal=None
        )
        
        fit = model.fit(optimized=True)
        
        # Prediksi untuk 1 periode ke depan
        forecast = fit.forecast(1)
        
        # Format hasil
        result = {
            'success': True,
            'data': {
                'historical': sales_data,
                'forecast': int(forecast),
                'parameters': {
                    'alpha': float(fit.params['smoothing_level']),
                    'beta': float(fit.params['smoothing_trend'])
                }
            }
        }
        
        return jsonify(result), 200
        
    except Exception as e:
        print(e)
        return jsonify({
            'success': False,
            'message': str(e)
        }), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'OK',
        'message': 'API is running'
    }), 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=33, debug=True)