#!/usr/bin/env python3
"""
ocr_runner.py
Entry point for the CedulaOCR package.
Receives image path(s) via command-line arguments, delegates processing
to CedulaProcessor, and outputs the result as JSON to stdout.

Usage:
    python ocr_runner.py <image_path> [second_image_path]
"""

import sys
import json
import os

# Ensure the package directory is in the path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from cedula_processor import CedulaProcessor


def main():
    try:
        if len(sys.argv) < 2:
            print(json.dumps({
                "status": 400,
                "error_code": "IMAGE_NOT_FOUND",
                "data": None
            }, ensure_ascii=False))
            sys.exit(1)

        image_path = sys.argv[1]
        second_image_path = sys.argv[2] if len(sys.argv) >= 3 else None

        processor = CedulaProcessor()
        result = processor.process_id(image_path, second_image_path)

        print(json.dumps(result, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({
            "status": 500,
            "error_code": "RUNNER_EXCEPTION",
            "data": {
                "message": str(e)[:200]
            }
        }, ensure_ascii=False))
        sys.exit(1)

if __name__ == "__main__":
    main()