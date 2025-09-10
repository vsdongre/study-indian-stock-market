"""
Utility functions for Indian stock market analysis.
"""

import pandas as pd
import json
from typing import Dict, List, Any, Optional
from datetime import datetime, timedelta

def save_analysis_to_file(analysis: Dict[str, Any], filename: str) -> bool:
    """
    Save analysis results to a JSON file.
    
    Args:
        analysis (dict): Analysis results to save
        filename (str): Output filename
        
    Returns:
        bool: True if saved successfully, False otherwise
    """
    try:
        with open(filename, 'w') as f:
            json.dump(analysis, f, indent=2, default=str)
        print(f"Analysis saved to {filename}")
        return True
    except Exception as e:
        print(f"Error saving analysis: {e}")
        return False

def load_analysis_from_file(filename: str) -> Optional[Dict[str, Any]]:
    """
    Load analysis results from a JSON file.
    
    Args:
        filename (str): Input filename
        
    Returns:
        dict or None: Analysis results if successful, None otherwise
    """
    try:
        with open(filename, 'r') as f:
            return json.load(f)
    except Exception as e:
        print(f"Error loading analysis: {e}")
        return None

def format_currency(amount: float, currency: str = 'INR') -> str:
    """
    Format currency values for Indian market.
    
    Args:
        amount (float): Amount to format
        currency (str): Currency code (default: INR)
        
    Returns:
        str: Formatted currency string
    """
    if amount is None:
        return "N/A"
    
    if amount >= 10000000:  # 1 crore
        return f"₹{amount/10000000:.2f} Cr"
    elif amount >= 100000:  # 1 lakh
        return f"₹{amount/100000:.2f} L"
    else:
        return f"₹{amount:,.2f}"

def get_indian_market_symbols() -> Dict[str, List[str]]:
    """
    Get commonly traded Indian stock symbols by sector.
    
    Returns:
        dict: Dictionary of sectors and their stock symbols
    """
    return {
        'IT': ['TCS', 'INFY', 'WIPRO', 'HCLTECH', 'TECHM', 'LTI', 'MPHASIS'],
        'Banking': ['HDFCBANK', 'ICICIBANK', 'SBIN', 'AXISBANK', 'KOTAKBANK', 'INDUSINDBK'],
        'Energy': ['RELIANCE', 'ONGC', 'BPCL', 'IOC', 'HINDPETRO', 'GAIL'],
        'Auto': ['MARUTI', 'TATAMOTORS', 'M&M', 'BAJAJ-AUTO', 'EICHERMOT', 'HERONOTOCORP'],
        'Pharma': ['SUNPHARMA', 'DRREDDY', 'CIPLA', 'DIVISLAB', 'BIOCON', 'LUPIN'],
        'FMCG': ['HINDUNILVR', 'ITC', 'NESTLEIND', 'BRITANNIA', 'DABUR', 'GODREJCP'],
        'Metals': ['TATASTEEL', 'JSWSTEEL', 'HINDALCO', 'VEDL', 'COALINDIA', 'NMDC'],
        'Telecom': ['BHARTIARTL', 'IDEA', 'MTNL', 'RCOM']
    }

def validate_portfolio(portfolio: List[Dict[str, Any]]) -> bool:
    """
    Validate portfolio structure and weights.
    
    Args:
        portfolio (list): Portfolio to validate
        
    Returns:
        bool: True if valid, False otherwise
    """
    if not isinstance(portfolio, list):
        print("Portfolio must be a list")
        return False
    
    total_weight = 0
    for holding in portfolio:
        if not isinstance(holding, dict):
            print("Each holding must be a dictionary")
            return False
        
        if 'symbol' not in holding or 'weight' not in holding:
            print("Each holding must have 'symbol' and 'weight' keys")
            return False
        
        if not isinstance(holding['weight'], (int, float)):
            print("Weight must be a number")
            return False
        
        if holding['weight'] < 0:
            print("Weight cannot be negative")
            return False
        
        total_weight += holding['weight']
    
    if abs(total_weight - 1.0) > 0.01:  # Allow small floating point errors
        print(f"Portfolio weights must sum to 1.0 (current sum: {total_weight})")
        return False
    
    return True

def generate_sample_portfolio(sector: str = None, size: int = 5) -> List[Dict[str, Any]]:
    """
    Generate a sample portfolio for testing.
    
    Args:
        sector (str, optional): Focus on specific sector
        size (int): Number of holdings (default: 5)
        
    Returns:
        list: Sample portfolio
    """
    symbols = get_indian_market_symbols()
    
    if sector and sector in symbols:
        stock_list = symbols[sector][:size]
    else:
        # Mix from different sectors
        stock_list = []
        for sector_stocks in symbols.values():
            stock_list.extend(sector_stocks[:2])
            if len(stock_list) >= size:
                break
        stock_list = stock_list[:size]
    
    # Equal weights
    weight = 1.0 / len(stock_list)
    
    return [{'symbol': symbol, 'weight': weight} for symbol in stock_list]

def print_analysis_summary(analysis: Dict[str, Any]) -> None:
    """
    Print a formatted summary of analysis results.
    
    Args:
        analysis (dict): Analysis results to summarize
    """
    print("\n" + "="*60)
    print("ANALYSIS SUMMARY")
    print("="*60)
    
    if 'error' in analysis:
        print(f"❌ Error: {analysis['error']}")
        return
    
    if 'symbol' in analysis:
        print(f"📈 Stock: {analysis['symbol']}")
        
        if 'basic_info' in analysis and analysis['basic_info']:
            info = analysis['basic_info']
            if info.get('current_price'):
                print(f"💰 Current Price: ₹{info['current_price']}")
            if info.get('market_cap'):
                print(f"🏢 Market Cap: {format_currency(info['market_cap'])}")
            if info.get('sector'):
                print(f"🏭 Sector: {info['sector']}")
        
        if 'sentiment' in analysis:
            print("📊 Sentiment Analysis: Available")
        
        if 'price_predictions' in analysis:
            print("🔮 Price Predictions: Available")
    
    elif 'portfolio_risk' in analysis:
        print("📊 Portfolio Analysis")
        print(f"🏦 Holdings: {len(analysis.get('portfolio_composition', []))}")
        print("⚖️ Risk Analysis: Available")
    
    elif 'market_insights' in analysis:
        print("🌐 Market Overview")
        print("📈 Market Insights: Available")
    
    elif 'sector' in analysis:
        print(f"🏭 Sector Analysis: {analysis['sector']}")
    
    if 'timestamp' in analysis:
        print(f"⏰ Timestamp: {analysis['timestamp']}")
    
    print("="*60)