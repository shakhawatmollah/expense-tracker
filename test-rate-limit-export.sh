#!/bin/bash

echo "🧪 Testing Rate Limiting & Export Functionality"
echo "=============================================="
echo ""

BASE_URL="http://localhost:8000/api"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Rate Limiting on Login
echo "📝 Test 1: Login Rate Limiting (should block after 5 attempts)"
echo "Attempting 7 login requests..."
for i in {1..7}; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}')
  
  if [ "$STATUS" = "429" ]; then
    echo -e "${GREEN}✓${NC} Attempt $i: Blocked (429) - Rate limiting working!"
  elif [ "$STATUS" = "401" ]; then
    echo -e "${YELLOW}○${NC} Attempt $i: Unauthorized (401) - Normal response"
  else
    echo -e "${RED}✗${NC} Attempt $i: Unexpected status $STATUS"
  fi
  sleep 0.5
done

echo ""
echo "⏱️  Waiting 20 seconds for rate limit to reset..."
sleep 20

# Test 2: Successful Login
echo ""
echo "📝 Test 2: Successful Login"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}')

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
  echo -e "${GREEN}✓${NC} Login successful - Token obtained"
else
  echo -e "${RED}✗${NC} Login failed - No token received"
  echo "Response: $LOGIN_RESPONSE"
  exit 1
fi

# Test 3: Export Functionality
echo ""
echo "📝 Test 3: Export Expenses (CSV)"
EXPORT_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/export/expenses?format=csv" \
  -H "Authorization: Bearer $TOKEN")

if [ "$EXPORT_RESPONSE" = "200" ]; then
  echo -e "${GREEN}✓${NC} Export successful (200)"
else
  echo -e "${RED}✗${NC} Export failed (Status: $EXPORT_RESPONSE)"
fi

# Test 4: Export Rate Limiting
echo ""
echo "📝 Test 4: Export Rate Limiting (should block after 10 attempts)"
echo "Attempting 12 export requests..."
for i in {1..12}; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/export/expenses?format=csv" \
    -H "Authorization: Bearer $TOKEN")
  
  if [ "$STATUS" = "429" ]; then
    echo -e "${GREEN}✓${NC} Attempt $i: Blocked (429) - Export rate limiting working!"
  elif [ "$STATUS" = "200" ]; then
    echo -e "${YELLOW}○${NC} Attempt $i: Success (200)"
  else
    echo -e "${RED}✗${NC} Attempt $i: Unexpected status $STATUS"
  fi
  sleep 1
done

echo ""
echo "=============================================="
echo "🎉 Testing Complete!"
echo ""
echo "Summary:"
echo "  - Login rate limiting: Check output above"
echo "  - Export functionality: Check output above"
echo "  - Export rate limiting: Check output above"
echo ""
echo "Next steps:"
echo "  1. Check the exported CSV files in backend/storage/app/exports/"
echo "  2. Test the frontend export modal in the dashboard"
echo "  3. Review rate limit headers in the responses"
