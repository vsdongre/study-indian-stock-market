#!/usr/bin/env python3
"""
Demo script showing how to use Hydra AI for Indian stock market analysis.

This script demonstrates various features of the Hydra AI integration
without making actual API calls (for demonstration purposes).
"""

from stock_analyzer import IndianStockAnalyzer
from utils import (
    print_analysis_summary, 
    save_analysis_to_file, 
    generate_sample_portfolio,
    get_indian_market_symbols,
    format_currency
)
import json

def demo_basic_setup():
    """Demonstrate basic setup and configuration."""
    print("🚀 HYDRA AI INDIAN STOCK MARKET ANALYZER")
    print("=" * 50)
    
    print("\n📋 Setup Instructions:")
    print("1. Copy .env.example to .env")
    print("2. Add your Hydra AI API key: HYDRA_AI_API_KEY=your_key_here")
    print("3. Install dependencies: pip install -r requirements.txt")
    print("4. Run analysis: python stock_analyzer.py")

def demo_market_symbols():
    """Demonstrate available market symbols."""
    print("\n📈 Available Indian Market Symbols by Sector:")
    print("-" * 50)
    
    symbols = get_indian_market_symbols()
    for sector, stocks in symbols.items():
        print(f"\n🏭 {sector}:")
        for stock in stocks[:5]:  # Show first 5
            print(f"   • {stock}")
        if len(stocks) > 5:
            print(f"   ... and {len(stocks) - 5} more")

def demo_portfolio_examples():
    """Demonstrate portfolio examples."""
    print("\n💼 Portfolio Examples:")
    print("-" * 30)
    
    # Conservative portfolio
    conservative = [
        {'symbol': 'HDFCBANK', 'weight': 0.30},
        {'symbol': 'RELIANCE', 'weight': 0.25},
        {'symbol': 'TCS', 'weight': 0.20},
        {'symbol': 'ITC', 'weight': 0.15},
        {'symbol': 'SBIN', 'weight': 0.10}
    ]
    
    # Tech-focused portfolio
    tech_focused = generate_sample_portfolio('IT', 4)
    
    # Diversified portfolio
    diversified = [
        {'symbol': 'RELIANCE', 'weight': 0.15},  # Energy
        {'symbol': 'TCS', 'weight': 0.15},       # IT
        {'symbol': 'HDFCBANK', 'weight': 0.15},  # Banking
        {'symbol': 'MARUTI', 'weight': 0.15},    # Auto
        {'symbol': 'SUNPHARMA', 'weight': 0.10}, # Pharma
        {'symbol': 'HINDUNILVR', 'weight': 0.10}, # FMCG
        {'symbol': 'TATASTEEL', 'weight': 0.10},  # Metals
        {'symbol': 'BHARTIARTL', 'weight': 0.10}  # Telecom
    ]
    
    portfolios = {
        "Conservative": conservative,
        "Tech-Focused": tech_focused,
        "Diversified": diversified
    }
    
    for name, portfolio in portfolios.items():
        print(f"\n📊 {name} Portfolio:")
        for holding in portfolio:
            percentage = holding['weight'] * 100
            print(f"   • {holding['symbol']}: {percentage:.1f}%")

def demo_analysis_workflow():
    """Demonstrate the analysis workflow."""
    print("\n🔍 Analysis Workflow Example:")
    print("-" * 35)
    
    workflow_steps = [
        "1. Initialize analyzer with API key",
        "2. Analyze individual stocks (RELIANCE, TCS, etc.)",
        "3. Get AI-powered sentiment analysis",
        "4. Retrieve price predictions",
        "5. Analyze portfolio risk metrics",
        "6. Get market overview and trends",
        "7. Perform sector analysis",
        "8. Save results for further analysis"
    ]
    
    for step in workflow_steps:
        print(f"   {step}")

def demo_expected_outputs():
    """Show what kind of outputs to expect."""
    print("\n📊 Expected Analysis Outputs:")
    print("-" * 32)
    
    sample_outputs = {
        "Stock Analysis": [
            "Sentiment score and trend",
            "Price predictions (7-day forecast)",
            "Risk metrics",
            "Current market data",
            "Technical indicators"
        ],
        "Portfolio Analysis": [
            "Portfolio risk score",
            "Diversification metrics", 
            "Individual stock analysis",
            "Optimization suggestions",
            "Performance projections"
        ],
        "Market Overview": [
            "Index trends (NIFTY50, SENSEX)",
            "Market sentiment",
            "Volatility analysis",
            "Economic indicators",
            "Market predictions"
        ],
        "Sector Analysis": [
            "Sector performance",
            "Top performing stocks",
            "Risk assessment",
            "Growth predictions",
            "Investment opportunities"
        ]
    }
    
    for category, outputs in sample_outputs.items():
        print(f"\n🎯 {category}:")
        for output in outputs:
            print(f"   • {output}")

def demo_currency_formatting():
    """Demonstrate currency formatting for Indian market."""
    print("\n💰 Currency Formatting Examples:")
    print("-" * 33)
    
    amounts = [
        1500,           # Small amount
        125000,         # 1.25 Lakh
        2500000,        # 25 Lakh
        15000000,       # 1.5 Crore
        250000000       # 25 Crore
    ]
    
    for amount in amounts:
        formatted = format_currency(amount)
        print(f"   {amount:,} → {formatted}")

def demo_code_examples():
    """Show code examples for common tasks."""
    print("\n💻 Code Examples:")
    print("-" * 18)
    
    examples = {
        "Single Stock Analysis": '''
from stock_analyzer import IndianStockAnalyzer

analyzer = IndianStockAnalyzer()
analysis = analyzer.analyze_single_stock('RELIANCE')
print_analysis_summary(analysis)
        ''',
        
        "Portfolio Analysis": '''
portfolio = [
    {'symbol': 'TCS', 'weight': 0.4},
    {'symbol': 'INFY', 'weight': 0.3},
    {'symbol': 'WIPRO', 'weight': 0.3}
]
result = analyzer.analyze_portfolio(portfolio)
save_analysis_to_file(result, 'portfolio.json')
        ''',
        
        "Market Overview": '''
market_data = analyzer.get_market_overview()
print("NIFTY50 Trend:", market_data['market_insights'])
        ''',
        
        "Sector Analysis": '''
it_analysis = analyzer.analyze_sector('IT')
print("IT Sector Outlook:", it_analysis)
        '''
    }
    
    for title, code in examples.items():
        print(f"\n🔧 {title}:")
        print(code)

def main():
    """Run the complete demo."""
    demo_basic_setup()
    demo_market_symbols()
    demo_portfolio_examples()
    demo_analysis_workflow()
    demo_expected_outputs()
    demo_currency_formatting()
    demo_code_examples()
    
    print("\n" + "=" * 50)
    print("🎉 Demo completed!")
    print("\nTo get started with real analysis:")
    print("1. Set up your Hydra AI API key")
    print("2. Run: python stock_analyzer.py")
    print("3. Explore the comprehensive documentation in README.md")

if __name__ == "__main__":
    main()