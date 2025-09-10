"""
Configuration module for Hydra AI integration and stock market analysis.
"""
import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

class Config:
    """Configuration class for managing API keys and settings."""
    
    # Hydra AI API Configuration
    HYDRA_AI_API_KEY = os.getenv('HYDRA_AI_API_KEY')
    HYDRA_AI_BASE_URL = os.getenv('HYDRA_AI_BASE_URL', 'https://api.hydra-ai.com/v1')
    
    # Stock Market Configuration
    DEFAULT_MARKET = 'NSE'  # National Stock Exchange of India
    DEFAULT_INDICES = ['NIFTY50', 'SENSEX', 'BANKNIFTY']
    
    # Request Configuration
    REQUEST_TIMEOUT = 30
    MAX_RETRIES = 3
    
    @classmethod
    def validate_config(cls, skip_validation=False):
        """
        Validate that required configuration is present.
        
        Args:
            skip_validation (bool): Skip validation for testing purposes
        """
        if skip_validation:
            return True
            
        if not cls.HYDRA_AI_API_KEY:
            raise ValueError(
                "HYDRA_AI_API_KEY is required. Please set it in your .env file or environment variables."
            )
        return True