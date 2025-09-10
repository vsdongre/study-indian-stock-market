"""
Hydra AI Integration Module for Indian Stock Market Analysis.

This module provides functions to interact with Hydra AI API for 
analyzing Indian stock market data, trends, and predictions.
"""

import requests
import pandas as pd
import json
from typing import Dict, List, Optional, Any
from config import Config

class HydraAIClient:
    """Client class for interacting with Hydra AI API."""
    
    def __init__(self, api_key: Optional[str] = None, skip_validation: bool = False):
        """
        Initialize Hydra AI client.
        
        Args:
            api_key (str, optional): Hydra AI API key. If not provided, 
                                   will use from config.
            skip_validation (bool): Skip API key validation for testing
        """
        self.api_key = api_key or Config.HYDRA_AI_API_KEY
        self.base_url = Config.HYDRA_AI_BASE_URL
        self.session = requests.Session()
        self.session.headers.update({
            'Authorization': f'Bearer {self.api_key}',
            'Content-Type': 'application/json',
            'User-Agent': 'Indian-Stock-Market-Study/1.0'
        })
        
        # Validate configuration
        Config.validate_config(skip_validation)
    
    def _make_request(self, endpoint: str, method: str = 'GET', 
                     data: Optional[Dict] = None) -> Dict:
        """
        Make HTTP request to Hydra AI API.
        
        Args:
            endpoint (str): API endpoint
            method (str): HTTP method
            data (dict, optional): Request payload
            
        Returns:
            dict: API response
            
        Raises:
            requests.RequestException: If API request fails
        """
        url = f"{self.base_url}/{endpoint.lstrip('/')}"
        
        try:
            if method.upper() == 'GET':
                response = self.session.get(url, params=data, 
                                          timeout=Config.REQUEST_TIMEOUT)
            else:
                response = self.session.post(url, json=data, 
                                           timeout=Config.REQUEST_TIMEOUT)
            
            response.raise_for_status()
            return response.json()
            
        except requests.RequestException as e:
            raise requests.RequestException(f"Hydra AI API request failed: {e}")
    
    def analyze_stock_sentiment(self, symbol: str, 
                              market: str = 'NSE') -> Dict[str, Any]:
        """
        Analyze sentiment for a specific Indian stock.
        
        Args:
            symbol (str): Stock symbol (e.g., 'RELIANCE', 'TCS')
            market (str): Market exchange (default: NSE)
            
        Returns:
            dict: Sentiment analysis results
        """
        endpoint = 'sentiment/stock'
        data = {
            'symbol': symbol,
            'market': market,
            'country': 'IN'
        }
        
        return self._make_request(endpoint, 'POST', data)
    
    def get_market_insights(self, indices: Optional[List[str]] = None) -> Dict[str, Any]:
        """
        Get market insights for Indian stock indices.
        
        Args:
            indices (list, optional): List of indices to analyze.
                                    Defaults to NIFTY50, SENSEX, BANKNIFTY
            
        Returns:
            dict: Market insights and analysis
        """
        if indices is None:
            indices = Config.DEFAULT_INDICES
            
        endpoint = 'market/insights'
        data = {
            'indices': indices,
            'market': Config.DEFAULT_MARKET,
            'country': 'IN'
        }
        
        return self._make_request(endpoint, 'POST', data)
    
    def predict_stock_price(self, symbol: str, days: int = 7, 
                           market: str = 'NSE') -> Dict[str, Any]:
        """
        Get stock price predictions using Hydra AI.
        
        Args:
            symbol (str): Stock symbol
            days (int): Number of days to predict (default: 7)
            market (str): Market exchange (default: NSE)
            
        Returns:
            dict: Price prediction results
        """
        endpoint = 'prediction/stock'
        data = {
            'symbol': symbol,
            'market': market,
            'prediction_days': days,
            'country': 'IN'
        }
        
        return self._make_request(endpoint, 'POST', data)
    
    def analyze_portfolio_risk(self, portfolio: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Analyze risk metrics for a portfolio of Indian stocks.
        
        Args:
            portfolio (list): List of portfolio holdings with symbol and weight
                            Example: [{'symbol': 'RELIANCE', 'weight': 0.3}, ...]
            
        Returns:
            dict: Risk analysis results
        """
        endpoint = 'portfolio/risk'
        data = {
            'portfolio': portfolio,
            'market': Config.DEFAULT_MARKET,
            'country': 'IN'
        }
        
        return self._make_request(endpoint, 'POST', data)
    
    def get_sector_analysis(self, sector: str) -> Dict[str, Any]:
        """
        Get sector-wise analysis for Indian market.
        
        Args:
            sector (str): Sector name (e.g., 'IT', 'Banking', 'Pharma')
            
        Returns:
            dict: Sector analysis results
        """
        endpoint = 'sector/analysis'
        data = {
            'sector': sector,
            'market': Config.DEFAULT_MARKET,
            'country': 'IN'
        }
        
        return self._make_request(endpoint, 'POST', data)

def create_hydra_client(api_key: Optional[str] = None, skip_validation: bool = False) -> HydraAIClient:
    """
    Factory function to create Hydra AI client.
    
    Args:
        api_key (str, optional): API key for Hydra AI
        skip_validation (bool): Skip validation for testing
        
    Returns:
        HydraAIClient: Configured client instance
    """
    return HydraAIClient(api_key, skip_validation)