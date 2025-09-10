#!/usr/bin/env python3
"""
Basic test script to verify the Hydra AI integration setup.
This script tests the module imports and basic functionality without requiring API calls.
"""

import sys
import os

def test_imports():
    """Test that all modules can be imported successfully."""
    print("Testing module imports...")
    
    try:
        import config
        print("✓ config.py imported successfully")
        
        import hydra_ai
        print("✓ hydra_ai.py imported successfully")
        
        import stock_analyzer
        print("✓ stock_analyzer.py imported successfully")
        
        import utils
        print("✓ utils.py imported successfully")
        
        return True
    except Exception as e:
        print(f"✗ Import failed: {e}")
        return False

def test_config():
    """Test configuration validation (without actual API key)."""
    print("\nTesting configuration...")
    
    try:
        from config import Config
        
        # Test default values
        print(f"✓ Default market: {Config.DEFAULT_MARKET}")
        print(f"✓ Default indices: {Config.DEFAULT_INDICES}")
        print(f"✓ Request timeout: {Config.REQUEST_TIMEOUT}")
        print(f"✓ Base URL: {Config.HYDRA_AI_BASE_URL}")
        
        return True
    except Exception as e:
        print(f"✗ Configuration test failed: {e}")
        return False

def test_utils():
    """Test utility functions."""
    print("\nTesting utility functions...")
    
    try:
        from utils import (
            format_currency, 
            get_indian_market_symbols, 
            validate_portfolio, 
            generate_sample_portfolio
        )
        
        # Test currency formatting
        formatted = format_currency(1250000)
        print(f"✓ Currency formatting: {formatted}")
        
        # Test market symbols
        symbols = get_indian_market_symbols()
        print(f"✓ Market symbols loaded: {len(symbols)} sectors")
        
        # Test portfolio generation
        sample_portfolio = generate_sample_portfolio('IT', 3)
        print(f"✓ Sample portfolio generated: {len(sample_portfolio)} holdings")
        
        # Test portfolio validation
        is_valid = validate_portfolio(sample_portfolio)
        print(f"✓ Portfolio validation: {is_valid}")
        
        return True
    except Exception as e:
        print(f"✗ Utility test failed: {e}")
        return False

def test_hydra_client_creation():
    """Test Hydra AI client creation (without API calls)."""
    print("\nTesting Hydra AI client creation...")
    
    try:
        # Set a dummy API key for testing
        os.environ['HYDRA_AI_API_KEY'] = 'test_key_for_basic_functionality'
        
        from hydra_ai import create_hydra_client
        
        client = create_hydra_client(skip_validation=True)
        print("✓ Hydra AI client created successfully")
        print(f"✓ Client base URL: {client.base_url}")
        
        return True
    except Exception as e:
        print(f"✗ Client creation failed: {e}")
        return False

def test_analyzer_creation():
    """Test stock analyzer creation (without API calls)."""
    print("\nTesting stock analyzer creation...")
    
    try:
        # Set a dummy API key for testing
        os.environ['HYDRA_AI_API_KEY'] = 'test_key_for_basic_functionality'
        
        from stock_analyzer import IndianStockAnalyzer
        
        analyzer = IndianStockAnalyzer(skip_validation=True)
        print("✓ Stock analyzer created successfully")
        
        return True
    except Exception as e:
        print(f"✗ Analyzer creation failed: {e}")
        return False

def main():
    """Run all basic tests."""
    print("Indian Stock Market Analyzer - Basic Functionality Test")
    print("=" * 60)
    
    tests = [
        test_imports,
        test_config,
        test_utils,
        test_hydra_client_creation,
        test_analyzer_creation
    ]
    
    passed = 0
    total = len(tests)
    
    for test in tests:
        try:
            if test():
                passed += 1
            else:
                print("✗ Test failed")
        except Exception as e:
            print(f"✗ Test error: {e}")
    
    print("\n" + "=" * 60)
    print(f"Test Results: {passed}/{total} tests passed")
    
    if passed == total:
        print("✅ All basic functionality tests passed!")
        print("\nNext steps:")
        print("1. Set up your actual Hydra AI API key in .env file")
        print("2. Copy .env.example to .env and add your API key")
        print("3. Run: python stock_analyzer.py")
        return True
    else:
        print("❌ Some tests failed. Please check the errors above.")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)