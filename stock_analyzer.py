"""
Indian Stock Market Analyzer using Hydra AI.

This module provides a comprehensive tool for analyzing Indian stocks
using Hydra AI's advanced analytics and predictions.
"""

import pandas as pd
import yfinance as yf
from typing import List, Dict, Any, Optional
from hydra_ai import create_hydra_client, HydraAIClient
from config import Config

class IndianStockAnalyzer:
    """Main class for analyzing Indian stock market using Hydra AI."""
    
    def __init__(self, api_key: Optional[str] = None, skip_validation: bool = False):
        """
        Initialize the stock analyzer.
        
        Args:
            api_key (str, optional): Hydra AI API key
            skip_validation (bool): Skip validation for testing
        """
        self.hydra_client = create_hydra_client(api_key, skip_validation)
        
    def analyze_single_stock(self, symbol: str, market: str = 'NSE') -> Dict[str, Any]:
        """
        Comprehensive analysis of a single stock.
        
        Args:
            symbol (str): Stock symbol (e.g., 'RELIANCE', 'TCS')
            market (str): Market exchange
            
        Returns:
            dict: Complete analysis results
        """
        print(f"Analyzing {symbol} on {market}...")
        
        analysis_results = {
            'symbol': symbol,
            'market': market,
            'timestamp': pd.Timestamp.now().isoformat()
        }
        
        try:
            # Get sentiment analysis
            print("Getting sentiment analysis...")
            sentiment = self.hydra_client.analyze_stock_sentiment(symbol, market)
            analysis_results['sentiment'] = sentiment
            
            # Get price predictions
            print("Getting price predictions...")
            predictions = self.hydra_client.predict_stock_price(symbol, days=7, market=market)
            analysis_results['price_predictions'] = predictions
            
            # Get basic stock data from yfinance for context
            yahoo_symbol = f"{symbol}.NS" if market == 'NSE' else f"{symbol}.BO"
            try:
                stock_data = yf.Ticker(yahoo_symbol)
                info = stock_data.info
                analysis_results['basic_info'] = {
                    'current_price': info.get('currentPrice'),
                    'market_cap': info.get('marketCap'),
                    'sector': info.get('sector'),
                    'industry': info.get('industry')
                }
            except Exception as e:
                print(f"Warning: Could not fetch basic stock data: {e}")
                analysis_results['basic_info'] = None
            
            print(f"Analysis completed for {symbol}")
            return analysis_results
            
        except Exception as e:
            print(f"Error analyzing {symbol}: {e}")
            analysis_results['error'] = str(e)
            return analysis_results
    
    def analyze_portfolio(self, portfolio: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Analyze a portfolio of Indian stocks.
        
        Args:
            portfolio (list): Portfolio holdings
                            Example: [{'symbol': 'RELIANCE', 'weight': 0.3}, ...]
        
        Returns:
            dict: Portfolio analysis results
        """
        print("Analyzing portfolio...")
        
        try:
            # Get portfolio risk analysis from Hydra AI
            risk_analysis = self.hydra_client.analyze_portfolio_risk(portfolio)
            
            # Analyze individual stocks in portfolio
            individual_analyses = {}
            for holding in portfolio:
                symbol = holding['symbol']
                individual_analyses[symbol] = self.analyze_single_stock(symbol)
            
            return {
                'portfolio_risk': risk_analysis,
                'individual_stocks': individual_analyses,
                'portfolio_composition': portfolio,
                'timestamp': pd.Timestamp.now().isoformat()
            }
            
        except Exception as e:
            print(f"Error analyzing portfolio: {e}")
            return {'error': str(e)}
    
    def get_market_overview(self, indices: Optional[List[str]] = None) -> Dict[str, Any]:
        """
        Get comprehensive market overview.
        
        Args:
            indices (list, optional): Indices to analyze
            
        Returns:
            dict: Market overview results
        """
        print("Getting market overview...")
        
        try:
            market_insights = self.hydra_client.get_market_insights(indices)
            
            return {
                'market_insights': market_insights,
                'timestamp': pd.Timestamp.now().isoformat()
            }
            
        except Exception as e:
            print(f"Error getting market overview: {e}")
            return {'error': str(e)}
    
    def analyze_sector(self, sector: str) -> Dict[str, Any]:
        """
        Analyze a specific sector in Indian market.
        
        Args:
            sector (str): Sector name (e.g., 'IT', 'Banking', 'Pharma')
            
        Returns:
            dict: Sector analysis results
        """
        print(f"Analyzing {sector} sector...")
        
        try:
            sector_analysis = self.hydra_client.get_sector_analysis(sector)
            
            return {
                'sector': sector,
                'analysis': sector_analysis,
                'timestamp': pd.Timestamp.now().isoformat()
            }
            
        except Exception as e:
            print(f"Error analyzing {sector} sector: {e}")
            return {'error': str(e)}

def main():
    """Main function demonstrating Hydra AI functionality."""
    print("Indian Stock Market Analyzer with Hydra AI")
    print("=" * 50)
    
    try:
        # Create analyzer instance
        analyzer = IndianStockAnalyzer()
        
        # Example 1: Analyze a single stock
        print("\n1. Single Stock Analysis:")
        reliance_analysis = analyzer.analyze_single_stock('RELIANCE')
        print(f"Reliance analysis keys: {list(reliance_analysis.keys())}")
        
        # Example 2: Market overview
        print("\n2. Market Overview:")
        market_overview = analyzer.get_market_overview()
        print(f"Market overview keys: {list(market_overview.keys())}")
        
        # Example 3: Portfolio analysis
        print("\n3. Portfolio Analysis:")
        sample_portfolio = [
            {'symbol': 'RELIANCE', 'weight': 0.25},
            {'symbol': 'TCS', 'weight': 0.20},
            {'symbol': 'INFY', 'weight': 0.15},
            {'symbol': 'HDFCBANK', 'weight': 0.20},
            {'symbol': 'ICICIBANK', 'weight': 0.20}
        ]
        portfolio_analysis = analyzer.analyze_portfolio(sample_portfolio)
        print(f"Portfolio analysis keys: {list(portfolio_analysis.keys())}")
        
        # Example 4: Sector analysis
        print("\n4. Sector Analysis:")
        it_sector = analyzer.analyze_sector('IT')
        print(f"IT sector analysis keys: {list(it_sector.keys())}")
        
        print("\nAnalysis completed successfully!")
        
    except Exception as e:
        print(f"Error in main execution: {e}")
        print("\nPlease ensure you have set up your Hydra AI API key in the .env file.")
        print("Copy .env.example to .env and add your API key.")

if __name__ == "__main__":
    main()