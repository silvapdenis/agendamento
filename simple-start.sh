#!/bin/bash
echo "🚀 Starting Simple Laravel Server..."

# Just start PHP server without complex setup
exec php -S 0.0.0.0:${PORT:-8000} -t public