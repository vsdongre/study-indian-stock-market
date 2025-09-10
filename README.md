# Study Indian Stock Market

A comprehensive Python application for analyzing Indian stock market data using Hydra AI's advanced analytics and machine learning capabilities.

## Features

- 🤖 **Hydra AI Integration**: Leverage advanced AI for stock market analysis
- 📈 **Stock Analysis**: Comprehensive analysis of individual Indian stocks
- 💼 **Portfolio Management**: Risk analysis and portfolio optimization
- 🏭 **Sector Analysis**: Industry-wise market insights
- 🌐 **Market Overview**: Real-time market trends and predictions
- 📊 **Data Visualization**: Rich charts and graphs for analysis results

## Prerequisites

- Python 3.8 or higher
- Hydra AI API key (obtain from Hydra AI platform)
- Internet connection for real-time data

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/vsdongre/study-indian-stock-market.git
   cd study-indian-stock-market
   ```

2. **Create a virtual environment**:
   ```bash
   python -m venv venv
   
   # On Windows
   venv\Scripts\activate
   
   # On macOS/Linux
   source venv/bin/activate
   ```

3. **Install dependencies**:
   ```bash
   pip install -r requirements.txt
   ```

4. **Set up your API key**:
   ```bash
   cp .env.example .env
   ```
   
   Edit the `.env` file and add your Hydra AI API key:
   ```
   HYDRA_AI_API_KEY=your_actual_api_key_here
   ```

## Quick Start

### Basic Usage

```python
from stock_analyzer import IndianStockAnalyzer

# Initialize the analyzer
analyzer = IndianStockAnalyzer()

# Analyze a single stock
analysis = analyzer.analyze_single_stock('RELIANCE')
print(analysis)

# Get market overview
market_data = analyzer.get_market_overview()
print(market_data)
```

### Portfolio Analysis

```python
# Define your portfolio
portfolio = [
    {'symbol': 'RELIANCE', 'weight': 0.25},
    {'symbol': 'TCS', 'weight': 0.20},
    {'symbol': 'INFY', 'weight': 0.15},
    {'symbol': 'HDFCBANK', 'weight': 0.20},
    {'symbol': 'ICICIBANK', 'weight': 0.20}
]

# Analyze portfolio risk
portfolio_analysis = analyzer.analyze_portfolio(portfolio)
print(portfolio_analysis)
```

### Sector Analysis

```python
# Analyze IT sector
it_analysis = analyzer.analyze_sector('IT')
print(it_analysis)
```

## Available Functions

### HydraAIClient Class

- `analyze_stock_sentiment(symbol, market)`: Get AI-powered sentiment analysis
- `get_market_insights(indices)`: Retrieve market insights and trends
- `predict_stock_price(symbol, days, market)`: AI-based price predictions
- `analyze_portfolio_risk(portfolio)`: Portfolio risk assessment
- `get_sector_analysis(sector)`: Sector-wise market analysis

### IndianStockAnalyzer Class

- `analyze_single_stock(symbol, market)`: Comprehensive stock analysis
- `analyze_portfolio(portfolio)`: Complete portfolio analysis
- `get_market_overview(indices)`: Market overview and insights
- `analyze_sector(sector)`: Detailed sector analysis

### Utility Functions

- `save_analysis_to_file()`: Save results to JSON
- `load_analysis_from_file()`: Load previous analysis
- `format_currency()`: Format Indian currency values
- `generate_sample_portfolio()`: Create test portfolios
- `validate_portfolio()`: Validate portfolio structure

## Supported Markets and Symbols

### Markets
- **NSE (National Stock Exchange)**: Primary market
- **BSE (Bombay Stock Exchange)**: Secondary support

### Popular Stock Symbols
- **IT**: TCS, INFY, WIPRO, HCLTECH, TECHM
- **Banking**: HDFCBANK, ICICIBANK, SBIN, AXISBANK
- **Energy**: RELIANCE, ONGC, BPCL, IOC
- **Auto**: MARUTI, TATAMOTORS, M&M, BAJAJ-AUTO
- **Pharma**: SUNPHARMA, DRREDDY, CIPLA, DIVISLAB

## Running the Application

### Command Line
```bash
python stock_analyzer.py
```

### Interactive Analysis
```python
# Import required modules
from stock_analyzer import IndianStockAnalyzer
from utils import print_analysis_summary, save_analysis_to_file

# Create analyzer
analyzer = IndianStockAnalyzer()

# Run analysis
result = analyzer.analyze_single_stock('TCS')

# Display results
print_analysis_summary(result)

# Save results
save_analysis_to_file(result, 'tcs_analysis.json')
```

## Configuration

The application can be configured through the `config.py` file or environment variables:

```python
# Default configuration
HYDRA_AI_API_KEY = "your_api_key"
HYDRA_AI_BASE_URL = "https://api.hydra-ai.com/v1"
DEFAULT_MARKET = "NSE"
DEFAULT_INDICES = ["NIFTY50", "SENSEX", "BANKNIFTY"]
REQUEST_TIMEOUT = 30
MAX_RETRIES = 3
```

## Error Handling

The application includes comprehensive error handling:

- API key validation
- Network connectivity checks
- Invalid symbol handling
- Portfolio validation
- Rate limiting protection

## Data Sources

- **Hydra AI**: Primary AI analytics and predictions
- **Yahoo Finance**: Supplementary market data and stock information
- **NSE/BSE**: Real-time Indian market data

## Examples

### Example 1: Daily Stock Monitoring
```python
stocks_to_monitor = ['RELIANCE', 'TCS', 'HDFCBANK', 'INFY']
for stock in stocks_to_monitor:
    analysis = analyzer.analyze_single_stock(stock)
    print_analysis_summary(analysis)
```

### Example 2: Sector Comparison
```python
sectors = ['IT', 'Banking', 'Energy', 'Auto']
for sector in sectors:
    analysis = analyzer.analyze_sector(sector)
    print(f"{sector} Sector Analysis:", analysis)
```

### Example 3: Portfolio Optimization
```python
portfolio = generate_sample_portfolio('IT', 5)
risk_analysis = analyzer.analyze_portfolio(portfolio)
print("Portfolio Risk Metrics:", risk_analysis)
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Disclaimer

This application is for educational and research purposes only. Stock market investments carry risk, and past performance does not guarantee future results. Always consult with financial professionals before making investment decisions.

## Support

For support, please open an issue on GitHub or contact the maintainers.

## Changelog

### v1.0.0
- Initial release with Hydra AI integration
- Stock analysis functionality
- Portfolio management features
- Sector analysis capabilities
- Market overview insights