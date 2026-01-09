#!/bin/bash
# Path Verification Script - Kiểm tra tất cả paths

echo "================================"
echo "🔍 PATH VERIFICATION - v1.1.2"
echo "================================"
echo ""

ROOT="/workspaces/hotel-management-system"
cd "$ROOT"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERROR_COUNT=0
SUCCESS_COUNT=0

# Function to check path
check_path() {
    local file=$1
    local expected=$2
    
    if grep -q "require_once '$expected" "$file" 2>/dev/null; then
        echo -e "${GREEN}✅${NC} $file → $expected"
        ((SUCCESS_COUNT++))
    else
        actual=$(grep "require_once" "$file" 2>/dev/null | head -1 | sed "s/.*require_once '//" | sed "s/'.*//" | head -c 20)
        echo -e "${RED}❌${NC} $file"
        echo -e "   Expected: $expected"
        echo -e "   Got: $actual..."
        ((ERROR_COUNT++))
    fi
}

echo "📋 CHECKING ALL PHP FILES:"
echo "---"

# ROOT LEVEL
echo ""
echo "📁 Root Level (0 ups):"
check_path "index.php" "config/constants.php"

# API LEVEL
echo ""
echo "📁 API Level (1 up):"
check_path "api/check_room_availability.php" "../config/constants.php"

# ADMIN DASHBOARD
echo ""
echo "📁 Admin Dashboard (1 up):"
check_path "modules/admin/dashboard.php" "../../config/constants.php"

# AUTH MODULE
echo ""
echo "📁 Auth Module (1 up from modules/):"
check_path "modules/auth/login.php" "../../config/constants.php"
check_path "modules/auth/logout.php" "../../config/constants.php"
check_path "modules/auth/register.php" "../../config/constants.php"
check_path "modules/auth/profile.php" "../../config/constants.php"

# CUSTOMER MODULE
echo ""
echo "📁 Customer Module (1 up from modules/):"
check_path "modules/customer/dashboard.php" "../../config/constants.php"
check_path "modules/customer/search_rooms.php" "../../config/constants.php"
check_path "modules/customer/book_room.php" "../../config/constants.php"
check_path "modules/customer/booking_detail.php" "../../config/constants.php"
check_path "modules/customer/booking_history.php" "../../config/constants.php"
check_path "modules/customer/payment_confirmation.php" "../../config/constants.php"
check_path "modules/customer/invoices.php" "../../config/constants.php"

# ADMIN ROOMS
echo ""
echo "📁 Admin Rooms Module (2 ups):"
check_path "modules/admin/rooms/index.php" "../../../config/constants.php"
check_path "modules/admin/rooms/add.php" "../../../config/constants.php"
check_path "modules/admin/rooms/edit.php" "../../../config/constants.php"
check_path "modules/admin/rooms/delete.php" "../../../config/constants.php"

# ADMIN BOOKINGS
echo ""
echo "📁 Admin Bookings Module (2 ups):"
check_path "modules/admin/bookings/index.php" "../../../config/constants.php"
check_path "modules/admin/bookings/create.php" "../../../config/constants.php"
check_path "modules/admin/bookings/view.php" "../../../config/constants.php"
check_path "modules/admin/bookings/edit.php" "../../../config/constants.php"

# ADMIN SERVICES
echo ""
echo "📁 Admin Services Module (2 ups):"
check_path "modules/admin/services/index.php" "../../../config/constants.php"
check_path "modules/admin/services/add.php" "../../../config/constants.php"
check_path "modules/admin/services/edit.php" "../../../config/constants.php"

# ADMIN CUSTOMERS
echo ""
echo "📁 Admin Customers Module (2 ups):"
check_path "modules/admin/customers/index.php" "../../../config/constants.php"
check_path "modules/admin/customers/view.php" "../../../config/constants.php"

# ADMIN REPORTS
echo ""
echo "📁 Admin Reports Module (2 ups):"
check_path "modules/admin/reports/index.php" "../../../config/constants.php"

# SUMMARY
echo ""
echo "---"
echo "📊 SUMMARY:"
echo "================================"
echo -e "${GREEN}✅ Success:${NC} $SUCCESS_COUNT"
echo -e "${RED}❌ Errors:${NC} $ERROR_COUNT"
echo "================================"

if [ $ERROR_COUNT -eq 0 ]; then
    echo -e "${GREEN}🎉 All paths are correct!${NC}"
    exit 0
else
    echo -e "${RED}⚠️  Found $ERROR_COUNT errors!${NC}"
    exit 1
fi
