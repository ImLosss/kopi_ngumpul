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
        data = request.get_json(silent=True)

        if data is None:
            return jsonify({
                'success': False,
                'message': 'Body request harus berupa JSON'
            }), 400

        # ============================
        # MODE BARU: data dalam "items"
        # ============================
        if 'items' in data:
            items = data['items']

            if not isinstance(items, list) or len(items) == 0:
                return jsonify({
                    'success': False,
                    'message': 'Parameter "items" harus berupa list dan tidak boleh kosong'
                }), 400

            results = []

            for item in items:
                product_id = item.get('product_id')
                sales = item.get('sales')

                # Validasi per item
                if product_id is None or sales is None:
                    return jsonify({
                        'success': False,
                        'message': 'Setiap item harus memiliki "product_id" dan "sales"'
                    }), 400

                if not isinstance(sales, (list, tuple)):
                    return jsonify({
                        'success': False,
                        'message': f'"sales" untuk product_id {product_id} harus berupa list'
                    }), 400

                if len(sales) < 8:
                    return jsonify({
                        'success': False,
                        'message': f'Data minimal harus 8 periode untuk product_id {product_id}'
                    }), 400

                # Konversi ke float dan ke Series
                sales_data = [float(x) for x in sales]
                series = pd.Series(sales_data)

                # Model Holt (trend additive, tanpa musiman)
                model = ExponentialSmoothing(
                    series,
                    trend='add',
                    seasonal=None
                )

                fit = model.fit(optimized=True)

                # forecast() mengembalikan Series, ambil nilai pertama
                forecast_series = fit.forecast(1)
                forecast_value = float(forecast_series.iloc[0])

                results.append({
                    'product_id': product_id,
                    'historical': sales_data,
                    'forecast': int(round(forecast_value)),
                    'parameters': {
                        'alpha': float(fit.params.get('smoothing_level', 0.0)),
                        'beta': float(fit.params.get('smoothing_trend', 0.0))
                    }
                })

            return jsonify({
                'success': True,
                'data': results
            }), 200

        # ======================================
        # MODE LAMA (opsional): data dalam "data"
        # ======================================
        if 'data' in data:
            sales_data = data['data']

            if not isinstance(sales_data, (list, tuple)):
                return jsonify({
                    'success': False,
                    'message': 'Parameter "data" harus berupa list'
                }), 400

            sales_data = [float(x) for x in sales_data]

            if len(sales_data) < 8:
                return jsonify({
                    'success': False,
                    'message': 'Data minimal harus 8 periode'
                }), 400

            series = pd.Series(sales_data)

            model = ExponentialSmoothing(
                series,
                trend='add',
                seasonal=None
            )

            fit = model.fit(optimized=True)

            forecast_series = fit.forecast(1)
            forecast_value = float(forecast_series.iloc[0])

            result = {
                'success': True,
                'data': {
                    'historical': sales_data,
                    'forecast': int(round(forecast_value)),
                    'parameters': {
                        'alpha': float(fit.params.get('smoothing_level', 0.0)),
                        'beta': float(fit.params.get('smoothing_trend', 0.0))
                    }
                }
            }

            return jsonify(result), 200

        # Kalau tidak ada "items" dan tidak ada "data"
        return jsonify({
            'success': False,
            'message': 'Parameter "items" atau "data" diperlukan'
        }), 400

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
    app.run(host='0.0.0.0', port=3333, debug=True)
